# Code Review Report

## 1. Executive Summary
This report evaluates the existing codebase across **10 technical audit pillars**: SOLID, DRY, Performance, Security, Validation, Transactions, Events, Queues, Policies, and Automated Tests.

---

## 2. Comprehensive Code Review Findings

```
+------------------------------------+----------+-------------------------------------------------------------------+
| Review Pillar                      | Status   | Primary Finding                                                   |
+------------------------------------+----------+-------------------------------------------------------------------+
| 1. SOLID Principles                | FAILED   | SRP & OCP violations; controllers handle business logic & HTML.   |
| 2. DRY Principles                  | FAILED   | Stock deduction & cart total math duplicated in 4 controllers.    |
| 3. Performance                     | WARNING  | N+1 queries in POS & Sales listing; HTML payloads in JSON responses.|
| 4. Security                        | WARNING  | Missing table token verification on QR menu; un-scoped ID lookups. |
| 5. Validation                      | WARNING  | 0% FormRequest usage; inline $request->validate() throughout.      |
| 6. Transactions                    | CRITICAL | Multi-statement checkout & order placements run outside DB transactions.|
| 7. Events                          | ABSENT   | 0% Event usage; no domain events fired on sale/order creation.    |
| 8. Queues                          | ABSENT   | 0% Queue usage; heavy operations execute synchronously.           |
| 9. Policies                        | ABSENT   | 0% Policy usage; authorization relies solely on global middleware.|
| 10. Automated Tests                | CRITICAL | 0% domain test coverage; only framework stubs present.           |
+------------------------------------+----------+-------------------------------------------------------------------+
```

---

### 2.1 SOLID Principles Audit
- **Single Responsibility Principle (SRP) - Violations**:
  - [`PosController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/PosController.php): Violates SRP by combining cart session management, stock availability arithmetic, HTML snippet rendering, order holding, and checkout inside a single class.
  - [`SaleController.php`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/SaleController.php): Violates SRP by combining sales history queries, thermal receipt view rendering, due payment updates, and legacy cart operations.
- **Open/Closed Principle (OCP) - Violations**:
  - Adding new order types (e.g. Dine-In, Takeaway, QR Order) requires directly modifying `PosController@checkout` rather than extending behavior through Action classes or Strategy objects.
- **Dependency Inversion Principle (DIP) - Violations**:
  - High coupling to static facades (`Product::find()`, `View::make()`, `Cart::where()`) instead of injecting interface-backed domain services.

---

### 2.2 DRY (Don't Repeat Yourself) Audit
- **Duplicated Stock Decrements**:
  - Stock availability calculation (`$product->stock_in - $product->stock_out`) and manual increment (`$product->stock_out += $qty`) are duplicated in [`PosController@addItem`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/PosController.php#L67-L84), [`SaleController@addToCart`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Seller/SaleController.php#L57-L113), [`MenuController@placeOrder`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/MenuController.php#L50-L51), and [`SupplyController@checkout`](file:///d:/projects/php_projects/restaurant_pos/app/Http/Controllers/Supplier/SupplyController.php#L80-L120).
- **Duplicated Cart Item Calculation**:
  - Line item total calculation `($quantity * $unit_price) - $discount` is calculated manually across multiple controller methods instead of encapsulating logic inside the `CartItem` / `SaleItem` models.

---

### 2.3 Performance Audit
- **N+1 Query Hazards**:
  - `PosController@index` (line 24): Fetches products via `Product::self()->latest('id')->get()` without eager loading `category` or `unit`.
  - `SaleController@index` (line 19): Eager loads `customer` and `items.product`, but omits `table` (`dining_table_id`) and `waiter` (`seller_employee_id`).
- **Server CPU & Payload Bandwidth Bloat**:
  - `PosController@addItem` invokes `View::make('components.pos.cart-item', ...)->render()` to send compiled HTML strings inside an API JSON response.

---

### 2.4 Security Audit
- **Public QR Route Vulnerability**:
  - `MenuController@placeOrder` accepts any `DiningTable` model ID directly from the URL path without validating an encrypted table session token or verifying table active status.
- **Missing Resource Ownership Scoping**:
  - `PosController@removeItem` fetches `CartItem::find($request->cart_item_id)` without scoping to `auth()->id()`.
- **Loose String Role Equality**:
  - `app/helpers.php` uses loose string comparisons (`$user->role == 'seller'`) rather than strict PHP 8 Enums (`UserRole::SELLER`).

---

### 2.5 Validation Audit
- **0% FormRequest Coverage**:
  - All validation rules are hardcoded inline inside controller action methods using `$request->validate([...])`.
- **Incomplete Validation Rules**:
  - Table capacity and employee phone numbers lack format validation rules.

---

### 2.6 Transactions Audit
- **Non-Atomic Database Operations**:
  - `PosController@checkout`: Performs `Sale` creation, `SaleItem` inserts, `CartItem` deletions, `Cart` reset, and `DiningTable` status updates across multiple standalone SQL statements outside a transaction.
  - `MenuController@placeOrder`: Stock increments, `Sale` creation, `SaleItem` creation, and `DiningTable` state changes execute outside `DB::transaction()`.

---

### 2.7 Events Audit
- **0% Domain Event Usage**:
  - No custom Laravel Event classes (`App\Events\*`) are defined or dispatched when orders are created, status is updated, or tables change state.

---

### 2.8 Queues Audit
- **0% Queue Usage**:
  - No asynchronous Job queue classes (`App\Jobs\*`) are present. Heavy tasks (receipt rendering, supply invoice processing) run synchronously on the HTTP request worker.

---

### 2.9 Policies Audit
- **0% Policy Usage**:
  - No Laravel Policy classes (`App\Policies\*`) exist. Resource authorization relies entirely on global HTTP middleware (`Seller`, `Supplier`).

---

### 2.10 Automated Tests Audit
- **0% Domain Test Coverage**:
  - The codebase contains zero custom unit or feature tests for POS checkout, stock calculations, QR ordering, or supplier supply creation. Only framework default stubs exist in `tests/`.
