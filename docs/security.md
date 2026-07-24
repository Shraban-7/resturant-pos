# Security & Authorization Assessment

## 1. Security Baseline

```
+-----------------------------------------------------------------------------------+
|                              SECURITY BOUNDARIES                                  |
+-----------------------------------------------------------------------------------+
| 1. AUTHENTICATION                                                                 |
|    - Web Session Guard (`auth`) for Seller, Supplier, Admin.                      |
|    - Guest Middleware (`guest`) for login page.                                   |
+-----------------------------------------------------------------------------------+
| 2. ROLE-BASED ACCESS CONTROL (RBAC)                                              |
|    - Custom Middleware: `SellerMiddleware`, `SupplierMiddleware`.                 |
|    - Route grouping under `/seller/*` and `/supplier/*`.                          |
+-----------------------------------------------------------------------------------+
| 3. TENANT ISOLATION                                                               |
|    - Global Eloquent Trait `HasCommonScopes` scopes queries via `auth()->id()`.     |
+-----------------------------------------------------------------------------------+
| 4. PUBLIC DIGITAL MENU ACCESS                                                      |
|    - Unauthenticated guest access to `/menu/{table}`.                              |
+-----------------------------------------------------------------------------------+
```

---

## 2. Identified Security Risks & Vulnerabilities

### 2.1 Missing Policy / Ownership Checks on Table QR Ordering
- **Issue**: `/menu/{table}/order` takes a `DiningTable` parameter from the URL route binding without validating if the table is active, belongs to a valid seller, or if the session is legitimate.
- **Risk**: Malicious guests could forge HTTP POST requests to arbitrary table IDs and place unauthorized orders.

### 2.2 Direct ID Bypasses in Cart & Item Operations
- **Issue**: In `PosController@removeItem`, `$cart_item = CartItem::find($request->cart_item_id);` does not check if `$cart_item->cart` belongs to `auth()->id()`.
- **Risk**: A logged-in seller could pass another seller's `cart_item_id` and tamper with their cart items or stock.

### 2.3 Unsafe User Creation / Role Assignment
- **Issue**: Role checks rely on string comparison `$user->role == 'seller'`.
- **Recommendation**: Standardize roles using PHP 8 Enums (`App\Enums\UserRole`) and Laravel Gate/Policy permissions.

### 2.4 CSRF Protection & Input Sanitization
- Blade forms contain `@csrf` directives.
- API endpoints use JSON payloads; input parameter types require strict validation via FormRequests to prevent injection.
