# Performance & Query Optimization Specification

## 1. Query Analysis & N+1 Bottlenecks

### 1.1 `PosController@index`
- **Issue**: Fetches `$products = Product::self()->latest('id')->get();` without eager loading `category` or `unit`.
- **Impact**: N+1 queries executed during Blade rendering when outputting product unit names or category titles.

### 1.2 `SaleController@index`
- **Current Query**: `Sale::self()->with('customer', 'items.product')->latest('id')->paginate(20);`
- **Recommendation**: Add eager loading for `table` and `waiter` (`seller_employee_id`) to avoid lazy loading when displaying dining table numbers and server staff names in sales tables.

### 1.3 `MenuController@index`
- **Current Query**: `ProductCategory::with(['products' => fn($q) => $q->where('is_active', 1)])->get();`
- **Recommendation**: Eager load `products.unit` to prevent N+1 queries when formatting item unit descriptions (`$product->unit->short_name`).

---

## 2. Missing Indexes Matrix

| Table | Target Columns | Index Type | Rationale |
| :--- | :--- | :--- | :--- |
| `sales` | `(seller_id, is_hold, created_at)` | Composite Index | Speeds up POS recent and running sales queries |
| `sales` | `(dining_table_id, status)` | Composite Index | Speeds up table active order lookups |
| `sale_items` | `(sale_id, product_id)` | Composite Index | Speeds up sale item aggregations and detail loading |
| `products` | `(seller_id, is_active, category_id)` | Composite Index | Speeds up product catalog rendering on POS & Menu |
| `dining_tables` | `(seller_id, status)` | Composite Index | Speeds up floor view status filtering |

---

## 3. Caching Strategy Specification
- **Product & Category Cache**: Cache product categories and active menu items per seller in Redis/Memcached/File cache. Flush cache tag `seller_{seller_id}_menu` on product update/create/delete.
- **Business Settings Cache**: Cache business profile settings (`user_id` mapped) to avoid repetitive DB lookups on receipt rendering and header display.
