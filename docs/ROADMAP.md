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
- **Deployable Value**: Sellers can organize tables by floor zones (e.g., Main Room, Patio) and track status (Free, Occupied, Reserved).
- **Key Deliverables**:
  - Floor zone management CRUD controllers and Blade views.
  - Interactive visual floor map view with real-time status badges.
  - Table reservation booking system.

---

## Milestone 4: Product Modifiers & Recipe BOM Auto-Stock Deductor
- **Goal**: Enable dish modifiers (add-ons/exclusions) and raw ingredient inventory auto-deduction.
- **Deployable Value**: Automatically deducts raw ingredients (flour, meat, cheese) when dishes are sold.
- **Key Deliverables**:
  - Modifiers management UI and POS checkout modal selector.
  - `DeductRecipeStockAction` for Bill of Materials (BOM) raw material deduction.

---

## Milestone 5: Real-Time Kitchen Display System (KDS) & WebSockets
- **Goal**: Deploy Laravel Reverb WebSocket server and touch-screen Kitchen Display System.
- **Deployable Value**: Eliminates paper kitchen tickets with instant touch-screen order cards and prep timers.
- **Key Deliverables**:
  - Installed and configured Laravel Reverb server.
  - Domain broadcast events (`OrderPlacedEvent`, `KitchenStatusUpdatedEvent`, `TableStatusChangedEvent`).
  - Touch-friendly KDS Blade view (`/seller/kds`) with status controls (Start Prep, Ready, Served).

---

## Milestone 6: Enhanced Digital QR Code Menu & Customer Tracker
- **Goal**: Upgrade public QR menu with dish modifier options and smartphone order tracking.
- **Deployable Value**: Customers can order from table QR codes and track meal preparation progress live.
- **Key Deliverables**:
  - Table QR code card generator.
  - Digital menu modifier selector.
  - Real-time customer order tracker view (`/menu/tracker/{token}`).

---

## Milestone 7: Offline-First Service Worker PWA & IndexedDB Sync
- **Goal**: Provide zero-downtime POS checkout during internet outages.
- **Deployable Value**: Cashiers can continue placing orders offline; transactions sync automatically upon reconnection.
- **Key Deliverables**:
  - Service Worker script (`public/sw.js`).
  - IndexedDB storage engine (`public/js/pos-idb.js`) for products & offline order queues.
  - Server-side idempotent offline sync endpoint (`POST /api/seller/pos/offline-sync`).

---

## Milestone 8: Automated Test Suite & CI/CD Deployment Pipeline
- **Goal**: Validate application stability with automated PHPUnit/Pest tests and deployment scripts.
- **Deployable Value**: Guarantees production readiness and regression prevention.
- **Key Deliverables**:
  - Unit tests for `StockService` and `DeductRecipeStockAction`.
  - Feature tests for POS checkout safety, KDS ticket routing, and QR order submission.
  - Docker container configuration and production deployment script.
