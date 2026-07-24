# User Interface & Frontend Architecture

## 1. Frontend Technology Stack
- **Templating Engine**: Laravel Blade
- **CSS Framework**: TailwindCSS 4.x & Theme SCSS assets (`public/theme/scss/`)
- **JavaScript Engine**: Vanilla JS, Alpine.js 3.x, Axios HTTP client
- **Icons**: Feather Icons, FontAwesome, Themify Icons

---

## 2. Layout Structure & Template Mapping

```
resources/views/
├── layouts/
│   ├── app.blade.php                 <-- Base layout wrapper
│   ├── admin.blade.php               <-- Main dashboard layout with sidebar & header
│   ├── pos.blade.php                 <-- Fullscreen POS layout
│   ├── auth.blade.php                <-- Authentication views layout
│   └── partials/
│       └── admin/
│           ├── head.blade.php
│           ├── navbar.blade.php
│           ├── sidebar.blade.php
│           ├── footer.blade.php
│           └── scripts.blade.php
├── components/
│   ├── alert.blade.php
│   ├── flash-message.blade.php
│   ├── sidebar-list-item.blade.php
│   ├── pos/
│   │   ├── _cart-panel.blade.php     <-- POS cart panel
│   │   ├── _order-chips.blade.php    <-- Order filter chips
│   │   ├── cart-item.blade.php       <-- Individual cart row
│   │   ├── item.blade.php            <-- Product grid item card
│   │   ├── item-modal.blade.php      <-- Quantity & price modal
│   │   ├── navbar.blade.php          <-- POS top navigation bar
│   │   ├── recent-sales-modal.blade.php
│   │   └── sale-item.blade.php
│   └── seller/
│       └── dining-table-card.blade.php <-- Dining table status card
├── seller/
│   ├── pos.blade.php                 <-- Main POS interactive workspace
│   ├── sales/
│   ├── products/
│   ├── dining-tables/
│   ├── employees/
│   └── report/
└── digital-menu.blade.php            <-- Customer public QR ordering interface
```

---

## 3. UI Component Enhancements for Restaurant POS
1. **Interactive Floor & Table Map**: Upgrade `dining-table-card.blade.php` to render real-time color-coded table states (Free: Green, Occupied: Red, Reserved: Yellow).
2. **Kitchen Display System (KDS)**: Add a dedicated high-contrast full-screen view for kitchen staff to manage pending and preparing kitchen order tickets (KOT).
3. **Recipe & Modifier Modal**: Enhance `item-modal.blade.php` to support recipe options, ingredient exclusions, and paid add-ons/modifiers (e.g. Extra Cheese, Spice Level).
