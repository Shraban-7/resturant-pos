# Offline Architecture

## 1. Purpose

Define how Restaurant POS remains operable during network loss using:

- **Service Worker** — request interception, asset/API caching, sync trigger
- **IndexedDB** — durable offline reads/writes and queue storage
- **Background Sync** — deferred upload when connectivity returns
- **Retry Queue** — ordered, durable failed-request replay
- **Conflict Resolution** — safe merge when offline mutations disagree with server truth

**Hard rule:** Never use `LocalStorage` for catalog cache, sessions of record, pending orders, payments, queue state, or sync metadata. All durable client state lives in IndexedDB (and Cache Storage for immutable/static assets via the Service Worker).

This design is additive to the existing commercial spine (`Sale` / `SaleItem`, Products, Inventory, Payments, Reports). Offline is a **client resilience layer**, not a second order database on the server.

---

## 2. Scope & Non-Goals

### In scope
- Seller POS (dine-in / retail / takeaway checkout)
- Floor/table status reads while offline (last known snapshot)
- Product/menu/modifier catalog reads while offline
- Queued order create / payment capture / table occupy intents
- Sync + retry + conflict handling on reconnect

### Out of scope / deferred
- Full offline Kitchen Display System collaboration across multiple kitchen devices (KDS remains primarily online; offline POS may queue KOTs for later server generation)
- Guest QR Ordering as a fully offline multi-guest shared cart (QR guests typically require connectivity; see §11)
- Supplier procurement offline checkout
- Replacing server inventory or reports with client-only ledgers

---

## 3. High-Level Architecture

```
+-----------------------------------------------------------------------------------+
|  POS UI (Blade / Alpine / Axios)                                                  |
|  - Detects online/offline                                                         |
|  - Writes mutations to IndexedDB first when offline or request fails              |
|  - Reads catalog/tables from IndexedDB when network unavailable                   |
+----------------------------------------+------------------------------------------+
                                         |
+----------------------------------------v------------------------------------------+
|  SERVICE WORKER                                                                   |
|  - Precache app shell                                                             |
|  - Runtime cache strategies per request class                                     |
|  - Register Background Sync tags                                                  |
|  - Drain Retry Queue on `sync` / `online`                                         |
+----------------------------------------+------------------------------------------+
                                         |
          +------------------------------+------------------------------+
          | online success               | offline / failed write         |
          v                              v                                |
+-------------------+          +------------------------------------------+
| Laravel API       |          | IndexedDB (pos_offline_db)               |
| (source of truth) |◄─sync─── | cache_* | offline_orders | retry_queue   |
+-------------------+          | conflict_log | sync_meta                 |
                               +------------------------------------------+
```

**Authority model:**
- **Server MySQL** = system of record for money, stock, and reports after sync.
- **IndexedDB** = operational continuity store and outbox until acknowledged.
- **Cache Storage** = HTTP response/asset cache only (not business mutations).

---

## 4. IndexedDB Design (No LocalStorage)

Database name: `pos_offline_db`  
Versioned schema; migrations bump DB version and upgrade stores.

### 4.1 Object stores

| Store | Key | Purpose |
| :--- | :--- | :--- |
| `cache_products` | `product_id` | Sellable catalog snapshot (price, name, category, active, modifiers summary) |
| `cache_categories` | `category_id` | Category tree for POS browsing |
| `cache_modifiers` | `modifier_id` | Modifier definitions + product links snapshot |
| `cache_tables` | `table_id` | Floor/table layout + last known status |
| `cache_floors` | `floor_id` | Floor zones for map filtering |
| `cache_customers` | `customer_id` | Recent/frequent customers subset (bounded) |
| `cache_settings` | `seller_id` | Tax, currency, receipt headers (business settings snapshot) |
| `offline_orders` | `client_order_id` | Durable offline order documents awaiting sync |
| `retry_queue` | `queue_id` (UUID) | Generic mutation outbox (orders, payments, table intents) |
| `conflict_log` | `conflict_id` | Human-reviewable sync conflicts |
| `sync_meta` | `key` | Cursors, last_sync_at, catalog_etag, device_id |

### 4.2 Why not LocalStorage
- Synchronous main-thread I/O blocks POS UI under load
- ~5MB practical limit insufficient for catalog + order queue
- No indexed queries, transactions, or structured large blobs
- Easy accidental persistence of secrets/PII without store isolation
- Not available reliably to Service Worker the same way IndexedDB is

### 4.3 In-memory only (allowed)
Ephemeral UI state (open modal, selected tab) may use JS memory. It must not be the durability path for orders or sync.

---

## 5. Cache Strategy

Service Worker applies **class-based** strategies. Strategies refer to Cache Storage for HTTP responses and IndexedDB for domain snapshots.

### 5.1 Request classes

| Class | Examples | Strategy | Notes |
| :--- | :--- | :--- | :--- |
| **App shell** | POS layout CSS/JS, icons, offline fallback page | Cache-first (precache on install) | Versioned build hashes; activate clears old caches |
| **Static assets** | Vite-built chunks, images already fingerprinted | Cache-first | Immutable URLs preferred |
| **Catalog read APIs** | products, categories, modifiers, floors/tables list | Stale-while-revalidate + IndexedDB mirror | SW serves cache quickly; background refresh updates Cache Storage **and** writes IndexedDB snapshot |
| **Settings read** | business settings, tax | Network-first with IndexedDB fallback | Prefer fresh tax/currency; fall back to last snapshot |
| **Mutating APIs** | checkout, mark paid, table status, send-to-kitchen | Network-only + outbox on failure | Never cache POST/PUT/PATCH/DELETE success as substitute for server ack |
| **Reports / analytics** | daily sales reports | Network-only | Do not invent offline report truth |
| **Auth** | login, CSRF, session | Network-only | Do not store passwords; session cookies remain browser-managed |

### 5.2 Catalog snapshot pipeline

1. On login / POS open (online): fetch catalog + tables + settings.
2. Write normalized records into IndexedDB cache_* stores inside one IDB transaction.
3. Record `catalog_etag` / `last_catalog_sync_at` in `sync_meta`.
4. While offline: UI reads exclusively from IndexedDB cache_* stores.
5. On reconnect: differential or full refresh; replace snapshots transactionally.

### 5.3 Stock display while offline

- Cached `availableStock` is **indicative only**.
- Offline sale may proceed with a soft warning when local stock would go negative.
- Final stock authority is server-side during sync (see Conflict Handling).
- Do not decrement server stock from the client; only adjust a local provisional field on the cached product for UX, marked `provisional: true`.

### 5.4 Cache invalidation

- Build deploy → new Service Worker → `skipWaiting`/`clients.claim` policy as product decision; always delete obsolete Cache Storage buckets.
- Domain data invalidation driven by `sync_meta` timestamps/etags, not by LocalStorage flags.
- Seller switch / logout → clear seller-scoped IndexedDB stores (keep schema).

---

## 6. Offline Order Storage

### 6.1 When an offline order is created

Create/append an `offline_orders` record when any of:

- Navigator reports offline before checkout
- Checkout HTTP request fails with network error / 5xx after local validation
- Explicit “Save offline” path (optional)

Online happy-path checkout still goes to the server first; IndexedDB is not required for successful online sales.

### 6.2 Order document shape (logical)

```
offline_orders
├── client_order_id          (UUID, client-generated, globally unique per device)
├── device_id                (stable install id in sync_meta)
├── seller_id
├── channel                  (retail | dine_in | takeaway)
├── dining_table_id? 
├── customer_id?
├── seller_employee_id?
├── items[]
│     ├── product_id
│     ├── quantity
│     ├── unit_price_snapshot
│     ├── modifiers[]        (id + name + price snapshot)
│     └── notes?
├── amounts
│     ├── subtotal, tax, discount, payable, paid, due
│     └── payment_type
├── pricing_context          (tax_rate, currency snapshots)
├── created_at_client        (ISO-8601)
├── sync_status              (pending | syncing | synced | conflict | dead_letter)
├── attempt_count
├── last_error?
├── server_sale_id?          (filled after ack)
└── schema_version
```

### 6.3 Durability rules

- Write order + enqueue retry item in **one IndexedDB transaction**.
- Only after durable commit may UI show “Order saved (pending sync)” and print a local provisional receipt marked **UNSYNCED**.
- `client_order_id` is printed/stored so staff can reconcile later.
- Never delete an offline order until server ack returns matching `client_order_id` (or manager resolves dead-letter).

### 6.4 Related intents in Retry Queue

Not every offline action is a full order. The `retry_queue` also stores:

| Intent type | Payload summary | Server effect on sync |
| :--- | :--- | :--- |
| `order.create` | Full offline order document | Create `Sale` + items; trigger kitchen tickets if applicable |
| `order.payment` | `client_order_id` or `server_sale_id` + payment | Apply payment fields on existing sale |
| `table.status` | `table_id`, target status, reason | Update table operational status |
| `order.hold` / `order.add_items` | Partial updates | Merge into open sale if still valid |

Orders are the primary offline document; other intents reference `client_order_id` until server IDs exist.

---

## 7. Retry Queue

### 7.1 Queue record

```
retry_queue
├── queue_id                 (UUID)
├── type                     (order.create | order.payment | table.status | ...)
├── payload_ref              (client_order_id or inline payload)
├── priority                 (lower = sooner; payments after their order.create)
├── created_at
├── next_attempt_at
├── attempt_count
├── max_attempts
├── last_error_code?
├── state                    (queued | in_flight | ack | failed | conflict)
└── depends_on_queue_id?     (optional causal dependency)
```

### 7.2 Ordering guarantees

1. Process FIFO within the same `device_id` + `seller_id`, respecting `depends_on_queue_id`.
2. `order.create` must ack before dependent `order.payment` for the same `client_order_id`.
3. Single-flight drain: one active sync worker per tab/SW claim to avoid duplicate posts (combine with server idempotency).

### 7.3 Retry strategy

| Attempt | Backoff | Jitter |
| :--- | :--- | :--- |
| 1 | Immediate on sync event | none |
| 2 | 5 seconds | ± 20% |
| 3 | 30 seconds | ± 20% |
| 4 | 2 minutes | ±20% |
| 5 | 10 minutes | ±20% |
| 6+ | 30 minutes capped | ±20% |

**Rules:**
- Network / 5xx / 429 → retry with backoff; honor `Retry-After` when present.
- 401 / 403 → pause queue, require re-auth; do not burn max attempts blindly.
- 422 business validation → move to conflict or dead-letter (no infinite retry).
- Idempotent replay always sends same `client_order_id` / `Idempotency-Key`.
- `max_attempts` default: 25 (or ~24h window). Exceeding → `dead_letter` + `conflict_log` entry for manager.

### 7.4 Background Sync integration

1. After enqueuing, Service Worker registers sync tag e.g. `pos-retry-drain`.
2. On `sync` event (or `online` fallback where Background Sync unsupported): drain queue.
3. Periodic safety: on POS focus while online, run drain if `retry_queue` non-empty (polyfill path).
4. Browser without Background Sync: use `online` event + visibility polling; still persist queue in IndexedDB so reloads do not lose work.

---

## 8. Sync Algorithm

### 8.1 Goals

- At-least-once delivery of offline mutations
- Exactly-once **business effect** via server idempotency on `client_order_id`
- Preserve causal order for dependent intents
- Surface conflicts without blocking unrelated queue items when safe

### 8.2 End-to-end flow

```
[Reconnect / sync event]
        │
        v
1. Load sync_meta (device_id, seller_id, cursors)
        │
        v
2. Select next retry_queue row where state=queued AND next_attempt_at <= now
   (respect depends_on)
        │
        v
3. Mark in_flight (IDB) → POST to server sync endpoint with Idempotency-Key
        │
        ├── 2xx + ack
        │     → bind server_sale_id
        │     → mark offline_orders.synced
        │     → mark queue ack / delete
        │     → continue drain
        │
        ├── 409 / conflict payload
        │     → write conflict_log
        │     → mark order/queue conflict
        │     → continue with next non-dependent item
        │
        └── retryable error
              → attempt_count++
              → schedule next_attempt_at
              → exit or continue other independent items
```

### 8.3 Server reconciliation responsibilities

For `order.create` sync:

1. Reject unauthenticated / wrong seller.
2. Look up existing sale by `client_order_id`; if found, return prior ack (idempotent success).
3. Validate products/modifiers still exist; re-price policy: **trust client snapshots for historical bill** or **reprice and flag delta** (product choice — see conflicts).
4. Apply Inventory (and Recipes BOM when present) in a DB transaction.
5. Persist `Sale` / `SaleItem` with `client_order_id`, `device_id`, `created_at` (client) + `synced_at` (server).
6. Generate Kitchen Tickets only after successful commit.
7. Return ack: `server_sale_id`, ticket ids, stock warnings.

### 8.4 Catalog sync (pull)

Separate from mutation drain:

1. Pull catalog/tables/settings using etag/cursor.
2. Replace IndexedDB snapshots transactionally.
3. Do not block order drain on catalog pull unless auth failure.

### 8.5 Multi-tab safety

- Prefer Service Worker as sole drain executor.
- Use `sync_meta.drain_lock` with timestamp + holder id; steal lock if stale (>60s).
- UI tabs subscribe to IDB changes (or BroadcastChannel) to refresh pending badges — channel is ephemeral signaling only, not storage of record.

---

## 9. Conflict Handling

### 9.1 Conflict classes

| Code | Cause | Default resolution |
| :--- | :--- | :--- |
| `duplicate_client_order_id` | Replay of already-acked order | Idempotent success; treat as synced |
| `insufficient_stock` | Server stock cannot satisfy items | Accept order into `conflict` review OR accept with negative stock override (seller policy); never silently drop |
| `product_inactive` | Product disabled since offline snapshot | Keep line in conflict_log; manager substitutes/removes |
| `price_changed` | Server price ≠ snapshot | Prefer **client snapshot** for already-collected cash; log price delta for audit |
| `table_state_conflict` | Table free/occupied/reserved disagrees | Server wins table status; order still created if payment valid; flag floor map refresh |
| `open_sale_exists` | Table already has open server sale | Attach as add-items if policy allows; else conflict for staff merge |
| `payment_exceeds_due` | Offline payment math invalid vs server | Reject payment intent; keep order; staff adjusts |
| `auth_tenant_mismatch` | Wrong seller/device | Dead-letter; do not apply |

### 9.2 Resolution principles

1. **Money already taken at the till (cash) must not vanish.** Prefer accepting the sale at snapshot prices and logging inventory/price exceptions for manager follow-up.
2. **Inventory is server-authoritative.** Offline provisional stock never overrides server ledgers.
3. **Tables are server-authoritative after sync.** Offline occupy is an intent.
4. **Do not auto-delete conflicting orders.** Move to `conflict` + `conflict_log` until resolved.
5. **Independent queue items continue** while one order is in conflict, unless dependency requires it.

### 9.3 Manager resolution actions (design)

From conflict UI (online):

- **Force accept** — commit sale, allow negative stock, write audit
- **Adjust & resubmit** — edit qty/product, new attempt with same `client_order_id` only if server never committed; otherwise new compensating flow
- **Discard** — rare; requires reason; only if no goods/cash exchanged
- **Merge into open table sale** — when table already occupied on server

All resolutions write an audit trail on the server; client clears local conflict only on ack.

### 9.4 Conflict log record

```
conflict_log
├── conflict_id
├── client_order_id?
├── queue_id?
├── code
├── server_message
├── client_payload_snapshot
├── server_context_snapshot
├── created_at
└── resolution_status (open | resolved | discarded)
```

Stored in IndexedDB for offline visibility of failures; mirrored/cleared when server acknowledges resolution.

---

## 10. Integration With Restaurant Domains

| Domain | Offline behavior |
| :--- | :--- |
| **Products / Modifiers** | Read from IndexedDB snapshots; selections stored on offline order lines |
| **Inventory / Recipes** | No authoritative offline deduction; applied on server at sync |
| **Customers** | Use cached customer_id or guest fields; create-customer intents optional in retry queue |
| **Orders / Payments** | Offline documents map to existing `Sale` / payment fields on sync |
| **Tables / Floors** | Last-known map offline; status changes queued |
| **Reservations** | Prefer online; if queued, conflict if slot taken |
| **Kitchen / Kitchen Tickets** | Created server-side after successful order sync; KDS updates after reconnect |
| **Reports** | Include sales only after server commit; unsynced orders excluded from financial reports |
| **QR Ordering** | See §11 |

---

## 11. Channel Notes

### POS (primary offline target)
Full offline order + payment + table intent support as specified.

### Kitchen (KDS)
Online-first. If kitchen goes offline, tickets already on screen may remain in memory/IDB **read cache** for display, but bump/status updates enqueue in `retry_queue` with same retry/conflict rules. Do not allow kitchen to invent tickets without a synced/known sale reference.

### QR Ordering
Guests on mobile data may still reach the server while restaurant LAN is down (or vice versa). Design choice:

- **Default:** QR requires server reachability for place-order.
- **Optional later:** table-scoped offline is not shared-safe without server; avoid multi-guest offline carts.

---

## 12. Security & Privacy

- IndexedDB holds PII (customer phone/name) and payment method types — restrict to seller device profiles; clear on logout.
- Do not store raw card PANs offline.
- Sync endpoints require same seller auth as POS; `device_id` registered per seller.
- Service Worker scope limited to app origin; no caching of admin-only reports into shared caches.
- Provisional receipts clearly marked unsynced to prevent double-charging confusion after sync.

---

## 13. Failure Modes & UX Signals

| State | UI signal |
| :--- | :--- |
| Offline | Banner: Offline mode — orders will sync later |
| Pending sync count | Badge from `offline_orders` where status pending/syncing |
| Syncing | Non-blocking progress on drain |
| Conflict | Alert + link to conflict resolution list |
| Dead letter | Persistent error requiring manager |

POS must remain usable for new offline orders even if older conflicts exist.

---

## 14. Acceptance Criteria (Design)

1. No LocalStorage usage for offline commerce or sync metadata.
2. Service Worker implements documented cache classes.
3. Offline checkout persists to IndexedDB before acknowledging staff.
4. Background Sync (or documented polyfill) drains Retry Queue on reconnect.
5. Server idempotency on `client_order_id` prevents duplicate sales.
6. Conflicts are logged and resolvable without silent data loss.
7. Synced sales appear in existing Orders, Payments, and Reports — no parallel financial store.

---

## 15. Document Boundary

Architecture only: cache strategy, sync algorithm, retry strategy, offline order storage, and conflict handling.

No application code, Service Worker scripts, migrations, or UI implementation in this document.
