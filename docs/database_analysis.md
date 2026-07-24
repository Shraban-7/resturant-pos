# Comprehensive Database & Eloquent Models Analysis

## 1. Executive Summary
This document presents a granular code-level analysis of all **25 database migration files** and **20 Eloquent Models** in the codebase. It details columns, data types, foreign keys, indexes, model relationships, enum constants, soft delete statuses, and explicit reuse candidates for Restaurant POS integration.

---

## 2. Granular Table & Model Specifications

### 2.1 Table: `users`
- **Migration**: `2014_10_12_000000_create_users_table.php`
- **Eloquent Model**: [`App\Models\User`](file:///d:/projects/php_projects/restaurant_pos/app/Models/User.php)
- **Columns & Data Types**:
  - `id` (bigint, PK, auto_increment)
  - `role` (string) - Roles: `seller`, `supplier`, `admin`
  - `name` (string)
  - `email` (string, unique)
  - `email_verified_at` (timestamp, nullable)
  - `phone` (string, nullable)
  - `password` (string)
  - `remember_token` (string, nullable)
  - `created_at`, `updated_at` (timestamps)
- **Foreign Keys**: None
- **Indexes**: `users_email_unique`
- **Model Relationships**: None defined explicitly on model.
- **Enums**: Roles (`seller`, `supplier`, `admin`).
- **Soft Deletes**: **NO** (`SoftDeletes` trait not used).

---

### 2.2 Table: `products`
- **Migration**: `2023_08_27_140529_create_products_table.php`, `2025_04_10_092554_add_unit_id_column_to_products_table.php`
- **Eloquent Model**: [`App\Models\Product`](file:///d:/projects/php_projects/restaurant_pos/app/Models/Product.php)
- **Columns & Data Types**:
  - `id` (bigint, PK, auto_increment)
  - `seller_id` (bigint, FK -> users.id)
  - `category_id` (bigint, FK -> product_categories.id)
  - `unit_id` (bigint, FK -> product_units.id, nullable)
  - `name` (string)
  - `image` (string, nullable)
  - `buying_price` (decimal 10,2)
  - `selling_price` (decimal 10,2)
  - `stock_in` (decimal 10,2, default 0)
  - `stock_out` (decimal 10,2, default 0)
  - `is_active` (boolean, default 1)
  - `created_at`, `updated_at` (timestamps)
- **Foreign Keys**: `seller_id` -> `users(id)`, `category_id` -> `product_categories(id)`, `unit_id` -> `product_units(id)`
- **Indexes**: Implicit FK indexes. Missing composite index `(seller_id, is_active, category_id)`.
- **Model Relationships**:
  - `category()`: `BelongsTo(ProductCategory)`
  - `unit()`: `BelongsTo(ProductUnit)`
  - `supplierStocks()`: `HasMany(SupplierProductStock)`
- **Accessors**: `availableStock` (`stock_in - stock_out`).
- **Enums**: None.
- **Soft Deletes**: **NO**.

---

### 2.3 Table: `product_categories`
- **Migration**: `2025_04_14_091050_create_product_categories_table.php`
- **Eloquent Model**: [`App\Models\ProductCategory`](file:///d:/projects/php_projects/restaurant_pos/app/Models/ProductCategory.php)
- **Columns & Data Types**:
  - `id` (bigint, PK, auto_increment)
  - `seller_id` (bigint, FK -> users.id)
  - `name` (string)
  - `created_at`, `updated_at` (timestamps)
- **Foreign Keys**: `seller_id` -> `users(id)`
- **Indexes**: None.
- **Model Relationships**:
  - `products()`: `HasMany(Product, 'category_id')`
- **Soft Deletes**: **NO**.

---

### 2.4 Table: `product_units`
- **Migration**: `2025_03_19_065556_create_product_units_table.php`
- **Eloquent Model**: [`App\Models\ProductUnit`](file:///d:/projects/php_projects/restaurant_pos/app/Models/ProductUnit.php)
- **Columns & Data Types**:
  - `id` (bigint, PK, auto_increment)
  - `name` (string) - e.g. "Kilogram", "Piece", "Plate"
  - `short_name` (string) - e.g. "kg", "pcs", "plate"
  - `created_at`, `updated_at` (timestamps)
- **Foreign Keys**: None
- **Indexes**: None
- **Model Relationships**:
  - `products()`: `HasMany(Product, 'unit_id')`
- **Soft Deletes**: **NO**.

---

### 2.5 Table: `product_stocks`
- **Migration**: `2025_04_13_062423_create_product_stocks_table.php`
- **Eloquent Model**: [`App\Models\ProductStock`](file:///d:/projects/php_projects/restaurant_pos/app/Models/ProductStock.php)
- **Columns & Data Types**:
  - `id` (bigint, PK, auto_increment)
  - `product_id` (bigint, FK -> products.id)
  - `quantity` (decimal 10,2)
  - `buying_price` (decimal 10,2)
  - `selling_price` (decimal 10,2)
  - `created_at`, `updated_at` (timestamps)
- **Foreign Keys**: `product_id` -> `products(id)`
- **Indexes**: None
- **Model Relationships**:
  - `product()`: `BelongsTo(Product)`
- **Soft Deletes**: **NO**.

---

### 2.6 Table: `dining_tables`
- **Migration**: `2025_04_27_091924_create_dining_tables_table.php`
- **Eloquent Model**: [`App\Models\DiningTable`](file:///d:/projects/php_projects/restaurant_pos/app/Models/DiningTable.php)
- **Columns & Data Types**:
  - `id` (bigint, PK, auto_increment)
  - `seller_id` (bigint, FK -> users.id)
  - `number_or_name` (string)
  - `capacity` (integer)
  - `status` (string, default 'free') - Constants: `FREE = 'free'`, `OCCUPIED = 'occupied'`, `RESERVED = 'reserved'`
  - `created_at`, `updated_at` (timestamps)
- **Foreign Keys**: `seller_id` -> `users(id)`
- **Indexes**: None
- **Model Relationships**:
  - `seller()`: `BelongsTo(User, 'seller_id')`
- **Enums**: Constants (`FREE`, `OCCUPIED`, `RESERVED`).
- **Soft Deletes**: **NO**.

---

### 2.7 Table: `seller_employees`
- **Migration**: `2025_04_27_095533_create_seller_employees_table.php`
- **Eloquent Model**: [`App\Models\SellerEmployee`](file:///d:/projects/php_projects/restaurant_pos/app/Models/SellerEmployee.php)
- **Columns & Data Types**:
  - `id` (bigint, PK, auto_increment)
  - `seller_id` (bigint, FK -> users.id)
  - `name` (string)
  - `phone` (string)
  - `role` (string) - e.g. "Waiter", "Cashier", "Chef"
  - `created_at`, `updated_at` (timestamps)
- **Foreign Keys**: `seller_id` -> `users(id)`
- **Indexes**: None
- **Model Relationships**:
  - `seller()`: `BelongsTo(User, 'seller_id')`
- **Soft Deletes**: **NO**.

---

### 2.8 Table: `customers`
- **Migration**: `2023_08_27_140650_create_customers_table.php`
- **Eloquent Model**: [`App\Models\Customer`](file:///d:/projects/php_projects/restaurant_pos/app/Models/Customer.php)
- **Columns & Data Types**:
  - `id` (bigint, PK, auto_increment)
  - `seller_id` (bigint, FK -> users.id)
  - `name` (string)
  - `phone` (string, nullable)
  - `email` (string, nullable)
  - `address` (text, nullable)
  - `created_at`, `updated_at` (timestamps)
- **Foreign Keys**: `seller_id` -> `users(id)`
- **Indexes**: None
- **Model Relationships**:
  - `sales()`: `HasMany(Sale, 'customer_id')`
- **Soft Deletes**: **NO**.

---

### 2.9 Table: `carts` & `cart_items`
- **Migrations**: `2025_04_10_074848_create_carts_table.php`, `2025_04_10_075210_create_cart_items_table.php`
- **Eloquent Models**: [`App\Models\Cart`](file:///d:/projects/php_projects/restaurant_pos/app/Models/Cart.php), [`App\Models\CartItem`](file:///d:/projects/php_projects/restaurant_pos/app/Models/CartItem.php)
- **Columns (`carts`)**: `id`, `seller_id` (FK), `order_id` (string), `timestamps`
- **Columns (`cart_items`)**: `id`, `cart_id` (FK), `item_id` (FK -> products.id), `unit_price`, `discount`, `quantity`, `total_price`, `timestamps`
- **Foreign Keys**: `cart_id` -> `carts(id)`, `item_id` -> `products(id)`
- **Model Relationships**:
  - `Cart`: `items()` -> `HasMany(CartItem)`
  - `CartItem`: `cart()` -> `BelongsTo(Cart)`, `item()` -> `BelongsTo(Product, 'item_id')`
- **Soft Deletes**: **NO**.

---

### 2.10 Table: `sales` & `sale_items`
- **Migrations**: `2023_08_27_140651_create_sales_table.php`, `2023_08_27_140920_create_sale_items_table.php`, `2026_01_07_093827_add_is_hold_to_sales_table.php`
- **Eloquent Models**: [`App\Models\Sale`](file:///d:/projects/php_projects/restaurant_pos/app/Models/Sale.php), [`App\Models\SaleItem`](file:///d:/projects/php_projects/restaurant_pos/app/Models/SaleItem.php)
- **Columns (`sales`)**:
  - `id` (bigint, PK)
  - `seller_id` (bigint, FK)
  - `order_id` (string, unique)
  - `customer_id` (bigint, FK, nullable)
  - `dining_table_id` (bigint, FK, nullable)
  - `seller_employee_id` (bigint, FK, nullable)
  - `subtotal` (decimal 10,2)
  - `discount` (decimal 10,2, default 0)
  - `tax` (decimal 10,2, default 0)
  - `payable` (decimal 10,2)
  - `paid` (decimal 10,2, default 0)
  - `due` (decimal 10,2, default 0)
  - `payment_type` (string, default 'cash')
  - `is_hold` (boolean, default 0)
  - `status` (string, default 'completed')
  - `sale_date` (timestamp)
  - `timestamps`
- **Columns (`sale_items`)**:
  - `id` (bigint, PK)
  - `sale_id` (bigint, FK)
  - `product_id` (bigint, FK)
  - `price` (decimal 10,2)
  - `quantity` (decimal 10,2)
  - `total_price` (decimal 10,2)
  - `timestamps`
- **Foreign Keys**: `sale_id` -> `sales(id)`, `product_id` -> `products(id)`, `customer_id` -> `customers(id)`, `dining_table_id` -> `dining_tables(id)`, `seller_employee_id` -> `seller_employees(id)`
- **Model Relationships**:
  - `Sale`: `customer()` -> `BelongsTo(Customer)`, `table()` -> `BelongsTo(DiningTable)`, `waiter()` -> `BelongsTo(SellerEmployee)`, `items()` -> `HasMany(SaleItem)`
  - `SaleItem`: `sale()` -> `BelongsTo(Sale)`, `product()` -> `BelongsTo(Product)`
- **Soft Deletes**: **NO**.

---

### 2.11 Supplier & Procurement Tables
- **`supplier_products`**: Vendor catalog (`supplier_id`, `category_id`, `name`, `price`, `stock_in`, `stock_out`).
- **`supplier_product_categories`**: Vendor product categories.
- **`supplier_product_stocks`**: Restock logs for supplier items.
- **`supplier_carts` & `supplier_cart_items`**: Procurement order drafts.
- **`supplier_sales` & `supplier_sale_items`**: Final B2B procurement invoices.

---

### 2.12 Table: `business_settings`
- **Migration**: `2025_04_07_072337_create_business_settings_table.php`
- **Eloquent Model**: [`App\Models\BusinessSetting`](file:///d:/projects/php_projects/restaurant_pos/app/Models/BusinessSetting.php)
- **Columns**: `id`, `user_id` (FK), `business_name`, `logo`, `address`, `phone`, `email`, `currency`, `tax_percentage`, `receipt_header`, `receipt_footer`, `timestamps`.

---

## 3. Existing Tables Reuse Strategy for Restaurant POS

| Existing Table | Current Purpose | Restaurant POS Extension Plan | Reusability Rating |
| :--- | :--- | :--- | :--- |
| **`dining_tables`** | Physical table status & capacity | Add `floor_id` (FK -> floors), `qr_code_token` (encrypted string), `x_position`, `y_position`. | **100% Core Reuse** |
| **`products`** | Menu items & stock counts | Relate to `recipes` (BOM) and `product_modifiers` without altering existing product columns. | **100% Core Reuse** |
| **`product_categories`** | Product categorizations | Reused directly for digital QR menu and POS categories. | **100% Core Reuse** |
| **`product_units`** | Measurement units | Reused for recipe raw materials (e.g. grams, liters, slices). | **100% Core Reuse** |
| **`sales`** | POS sales & receipts | Extend with `order_type` (`retail`, `dine_in`, `takeaway`, `qr_table`) and relate to `kitchen_tickets`. | **100% Core Reuse** |
| **`sale_items`** | Sales line items | Store `modifiers_json` and `special_instructions` without breaking legacy queries. | **100% Core Reuse** |
| **`seller_employees`** | Staff directory | Use `role` column to distinguish Waiters, Cashiers, and Kitchen Staff. | **100% Core Reuse** |
| **`customers`** | Customer directory | Reused for dine-in guest profiles and CRM loyalty tracking. | **100% Core Reuse** |
| **`business_settings`** | Business configuration | Store KDS auto-refresh timers, QR menu features, and tax settings. | **100% Core Reuse** |
