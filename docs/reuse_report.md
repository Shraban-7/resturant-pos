# Reusable Functionality & Module Dependency Report

## 1. Executive Overview
This document evaluates the existing core modules in the Laravel codebase, identifying explicit reuse candidates for the **Restaurant POS Extension**. Reusing these proven foundation modules guarantees that all existing e-commerce and retail POS workflows remain 100% operational while preventing code duplication.

---

## 2. Reusable Modules Breakdown

```
+-----------------------------------------------------------------------------------+
|                           EXISTING FOUNDATION MODULES                             |
|  1. Products            5. Payments           9. Reports                          |
|  2. Inventory           6. Orders            10. Branches / Settings              |
|  3. Purchases (Supply)  7. Users             11. Notifications / Flash            |
|  4. Customers           8. Staff Roles       12. File Upload Utilities            |
+----------------------------------------+------------------------------------------+
                                         | Extends & Supports
+----------------------------------------v------------------------------------------+
|                         RESTAURANT POS EXTENSION MODULES                          |
|  - Kitchen Display System (KDS)               - Bill of Materials (BOM) Recipes   |
|  - Digital QR Code Ordering                   - Product Modifiers & Variants      |
|  - Multi-Floor & Table Floor Plan             - Kitchen Order Tickets (KOT)       |
+-----------------------------------------------------------------------------------+
```

---

### 2.1 Products Module
- **Existing Files**: [`app/Models/Product.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/Product.php), [`ProductCategory.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/ProductCategory.php), [`ProductUnit.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/ProductUnit.php), [`ProductController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/ProductController.php)
- **Why It Should Be Reused**:
  - The product module provides complete catalog management, price fields (`buying_price`, `selling_price`), measurement units, category hierarchies, and active state flags (`is_active`). Reusing `Product` ensures unified sales reporting and inventory tracking.
- **Dependent Restaurant Modules**:
  - **Digital QR Code Menu**: Uses active products for customer mobile ordering.
  - **Modifiers & Add-ons**: Connects add-on options (e.g. Extra Cheese) to base `Product` IDs.
  - **Recipe Bill of Materials (BOM)**: Links cooked dishes (`Product`) to raw material items (`Product`).
  - **Kitchen Display System (KDS)**: Displays product names and quantities on kitchen order cards.

---

### 2.2 Inventory & Stock Module
- **Existing Files**: [`app/Models/ProductStock.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/ProductStock.php), `Product` (`availableStock` accessor), [`StockController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/StockController.php)
- **Why It Should Be Reused**:
  - The inventory system tracks cumulative stock movements (`stock_in` - `stock_out`) and logs stock entry additions in `product_stocks`.
- **Dependent Restaurant Modules**:
  - **Recipe Ingredient Auto-Deduction Engine**: When a restaurant dish is ordered, the system automatically deducts raw ingredient quantities from the inventory using the existing stock calculation rules.

---

### 2.3 Purchases & Supply Module
- **Existing Files**: [`app/Models/SupplierSale.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/SupplierSale.php), [`SupplierSaleItem.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/SupplierSaleItem.php), [`SupplyController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Supplier/SupplyController.php)
- **Why It Should Be Reused**:
  - Provides a complete B2B procurement workflow allowing store owners to purchase restock inventory directly from suppliers with invoices.
- **Dependent Restaurant Modules**:
  - **Kitchen Raw Material Procurement**: Enables restaurant managers to restock raw food ingredients (flour, meat, spices) directly into the inventory system.

---

### 2.4 Customers Module
- **Existing Files**: [`app/Models/Customer.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/Customer.php), [`CustomerController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/CustomerController.php)
- **Why It Should Be Reused**:
  - Stores patron details (name, phone, email, address) isolated per seller account.
- **Dependent Restaurant Modules**:
  - **Table Reservations**: Connects table bookings to registered customer profiles.
  - **QR Code Guest Orders**: Allows customers to save contact info for digital receipts.
  - **Customer Loyalty & History**: Tracks dine-in and takeout order history per patron.

---

### 2.5 Payments Module
- **Existing Files**: [`app/Models/Sale.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/Sale.php) (`payable`, `paid`, `due`, `payment_type`), [`SaleController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/SaleController.php#L34-L45) (`markPaid`)
- **Why It Should Be Reused**:
  - Standardizes payment classification (`cash`, `card`, `mobile_banking`), split payments, due balance tracking, and receipt payment status calculations.
- **Dependent Restaurant Modules**:
  - **POS Table Billing**: Handles final payment processing at the cashier station for dine-in tables.
  - **Split Check Payments**: Manages partial payments against table sales.

---

### 2.6 Orders Module
- **Existing Files**: [`app/Models/Sale.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/Sale.php), [`SaleItem.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/SaleItem.php), [`PosController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/PosController.php), [`MenuController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/MenuController.php)
- **Why It Should Be Reused**:
  - The `sales` and `sale_items` tables act as the single source of truth for order identification (`order_id`), line items, pricing, discounts, and status tracking. Reusing this entity prevents fragmented database tables.
- **Dependent Restaurant Modules**:
  - **Kitchen Order Tickets (KOT)**: Generated directly from `Sale` and `SaleItem` records.
  - **Kitchen Display System (KDS)**: Renders live kitchen tickets derived from pending `Sale` records.
  - **Dine-in / Takeaway / QR Orders**: All order channels write directly to the `sales` entity with an added `order_type` flag.

---

### 2.7 Users & Authentication Module
- **Existing Files**: [`app/Models/User.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/User.php), [`LoginController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Auth/LoginController.php), [`HasCommonScopes.php`](file:///d:/projects/php_projects/restaurant_pos/app/Traits/HasCommonScopes.php)
- **Why It Should Be Reused**:
  - Manages password encryption, session security, Sanctum API tokens, and global tenant scope filtering (`seller_id = auth()->id()`).
- **Dependent Restaurant Modules**:
  - **Multi-Tenant Restaurant Security**: Ensures restaurant data remains strictly isolated between different store owners.
  - **Staff Authentication**: Powers login access for Cashiers, Waiters, and Kitchen Staff.

---

### 2.8 Roles & Staff Management Module
- **Existing Files**: [`app/Models/SellerEmployee.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/SellerEmployee.php), [`EmployeeController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/EmployeeController.php)
- **Why It Should Be Reused**:
  - `seller_employees` maintains staff records, phone numbers, and assigned operational roles (`role`).
- **Dependent Restaurant Modules**:
  - **Waiter Table Assignment**: Links waiters (`seller_employee_id`) to active table orders.
  - **KDS Kitchen Ticket Routing**: Routes kitchen tickets based on designated chef/kitchen roles.

---

### 2.9 Reports & Analytics Module
- **Existing Files**: [`ReportController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/ReportController.php), [`resources/views/seller/report/index.blade.php`](file:///d:/projects/php_projects/restaurant_pos/resources/views/seller/report/index.blade.php)
- **Why It Should Be Reused**:
  - Aggregates daily/monthly sales, total revenue, and order metrics across the business.
- **Dependent Restaurant Modules**:
  - **Restaurant Business Analytics**: Automatically incorporates dine-in, takeaway, and QR order revenues into master financial reports without modifying reporting queries.

---

### 2.10 Branches & Settings Module
- **Existing Files**: [`app/Models/BusinessSetting.php`](file:///d:/projects/php_projects/restaurant_pos/app/Models/BusinessSetting.php), [`SettingController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/SettingController.php)
- **Why It Should Be Reused**:
  - Manages store metadata (business name, logo, phone, address, currency, tax rates, receipt header/footer).
- **Dependent Restaurant Modules**:
  - **Multi-Floor & Table Settings**: Inherits global currency and tax configurations.
  - **Restaurant Thermal Receipts**: Applies custom headers and logos onto printed restaurant bills.

---

### 2.11 Notifications & Flash Alert Module
- **Existing Files**: [`app/helpers.php`](file:///d:/projects/php_projects/restaurant_pos/app/helpers.php) (`apiResponse`, `errorResponse`), [`flash-message.blade.php`](file:///d:/projects/php_projects/restaurant_pos/resources/views/flash-message.blade.php)
- **Why It Should Be Reused**:
  - Standardizes session flash notifications and API JSON response envelopes (`status`, `message`, `data`).
- **Dependent Restaurant Modules**:
  - **KDS Ready Alerts**: Displays flash notifications when kitchen tickets are marked ready.
  - **POS Order Action Feeds**: Returns standardized API responses during table status updates.

---

### 2.12 File Upload Utilities Module
- **Existing Files**: [`app/helpers.php`](file:///d:/projects/php_projects/restaurant_pos/app/helpers.php) (`upload_file`, `storage_url`, `delete_file`), [`config/filesystems.php`](file:///d:/projects/php_projects/restaurant_pos/config/filesystems.php)
- **Why It Should Be Reused**:
  - `upload_file()` manages directory creation, unique file naming (`time() . rand()`), and file persistence on local storage disks safely.
- **Dependent Restaurant Modules**:
  - **Digital Menu Dish Photography**: Manages image uploads for food menu items.
  - **Restaurant Logo Customization**: Uploads branding assets for receipt bills.
