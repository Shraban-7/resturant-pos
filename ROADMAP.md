# Implementation Roadmap

## Overview
This roadmap outlines the multi-stage deployment plan to transform the existing Laravel application into a production-grade **Restaurant POS**. 

Each milestone is **independently deployable**, ensuring zero disruption to existing retail POS and e-commerce operations.

---

## Milestone 1: Technical Debt Remediation & Baseline Safety
- **Goal**: Fix critical architectural risks (N+1 queries, un-atomic stock operations, inline validation) without modifying UI behavior.
- **Deployable Value**: Ensures 100% database transaction safety and eliminates race conditions on POS sales.
- **Key Deliverables**:
  - `FormRequest` classes for POS and Sales endpoints.
  - `StockService` for atomic stock calculations and deductions.
  - Database transaction wrappers around checkout and order placement.
  - Composite performance indexes on `sales`, `sale_items`, and `products`.

---

## Milestone 2: Restaurant Schema Extensions & Domain Models
- **Goal**: Introduce restaurant-specific database tables and Eloquent models without altering existing commercial tables.
- **Status**: COMPLETE (TASK-201–206)
- **Deployable Value**: Establishes schema support for floors, tables, reservations, recipes (BOM), modifiers, and KOT tickets.
- **Key Deliverables**:
  - Migrations for `floors`, `reservations`, `recipes`, `recipe_ingredients`, `modifiers`, `product_modifiers`, `kitchen_tickets`, `kitchen_ticket_items`.
  - Extended `dining_tables` with `floor_id`, QR token, and layout coordinates.
  - Eloquent models with relationships and `scopeSelf()` tenant helpers.

---

## Milestone 3: Multi-Floor & Interactive Table Layout Manager
- **Goal**: Provide sellers with visual table management and multi-floor zoning.
- **Status**: COMPLETE (TASK-301–303)
- **Deployable Value**: Sellers can organize tables by floor zones (e.g., Main Room, Patio), drag tables on a floor map, and manage reservations.
- **Key Deliverables**:
  - Floor zone management CRUD at `/seller/floors`.
  - Interactive visual floor map at `/seller/dining-tables/floor-map` with drag-to-save positions.
  - Reservation booking at `/seller/reservations` with table status sync.

---

## Milestone 4: Product Modifiers & Recipe BOM Auto-Stock Deductor
- **Goal**: Enable dish modifiers (add-ons/exclusions) and raw ingredient inventory auto-deduction.
- **Status**: COMPLETE (TASK-401–403)
- **Deployable Value**: POS can sell configurable add-ons; recipes automatically deduct raw ingredients when dishes are sold.
- **Key Deliverables**:
  - Product modifiers UI at `/seller/products/{product}/modifiers`.
  - POS item modal modifier selection + special instructions.
  - `DeductRecipeStockAction` wired into POS cart mutations and QR `placeOrder`.

---

## Milestone 5: Real-Time Kitchen Display System (KDS) & WebSockets
- **Goal**: Deploy Laravel Reverb WebSocket server and touch-screen Kitchen Display System.
- **Status**: COMPLETE (TASK-501–505)
- **Deployable Value**: Eliminates paper kitchen tickets with instant touch-screen order cards and prep timers.
- **Key Deliverables**:
  - Installed and configured Laravel Reverb server.
  - Domain broadcast events (`OrderPlacedEvent`, `KitchenStatusUpdatedEvent`, `TableStatusChangedEvent`).
  - Touch-friendly KDS Blade view (`/seller/kds`) with status controls (Start Prep, Ready, Served).
  - Echo listeners on KDS and POS for live ticket and ready-status updates.
---

## Milestone 6: Enhanced Digital QR Code Menu & Customer Tracker
- **Goal**: Upgrade public QR menu with dish modifier options and smartphone order tracking.
- **Status**: COMPLETE (TASK-601–603)
- **Deployable Value**: Customers can order from table QR codes and track meal preparation progress live.
- **Key Deliverables**:
  - Table QR code card generator (print/PDF + SVG download).
  - Digital menu modifier selector with special notes.
  - Real-time customer order tracker view (`/menu/tracker/{token}`).

---

## Milestone 7: Offline-First Service Worker PWA & IndexedDB Sync
- **Goal**: Provide zero-downtime POS checkout during internet outages.
- **Status**: COMPLETE (TASK-701–703)
- **Deployable Value**: Cashiers can continue placing orders offline; transactions sync automatically upon reconnection.
- **Key Deliverables**:
  - PWA manifest, offline fallback, and versioned Service Worker app-shell/runtime caching.
  - IndexedDB catalog snapshots, durable offline orders, retry queue, and conflict log—without LocalStorage.
  - Background Sync plus online/visibility fallbacks and POS connectivity/pending-order indicators.
  - Server-side idempotent reconciliation endpoint (`POST /api/seller/pos/offline-sync`) with stock, table, sale, and KOT integration.

---

## Milestone 8: Automated Test Suite & Production Deployment — ✅ COMPLETE
- **Goal**: Validate application stability with automated PHPUnit tests and deployment scripts.
- **Deployable Value**: Guarantees production readiness and regression prevention.
- **Key Deliverables**:
  - ✅ Unit tests for `StockService` and `DeductRecipeStockAction` (`tests/Unit`).
  - ✅ Feature tests for POS checkout transaction safety & KDS ticket creation (`tests/Feature/PosCheckoutTest`, `KdsTicketTest`).
  - ✅ Feature tests for QR order submission, modifier pricing & table status locking (`tests/Feature/QrOrderTest`).
  - ✅ Reconciled fresh-install migrations (kitchen tickets, modifiers, recipes, categories) so the schema matches the models on any new environment.
  - ✅ Multi-stage `Dockerfile` (Node asset build → Composer → PHP-FPM runtime → Nginx) with a hardened `docker-compose.prod.yml` (app, web, reverb, queue worker, scheduler, MySQL, Redis).
  - ✅ Zero-touch `deploy.sh` production deployment script + `.env.production.example`.
- **Result**: `php artisan test` — 29 passing tests (88 assertions).
