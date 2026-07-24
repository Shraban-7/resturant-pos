# Restaurant Database Schema & Migration Design

## 1. Schema Extensions & New Tables

```
+------------------+         +--------------------+         +-------------------+
|     floors       |         |   dining_tables    |         |   reservations    |
+------------------+         +--------------------+         +-------------------+
| id (PK)          |<-------+| id (PK)            |<-------+| id (PK)           |
| seller_id (FK)   |         | floor_id (FK) [NEW]|         | table_id (FK)     |
| name             |         | qr_code_token[NEW] |         | customer_name     |
| priority         |         | status (0,1,2,3)   |         | reservation_time  |
+------------------+         +--------------------+         +-------------------+

+------------------+         +--------------------+         +-------------------+
|    recipes       |         | recipe_ingredients |         |    modifiers      |
+------------------+         +--------------------+         +-------------------+
| id (PK)          |<-------+| id (PK)            |         | id (PK)           |
| product_id (FK)  |         | recipe_id (FK)     |         | seller_id (FK)    |
| instructions     |         | ingredient_id (FK) |         | name              |
+------------------+         | quantity           |         | price             |
                             +--------------------+         +-------------------+

+------------------+         +--------------------+
| kitchen_tickets  |         |kitchen_ticket_items|
+------------------+         +--------------------+
| id (PK)          |<-------+| id (PK)            |
| sale_id (FK)     |         | ticket_id (FK)     |
| table_id (FK)    |         | product_id (FK)    |
| status           |         | quantity           |
+------------------+         | notes / modifiers  |
                             +--------------------+
```

---

## 2. New Database Tables Specification

### 2.1 `floors`
- `id` (bigint, PK, auto_increment)
- `seller_id` (bigint, FK -> users.id, indexed)
- `name` (string) - e.g. "Main Dining Room", "Rooftop Terrace"
- `priority` (integer) - Sort order
- `timestamps`, `softDeletes`

### 2.2 `dining_tables` (Extended)
- Add `floor_id` (bigint, FK -> floors.id, nullable)
- Add `qr_code_token` (string, unique, 64 chars)
- Add `x_position`, `y_position` (integer, for visual floor plan layout)
- Add `softDeletes`

### 2.3 `reservations`
- `id` (bigint, PK)
- `seller_id` (bigint, FK)
- `table_id` (bigint, FK)
- `customer_name`, `customer_phone` (string)
- `guest_count` (integer)
- `reservation_time` (datetime)
- `status` (enum: `pending`, `confirmed`, `seated`, `cancelled`)
- `notes` (text)
- `timestamps`, `softDeletes`

### 2.4 `recipes` & `recipe_ingredients` (Bill of Materials)
- `recipes`: `id`, `product_id` (FK), `preparation_time_minutes`, `timestamps`
- `recipe_ingredients`: `id`, `recipe_id` (FK), `ingredient_product_id` (FK -> products.id), `quantity` (decimal 8,3), `unit_id` (FK)

### 2.5 `modifiers` & `product_modifiers`
- `modifiers`: `id`, `seller_id` (FK), `group_name` (e.g. "Cheese Extras"), `name` (e.g. "Cheddar Cheese"), `price` (decimal 10,2), `timestamps`
- `product_modifiers`: `id`, `product_id` (FK), `modifier_id` (FK), `is_required` (boolean)

### 2.6 `kitchen_tickets` & `kitchen_ticket_items`
- `kitchen_tickets`: `id`, `sale_id` (FK), `table_id` (FK, nullable), `ticket_number` (string), `status` (`pending`, `preparing`, `ready`, `served`), `prepared_at` (timestamp), `timestamps`
- `kitchen_ticket_items`: `id`, `ticket_id` (FK), `sale_item_id` (FK), `product_id` (FK), `quantity` (integer), `modifiers_json` (json), `special_instructions` (string), `status` (`pending`, `preparing`, `ready`)

---

## 3. Database Migration Indexes & Foreign Keys
- `INDEX idx_kitchen_tickets_status (sale_id, status)`
- `INDEX idx_dining_tables_floor_status (seller_id, floor_id, status)`
- `INDEX idx_reservations_time (seller_id, reservation_time, status)`
- `INDEX idx_recipe_ingredients_recipe (recipe_id, ingredient_product_id)`
