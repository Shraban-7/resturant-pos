# Existing System Workflows Analysis

## 1. Workflow 1: POS Item Addition & Cart Update

```
  [User / Cashier]                    [PosController]                   [Database]
         |                                  |                                |
         |--- 1. POST /seller/pos/item/add ->|                                |
         |    (order_id, product_id, qty)   |                                |
         |                                  |--- 2. Check product stock ---->|
         |                                  |    (availableStock < qty?)     |
         |                                  |<-- Returns available stock ----|
         |                                  |                                |
         |                                  |--- 3. Create CartItem -------->|
         |                                  |--- 4. Increment stock_out ---->|
         |                                  |                                |
         |                                  |--- 5. Render cart-item HTML -->|
         |<-- 6. JSON {cart_item_html} ------|                                |
```

---

## 2. Workflow 2: Digital QR Menu Customer Order

```
  [Customer / QR Guest]             [MenuController]                    [Database]
         |                                  |                                |
         |--- 1. GET /menu/{table} -------->|                                |
         |<-- 2. Render digital-menu view---|                                |
         |                                  |                                |
         |--- 3. POST /menu/{table}/order ->|                                |
         |    (items: [{id, quantity}])     |                                |
         |                                  |--- 4. Create Sale (pending) --->|
         |                                  |--- 5. Create SaleItems ------->|
         |                                  |--- 6. Increment stock_out ----->|
         |                                  |--- 7. Update Table OCCUPIED ->|
         |<-- 8. JSON {order_id} -----------|                                |
```

---

## 3. Workflow 3: POS Order Checkout & Receipt Printing
1. **Cashier Click Checkout**: `PosController@checkout` accepts `cart_id`, `customer_id`, `dining_table_id`, `seller_employee_id`, `paid_amount`, `discount_amount`, `payment_type`.
2. **Sale Record Creation**: Computes subtotal, tax, discount, payable, paid, and due. Creates `Sale` record with `is_hold = 0` and `status = 'completed'`.
3. **Sale Items Migration**: Converts all `CartItem` entries from the active cart into `SaleItem` rows linked to the new `Sale` record.
4. **Cart Cleanup**: Empties `CartItem` rows and deletes/resets the active `Cart`.
5. **Table Status Update**: If a `dining_table_id` was selected, marks `DiningTable::status = OCCUPIED`.
6. **Receipt Response**: Returns JSON response containing invoice rendering URL for thermal receipt printing.

---

## 4. Workflow 4: Supplier Restock & Procurement Supply
1. **Supplier Restock Cart**: Supplier adds products to `SupplierCart` via `SupplyController@addItem`.
2. **Supply Checkout**: `SupplyController@checkout` creates `SupplierSale`, logs items in `SupplierSaleItem`, updates supplier product stock, and generates supply invoice.
