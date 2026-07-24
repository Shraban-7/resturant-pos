# Dependency Graph & Package Architecture

## 1. Class & Model Dependency Matrix

```
                          +-------------------------+
                          |   App\Models\User       |
                          +------------+------------+
                                       |
                                       | (1 to Many)
                                       v
         +-----------------------------+-----------------------------+
         |                             |                             |
         v                             v                             v
+------------------+         +--------------------+         +-------------------+
|  ProductCategory |         |      Product       |         |   DiningTable     |
+------------------+         +---------+----------+         +---------+---------+
                                       |                              |
                                       | (1 to Many)                  | (1 to Many)
                                       v                              v
                             +--------------------+         +-------------------+
                             |     SaleItem       |<--------|       Sale        |
                             +--------------------+         +-------------------+
                                       ^                              ^
                                       |                              |
                                       +------------------------------+
                                            (BelongsTo Relationship)
```

---

## 2. Composer & External Package Dependencies

| Package | Version | Tier | Usage & Role |
| :--- | :--- | :--- | :--- |
| `laravel/framework` | `^11.0` | Core Engine | Base Web Framework & Eloquent ORM |
| `laravel/sanctum` | `^4.0` | Auth Guard | API Token Authentication |
| `guzzlehttp/guzzle` | `^7.8` | HTTP Client | External Web Service Client |
| `laravel/reverb` | *(To install)* | Realtime | WebSocket Server for live KDS & table updates |
| `spatie/laravel-permission` | *(Optional)* | RBAC | Role & Permission authorization matrix |

---

## 3. NPM Frontend Dependencies

| Package | Version | Type | Usage |
| :--- | :--- | :--- | :--- |
| `tailwindcss` | `^4.3.0` | Styles | CSS Utility Framework |
| `alpinejs` | `^3.15.12` | JS Engine | Lightweight client reactivity for modals & cart |
| `axios` | `^1.6.4` | HTTP Client | Asynchronous POS & QR ordering requests |
| `vite` | `^5.0` | Asset Bundler | Frontend module compilation |
