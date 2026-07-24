# Restaurant POS Integration Architecture

## 1. Purpose

This document defines how Restaurant POS capabilities integrate into the existing Laravel monolith **without replacing** any established retail/POS modules.

Restaurant features are **additive domain layers**. They attach to, and consume, the current Products, Inventory, Customers, Orders, Payments, and Reports foundations. Legacy retail POS, supplier procurement, and seller reporting continue to operate unchanged.

---

## 2. Integration Principles

| Principle | Rule |
| :--- | :--- |
| **Never replace** | Do not rewrite, rename, or retire Products, Inventory, Customers, Orders (`Sale`/`SaleItem`), Payments, or Reports. |
| **Reuse as source of truth** | Menu items, stock balances, patrons, order headers/lines, money movement, and financial aggregates remain in existing tables and controllers. |
| **Additive only** | New restaurant concerns live in new tables, models, services, routes, and UI surfaces. |
| **Optional activation** | Restaurant modules activate per seller when floors/tables/recipes/KDS are configured. Sellers without restaurant setup keep current retail POS behavior. |
| **Unified commercial spine** | All channels (retail POS, dine-in, takeaway, QR) still persist commercial outcomes as `sales` / `sale_items` with payment fields already on `Sale`. |
| **Extend, don’t fork** | Prefer nullable FKs and optional flags on existing entities over parallel order/payment/product systems. |

---

## 3. Module Ownership Map

```
+-----------------------------------------------------------------------------------+
|                         EXISTING MODULES (REUSE — DO NOT REPLACE)                 |
|  Products | Inventory | Customers | Orders | Payments | Reports                   |
+----------------------------------------+------------------------------------------+
                                         | consumed by / extended by
+----------------------------------------v------------------------------------------+
|                         NEW RESTAURANT MODULES (DESIGN ONLY HERE)                 |
|  Floors | Tables | Reservations | Kitchen | Kitchen Tickets                       |
|  Recipes | Ingredients | Modifiers | QR Ordering                                  |
+-----------------------------------------------------------------------------------+
```

### 3.1 Reused Modules (unchanged ownership)

| Existing Module | Canonical entities | Restaurant consumption |
| :--- | :--- | :--- |
| **Products** | `Product`, `ProductCategory`, `ProductUnit` | Menu catalog, modifier targets, recipe finished goods, ingredient SKUs |
| **Inventory** | `ProductStock`, product `stock_in` / `stock_out` | Recipe auto-deduction and restock via existing stock rules |
| **Customers** | `Customer` | Reservation guests, dine-in / QR order patrons, history |
| **Orders** | `Sale`, `SaleItem`, `Cart`, `CartItem`, POS flows | Single order spine for dine-in, takeaway, QR, and retail |
| **Payments** | `Sale` payment fields (`payable`, `paid`, `due`, `payment_type`) | Table billing, split/partial pay, receipts |
| **Reports** | Seller `ReportController` aggregations | Include restaurant channel revenue without a parallel reporting store |

### 3.2 New Modules (restaurant domain)

| New Module | Responsibility | Must not replace |
| :--- | :--- | :--- |
| **Floors** | Physical zoning / sections for table layout | Settings, Products |
| **Tables** | Dining surface state, capacity, floor placement, QR binding | Orders, Payments |
| **Reservations** | Future bookings against tables/customers | Customers, Tables ownership of status |
| **Kitchen** | Kitchen Display System (KDS) operational UI and station workflow | Orders as commercial record |
| **Kitchen Tickets** | Kitchen Order Tickets (KOT) lifecycle derived from sales | `Sale` / `SaleItem` as bill of record |
| **Recipes** | BOM linking a sellable product to ingredient lines | Products catalog |
| **Ingredients** | Restaurant view of raw-material products used in recipes | Inventory ledger |
| **Modifiers** | Add-ons / variants priced and attached to products and sale lines | Product base pricing model |
| **QR Ordering** | Guest digital-menu channel onto existing order/payment spine | Orders, Payments, Products |

---

## 4. Layered System View

```
+-----------------------------------------------------------------------------------+
|  CLIENT SURFACES                                                                  |
|  Seller POS / Floor Map | KDS Screens | Reservation Desk | Guest QR Menu          |
+----------------------------------------+------------------------------------------+
                                         | HTTP / (future) WebSocket
+----------------------------------------v------------------------------------------+
|  APPLICATION SERVICES (new restaurant services sit beside existing controllers)   |
|  FloorPlan | Reservation | RecipeDeduction | ModifierPricing | KotGenerator | KDS |
+----------------------------------------+------------------------------------------+
                                         | reads/writes
+----------------------------------------v------------------------------------------+
|  PERSISTENCE                                                                      |
|  EXISTING: products, product_stocks, customers, sales, sale_items, ...            |
|  NEW:      floors, reservations, recipes, recipe_ingredients, modifiers,          |
|            product_modifiers, kitchen_tickets, kitchen_ticket_items               |
|  EXTEND:   dining_tables (+ floor, layout, QR token); sales (+ order_type, etc.)  |
+-----------------------------------------------------------------------------------+
```

Existing controller domains (`PosController`, `SaleController`, `ProductController`, `StockController`, `CustomerController`, `ReportController`, `MenuController`) remain the commercial entry points. Restaurant services hook into their success paths; they do not become a second checkout or inventory engine.

---

## 5. New Module Designs

### 5.1 Floors

**Purpose:** Group dining surfaces into zones (Main Hall, Patio, VIP).

**Owns:** Floor identity, display name, sort priority, seller tenancy.

**Depends on:** Seller auth / tenant scope only.

**Does not own:** Table commercial state, orders, or payments.

**Integration:**
- One seller has many floors.
- Tables optionally belong to a floor (`floor_id` nullable).
- Floor plan UI composes Floors + Tables; POS table picker can filter by floor.
- Sellers with no floors treat all tables as a single implicit “Main Floor” for UI only.

---

### 5.2 Tables

**Purpose:** Operational dining surfaces used by POS, reservations, KDS context, and QR.

**Relation to today:** Evolves the existing `DiningTable` concept into the restaurant **Tables** module. This is an extension of the current dining-table feature, not a replacement of Orders or Payments.

**Owns:** Table code/name, capacity, status, floor placement, optional layout coordinates, QR token binding.

**Status model (operational):**
- Free
- Occupied
- Reserved
- (Optional later) Cleaning / Dirty

**Depends on:** Floors (optional FK), Seller tenancy.

**Consumed by:** Reservations, Orders (`Sale.dining_table_id`), Kitchen Tickets (context), QR Ordering.

**Integration rules:**
- Occupied when an open dine-in / QR sale is bound to the table.
- Reserved when a confirmed reservation window is active (Reservations module drives this transition).
- Free when bill is closed (Payments/Orders complete) and no active reservation holds the table.
- Table status is **operational cache**; the financial truth remains `Sale` payment state.

---

### 5.3 Reservations

**Purpose:** Book tables for future guests without creating a sale until seating/order time.

**Owns:** Reservation records (time window, guest count, notes, status lifecycle).

**Depends on:**
- **Tables** — which surface is booked
- **Customers** — preferred link to existing `Customer`; guest name/phone allowed as denormalized fallback for walk-up bookings

**Status lifecycle:** `pending` → `confirmed` → `seated` → (`cancelled` | `no_show` | completed via seating)

**Integration:**
- Confirming a reservation may set table status to Reserved for the window.
- Seating creates or attaches an Orders-channel dine-in `Sale` (existing Orders module) and moves table to Occupied.
- Cancellation releases table if no open sale exists.
- Never stores revenue; payment happens only through existing Payments on `Sale`.

---

### 5.4 Ingredients

**Purpose:** Restaurant domain for raw materials used in cooking.

**Critical reuse rule:** Ingredients are **not** a second product catalog and **not** a second stock ledger. An ingredient is a `Product` (or a product flagged/categorized for kitchen use) whose movements continue through **Inventory**.

**Owns:** Ingredient classification rules, recipe-facing metadata (default unit expectations, kitchen name aliases if needed).

**Depends on:** Products + Inventory.

**Integration:**
- Procurement of ingredients continues via existing supplier/stock flows into Inventory.
- Recipe lines reference ingredient products by `product_id`.
- Low-stock signals for kitchen use Inventory balances, not a parallel quantity field.

---

### 5.5 Recipes

**Purpose:** Bill of Materials (BOM) for sellable menu products.

**Owns:** Recipe header per finished `Product`, preparation guidance/time, ingredient lines with quantities and units.

**Depends on:**
- **Products** — finished dish = sellable product
- **Ingredients** — lines point at ingredient products
- **Inventory** — deduction engine consumes stock rules

**Integration:**
- At order commit (POS checkout or QR order placement), if a `SaleItem` product has a recipe, deduct ingredient quantities × line qty via Inventory.
- If no recipe exists, preserve today’s behavior: direct product stock movement (retail-compatible).
- Recipes never create alternate sale lines; they only explain how Inventory reacts to Orders.

---

### 5.6 Modifiers

**Purpose:** Configurable add-ons, exclusions, and variants (size, toppings, cooking preference) with optional price deltas.

**Owns:** Modifier definitions/groups, product–modifier attachments, required/optional rules.

**Depends on:** Products (attachment targets).

**Integration with Orders / Payments:**
- Selected modifiers are captured on the order line (extension of `SaleItem` / cart item payload).
- Price deltas fold into line totals that existing Orders calculation and Payments already settle.
- Kitchen Tickets display modifier text for prep; Modifiers do not become their own payment documents.

**Non-goals:** Do not replace product base price; do not invent a separate checkout for add-ons.

---

### 5.7 Kitchen Tickets

**Purpose:** Kitchen Order Tickets (KOT) — prep work units derived from commercial order lines.

**Owns:** Ticket header (ticket number, status, timestamps, station hints), ticket items (qty, modifiers snapshot, special instructions, item-level prep status).

**Depends on:**
- **Orders** — created from `Sale` / `SaleItem` (required)
- **Tables** — optional context for dine-in
- **Modifiers** — snapshot of choices at ticket creation time
- **Products** — names/identity for prep display

**Lifecycle:** `pending` → `preparing` → `ready` → `served`

**Integration rules:**
- Ticket generation is a side effect of order placement / send-to-kitchen, not a replacement for `Sale`.
- Void/cancel of commercial lines must reconcile open ticket items.
- Billing and tips remain on Orders + Payments; ticket “served” is operational only.
- Historical KOTs may be retained for kitchen analytics; financial reports still read `sales`.

---

### 5.8 Kitchen

**Purpose:** Kitchen Display System (KDS) — the operational console for stations to work Kitchen Tickets.

**Owns:** Presentation and station workflow (queues, timers, bump/recall actions, station filters). Does **not** own commercial totals.

**Depends on:** Kitchen Tickets as its data source; Products/Modifiers for display; Tables for location context.

**Integration:**
- Reads/updates ticket and ticket-item statuses.
- May notify POS / floor when items are ready (realtime channel optional).
- Staff identity can reuse existing seller employees/users; no parallel auth system.

**Boundary:** Kitchen never marks invoices paid, never adjusts Inventory directly (Inventory changes happen from Orders/Recipes at commit time), and never mutates Reports stores.

---

### 5.9 QR Ordering

**Purpose:** Guest self-order channel bound to a table’s QR identity.

**Relation to today:** Formalizes and extends the existing digital menu (`MenuController` / `/menu/{table}`) into a first-class restaurant module.

**Owns:** QR token validation, guest menu session UX, guest cart assembly rules for table-scoped ordering.

**Reuses:**
- **Products** — active sellable menu
- **Modifiers** — guest-configurable options
- **Tables** — token → table resolution, occupy on order
- **Orders** — creates `Sale` + `SaleItem`
- **Payments** — deferred or immediate per business rules already modeled on `Sale`
- **Customers** — optional guest identity capture
- **Kitchen Tickets / Kitchen** — post-order ticket generation and KDS visibility
- **Recipes / Inventory** — same deduction path as POS orders

**Integration rules:**
- QR is an **ingress channel**, not a second order database.
- Successful guest submit follows the same restaurant kitchen pipeline as dine-in POS send-to-kitchen.
- Table token rotation/security belongs to Tables + QR Ordering; catalog truth stays in Products.

---

## 6. Cross-Module Dependency Graph

```
Floors
  └── Tables
        ├── Reservations ─────────────── Customers (reuse)
        ├── QR Ordering ─┐
        └── Orders (reuse) ◄──────────── Products (reuse)
              │                ▲            │
              │                │            ├── Modifiers
              │                │            ├── Recipes
              │                │            │     └── Ingredients ── Inventory (reuse)
              │                │            │
              ├── Payments (reuse)
              ├── Reports (reuse)   [read-only aggregates]
              └── Kitchen Tickets ── Kitchen (KDS)
```

**Read direction summary:**
- New modules may depend on reused modules.
- Reused modules must not be rewritten to depend on restaurant modules.
- Soft coupling from reused flows to restaurant behavior happens via optional hooks/events (e.g., “after sale created → generate KOT if restaurant mode”).

---

## 7. Channel → Spine → Restaurant Pipeline

```
Retail POS ──────┐
Dine-in POS ─────┼──► Orders (Sale / SaleItem) ──► Payments fields on Sale
QR Ordering ─────┘              │
                                ├──► Reports (unchanged aggregation source)
                                ├──► Inventory (direct SKU and/or Recipe BOM)
                                └──► Kitchen Tickets ──► Kitchen (KDS)

Reservations ──► Tables (status) ──► (on seat) Orders
Floors ──► Tables (layout only)
Modifiers ──► Cart/SaleItem pricing + KOT display
```

All revenue channels share one commercial spine. Restaurant modules decorate operations (space, prep, BOM, guest ingress); they do not fork money or stock ledgers.

---

## 8. Extension Points on Existing Entities (non-replacing)

These are **additive** touches only—enough for integration, not a redesign of reused modules.

| Existing entity | Allowed extension | Forbidden |
| :--- | :--- | :--- |
| `Product` | Optional links to Recipe, Modifiers; ingredient usability | New parallel products table for menu |
| `Product` stock / `ProductStock` | Deduction called by Recipe engine | Separate kitchen stock balances |
| `Customer` | Linked from Reservations / QR guest save | Separate guest CRM for restaurants |
| `Sale` | Optional `order_type`, table FK (already present), channel metadata | Restaurant-only orders table as bill of record |
| `SaleItem` | Modifier snapshot / notes for KOT | Separate line-item money store |
| `DiningTable` | Floor FK, QR token, layout fields, richer status | Replacing `Sale` for open checks |
| Reports queries | Filter/group by `order_type` when present | Duplicating sales fact tables |

---

## 9. Compatibility Matrix

| Scenario | Behavior |
| :--- | :--- |
| Retail-only seller | No floors/recipes/KDS required; POS + Inventory + Reports work as today |
| Product without recipe | Inventory deducts the product itself (legacy path) |
| Product with recipe | Inventory deducts ingredient products per BOM |
| Sale without table | Valid (retail / takeaway); KOT may omit table context |
| Table without floor | Valid; UI shows under default section |
| Reservation without customer_id | Valid with name/phone; may later attach `Customer` |
| QR order | Writes `Sale` like other channels; generates Kitchen Tickets when kitchen module enabled |
| Payment / partial pay | Existing `payable` / `paid` / `due` semantics |
| Reporting | All channels included because they share `sales` |

---

## 10. Bounded Context Summary

| Context | Write authority | Downstream listeners |
| :--- | :--- | :--- |
| Catalog & stock | Products, Inventory | Recipes, Ingredients, Modifiers, QR menu |
| People | Customers, Users/Staff | Reservations, waiter assignment |
| Commerce | Orders, Payments | Reports, Kitchen Tickets, table occupy/free |
| Space | Floors, Tables, Reservations | POS floor map, QR binding |
| Prep | Kitchen Tickets, Kitchen | Ready notifications to floor/POS |
| Guest ingress | QR Ordering | Orders → (Recipes/Inventory, Kitchen Tickets) |

---

## 11. Explicit Non-Goals

- Do not replace or fork Products, Inventory, Customers, Orders, Payments, or Reports.
- Do not introduce a second invoice/payment ledger for dine-in or QR.
- Do not store ingredient quantities outside Inventory.
- Do not make Kitchen or Kitchen Tickets the system of record for revenue.
- Do not require restaurant modules for non-restaurant sellers.

---

## 12. Document Boundaries

This file is **architecture only**: module ownership, reuse contracts, dependency direction, and integration pipelines.

Out of scope here: application code, migrations, API contracts, UI mock implementation, and realtime/offline transport details (covered by sibling design docs when needed).
