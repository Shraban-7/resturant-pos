# System Architecture Specification

## 1. Executive Overview
The **Restaurant POS Application** is built as a multi-tenant / multi-role Laravel 11 monolith, supporting **Seller (Restaurant/POS owner)**, **Supplier (Vendor/Inventory supplier)**, and **Customer/Guest (Digital Menu QR ordering)** domains.

```
+-----------------------------------------------------------------------------------+
|                                 CLIENT LAYER                                      |
|  +------------------------+  +------------------------+  +---------------------+  |
|  | Blade / Tailwind UI    |  | Vanilla JS / Axios POS |  | Mobile QR Digital   |  |
|  | (Admin/Seller/Supplier)|  | Interface & Modals     |  | Menu (Customer)     |  |
|  +------------------------+  +------------------------+  +---------------------+  |
+----------------------------------------+------------------------------------------+
                                         | HTTP / REST
+----------------------------------------v------------------------------------------+
|                                LARAVEL APPLICATION                                |
|  +----------------------+  +-------------------------+  +----------------------+  |
|  | Web & API Routes     |  | Role Middleware Guards  |  | Helper Utilities     |  |
|  | (web.php, supplier)  |  | (seller, supplier, auth)|  | (app/helpers.php)    |  |
|  +----------------------+  +-------------------------+  +----------------------+  |
|                                                                                   |
|  +-----------------------------------------------------------------------------+  |
|  | CONTROLLER LAYER                                                            |  |
|  | App\Http\Controllers\Seller\ (PosController, SaleController, StockController) |  |
|  | App\Http\Controllers\Supplier\ (SupplyController, StockController, etc.)   |  |
|  | App\Http\Controllers\MenuController                                         |  |
|  +-----------------------------------------------------------------------------+  |
|                                                                                   |
|  +-----------------------------------------------------------------------------+  |
|  | ELOQUENT MODEL LAYER & SCOPES                                               |  |
|  | App\Models\ (Sale, Product, DiningTable, Customer, Cart, CartItem, etc.)      |  |
|  | App\Traits\HasCommonScopes (Seller tenant isolation via auth()->id())        |  |
|  +-----------------------------------------------------------------------------+  |
+----------------------------------------+------------------------------------------+
                                         | PDO / SQL
+----------------------------------------v------------------------------------------+
|                              PERSISTENCE & STORAGE                                |
|  +------------------------+  +------------------------+  +---------------------+  |
|  | MySQL / PostgreSQL     |  | Local / Public File    |  | Session & Cache     |  |
|  | Database               |  | Storage (Disk Storage) |  | Framework Engine    |  |
|  +------------------------+  +------------------------+  +---------------------+  |
+-----------------------------------------------------------------------------------+
```

---

## 2. Layered Architecture Breakdown

### 2.1 Request & Routing Layer
- **`routes/web.php`**: Handlers for guest login, digital menu (`/menu/{table}`), and seller domain routes under `/seller/*` protected by `middleware('seller')`.
- **`routes/supplier.php`**: Handlers for supplier domain routes under `/supplier/*` protected by `middleware('supplier')`.
- **`routes/api.php`**: Sanctum API placeholders.

### 2.2 Middleware & Authorization Layer
- **`App\Http\Middleware\SellerMiddleware`**: Verifies user is authenticated and `role === 'seller'`.
- **`App\Http\Middleware\SupplierMiddleware`**: Verifies user is authenticated and `role === 'supplier'`.
- **`HasCommonScopes` Trait**: Appends `where('seller_id', auth()->id())` automatically to model queries via `scopeSelf()` and `scopeSeller()`.

### 2.3 Controller Layer
- Fat controllers containing inline validation, business logic, stock increments/decrements, HTML view rendering, and JSON formatting.
- **Key Controllers**:
  - `PosController`: Cart creation, item adding/removing, stock calculation, checkout, order holding.
  - `SaleController`: Sales listing, invoices, mark paid, cart updates.
  - `MenuController`: Digital QR menu listing & customer order placement.
  - `SupplyController`: Supplier cart & supply invoicing.

### 2.4 Persistence & Domain Models
- Eloquent models with direct attribute accessors (e.g. `availableStock` on `Product`).
- Soft deletes missing across major entities.
- Transaction handling missing in key multi-step operations (e.g., checkout, order placement).

---

## 3. Technology Stack Specification
- **Framework**: Laravel 11.x (PHP 8.2+)
- **Frontend Stack**: Laravel Blade, Vite, TailwindCSS 4.x, Alpine.js 3.x, Axios, Vanilla JS.
- **Database**: MySQL 8.0+ / PostgreSQL.
- **Authentication**: Laravel Sanctum & Session-based Web Guards.
- **Testing Engine**: PHPUnit 11.x / Pest.
