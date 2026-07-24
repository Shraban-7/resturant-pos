# Comprehensive Codebase Analysis Report

## 1. Current Architecture
The application is constructed as a **Laravel 11.x Monolith** following a traditional Model-View-Controller (MVC) pattern with Blade views and asset bundling via Vite.

```
+-----------------------------------------------------------------------------------+
|                                LARAVEL 11 MONOLITH                                |
+-----------------------------------------------------------------------------------+
|  CLIENT / PRESENTATION LAYER                                                      |
|  Blade Templates (resources/views/), Vite (vite.config.js), TailwindCSS 4.x,      |
|  SCSS Theme Assets (public/theme/), Vanilla JS & Axios.                            |
+-----------------------------------------------------------------------------------+
|  HTTP / ROUTING & MIDDLEWARE LAYER                                                 |
|  bootstrap/app.php (Laravel 11 routing & middleware aliases: 'seller', 'supplier') |
|  routes/web.php (Seller & Auth routes), routes/supplier.php (Supplier routes).    |
+-----------------------------------------------------------------------------------+
|  CONTROLLER & LOGIC LAYER                                                         |
|  App\Http\Controllers\Seller\* (PosController, SaleController, StockController)   |
|  App\Http\Controllers\Supplier\* (SupplyController, StockController, etc.)       |
|  App\Http\Controllers\MenuController                                             |
+-----------------------------------------------------------------------------------+
|  PERSISTENCE & DOMAIN LAYER                                                       |
|  App\Models\* (Sale, Product, DiningTable, Customer, Cart, CartItem, User, etc.)   |
|  App\Traits\HasCommonScopes (Active & Date Range filtering scopes)                |
|  app/helpers.php (Global helper functions: apiResponse, is_seller, is_supplier)   |
+-----------------------------------------------------------------------------------+
```

---

## 2. Folder Structure
```
d:\projects\php_projects\restaurant_pos\
├── app/
│   ├── Console/
│   ├── Enums/
│   │   └── UserRole.php
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── LoginController.php
│   │   │   ├── Seller/
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── DiningTableController.php
│   │   │   │   ├── EmployeeController.php
│   │   │   │   ├── PosController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── SaleController.php
│   │   │   │   ├── SettingController.php
│   │   │   │   └── StockController.php
│   │   │   ├── Supplier/
│   │   │   │   ├── CustomerController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── SettingController.php
│   │   │   │   ├── StockController.php
│   │   │   │   └── SupplyController.php
│   │   │   ├── Controller.php
│   │   │   └── MenuController.php
│   │   └── Middleware/
│   │       ├── Seller.php
│   │       └── Supplier.php
│   ├── Models/
│   │   ├── BusinessSetting.php, Cart.php, CartItem.php, Customer.php, DiningTable.php,
│   │   ├── Product.php, ProductCategory.php, ProductStock.php, ProductUnit.php,
│   │   ├── Sale.php, SaleItem.php, SellerEmployee.php, SupplierCart.php, SupplierCartItem.php,
│   │   └── SupplierProduct.php, SupplierProductCategory.php, SupplierProductStock.php,
│   │       SupplierSale.php, SupplierSaleItem.php, User.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   ├── Traits/
│   │   └── HasCommonScopes.php
│   └── helpers.php
├── bootstrap/
│   ├── app.php                            <-- Laravel 11 app & route configuration
│   └── cache/
├── config/
│   ├── app.php, auth.php, database.php, filesystems.php, logging.php, sanctum.php, etc.
├── database/
│   ├── factories/
│   ├── migrations/                        <-- 28 database migration files
│   └── seeders/
├── docs/                                  <-- Markdown documentation reports
├── public/
│   ├── theme/                             <-- Theme CSS, SCSS, fonts, vendors (Bootstrap, Select2, SweetAlert)
│   └── index.php
├── resources/
│   ├── css/
│   ├── js/
│   │   ├── app.js, bootstrap.js, toast.js
│   └── views/
│       ├── auth/
│       ├── components/
│       ├── layouts/
│       ├── seller/
│       ├── supplier/
│       └── digital-menu.blade.php
├── routes/
│   ├── api.php, channels.php, console.php, supplier.php, web.php
├── storage/
├── tests/
│   ├── Feature/ExampleTest.php
│   └── Unit/ExampleTest.php
├── composer.json
├── package.json
├── vite.config.js
├── ROADMAP.md
└── TASKS.md
```

---

## 3. Modules Breakdown
1. **Auth & User Management**: Login, logout, role checking (`is_seller()`, `is_supplier()`).
2. **Seller POS & Sales**: Active cart management, item adding/removing, hold orders, checkout, invoice receipts, sale history.
3. **Inventory & Products**: Product catalog (`products`), Categories (`product_categories`), Units (`product_units`), Stock tracking (`stock_in` - `stock_out`), manual stock entry logs (`product_stocks`).
4. **Dining Tables & Waiter Staff**: Table creation and status management (`DiningTable`), Employee registration (`SellerEmployee`).
5. **Digital QR Ordering**: Public menu (`/menu/{table}`), guest order placement (`placeOrder`), auto-locking table status.
6. **Supplier Procurement**: Supplier products, supplier carts (`supplier_carts`), procurement checkout & invoices (`supplier_sales`).
7. **Business Settings & Reports**: Store profile, currency config (`business_settings`), sales summary reports (`report.index`).

---

## 4. Authentication
- **Driver**: Session-based web authentication (`auth` guard).
- **Entry Points**:
  - `GET /login`: Rendered by `App\Http\Controllers\Auth\LoginController@show`.
  - `POST /login`: Processed by `LoginController@login` via `Auth::attempt(['email' => $request->email, 'password' => $request->password])`.
  - `GET /logout`: Terminated by `LoginController@logout` via `Auth::logout()`.
- **Redirect Logic**:
  - `GET /` redirects authenticated users to `{role}.dashboard` (e.g. `seller.dashboard` or `supplier.dashboard`).

---

## 5. Authorization
- **Middleware Aliases** (registered in `bootstrap/app.php`):
  - `'seller' => App\Http\Middleware\Seller::class`
  - `'supplier' => App\Http\Middleware\Supplier::class`
- **Helper Authorization Guard Functions** (in `app/helpers.php`):
  - `is_seller()`: Returns `true` if `Auth::user()->role === 'seller'`.
  - `is_supplier()`: Returns `true` if `Auth::user()->role === 'supplier'`.
- **Tenant Scope Isolation**:
  - Models implement `scopeSelf($query)` returning `$query->where('seller_id', auth()->id())`.

---

## 6. Database Structure & Entity Relationships
- **`users`**: Multi-role account holders (`role`: `seller`, `supplier`, `admin`).
- **`products`**: Belong to `seller_id`, `category_id`, `unit_id`. Available stock calculated dynamically via `stock_in - stock_out`.
- **`carts` & `cart_items`**: Transient POS cart belonging to `seller_id`, holding active items before checkout.
- **`sales` & `sale_items`**: Finalized or held orders. `sales` stores `order_id`, `seller_id`, `customer_id`, `dining_table_id`, `seller_employee_id`, `subtotal`, `discount`, `payable`, `paid`, `due`, `is_hold`, `status`.
- **`dining_tables`**: Physical tables belonging to `seller_id`, status (0: Free, 1: Occupied, 2: Reserved).
- **`seller_employees`**: Restaurant staff belonging to `seller_id`.
- **`supplier_products`**, `supplier_carts`, `supplier_sales`: Procurement and supply catalog tables.

---

## 7. Existing POS Flow
```
1. Cashier opens /seller/pos
   ├── Loads Seller's active Cart (or creates one with generateOrderId())
   ├── Loads active Products, Categories, Dining Tables, Staff Employees, Recent/Running Sales
2. Item Addition (/seller/pos/item/add)
   ├── Validates order_id, product_id, quantity, unit_price, discount
   ├── Verifies stock availability ($product->availableStock >= quantity)
   ├── Creates CartItem record linked to active Cart
   ├── Increments $product->stock_out += quantity
   └── Renders & returns components.pos.cart-item HTML snippet via JSON response
3. Hold Order (/seller/pos/hold)
   ├── Transfers CartItem rows into a new Sale record with is_hold = 1
   └── Resets active Cart
4. Checkout (/seller/pos/checkout)
   ├── Validates payment_type, paid_amount, customer_id, dining_table_id, employee_id
   ├── Creates Sale record (is_hold = 0, status = 'completed')
   ├── Converts CartItems to SaleItems
   ├── Deletes CartItems & resets Cart
   ├── Marks DiningTable status = 1 (Occupied) if table selected
   └── Returns invoice receipt URL for thermal printing
```

---

## 8. Inventory Flow
- **Stock Tracking Mechanism**:
  - `Product` table maintains two cumulative counter columns: `stock_in` and `stock_out`.
  - `availableStock` accessor returns `($this->stock_in - $this->stock_out)`.
- **Stock Addition**:
  - Handled in `StockController@update`.
  - Creates a history log entry in `product_stocks` (`quantity`, `buying_price`, `selling_price`).
  - Increments `$product->stock_in += quantity`.
- **Stock Reduction**:
  - Incremented on `$product->stock_out` during POS item addition, cart update, or QR menu order placement.

---

## 9. Order Flow
```
+------------------+     +-------------------+     +--------------------+
|  CUSTOMER QR     |     |  RETAIL POS       |     |  POS HOLD ORDER    |
|  MenuController  |     |  PosController    |     |  PosController     |
+--------+---------+     +---------+---------+     +---------+----------+
         |                         |                         |
         v                         v                         v
+-----------------------------------------------------------------------+
|                            sales Table                                |
|  (order_id, seller_id, customer_id, dining_table_id, subtotal, etc.)  |
+-----------------------------------+-----------------------------------+
                                    |
                                    v
+-----------------------------------------------------------------------+
|                          sale_items Table                             |
|  (sale_id, product_id, price, quantity, total_price)                  |
+-----------------------------------------------------------------------+
```

---

## 10. Payment Flow
- **Payment Types**: `cash`, `card`, `mobile_banking` (passed during POS checkout).
- **Payment Balance Tracking**:
  - `payable`: Total bill after discounts and tax.
  - `paid`: Amount rendered by customer.
  - `due`: Amount remaining (`payable - paid`).
- **Mark Paid Workflow**:
  - Cashier calls `GET /seller/sales/{sale}/mark-paid`.
  - Updates `$sale->paid = $sale->payable` and `$sale->due = 0`.
  - If linked to a table, sets `$table->status = 0` (Free).

---

## 11. Shared Services
- **Status**: Currently **None**.
- The application does not contain a `app/Services/` directory. Domain logic resides directly inside Controller methods and helper functions.

---

## 12. Repositories
- **Status**: Currently **None**.
- The application does not use Repository interfaces/classes. Data access is handled directly via Eloquent ORM model calls.

---

## 13. Actions
- **Status**: Currently **None**.
- The application does not contain a `app/Actions/` directory. Business actions are executed inline in Controller methods.

---

## 14. Events
- **Status**: Currently **None**.
- No custom Event classes (`App\Events\*`) or Listeners are registered in the codebase.

---

## 15. Jobs
- **Status**: Currently **None**.
- No asynchronous Job queue classes (`App\Jobs\*`) are present in the codebase.

---

## 16. Blade Layouts
- **`resources/views/layouts/app.blade.php`**: Base HTML shell.
- **`resources/views/layouts/admin.blade.php`**: Main dashboard layout including header navbar, sidebar navigation, and footer partials.
- **`resources/views/layouts/pos.blade.php`**: Full-screen workspace layout for POS cashier station.
- **`resources/views/layouts/auth.blade.php`**: Authentication screen layout wrapper.
- **`resources/views/digital-menu.blade.php`**: Mobile-responsive digital QR menu page.

---

## 17. JavaScript Architecture
- **Vite Asset Pipeline**: Configured in `vite.config.js` bundling `resources/css/app.css` and `resources/js/app.js`.
- **Dependencies** (`package.json`):
  - `alpinejs` (`^3.15.12`): Used for lightweight client reactivity (modal toggling, cart item state).
  - `axios` (`^1.6.4`): Used for asynchronous HTTP requests in POS and digital menu.
  - `tailwindcss` (`^4.3.0`): Utility styling framework.
- **Vendor Scripts** (`public/theme/vendors/`): Includes jQuery, Bootstrap JS, Select2, SweetAlert, and Chart.js for admin dashboard charts.
