# API & Web Endpoint Specification

## 1. Authentication Endpoints

| Method | Endpoint | Name | Guard / Middleware | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/login` | `login` | `guest` | Display login view |
| `POST` | `/login` | `login` | `guest` | Authenticate user & start session |
| `GET` | `/logout` | `logout` | `auth` | Terminate session & logout |

---

## 2. Digital QR Menu Endpoints (Public / Customer)

| Method | Endpoint | Name | Guard / Middleware | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/menu/{table}` | `menu.index` | Public | Load menu view for table |
| `POST` | `/menu/{table}/order` | `menu.placeOrder` | Public | Submit customer QR order |

---

## 3. Seller POS Endpoints

| Method | Endpoint | Name | Guard / Middleware | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/seller/dashboard` | `seller.dashboard` | `seller` | Load seller metrics dashboard |
| `GET` | `/seller/pos` | `seller.pos.index` | `seller` | Render main POS interface |
| `POST` | `/seller/pos/item/add` | `seller.pos.addItem` | `seller` | Add item to POS active cart |
| `POST` | `/seller/pos/item/remove` | `seller.pos.removeItem` | `seller` | Remove item from POS cart |
| `POST` | `/seller/pos/item/update-qty` | `seller.pos.updateQuantity` | `seller` | Update quantity in POS cart |
| `POST` | `/seller/pos/checkout` | `seller.pos.checkout` | `seller` | Finalize POS sale & print receipt |
| `POST` | `/seller/pos/hold` | `seller.pos.hold` | `seller` | Place active POS cart on hold |
| `POST` | `/seller/pos/sale-update` | `seller.pos.updateSale` | `seller` | Update an existing sale |
| `POST` | `/seller/pos/sale-item/add` | `seller.pos.saleItem.add` | `seller` | Add item to existing sale |
| `POST` | `/seller/pos/sale-item/remove` | `seller.pos.saleItem.remove` | `seller` | Remove item from existing sale |
| `POST` | `/seller/pos/sale-item/update-qty` | `seller.pos.saleItem.updateQuantity` | `seller` | Update item qty in existing sale |

---

## 4. Sales & Invoicing Endpoints

| Method | Endpoint | Name | Guard / Middleware | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/seller/sales` | `seller.sales.index` | `seller` | List sales history |
| `GET` | `/seller/sales/{sale}/invoice` | `seller.sales.invoice` | `seller` | Render thermal receipt invoice |
| `GET` | `/seller/sales/{sale}/mark-paid` | `seller.sales.mark-paid` | `seller` | Mark due sale as fully paid |

---

## 5. Dining Tables & Staff Endpoints

| Method | Endpoint | Name | Guard / Middleware | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/seller/dining-tables` | `seller.diningTables.index` | `seller` | List dining tables |
| `POST` | `/seller/dining-tables/store` | `seller.diningTables.store` | `seller` | Create new dining table |
| `POST` | `/seller/dining-tables/{table}/update` | `seller.diningTables.update` | `seller` | Edit table details |
| `DELETE` | `/seller/dining-tables/{table}/destroy` | `seller.diningTables.destroy` | `seller` | Delete dining table |
| `GET` | `/seller/employees` | `seller.employees.index` | `seller` | List staff / waiters |
| `POST` | `/seller/employees/store` | `seller.employees.store` | `seller` | Register staff employee |
| `POST` | `/seller/employees/{employee}/update` | `seller.employees.update` | `seller` | Edit employee details |

---

## 6. Supplier Procurement Endpoints

| Method | Endpoint | Name | Guard / Middleware | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/supplier/dashboard` | `supplier.dashboard` | `supplier` | Supplier dashboard |
| `GET` | `/supplier/supply` | `supplier.supply` | `supplier` | Procurement cart & supply creation |
| `POST` | `/supplier/supply/item/add` | `supplier.supply.addItem` | `supplier` | Add item to supply order |
| `POST` | `/supplier/supply/checkout` | `supplier.supply.checkout` | `supplier` | Complete supply invoice |
