# Actionable Task Backlog

> [!IMPORTANT]
> **Task Execution Constraints**:
> - Each task must take **less than 1 day** (< 1.0 day).
> - Each task must be **small, independent, testable, and rollback-safe**.

---

## Milestone 1: Technical Debt Remediation & Baseline Safety
- [x] **TASK-101** [Est: 0.5 day]: Create `App\Http\Requests\Seller\PosAddItemRequest` and `CheckoutPosRequest` to replace inline validation in `PosController`.
- [x] **TASK-102** [Est: 0.5 day]: Create `App\Services\StockService` class to centralize stock availability checks and stock deductions.
- [x] **TASK-103** [Est: 0.5 day]: Wrap `PosController@checkout` and `addItem` operations inside `DB::transaction()` blocks.
- [x] **TASK-104** [Est: 0.5 day]: Wrap `MenuController@placeOrder` operations inside `DB::transaction()` blocks.
- [x] **TASK-105** [Est: 0.5 day]: Create database migration adding composite indexes to `sales`, `sale_items`, `products`, and `dining_tables`.
- [x] **TASK-106** [Est: 0.5 day]: Fix N+1 queries in `PosController@index`, `SaleController@index`, and `MenuController@index` using eager loading.

---

## Milestone 2: Restaurant Database Schema Extensions
- [x] **TASK-201** [Est: 0.5 day]: Create migration `create_floors_table.php` and `Floor` model with seller relationship.
- [x] **TASK-202** [Est: 0.5 day]: Create migration adding `floor_id`, `qr_code_token`, `x_position`, and `y_position` to `dining_tables`.
- [x] **TASK-203** [Est: 0.5 day]: Create migration `create_reservations_table.php` and `Reservation` model.
- [x] **TASK-204** [Est: 0.5 day]: Create migration `create_recipes_and_ingredients_tables.php` and `Recipe` / `RecipeIngredient` models.
- [x] **TASK-205** [Est: 0.5 day]: Create migration `create_modifiers_and_product_modifiers_tables.php` and `Modifier` model.
- [x] **TASK-206** [Est: 0.5 day]: Create migration `create_kitchen_tickets_and_items_tables.php` and `KitchenTicket` models.

---

## Milestone 3: Multi-Floor & Table Floor Plan Manager
- [x] **TASK-301** [Est: 0.5 day]: Implement `FloorController` CRUD methods and routing under `/seller/floors`.
- [x] **TASK-302** [Est: 1.0 day]: Build interactive visual floor plan layout Blade view (`resources/views/seller/dining-tables/floor-map.blade.php`).
- [x] **TASK-303** [Est: 0.5 day]: Build reservation booking management controller and Blade view (`/seller/reservations`).

---

## Milestone 4: Product Modifiers & Recipe BOM Auto-Stock Deductor
- [ ] **TASK-401** [Est: 0.5 day]: Build Product Modifiers management interface under `/seller/products/{product}/modifiers`.
- [ ] **TASK-402** [Est: 0.5 day]: Update POS item checkout modal (`item-modal.blade.php`) to select modifiers and special notes.
- [ ] **TASK-403** [Est: 1.0 day]: Create `App\Actions\DeductRecipeStockAction` for automatic raw ingredient inventory deduction.

---

## Milestone 5: Real-Time Kitchen Display System (KDS) & WebSockets
- [ ] **TASK-501** [Est: 0.5 day]: Install `laravel/reverb` package and configure WebSocket settings in `.env`.
- [ ] **TASK-502** [Est: 0.5 day]: Create broadcast event classes (`OrderPlacedEvent`, `KitchenStatusUpdatedEvent`, `TableStatusChangedEvent`).
- [ ] **TASK-503** [Est: 0.5 day]: Configure channel authorization rules in `routes/channels.php`.
- [ ] **TASK-504** [Est: 1.0 day]: Build touch-screen Kitchen Display System (KDS) view (`resources/views/seller/kds/index.blade.php`).
- [ ] **TASK-505** [Est: 0.5 day]: Wire Alpine.js WebSocket Echo listeners in KDS view and Cashier POS navbar.

---

## Milestone 6: Enhanced Digital QR Code Menu & Customer Tracker
- [ ] **TASK-601** [Est: 0.5 day]: Add QR code card PDF/image generator per table in table settings.
- [ ] **TASK-602** [Est: 0.5 day]: Update `MenuController@index` to load item modifier choices for guest selection.
- [ ] **TASK-603** [Est: 1.0 day]: Build real-time customer order tracking screen (`resources/views/order-status.blade.php`).

---

## Milestone 7: Offline-First Service Worker PWA & IndexedDB Sync
- [ ] **TASK-701** [Est: 0.5 day]: Create Service Worker manifest & static asset caching script (`public/sw.js`).
- [ ] **TASK-702** [Est: 1.0 day]: Implement IndexedDB offline queue manager (`public/js/pos-idb.js`).
- [ ] **TASK-703** [Est: 0.5 day]: Build server-side offline reconciliation sync handler (`POST /api/seller/pos/offline-sync`).

---

## Milestone 8: Automated Test Suite & Production Deployment
- [ ] **TASK-801** [Est: 0.5 day]: Write PHPUnit unit tests for `StockService` and `DeductRecipeStockAction`.
- [ ] **TASK-802** [Est: 0.5 day]: Write PHPUnit feature tests for POS checkout transaction safety & KDS ticket creation.
- [ ] **TASK-803** [Est: 0.5 day]: Write feature tests for QR code order submission & table status locking.
- [ ] **TASK-804** [Est: 0.5 day]: Finalize Docker container configuration and production deployment script.
