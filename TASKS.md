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
- [x] **TASK-401** [Est: 0.5 day]: Build Product Modifiers management interface under `/seller/products/{product}/modifiers`.
- [x] **TASK-402** [Est: 0.5 day]: Update POS item checkout modal (`item-modal.blade.php`) to select modifiers and special notes.
- [x] **TASK-403** [Est: 1.0 day]: Create `App\Actions\DeductRecipeStockAction` for automatic raw ingredient inventory deduction.
- [x] **TASK-404** [Est: 0.5 day]: Build Recipe BOM management UI under `/seller/products/{product}/recipe` and wire held-sale stock path.

---

## Milestone 5: Real-Time Kitchen Display System (KDS) & WebSockets
- [x] **TASK-501** [Est: 0.5 day]: Install `laravel/reverb` package and configure WebSocket settings in `.env`.
- [x] **TASK-502** [Est: 0.5 day]: Create broadcast event classes (`OrderPlacedEvent`, `KitchenStatusUpdatedEvent`, `TableStatusChangedEvent`).
- [x] **TASK-503** [Est: 0.5 day]: Configure channel authorization rules in `routes/channels.php`.
- [x] **TASK-504** [Est: 1.0 day]: Build touch-screen Kitchen Display System (KDS) view (`resources/views/seller/kds/index.blade.php`).
- [x] **TASK-505** [Est: 0.5 day]: Wire Alpine.js WebSocket Echo listeners in KDS view and Cashier POS navbar.

---

## Milestone 6: Enhanced Digital QR Code Menu & Customer Tracker
- [x] **TASK-601** [Est: 0.5 day]: Add QR code card PDF/image generator per table in table settings.
- [x] **TASK-602** [Est: 0.5 day]: Update `MenuController@index` to load item modifier choices for guest selection.
- [x] **TASK-603** [Est: 1.0 day]: Build real-time customer order tracking screen (`resources/views/order-status.blade.php`).

---

## Milestone 7: Offline-First Service Worker PWA & IndexedDB Sync
- [x] **TASK-701** [Est: 0.5 day]: Create Service Worker manifest & static asset caching script (`public/sw.js`).
- [x] **TASK-702** [Est: 1.0 day]: Implement IndexedDB offline queue manager (`public/js/pos-idb.js`).
- [x] **TASK-703** [Est: 0.5 day]: Build server-side offline reconciliation sync handler (`POST /api/seller/pos/offline-sync`).

---

## Milestone 8: Automated Test Suite & Production Deployment
- [x] **TASK-801** [Est: 0.5 day]: Write PHPUnit unit tests for `StockService` and `DeductRecipeStockAction`.
- [x] **TASK-802** [Est: 0.5 day]: Write PHPUnit feature tests for POS checkout transaction safety & KDS ticket creation.
- [x] **TASK-803** [Est: 0.5 day]: Write feature tests for QR code order submission & table status locking.
- [x] **TASK-804** [Est: 0.5 day]: Finalize Docker container configuration and production deployment script.

---

## Milestone 9: Phase 2 Post-MVP Advanced Modules — PARTIAL
- [x] **TASK-901** [Est: 0.5 day]: Table reservation list/booking management, status tracking, and table locking.
- [x] **TASK-902** [Est: 0.5 day]: Guest QR Table ordering, modifier selection, and instant KDS ticket creation.
- [x] **TASK-903** [Est: 0.5 day]: Customer Display Screen (CDS) 2nd screen with live `BroadcastChannel` cart sync.
- [x] **TASK-904** [Est: 0.5 day]: Customer loyalty balances, displayed reward tiers, and manual point adjustments.
- [ ] **TASK-905**: Complete gift-card POS redemption. Issuance, expiry-aware verification, and balance display are implemented.
- [ ] ~~**TASK-906**: Delivery Orders management~~ — removed with the online storefront because no order-creation path remains.
- [ ] ~~**TASK-907**: Online Ordering Storefront portal~~ — removed from scope.

---

## Milestone 10: Phase 3 Future Expansion & Multi-Branch Enterprise Scaling
- [ ] **TASK-1001** [Est: 1.0 day]: Multi-Branch Management Architecture & branch-level data scoping.
- [ ] **TASK-1002** [Est: 1.0 day]: SaaS / Multi-Tenant Isolation & Subscription Plan Billing Engine.
- [ ] **TASK-1003** [Est: 1.0 day]: Centralized Executive Reporting & Multi-Branch Analytics Dashboard.
- [ ] **TASK-1004** [Est: 1.0 day]: Franchise System Support, Fee Tracking, and Royalty Calculation.
- [ ] **TASK-1005** [Est: 1.0 day]: Cross-Branch Inventory Transfer & Inter-Branch Stock Balancing.
- [ ] **TASK-1006** [Est: 1.0 day]: Centralized Purchasing Engine & Bulk Vendor Procurement Dispatch.
- [ ] **TASK-1007** [Est: 1.0 day]: Public Webhook & REST API Integrations (Aggregators, Accounting, Payments).
