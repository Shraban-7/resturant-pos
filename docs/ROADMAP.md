# Implementation Roadmap

## Overview
This roadmap outlines the multi-stage deployment plan to transform the existing Laravel application into a production-grade **Restaurant POS**. 

Each milestone is **independently deployable**, ensuring zero disruption to existing retail POS and e-commerce operations.

---

## Milestone 1: Technical Debt Remediation & Baseline Safety — ✅ COMPLETE
- **Goal**: Fix critical architectural risks (N+1 queries, un-atomic stock operations, inline validation) without modifying UI behavior.
- **Deployable Value**: Ensures 100% database transaction safety and eliminates race conditions on POS sales.
- **Key Deliverables**:
  - `FormRequest` classes for POS and Sales endpoints.
  - `StockService` for atomic stock calculations and deductions.
  - Database transaction wrappers around checkout and order placement.
  - Composite performance indexes on `sales`, `sale_items`, and `products`.

---

## Milestone 2: Restaurant Schema Extensions & Domain Models — ✅ COMPLETE
- **Goal**: Introduce restaurant-specific database tables and Eloquent models without altering existing commercial tables.
- **Deployable Value**: Establishes schema support for floors, tables, reservations, recipes (BOM), modifiers, and KOT tickets.
- **Key Deliverables**:
  - ✅ Migrations for `floors`, `reservations`, `recipes`, `recipe_ingredients`, `modifiers`, `product_modifiers`, `kitchen_tickets`, `kitchen_ticket_items`.
  - ✅ Extended `dining_tables` with `floor_id`, QR token, and layout coordinates.
  - ✅ Eloquent models with relationships, `scopeSelf()` tenant helpers, and `DiningTable::reservations()` inverse.

---

## Milestone 3: Multi-Floor & Interactive Table Layout Manager — ✅ COMPLETE
- **Goal**: Provide sellers with visual table management and multi-floor zoning.
- **Deployable Value**: Sellers can organize tables by floor zones, drag tables on a floor map, and manage reservations with status sync.
- **Key Deliverables**:
  - ✅ Floor zone CRUD at `/seller/floors`.
  - ✅ Interactive visual floor map at `/seller/dining-tables/floor-map` with drag-to-save positions.
  - ✅ Reservation booking at `/seller/reservations` with table status sync (`reserved` / `occupied` / release on cancel).
  - ✅ Table create/edit accepts `floor_id`; status whitelist + per-seller name uniqueness.
  - ✅ `markPaid` tenant isolation + table status persistence fixed.

---

## Milestone 4: Product Modifiers & Recipe BOM Auto-Stock Deductor — ✅ COMPLETE
- **Goal**: Enable dish modifiers (add-ons/exclusions) and raw ingredient inventory auto-deduction.
- **Deployable Value**: POS can sell configurable add-ons; recipes automatically deduct raw ingredients when dishes are sold.
- **Key Deliverables**:
  - ✅ Product modifiers UI at `/seller/products/{product}/modifiers`.
  - ✅ Recipe BOM management UI at `/seller/products/{product}/recipe`.
  - ✅ POS item modal modifier selection + special instructions.
  - ✅ `DeductRecipeStockAction` wired into POS cart mutations, held-sale mutations, and QR `placeOrder`.
  - ✅ POS server-side modifier price validation; recipe dishes allow qty+ when finished stock is 0.

---

## Milestone 5: Real-Time Kitchen Display System (KDS) & WebSockets — ✅ COMPLETE
- **Goal**: Deploy Laravel Reverb WebSocket server and touch-screen Kitchen Display System.
- **Deployable Value**: Eliminates paper kitchen tickets with instant touch-screen order cards and prep timers.
- **Key Deliverables**:
  - ✅ Installed and configured Laravel Reverb server.
  - ✅ Domain broadcast events (`OrderPlacedEvent`, `KitchenStatusUpdatedEvent`, `TableStatusChangedEvent`).
  - ✅ Touch-friendly KDS Blade view (`/seller/kds`) with status controls (Start Prep, Ready, Served).
  - ✅ Echo listeners on KDS and POS for live ticket and ready-status updates.

---

## Milestone 6: Enhanced Digital QR Code Menu & Customer Tracker — ✅ COMPLETE
- **Goal**: Upgrade public QR menu with dish modifier options and smartphone order tracking.
- **Deployable Value**: Customers can order from table QR codes and track meal preparation progress live.
- **Key Deliverables**:
  - ✅ Table QR code card generator (print/PDF + SVG download).
  - ✅ Digital menu modifier selector with special notes.
  - ✅ Real-time customer order tracker view (`/order-status/{order}`).

---

## Milestone 7: Offline-First Service Worker PWA & IndexedDB Sync — ✅ COMPLETE
- **Goal**: Provide zero-downtime POS checkout during internet outages.
- **Deployable Value**: Cashiers can continue placing orders offline; transactions sync automatically upon reconnection.
- **Key Deliverables**:
  - ✅ PWA manifest, offline fallback, and versioned Service Worker app-shell/runtime caching.
  - ✅ IndexedDB catalog snapshots, durable offline orders, retry queue, and conflict log.
  - ✅ Background Sync plus online/visibility fallbacks and POS connectivity/pending-order indicators.
  - ✅ Server-side idempotent reconciliation endpoint (`POST /api/seller/pos/offline-sync`) with stock, table, sale, and KOT integration.

---

## Milestone 8: Automated Test Suite & Production Deployment — ✅ COMPLETE
- **Goal**: Validate application stability with automated PHPUnit tests and deployment scripts.
- **Deployable Value**: Guarantees production readiness and regression prevention.
- **Key Deliverables**:
  - ✅ Unit tests for `StockService` and `DeductRecipeStockAction`.
  - ✅ Feature tests for POS checkout transaction safety & KDS ticket creation.
  - ✅ Feature tests for QR order submission, modifier pricing & table status locking.
  - ✅ Hardened production Docker containerization and deployment configuration scripts.

---

## Milestone 9: Phase 2 Post-MVP Advanced Modules — 🟡 PARTIAL
- **Goal**: Expand dine-in guest management and customer engagement tools.
- **Deployable Value**: Adds restaurant-focused reservation, QR ordering, loyalty, and gift-card tools.
- **Key Deliverables**:
  - ✅ **Table Reservations**: Booking list, guest count tracking, and automatic table status locking.
  - ✅ **QR Table Ordering**: Guest self-ordering with modifier choices and instant KDS ticket dispatch.
  - ❌ **Customer Display Screen (CDS)**: Removed by product decision (not needed).
  - ✅ **Loyalty Program**: Customer points balance tracking, reward tiers (Bronze/Silver/Gold), and manual point adjustments.
  - 🟡 **Gift Cards**: Issuance (`GC-XXXXX`) and expiry-aware verification are implemented; POS redemption remains pending.
  - ❌ **Delivery Management**: Removed because its order-creation path depended on the removed storefront.
  - ❌ **Online Ordering Storefront**: Removed by product decision (out of scope).

---

## Milestone 10: Phase 3 Multi-Branch Expansion — 🟡 PARTIAL
- **Goal**: Support multi-location restaurants under one seller account without SaaS/franchise complexity.
- **Deployable Value**: Owners can run downtown + airport (etc.) branches with scoped POS operations and comparative reporting.
- **Key Deliverables**:
  - ✅ **Multi-Branch Management**: `branches` CRUD, active-branch switcher, `branch_id` on floors/tables/employees/sales/reservations.
  - ✅ **Multi-Branch Reporting**: Date + branch filters and per-branch sales/orders/profit comparison.
  - ⏸️ **Deferred**: SaaS billing, franchise royalties, cross-branch stock transfers, central purchasing, aggregator APIs.
