# Performance & Query Optimization Specification

## 1. Query Analysis & N+1 Bottlenecks

### 1.1 `PosController@index`
- **Status**: RESOLVED (TASK-106)
- **Fix**: Eager loads products with `category`/`unit`, cart `items.item.unit`, optional sale relations, and recent/running sales with `customer`/`table`/`waiter`.

### 1.2 `SaleController@index`
- **Status**: RESOLVED (TASK-106)
- **Fix**: `Sale::self()->with(['customer', 'items.product', 'table', 'waiter'])->latest('id')->paginate(20)`.

### 1.3 `MenuController@index`
- **Status**: RESOLVED (TASK-106)
- **Fix**: Categories scoped to table `seller_id` with active `products.unit` eager-loaded.

---

## 2. Missing Indexes Matrix

| Table | Target Columns | Index Type | Status |
| :--- | :--- | :--- | :--- |
| `sales` | `(seller_id, is_hold, created_at)` | Composite | Done (TASK-105) |
| `sales` | `(seller_id, dining_table_id)` | Composite | Done (TASK-105) |
| `sales` | `(seller_id, sale_date)` | Composite | Done (TASK-105) |
| `sale_items` | `(sale_id, item_id)` | Composite | Done (TASK-105; live FK is `item_id`) |
| `products` | `(seller_id, is_active, category_id)` | Composite | Done (TASK-105) |
| `dining_tables` | `(seller_id, status)` | Composite | Done (TASK-105) |

Migration: `2026_07_24_232500_add_composite_indexes_to_pos_core_tables.php`.

---

## 3. Caching Strategy Specification
- **Product & Category Cache**: Cache product categories and active menu items per seller in Redis/Memcached/File cache. Flush cache tag `seller_{seller_id}_menu` on product update/create/delete.
- **Business Settings Cache**: Cache business profile settings (`user_id` mapped) to avoid repetitive DB lookups on receipt rendering and header display.
