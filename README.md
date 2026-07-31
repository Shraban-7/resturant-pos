# Restaurant POS

A full-stack restaurant Point of Sale and operations platform for dine-in service, kitchen workflows, inventory, and multi-branch reporting.

Built for restaurants that need a fast cashier terminal, live kitchen tickets, table management, and resilient checkout when the network drops.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Demo Accounts](#demo-accounts)
- [Project Structure](#project-structure)
- [Testing](#testing)
- [Docker](#docker)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)

---

## Features

- **POS terminal** — product grid, modifiers/add-ons, hold orders, table assignment, checkout, and receipts
- **Kitchen Display (KDS)** — real-time KOTs over Laravel Reverb with pending → preparing → ready → served workflow
- **Floors & tables** — multi-floor layout, floor map, occupancy status (`free` / `occupied` / `reserved`)
- **Recipe BOM** — ingredients linked to menu items with automatic stock deduction on sale
- **QR table ordering** — guest menu from table QR codes with live order status tracking
- **Offline PWA** — service worker + IndexedDB queue with idempotent sync when back online
- **Reservations** — bookings with guest count and table locking
- **Loyalty & gift cards** — points/tiers and gift-card issue + verify (POS redemption still evolving)
- **Multi-branch** — branch CRUD, active-branch switcher, scoped operations, comparative reports
- **Supplier portal** — wholesale supply intake and supplier invoices

---

## Tech Stack

| Layer | Stack |
| --- | --- |
| Backend | PHP 8.2+, Laravel 11, Eloquent |
| Frontend | Blade, Alpine.js 3, Tailwind CSS 4, Vite |
| Realtime | Laravel Reverb, Laravel Echo, Pusher protocol |
| Offline | Service Worker (`public/sw.js`), IndexedDB (`public/js/pos-idb.js`) |
| Database | MySQL 8+ / MariaDB |
| Tests | PHPUnit |
| Deploy | Docker / Docker Compose (production compose included) |

---

## Requirements

- PHP 8.2+ (`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `curl`, `bcmath`)
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+ or MariaDB 10.4+

---

## Installation

```bash
git clone https://github.com/Shraban-7/resturant-pos.git
cd resturant-pos

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configure the database (and optionally Reverb) in `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurant_pos
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=reverb
REVERB_APP_ID=local
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

Then:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

---

## Usage

Run the app (three processes in development):

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite
npm run dev

# Terminal 3 — WebSockets (KDS / order status)
php artisan reverb:start
```

Open `http://localhost:8000` (or your Valet/Herd domain).

Useful paths after login as seller:

| Area | Path |
| --- | --- |
| Dashboard | `/seller/dashboard` |
| POS | `/seller/pos` |
| Kitchen Display | `/seller/kds` |
| Sales / invoices | `/seller/sales` |
| Branches | `/seller/branches` |

Fresh migrate + seed anytime:

```bash
php artisan migrate:fresh --seed
```

---

## Demo Accounts

Seeded by `UserSeeder` (password for all: `12345678`):

| Role | Email |
| --- | --- |
| Admin | `admin@gmail.com` |
| Seller | `seller@gmail.com` |
| Supplier | `supplier@gmail.com` |

Change these credentials before any shared or production environment.

---

## Project Structure

```text
app/
  Actions/          # Domain actions (KOT, BOM deduction, modifiers)
  Events/           # Broadcast events (KDS, table status)
  Http/Controllers/ # Auth, Seller, Supplier, Menu/QR
  Models/
  Services/         # StockService and shared services
database/
  migrations/
  seeders/
public/
  sw.js             # Service worker
  js/pos-idb.js     # Offline IndexedDB helpers
resources/
  views/            # Blade UI (POS, KDS, admin, supplier)
  js/               # Alpine / Echo bootstrap
  css/
routes/
  web.php
  api.php           # Offline sync API
  supplier.php
docker/             # Nginx / PHP images
tests/
```

---

## Testing

```bash
php artisan test
```

---

## Docker

Production-oriented compose file:

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

Typical services: app (PHP-FPM), Nginx, Reverb, MySQL, and related workers as defined in the compose file.

---

## Contributing

Contributions are welcome.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-change`)
3. Keep commits focused and readable
4. Run `php artisan test` before opening a PR
5. Open a pull request describing the change and how to verify it

Please avoid committing `.env`, secrets, local docs, or generated IDE files.

---

## License

This project is open source under the [MIT License](LICENSE).

> If a `LICENSE` file is not present in the repo root yet, treat the project as MIT unless stated otherwise by the maintainer.

---

## Author

**Shraban-7**

- GitHub: [Shraban-7](https://github.com/Shraban-7)
- Repository: [resturant-pos](https://github.com/Shraban-7/resturant-pos)
- Email: [shakuatshraban@gmail.com](mailto:shakuatshraban@gmail.com)
