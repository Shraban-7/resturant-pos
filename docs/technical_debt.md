# Technical Debt & Refactoring Inventory

## 1. High Priority Technical Debt

### 1.1 Un-Atomic Stock Updates & Missing Transactions
- **Issue**: `PosController@addItem`, `PosController@removeItem`, `MenuController@placeOrder`, and `SaleController@addToCart` manipulate `$product->stock_out` directly without transactional safety (`DB::transaction()`).
- **Risk**: Concurrent orders or network hiccups can cause phantom stock decrements, dirty reads, or corrupted cart state.

### 1.2 Direct HTML Rendering inside Controller Methods
- **Issue**: `PosController@addItem` invokes `View::make('components.pos.cart-item', ['item' => $cart_item])->render()` to return HTML strings inside an API JSON response payload.
- **Risk**: Violates separation of concerns, breaks REST API conventions, prevents mobile app consumption, and bloats payload bandwidth.

### 1.3 Untracked Stock Movement Logs
- **Issue**: Stock additions log entries in `ProductStock`, but stock decrements (`stock_out`) do not log individual sale item decrements in a dedicated inventory audit table.
- **Impact**: Makes stock auditing and inventory discrepancy tracing impossible.

---

## 2. Medium & Low Priority Technical Debt

### 2.1 Missing Soft Deletes
- Deleting products, customers, or dining tables uses `$entity->delete()`, breaking relational foreign key historical lookups on old sales receipts.

### 2.2 Lack of Unit & Integration Tests
- Zero test coverage for POS cart calculations, hold order operations, stock decrements, or supplier supply creation.

### 2.3 Hardcoded Currency & Formatting Utilities
- `app/helpers.php` contains hardcoded `currency()` returning `BDT / ৳` without reading dynamic settings from `BusinessSetting`.
