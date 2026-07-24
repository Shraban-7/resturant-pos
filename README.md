# 🍽️ Restaurant POS & Enterprise Management System

[![Laravel](https://img.shields.io/badge/Laravel-v10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-v8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-v3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-v3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![Laravel Reverb](https://img.shields.io/badge/WebSockets-Reverb-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/docs/11.x/reverb)
[![Offline PWA](https://img.shields.io/badge/PWA-IndexedDB_Sync-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

A production-grade, full-featured **Restaurant Point-of-Sale (POS) & Operational Management System** built with **Laravel, Blade, Alpine.js, TailwindCSS, WebSockets (Laravel Reverb), and Service Worker PWA with IndexedDB offline sync**.

---

## 🌟 Core Features & Module Highlights

### ⚡ Cashier POS Terminal
- **Compact & High-Density Grid**: Responsive multi-column layout (6–8 cards per row on large displays) optimized for rapid order taking.
- **Product Modifiers & Add-ons**: Choose dish variants, extra toppings, exclusions, and custom cooking notes directly from Alpine.js modals.
- **Order Hold & Multi-Cart**: Hold active orders for dine-in tables or takeaway guests and resume instantly.
- **Split Payments & Invoice Printing**: Cash, card, mobile banking, and due management with auto-generated receipt formats.

### 🍳 Touchscreen Kitchen Display System (KDS)
- **Real-Time WebSockets Sync**: Instant kitchen order ticket (KOT) card dispatch powered by **Laravel Reverb**.
- **Bump-Bar Status Workflow**: Interactive ticket status transitions (`Pending` ➔ `Preparing` ➔ `Ready` ➔ `Served`).
- **Prep Time Alerts**: Dynamic timer indicators highlighting overdue orders for kitchen staff.

### 🗺️ Visual Floor Plan & Table Manager
- **Multi-Floor Zoning**: Organize tables across custom floor zones (e.g., Main Hall, Rooftop, Patio, VIP Section).
- **Interactive Visual Floor Map**: HTML5 drag-and-drop table layout designer with real-time coordinate saving.
- **Occupancy Status Locking**: Automatic status synchronization (`Available`, `Occupied`, `Reserved`).

### 📦 Bill of Materials (BOM) & Inventory Auto-Deduction
- **Recipe BOM Management**: Associate raw ingredients (e.g., flour, cheese, meat) with menu items and portion sizes.
- **Automatic Stock Deductor (`DeductRecipeStockAction`)**: Automatically deducts raw ingredients from inventory upon completed or held POS sales.

### 📲 Digital QR Code Menu & Customer Tracker
- **Table Tent QR Generator**: Instant downloadable/printable QR code cards (PDF & SVG) per dining table.
- **Contactless Guest Ordering**: Digital menu allowing guests to select item modifiers and submit orders from their smartphones.
- **Real-Time Order Status Tracker**: Live guest tracking page (`/order-status/{order}`) displaying dish preparation status.

### 📴 Offline-First PWA & IndexedDB Reconciliation
- **Zero-Downtime Service Worker**: Service Worker caching static assets and POS web application shell (`public/sw.js`).
- **IndexedDB Transaction Queue (`pos-idb.js`)**: Queue offline sales safely during internet outages.
- **Idempotent Background Sync**: Server reconciliation endpoint (`POST /api/seller/pos/offline-sync`) syncs offline orders automatically upon reconnection.

### 📅 Table Reservations Engine
- **Booking Calendar**: Manage table bookings with date, time slot, guest counts, and customer details.
- **Auto-Table Locking**: Automatically reserves designated tables and releases them upon checkout or cancellation.

### 🎁 Loyalty Program & Gift Cards
- **Customer Loyalty Points**: Accumulated reward points balance per purchase, reward tier calculations (Bronze, Silver, Gold), and manual point adjustments.
- **Digital Gift Cards**: Issue gift cards with unique codes (`GC-XXXXX`), track balance, verify expiry, and redeem at checkout.

### 🛵 Delivery & Driver Dispatch Management
- **Courier Assignment**: Assign delivery orders to drivers with contact information (`driver_name`, `driver_phone`).
- **Dispatch Lifecycle**: Track delivery progress (`Pending` ➔ `Assigned` ➔ `Out for Delivery` ➔ `Delivered`).

### 🚚 Supplier & Wholesale Module
- **Supplier Portal & Invoicing**: Dedicated supplier interface for managing raw material supplies, supply invoices, and wholesale stock intake.

---

## 🛠️ Technology Stack & Architecture

| Layer | Technologies Used |
| :--- | :--- |
| **Backend Framework** | Laravel 10.x, PHP 8.2+ |
| **Database & ORM** | MySQL / MariaDB, Eloquent ORM with Composite Performance Indexes |
| **Real-Time Engine** | Laravel Reverb (WebSockets), Laravel Echo, Event Broadcasting |
| **Frontend UI** | Laravel Blade, TailwindCSS 3.x, Alpine.js 3.x, Remix Icons |
| **Offline Architecture** | Service Worker PWA API, IndexedDB API (`pos-idb.js`) |
| **Testing & Quality** | PHPUnit Unit & Feature Test Suite, FormRequest Validation |
| **DevOps & Container** | Docker, Docker Compose, Nginx, PHP-FPM 8.2 |

---

## 🚀 Quick Start & Installation Guide

### Prerequisites
- **PHP** >= 8.2 (with `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `cURL` extensions)
- **Composer** >= 2.x
- **Node.js** >= 18.x & **npm**
- **MySQL** >= 8.0 or **MariaDB** >= 10.4

### Step-by-Step Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/Shraban-7/restaurant_pos.git
   cd restaurant_pos
   ```

2. **Install PHP & Node Dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment File**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Set your MySQL database credentials in `.env`:*
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=restaurant_pos
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

5. **Create Storage Symbolic Link**:
   ```bash
   php artisan storage:link
   ```

6. **Start Laravel Reverb WebSocket Server**:
   ```bash
   php artisan reverb:start
   ```

7. **Compile Frontend Assets & Run Local Server**:
   ```bash
   npm run dev
   php artisan serve
   ```
   *Access POS at:* `http://localhost:8000`

---

## 🐳 Production Docker Deployment

Deploy with zero hassle using the multi-stage production Docker setup:

```bash
# Build and start Docker containers in detached mode
docker-compose up -d --build
```

The Docker stack includes:
- **Nginx Web Server** (Port 80 / 443)
- **PHP-FPM App Server** (Laravel Core)
- **Laravel Reverb WebSocket Server** (Port 8080)
- **MySQL Database Server**
- **Redis Cache & Queue Worker**

---

## 🧪 Automated Testing

Execute the comprehensive PHPUnit test suite covering stock calculations, POS checkout transaction safety, KDS event dispatch, and QR table ordering:

```bash
php artisan test
```

---

## 🗺️ Project Roadmap Milestones

- [x] **Milestone 1**: Technical Debt Remediation & Baseline Safety (`StockService`, `DB::transaction()`, Composite Indexes).
- [x] **Milestone 2**: Restaurant Database Schema Extensions (Floors, Tables, Reservations, Recipes, Modifiers, KOT).
- [x] **Milestone 3**: Multi-Floor & Interactive Drag-and-Drop Table Floor Map Manager.
- [x] **Milestone 4**: Product Modifiers & Recipe BOM Auto-Stock Deductor.
- [x] **Milestone 5**: Real-Time Kitchen Display System (KDS) & WebSockets (Laravel Reverb).
- [x] **Milestone 6**: Enhanced Digital QR Code Menu & Customer Order Status Tracker.
- [x] **Milestone 7**: Offline-First Service Worker PWA & IndexedDB Sync Reconciliation.
- [x] **Milestone 8**: Automated Test Suite & Production Docker Deployment Infrastructure.
- [x] **Milestone 9**: Phase 2 Post-MVP Modules (Reservations, Loyalty, Gift Cards, Deliveries).
- [ ] **Milestone 10**: Phase 3 Multi-Branch Enterprise Scaling & API Framework (Planned).

---

## 👨‍💻 Author & Maintainer

**Shraban-7**  
- **Email**: [shakuatshraban@gmail.com](mailto:shakuatshraban@gmail.com)  
- **GitHub**: [Shraban-7](https://github.com/Shraban-7)

---

## 📄 License

This project is open-sourced software licensed under the [MIT License](LICENSE).
