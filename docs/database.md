# Database Architecture & Entity Relationship Specification

## 1. Overview
The database schema manages inventory, sales, carts, customers, dining tables, suppliers, and business settings.

---

## 2. Existing Schema Map

```
+------------------+         +--------------------+         +-------------------+
|      users       |         |     customers      |         |   dining_tables   |
+------------------+         +--------------------+         +-------------------+
| id               |<---+    | id                 |<---+    | id                |<---+
| name             |    |    | seller_id (FK)     |    |    | seller_id (FK)    |    |
| email            |    |    | name               |    |    | name              |    |
| role             |    |    | phone              |    |    | capacity          |    |
| status           |    |    +--------------------+    |    | status (0,1,2)    |    |
+------------------+    |                              |    +-------------------+    |
                        |                              |                             |
                        +--------------+---------------+-----------------------------+
                                       |
+--------------------------------------+---------------------------------------------+
|                                      v                                             |
|                               +--------------+                                     |
|                               |    sales     |                                     |
|                               +--------------+                                     |
|                               | id           |                                     |
|                               | seller_id(FK)|                                     |
|                               | order_id     |                                     |
|                               | customer_id  |                                     |
|                               | dining_table |                                     |
|                               | seller_emp_id|                                     |
|                               | subtotal     |                                     |
|                               | discount     |                                     |
|                               | tax          |                                     |
|                               | payable      |                                     |
|                               | paid         |                                     |
|                               | due          |                                     |
|                               | is_hold      |                                     |
|                               | status       |                                     |
|                               +------+-------+                                     |
|                                      |                                             |
|                                      v                                             |
|                               +--------------+                                     |
|                               |  sale_items  |                                     |
|                               +--------------+                                     |
|                               | id           |                                     |
|                               | sale_id (FK) |                                     |
|                               | product_id   |                                     |
|                               | unit_price   |                                     |
|                               | quantity     |                                     |
|                               | total_price  |                                     |
|                               +---------------+                                     |
+------------------------------------------------------------------------------------+
```

---

## 3. Existing Tables Breakdown

| Table Name | Primary Purpose | Key Foreign Keys | Status Column / Notes |
| :--- | :--- | :--- | :--- |
| `users` | User accounts (sellers, suppliers, admins) | None | Role column (`seller`, `supplier`, `admin`) |
| `products` | Restaurant menu & store items | `seller_id`, `category_id`, `unit_id` | Stock tracked via `stock_in` - `stock_out` |
| `product_categories` | Categories for products | `seller_id` | Category hierarchy |
| `product_units` | Measurement units (kg, pcs, plate) | None | Measurement units |
| `product_stocks` | Stock addition history logs | `product_id` | Log of added inventory |
| `dining_tables` | Physical tables in restaurant | `seller_id` | `status` (0: Free, 1: Occupied, 2: Reserved) |
| `seller_employees` | Restaurant staff / waiters | `seller_id` | Employee records |
| `customers` | Registered customers / patrons | `seller_id` | Phone, address, name |
| `carts` | POS transient carts | `seller_id` | Linked to `order_id` |
| `cart_items` | Active items in a POS cart | `cart_id`, `item_id` | Unit price, quantity, total |
| `sales` | Completed / held orders | `seller_id`, `customer_id`, `dining_table_id`, `seller_employee_id` | `is_hold` (0/1), `status` |
| `sale_items` | Items inside a sale | `sale_id`, `product_id` | Price, discount, quantity |
| `supplier_products` | Supplier catalog | `supplier_id`, `category_id` | Stock & pricing |
| `supplier_product_stocks`| Supplier stock logs | `supplier_product_id` | Quantity added |
| `supplier_carts` | Active procurement cart | `supplier_id`, `seller_id` | Procurement draft |
| `supplier_cart_items` | Procurement cart items | `supplier_cart_id` | Item quantities |
| `supplier_sales` | Completed procurement orders | `supplier_id`, `seller_id` | Invoices |
| `supplier_sale_items` | Procurement order items | `supplier_sale_id` | Purchased items |
| `business_settings` | Tax, currency, receipt configuration | `user_id` | Business profile |

---

## 4. Indexing & Constraint Gaps
1. `sales`: **Resolved (TASK-105)** — indexes `(seller_id, is_hold, created_at)`, `(seller_id, dining_table_id)`, `(seller_id, sale_date)`. (`status` is not a live column; omitted.)
2. `sale_items`: **Resolved (TASK-105)** — indexes `(sale_id, item_id)`, `(item_id)`, `(seller_id, sale_id)`. (Product FK column is `item_id`.)
3. `products`: **Resolved (TASK-105)** — index `(seller_id, is_active, category_id)`.
4. `dining_tables`: **Resolved (TASK-105)** — index `(seller_id, status)`.
5. Missing `softDeletes` columns on `products`, `sales`, `customers`, and `dining_tables`.

Migration: `database/migrations/2026_07_24_232500_add_composite_indexes_to_pos_core_tables.php`.
