# Reuse Candidates & Integration Matrix

## 1. Existing Components Reuse Strategy

```
+------------------+----------------------------------------------------+---------------------------------------------+
| Existing Entity  | Existing Capabilities                              | Restaurant POS Extension Strategy           |
+------------------+----------------------------------------------------+---------------------------------------------+
| Product          | Basic pricing, stock_in, stock_out, categories     | Extend with Recipes (BOM), Modifiers & KDS  |
| DiningTable      | Status (0,1,2), capacity, name, seller_id          | Extend with Floor IDs, QR token & live state|
| Sale             | order_id, subtotal, payable, paid, due, is_hold    | Extend with Order Type (DineIn, Takeout, QR)|
| SaleItem         | sale_id, product_id, price, quantity, total_price  | Extend with Modifiers & Special Instructions|
| SellerEmployee   | seller_id, name, phone, role                       | Extend with Kitchen Staff & Waiter roles    |
| BusinessSetting  | Store profile, currency, receipt info              | Extend with KDS & QR Menu configurations    |
+------------------+----------------------------------------------------+---------------------------------------------+
```

---

## 2. Shared Services & Component Architecture

1. **`App\Models\Product`**:
   - Reused for restaurant menu items.
   - Relates to new `Recipe` (Bill of Materials) and `ProductModifier` models.
2. **`App\Models\Sale`**:
   - Reused as the core Order container for Dine-in, Takeaway, Delivery, and QR Table Orders.
   - Relates to new `KitchenTicket` (KOT) entity for real-time KDS tracking.
3. **`App\Models\DiningTable`**:
   - Reused for physical floor plan management.
   - Relates to new `Floor` model for multi-floor restaurant zoning.
4. **`App\Models\Customer`**:
   - Reused for guest dine-in order history, loyalty tracking, and CRM.
