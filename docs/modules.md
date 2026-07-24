# System Modules Specification

## 1. Core Modules Overview

The application comprises 7 primary functional modules:

```
+-----------------------------------------------------------------------------------+
|                              CORE SYSTEM MODULES                                  |
+-----------------------------------------------------------------------------------+
| 1. AUTHENTICATION & USER MANAGEMENT                                              |
|    - User login & session management                                              |
|    - Role-based routing (Seller, Supplier, Admin)                                 |
|    - Staff/Employee management (Waiters, Cashiers)                                |
+-----------------------------------------------------------------------------------+
| 2. POS & ORDER MANAGEMENT                                                         |
|    - Real-time cart builder & item price calculations                              |
|    - Order hold/resume functionality                                              |
|    - Table assignment & status updates                                            |
|    - Checkout, receipt generation & payment recording                             |
+-----------------------------------------------------------------------------------+
| 3. INVENTORY & STOCK MANAGEMENT                                                   |
|    - Product catalog (Categories, Units, Prices)                                  |
|    - Available stock tracking (`stock_in` - `stock_out`)                          |
|    - Stock addition entry logs (`product_stocks`)                                 |
+-----------------------------------------------------------------------------------+
| 4. DIGITAL QR MENU & CUSTOMER ORDERING                                            |
|    - Table-specific QR public menu view                                           |
|    - Direct customer self-ordering & cart checkout                                |
|    - Automatic table status transition to Occupied                                |
+-----------------------------------------------------------------------------------+
| 5. SUPPLIER & PROCUREMENT MODULE                                                   |
|    - Supplier product catalog management                                          |
|    - Restock procurement carts & order invoicing                                  |
+-----------------------------------------------------------------------------------+
| 6. CUSTOMER & DINING TABLE MANAGEMENT                                             |
|    - Customer profile database & order history                                    |
|    - Table creation, capacity, and status management                              |
+-----------------------------------------------------------------------------------+
| 7. REPORTS & BUSINESS SETTINGS                                                    |
|    - Daily/Monthly sales revenue reports                                          |
|    - Business info, logo, tax rate, and receipt config                            |
+-----------------------------------------------------------------------------------+
```

---

## 2. Detailed Module Breakdown

### 2.1 POS & Order Processing Module
- **Controllers**: `App\Http\Controllers\Seller\PosController`, `App\Http\Controllers\Seller\SaleController`
- **Models**: `Cart`, `CartItem`, `Sale`, `SaleItem`, `DiningTable`
- **Responsibilities**: Handles cash register operations, quick item addition, price overrides, hold orders, checkout, and printing thermal receipts.

### 2.2 Inventory & Product Module
- **Controllers**: `App\Http\Controllers\Seller\ProductController`, `App\Http\Controllers\Seller\StockController`
- **Models**: `Product`, `ProductCategory`, `ProductUnit`, `ProductStock`
- **Responsibilities**: Catalog management, category trees, unit definitions, manual stock entry logs.

### 2.3 Dining Tables & Staff Module
- **Controllers**: `App\Http\Controllers\Seller\DiningTableController`, `App\Http\Controllers\Seller\EmployeeController`
- **Models**: `DiningTable`, `SellerEmployee`
- **Responsibilities**: Managing floor layout tables, capacity, free/occupied status, waiter profiles.

### 2.4 Digital QR Ordering Module
- **Controller**: `App\Http\Controllers\MenuController`
- **Views**: `resources/views/digital-menu.blade.php`
- **Responsibilities**: QR-code based customer menu, guest order submission, live table status locking.

### 2.5 Supplier Procurement Module
- **Controllers**: `App\Http\Controllers\Supplier\*`
- **Models**: `SupplierProduct`, `SupplierProductStock`, `SupplierCart`, `SupplierSale`
- **Responsibilities**: Vendor catalog, restocking orders, B2B invoices.
