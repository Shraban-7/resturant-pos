# Restaurant POS Architecture Design

## 1. Domain Architecture Overview

```
+-----------------------------------------------------------------------------------+
|                        RESTAURANT POS ADVANCED ARCHITECTURE                       |
+-----------------------------------------------------------------------------------+
|  +-------------------------+  +--------------------------+  +------------------+  |
|  | Multi-Floor & Table     |  | Kitchen Display System   |  | QR Code Self     |  |
|  | Management (Floors/     |  | (KDS - Live Prep Queue,  |  | Digital Menu     |  |
|  | Tables/Reservations)    |  | KOT Tickets, Timers)     |  | (Guest Ordering) |  |
|  +-------------------------+  +--------------------------+  +------------------+  |
|                                                                                   |
|  +-------------------------+  +--------------------------+  +------------------+  |
|  | Product Recipes (BOM)   |  | Product Modifiers &      |  | Realtime Sync    |  |
|  | & Automatic Raw         |  | Add-ons (Variants,       |  | Engine (Laravel  |  |
|  | Material Deduction      |  | Exclusions, Extras)      |  | Reverb WebSockets|  |
|  +-------------------------+  +--------------------------+  +------------------+  |
+-----------------------------------------------------------------------------------+
```

---

## 2. Core Feature Specifications

### 2.1 Multi-Floor & Interactive Table Layout
- **Floors**: Zoning engine allowing sellers to group tables by floor/section (e.g. Ground Floor, Patio, VIP Hall).
- **Table States**: Real-time status indication:
  - `0 = FREE` (Green)
  - `1 = OCCUPIED` (Red - displays elapsed order time & current bill total)
  - `2 = RESERVED` (Yellow - displays reservation customer & time)
  - `3 = CLEANING / DIRTY` (Blue)
- **Reservations**: Customer name, phone, guest count, reserved time window, table association, status (Pending, Confirmed, Seated, Cancelled).

### 2.2 Kitchen Display System (KDS) & Kitchen Order Tickets (KOT)
- **Kitchen Tickets (`kitchen_tickets`)**: Created automatically upon POS checkout or QR order placement.
- **KOT Ticket States**: `pending` -> `preparing` -> `ready` -> `served`.
- **Item Modifiers & Notes**: Displays custom options (e.g. "Medium Rare", "No Onions", "Extra Sauce").
- **KDS View**: High-contrast, touch-friendly grid showing live timers with color alerts for long-pending orders (>15 mins).

### 2.3 Recipe & Bill of Materials (BOM) Auto-Deduction
- **Recipe (`recipes`)**: Associates a menu item (`Product`) with its raw ingredient items.
- **Recipe Ingredients (`recipe_ingredients`)**: Quantities of raw ingredients required per recipe unit (e.g. 1 Burger requires 150g Beef Patty, 1 Bun, 20g Cheese).
- **Auto Stock Deduction**: When an order is completed, the system automatically deducts raw materials from inventory based on BOM recipes.

### 2.4 Product Modifiers & Add-ons
- **Modifiers (`modifiers`)**: Add-ons, toppings, or variants (e.g. Extra Cheese +$1.50, Gluten-Free Crust +$2.00).
- **Product Modifiers (`product_modifiers`)**: Pivot table linking products to allowed modifier groups (e.g. Size, Choice of Dressing, Extra Toppings).

### 2.5 QR Ordering & Self-Checkout
- **QR Token**: Unique cryptographically secure QR code generated per dining table (`menu/{table}?token=...`).
- **Live Sync**: Orders placed via QR immediately stream to the Kitchen Display (KDS) and Cashier POS via Laravel Reverb.
