# Restaurant Database Plan

## 1. Purpose

Logical database plan for Restaurant POS integration, derived from:

- `docs/restaurant_architecture.md`
- `docs/offline_architecture.md`
- `docs/restaurant_database_design.md`
- Current schema / migrations (as-is)

**Rules:**
- Do **not** replace Products, Inventory, Customers, Orders, Payments, or Reports stores.
- Prefer **extend existing tables** + **add restaurant-only tables**.
- Ingredients remain `products` rows (no parallel ingredient catalog or stock ledger).
- Offline queues live in **IndexedDB on the client**; only idempotency/audit columns needed on the server.
- **This document does not create migrations.**

---

## 2. Summary

| Category | Tables |
| :--- | :--- |
| **Extend (existing)** | `dining_tables`, `sales`, `sale_items`, `cart_items`, `products` |
| **Reuse unchanged** | `users`, `customers`, `product_categories`, `product_units`, `product_stocks`, `carts`, `seller_employees`, `business_settings` (+ supplier_* untouched) |
| **Create (new)** | `floors`, `reservations`, `recipes`, `recipe_ingredients`, `modifiers`, `product_modifiers`, `kitchen_tickets`, `kitchen_ticket_items` |

---

## 3. Existing Tables to Extend

### 3.1 `dining_tables` (Tables module)

**Current (baseline):** `id`, `branch_id`, `seller_id`, `name`, `status` (`free` \| `occupied` \| `reserved`), timestamps.

| Column to add | Type | Nullable | Purpose |
| :--- | :--- | :--- | :--- |
| `floor_id` | bigint unsigned | YES | FK → `floors.id`; null = default/unassigned floor |
| `capacity` | unsigned integer | YES | Guest capacity (if not already present in deployed DB) |
| `qr_code_token` | string(64) | YES | Unique token for QR Ordering |
| `x_position` | integer | YES | Floor-plan layout |
| `y_position` | integer | YES | Floor-plan layout |
| `deleted_at` | timestamp | YES | Soft delete (optional but recommended) |

**Status values:** keep `free`, `occupied`, `reserved`; optionally allow `cleaning` later without renaming existing values.

**Do not:** move open-check balances onto this table (Orders/`sales` remain commercial truth).

---

### 3.2 `sales` (Orders + Payments + offline idempotency)

**Current (baseline):** seller, table, employee, customer, `order_id`, amounts, payment fields, hold/draft flags, timestamps.

| Column to add | Type | Nullable | Purpose |
| :--- | :--- | :--- | :--- |
| `order_type` | string(32) | NO, default `retail` | `retail` \| `dine_in` \| `takeaway` \| `qr_table` |
| `client_order_id` | string(64) | YES | Offline/idempotent client UUID; unique per seller when set |
| `device_id` | string(64) | YES | Offline device identity |
| `synced_at` | timestamp | YES | When offline order was accepted by server |
| `reservation_id` | bigint unsigned | YES | FK → `reservations.id` when seated from a booking |

**Do not:** create a parallel restaurant orders table as bill of record.

---

### 3.3 `sale_items` (Modifiers + kitchen line context)

**Current (baseline):** sale, seller, item reference, name/price snapshots, qty, totals, note.

| Column to add | Type | Nullable | Purpose |
| :--- | :--- | :--- | :--- |
| `modifiers_json` | json | YES | Snapshot of selected modifiers (id, name, price) |
| `special_instructions` | string(255) | YES | Kitchen-facing line notes (optional if `note` reused) |
| `kitchen_status` | string(32) | YES | Optional denormalized prep hint; KOT items remain source for KDS |

**Do not:** store modifier money outside line totals already on the item.

---

### 3.4 `cart_items` (POS draft modifiers before checkout)

| Column to add | Type | Nullable | Purpose |
| :--- | :--- | :--- | :--- |
| `modifiers_json` | json | YES | Selected modifiers before conversion to `sale_items` |
| `special_instructions` | string(255) | YES | Carried into sale line / KOT |

---

### 3.5 `products` (Ingredients classification + recipe eligibility)

Ingredients reuse the product catalog and inventory ledger. Minimal flag only:

| Column to add | Type | Nullable | Purpose |
| :--- | :--- | :--- | :--- |
| `is_ingredient` | boolean | NO, default `false` | Marks product usable as recipe raw material |
| `is_sellable` | boolean | NO, default `true` | Allows raw materials to be non-POS-sellable if desired |

**Do not:** add a second stock quantity column; keep using `stock_in` / `stock_out` / `product_stocks`.

---

### 3.6 Explicitly not extended (reuse as-is)

| Table | Why unchanged |
| :--- | :--- |
| `customers` | Reservations/QR link via FK only |
| `product_categories` | Menu grouping unchanged |
| `product_units` | Referenced by recipe ingredient lines |
| `product_stocks` | Inventory movement log unchanged |
| `carts` | Draft header unchanged |
| `seller_employees` | Waiter/chef assignment via existing FKs |
| `business_settings` | Tax/currency/receipt config unchanged |
| `users` | Tenant (`seller_id`) unchanged |
| Supplier `*` tables | Procurement path unchanged |

---

## 4. New Tables Required

### 4.1 `floors`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint PK | |
| `seller_id` | bigint unsigned | FK → `users.id` |
| `name` | string | e.g. Main Hall, Patio |
| `priority` | integer | Sort order, default 0 |
| `is_active` | boolean | default true |
| `created_at`, `updated_at` | timestamps | |
| `deleted_at` | timestamp nullable | soft delete |

---

### 4.2 `reservations`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint PK | |
| `seller_id` | bigint unsigned | FK → `users.id` |
| `dining_table_id` | bigint unsigned | FK → `dining_tables.id` |
| `customer_id` | bigint unsigned nullable | FK → `customers.id` |
| `customer_name` | string | Required guest label (even if customer linked) |
| `customer_phone` | string nullable | |
| `guest_count` | unsigned integer | |
| `reserved_from` | datetime | Window start |
| `reserved_to` | datetime nullable | Window end |
| `status` | string(32) | `pending` \| `confirmed` \| `seated` \| `cancelled` \| `no_show` |
| `notes` | text nullable | |
| `sale_id` | bigint unsigned nullable | FK → `sales.id` after seating |
| `created_at`, `updated_at` | timestamps | |
| `deleted_at` | timestamp nullable | |

---

### 4.3 `recipes`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint PK | |
| `seller_id` | bigint unsigned | FK → `users.id` (denormalized tenant) |
| `product_id` | bigint unsigned | FK → `products.id` (finished/sellable dish) |
| `name` | string nullable | Optional override; default product name |
| `instructions` | text nullable | |
| `preparation_time_minutes` | unsigned integer nullable | |
| `is_active` | boolean | default true |
| `created_at`, `updated_at` | timestamps | |
| `deleted_at` | timestamp nullable | |

**Constraint intent:** one active recipe per `product_id` (unique on `product_id` where not deleted, or unique `product_id` if soft delete unused).

---

### 4.4 `recipe_ingredients`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint PK | |
| `recipe_id` | bigint unsigned | FK → `recipes.id` |
| `ingredient_product_id` | bigint unsigned | FK → `products.id` (raw material) |
| `quantity` | decimal(12,3) | Qty per 1 finished product unit |
| `unit_id` | bigint unsigned nullable | FK → `product_units.id` |
| `created_at`, `updated_at` | timestamps | |

**Constraint intent:** unique (`recipe_id`, `ingredient_product_id`).

---

### 4.5 `modifiers`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint PK | |
| `seller_id` | bigint unsigned | FK → `users.id` |
| `group_name` | string | e.g. Size, Extras |
| `name` | string | e.g. Extra Cheese |
| `price` | decimal(10,2) | default 0 |
| `is_active` | boolean | default true |
| `sort_order` | integer | default 0 |
| `created_at`, `updated_at` | timestamps | |
| `deleted_at` | timestamp nullable | |

---

### 4.6 `product_modifiers`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint PK | |
| `product_id` | bigint unsigned | FK → `products.id` |
| `modifier_id` | bigint unsigned | FK → `modifiers.id` |
| `is_required` | boolean | default false |
| `max_select` | unsigned integer nullable | Optional group limit helper |
| `created_at`, `updated_at` | timestamps | |

**Constraint intent:** unique (`product_id`, `modifier_id`).

---

### 4.7 `kitchen_tickets`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint PK | |
| `seller_id` | bigint unsigned | FK → `users.id` |
| `sale_id` | bigint unsigned | FK → `sales.id` |
| `dining_table_id` | bigint unsigned nullable | FK → `dining_tables.id` |
| `ticket_number` | string(64) | Display/print number |
| `status` | string(32) | `pending` \| `preparing` \| `ready` \| `served` \| `cancelled` |
| `station` | string(64) nullable | Optional station routing |
| `fired_at` | timestamp nullable | Sent to kitchen |
| `prepared_at` | timestamp nullable | |
| `served_at` | timestamp nullable | |
| `created_at`, `updated_at` | timestamps | |

---

### 4.8 `kitchen_ticket_items`

| Column | Type | Notes |
| :--- | :--- | :--- |
| `id` | bigint PK | |
| `kitchen_ticket_id` | bigint unsigned | FK → `kitchen_tickets.id` |
| `sale_item_id` | bigint unsigned nullable | FK → `sale_items.id` |
| `product_id` | bigint unsigned | FK → `products.id` |
| `product_name` | string | Snapshot for KDS |
| `quantity` | decimal(10,2) | |
| `modifiers_json` | json nullable | Snapshot for prep |
| `special_instructions` | string(255) nullable | |
| `status` | string(32) | `pending` \| `preparing` \| `ready` \| `cancelled` |
| `created_at`, `updated_at` | timestamps | |

---

### 4.9 Tables intentionally not created

| Rejected table | Reason |
| :--- | :--- |
| `ingredients` | Use `products` + `is_ingredient` |
| `ingredient_stocks` | Use `product_stocks` / product stock fields |
| `restaurant_orders` | Use `sales` |
| `offline_orders` (server) | Client IndexedDB only; server uses `client_order_id` on `sales` |
| `qr_sessions` | Token on `dining_tables` is enough for v1 |

---

## 5. Relationships

```
users (seller)
 ├── floors
 │    └── dining_tables
 │         ├── reservations ──┬── customers
 │         │                  └── sales (optional sale_id)
 │         ├── sales
 │         └── kitchen_tickets
 ├── products
 │    ├── recipes (1 product → 1 recipe)
 │    │    └── recipe_ingredients → products (ingredient_product_id)
 │    │         └── product_units
 │    ├── product_modifiers → modifiers
 │    ├── sale_items / cart_items
 │    └── kitchen_ticket_items
 ├── modifiers
 ├── sales
 │    ├── sale_items
 │    ├── kitchen_tickets
 │    │    └── kitchen_ticket_items
 │    ├── customers
 │    ├── seller_employees
 │    ├── dining_tables
 │    └── reservations (optional reservation_id)
 └── business_settings (unchanged)
```

### 5.1 Relationship matrix

| From | To | Type | FK column |
| :--- | :--- | :--- | :--- |
| `floors` | `users` | many-to-one | `seller_id` |
| `dining_tables` | `floors` | many-to-one (optional) | `floor_id` |
| `dining_tables` | `users` | many-to-one | `seller_id` |
| `reservations` | `users` | many-to-one | `seller_id` |
| `reservations` | `dining_tables` | many-to-one | `dining_table_id` |
| `reservations` | `customers` | many-to-one (optional) | `customer_id` |
| `reservations` | `sales` | many-to-one (optional) | `sale_id` |
| `sales` | `reservations` | many-to-one (optional) | `reservation_id` |
| `sales` | `dining_tables` | many-to-one (optional) | `dining_table_id` (existing) |
| `sales` | `customers` | many-to-one (optional) | `customer_id` (existing) |
| `sales` | `seller_employees` | many-to-one (optional) | `seller_employee_id` (existing) |
| `recipes` | `products` | one-to-one (finished) | `product_id` |
| `recipe_ingredients` | `recipes` | many-to-one | `recipe_id` |
| `recipe_ingredients` | `products` | many-to-one | `ingredient_product_id` |
| `recipe_ingredients` | `product_units` | many-to-one (optional) | `unit_id` |
| `modifiers` | `users` | many-to-one | `seller_id` |
| `product_modifiers` | `products` | many-to-one | `product_id` |
| `product_modifiers` | `modifiers` | many-to-one | `modifier_id` |
| `kitchen_tickets` | `sales` | many-to-one | `sale_id` |
| `kitchen_tickets` | `dining_tables` | many-to-one (optional) | `dining_table_id` |
| `kitchen_ticket_items` | `kitchen_tickets` | many-to-one | `kitchen_ticket_id` |
| `kitchen_ticket_items` | `sale_items` | many-to-one (optional) | `sale_item_id` |
| `kitchen_ticket_items` | `products` | many-to-one | `product_id` |

---

## 6. Foreign Keys

### 6.1 New table FKs

| Table | Column | References | On delete (recommended) |
| :--- | :--- | :--- | :--- |
| `floors` | `seller_id` | `users(id)` | CASCADE |
| `reservations` | `seller_id` | `users(id)` | CASCADE |
| `reservations` | `dining_table_id` | `dining_tables(id)` | RESTRICT |
| `reservations` | `customer_id` | `customers(id)` | SET NULL |
| `reservations` | `sale_id` | `sales(id)` | SET NULL |
| `recipes` | `seller_id` | `users(id)` | CASCADE |
| `recipes` | `product_id` | `products(id)` | CASCADE |
| `recipe_ingredients` | `recipe_id` | `recipes(id)` | CASCADE |
| `recipe_ingredients` | `ingredient_product_id` | `products(id)` | RESTRICT |
| `recipe_ingredients` | `unit_id` | `product_units(id)` | SET NULL |
| `modifiers` | `seller_id` | `users(id)` | CASCADE |
| `product_modifiers` | `product_id` | `products(id)` | CASCADE |
| `product_modifiers` | `modifier_id` | `modifiers(id)` | CASCADE |
| `kitchen_tickets` | `seller_id` | `users(id)` | CASCADE |
| `kitchen_tickets` | `sale_id` | `sales(id)` | CASCADE |
| `kitchen_tickets` | `dining_table_id` | `dining_tables(id)` | SET NULL |
| `kitchen_ticket_items` | `kitchen_ticket_id` | `kitchen_tickets(id)` | CASCADE |
| `kitchen_ticket_items` | `sale_item_id` | `sale_items(id)` | SET NULL |
| `kitchen_ticket_items` | `product_id` | `products(id)` | RESTRICT |

### 6.2 Extension FKs on existing tables

| Table | Column | References | On delete (recommended) |
| :--- | :--- | :--- | :--- |
| `dining_tables` | `floor_id` | `floors(id)` | SET NULL |
| `sales` | `reservation_id` | `reservations(id)` | SET NULL |

### 6.3 Existing FKs to enforce if missing

Current migrations often declare FK-shaped columns without DB constraints. When implementing later, prefer adding:

| Table | Column | References | On delete |
| :--- | :--- | :--- | :--- |
| `dining_tables` | `seller_id` | `users(id)` | CASCADE |
| `sales` | `dining_table_id` | `dining_tables(id)` | SET NULL |
| `sales` | `customer_id` | `customers(id)` | SET NULL |
| `sales` | `seller_employee_id` | `seller_employees(id)` | SET NULL |
| `sale_items` | `sale_id` | `sales(id)` | CASCADE |
| `sale_items` | `item_id` / product FK | `products(id)` | RESTRICT |

---

## 7. Indexes

### 7.1 New tables

| Table | Index | Columns | Purpose |
| :--- | :--- | :--- | :--- |
| `floors` | `floors_seller_priority_index` | (`seller_id`, `priority`) | Floor list order |
| `reservations` | `reservations_seller_time_status_index` | (`seller_id`, `reserved_from`, `status`) | Desk calendar / conflict checks |
| `reservations` | `reservations_table_time_index` | (`dining_table_id`, `reserved_from`, `status`) | Table double-booking checks |
| `reservations` | `reservations_customer_id_index` | (`customer_id`) | Customer history |
| `recipes` | `recipes_product_id_unique` | (`product_id`) UNIQUE | One recipe per dish |
| `recipes` | `recipes_seller_id_index` | (`seller_id`) | Tenant listing |
| `recipe_ingredients` | `recipe_ingredients_recipe_product_unique` | (`recipe_id`, `ingredient_product_id`) UNIQUE | No duplicate lines |
| `recipe_ingredients` | `recipe_ingredients_ingredient_index` | (`ingredient_product_id`) | “Where-used” for an ingredient |
| `modifiers` | `modifiers_seller_group_index` | (`seller_id`, `group_name`, `sort_order`) | Modifier admin/POS |
| `product_modifiers` | `product_modifiers_product_modifier_unique` | (`product_id`, `modifier_id`) UNIQUE | Attachment integrity |
| `product_modifiers` | `product_modifiers_modifier_id_index` | (`modifier_id`) | Reverse lookup |
| `kitchen_tickets` | `kitchen_tickets_seller_status_index` | (`seller_id`, `status`, `created_at`) | KDS queue |
| `kitchen_tickets` | `kitchen_tickets_sale_id_index` | (`sale_id`) | Tickets for an order |
| `kitchen_tickets` | `kitchen_tickets_table_status_index` | (`dining_table_id`, `status`) | Table prep context |
| `kitchen_tickets` | `kitchen_tickets_ticket_number_index` | (`seller_id`, `ticket_number`) | Lookup/print |
| `kitchen_ticket_items` | `kitchen_ticket_items_ticket_status_index` | (`kitchen_ticket_id`, `status`) | Item bump actions |
| `kitchen_ticket_items` | `kitchen_ticket_items_sale_item_id_index` | (`sale_item_id`) | Trace to sale line |

### 7.2 Extended existing tables

| Table | Index | Columns | Purpose |
| :--- | :--- | :--- | :--- |
| `dining_tables` | `dining_tables_seller_floor_status_index` | (`seller_id`, `floor_id`, `status`) | Floor map queries |
| `dining_tables` | `dining_tables_qr_code_token_unique` | (`qr_code_token`) UNIQUE (nullable) | QR resolution |
| `sales` | `sales_seller_order_type_date_index` | (`seller_id`, `order_type`, `sale_date`) | Channel reports |
| `sales` | `sales_seller_client_order_id_unique` | (`seller_id`, `client_order_id`) UNIQUE (nullable) | Offline idempotency |
| `sales` | `sales_dining_table_id_index` | (`dining_table_id`) | Open checks by table |
| `sales` | `sales_reservation_id_index` | (`reservation_id`) | Seating trace |
| `products` | `products_seller_ingredient_sellable_index` | (`seller_id`, `is_ingredient`, `is_sellable`) | Ingredient vs menu lists |

---

## 8. Integrity & Business Rules (schema-adjacent)

1. **Retail compatibility:** `sales.order_type` defaults to `retail`; restaurant fields nullable where optional.
2. **Recipe deduction:** on sale commit, if recipe exists for line product, deduct `recipe_ingredients` via existing inventory fields; else deduct sellable product as today.
3. **KOT lifecycle:** `kitchen_tickets` / items are operational; financial truth stays on `sales` / `sale_items`.
4. **QR:** resolve `dining_tables.qr_code_token` → table → create `sales` with `order_type = qr_table`.
5. **Offline:** durable queue is client-side; server dedupes with (`seller_id`, `client_order_id`).
6. **No LocalStorage / no server `offline_orders` table** for v1.

---

## 9. ER Overview

```
floors 1──* dining_tables 1──* reservations
                 │                  │
                 │                  └──? customers
                 │
                 └──* sales 1──* sale_items
                       │              │
                       │              └── modifiers_json (snapshot)
                       │
                       └──1──* kitchen_tickets 1──* kitchen_ticket_items

products 1──1 recipes 1──* recipe_ingredients *──1 products (ingredient)
products 1──* product_modifiers *──1 modifiers
```

---

## 10. Document Boundary

This file is a **database plan only**: tables to extend, tables to create, relationships, indexes, and foreign keys.

**No migrations, models, seeders, or application code are included or generated here.**
