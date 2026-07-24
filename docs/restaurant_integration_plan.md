# Restaurant POS Integration Plan

## 1. Non-Disruptive Architecture Principles
1. **Preserve Legacy POS**: General retail POS operations must continue functioning seamlessly without requiring dining tables or kitchen display routing.
2. **Modular Domain Extensions**: All restaurant-specific features (Tables, Kitchen Tickets, Modifiers, Recipes) are introduced as additive modules and database tables.
3. **Unified Order Processing**: Both retail POS and Restaurant Dine-In/QR orders funnel into the core `sales` and `sale_items` tables, preserving global sales reporting and accounting.

---

## 2. Multi-Domain Order Flow Architecture

```
+-----------------------------------------------------------------------------------+
|                                 UNIFIED ORDER INGESTION                           |
|  +------------------------+  +------------------------+  +---------------------+  |
|  | Retail POS Checkout    |  | Restaurant Dine-In POS |  | Customer QR Order   |  |
|  +------------------------+  +------------------------+  +---------------------+  |
+----------------------------------------+------------------------------------------+
                                         |
                                         v
+-----------------------------------------------------------------------------------+
|                              RESTAURANT KITCHEN ENGINE                            |
|  - Kitchen Ticket Generator (KOT Creation)                                        |
|  - Recipe Ingredient Inventory Deductor (BOM Auto-Deduction)                      |
|  - Realtime WebSocket Broadcast (Laravel Reverb to KDS & Cashier)                 |
+----------------------------------------+------------------------------------------+
                                         |
                                         v
+-----------------------------------------------------------------------------------+
|                              UNIFIED SALES PERSISTENCE                            |
|  - `sales` table (order_id, payable, paid, dining_table_id, order_type)          |
|  - `sale_items` table (product_id, quantity, modifiers_json)                      |
+-----------------------------------------------------------------------------------+
```
---

## 3. Backward Compatibility Matrix

| Feature | Legacy E-Commerce / POS | New Restaurant POS | Compatibility Mechanism |
| :--- | :--- | :--- | :--- |
| **Products** | Basic item with stock count | Item with optional Recipe & Modifiers | If no Recipe exists, fallback to direct `stock_out` deduction. |
| **Sales** | Retail cashier order | Table / QR / Takeout order | Default `order_type = 'retail'`. Restaurant uses `dine_in`, `qr_table`, `takeaway`. |
| **Tables** | Single dining table list | Multi-floor interactive table map | If `floor_id` is null, table renders under "Main Floor". |
