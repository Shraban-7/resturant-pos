# Real-Time Architecture Specification (Laravel Reverb)

## 1. Executive Strategy
This document presents the technical design for real-time state synchronization across all restaurant operational surfaces using **Laravel Reverb**.

```
+-----------------------------------------------------------------------------------+
|                        LARAVEL REVERB WEBSOCKET ARCHITECTURE                      |
+-----------------------------------------------------------------------------------+
|  EVENT PRODUCERS                                                                  |
|  - POS Cashier Checkout       - Digital QR Guest Ordering                         |
|  - Kitchen KDS Screen         - Waiter Mobile Station                             |
+----------------------------------------+------------------------------------------+
                                         | Triggers Domain Events
+----------------------------------------v------------------------------------------+
|  LARAVEL EVENT BROADCASTER & QUEUE ENGINE                                         |
|  - ShouldBroadcastNow (Zero-latency events)                                        |
|  - ShouldBroadcast (Queued background events via Redis/Database)                  |
+----------------------------------------+------------------------------------------+
                                         | WebSocket Protocol (Pusher Compatible)
+----------------------------------------v------------------------------------------+
|  LARAVEL REVERB WEBSOCKET SERVER                                                  |
|  Port: 8080 / SSL Port: 443 | Protocol: WS / WSS                                 |
+----------------------------------------+------------------------------------------+
                                         | Broadcasts to Subscribed Channels
+----------------------------------------v------------------------------------------+
|  SUBSCRIBED OPERATIONAL SURFACES                                                  |
|  1. Kitchen Display System (KDS)  --> `private-seller.{sellerId}.kds`             |
|  2. Cashier POS Station           --> `private-seller.{sellerId}.pos`             |
|  3. Interactive Table Floor Map   --> `private-seller.{sellerId}.tables`          |
|  4. Live Staff Presence           --> `presence-seller.{sellerId}.staff`          |
|  5. Customer QR Smartphone Screen --> `private-table.{tableToken}`                |
+-----------------------------------------------------------------------------------+
```

---

## 2. Operational Subsystems Design

### 2.1 Kitchen Display System (KDS)
- **Purpose**: Replaces paper kitchen order tickets with interactive touch screens in the kitchen.
- **Behavior**:
  - Subscribes to `private-seller.{sellerId}.kds`.
  - When `OrderPlacedEvent` fires, a new KOT card slides into the active grid with an audible chime.
  - Cards are color-coded by urgency (Green: <5 mins, Yellow: 5-15 mins, Red: >15 mins).
  - Kitchen staff touch "Start Prep", "Mark Ready", or "Serve" to broadcast status changes.

### 2.2 Cashier Station
- **Purpose**: Displays live running sales, pending table bills, and kitchen ticket status badges.
- **Behavior**:
  - Subscribes to `private-seller.{sellerId}.pos`.
  - Automatically updates running sales tables without requiring manual page refreshes.
  - Displays instant badge updates when kitchen tickets transition to `Ready`.

### 2.3 Customer Order Tracking
- **Purpose**: Gives QR menu customers live visibility into their food preparation progress.
- **Behavior**:
  - Subscribes to `private-table.{tableToken}`.
  - Displays a step-by-step progress tracker: `Order Received` -> `In Kitchen` -> `Food Ready` -> `Served`.

### 2.4 Interactive Table Status Map
- **Purpose**: Provides a live visual layout of all restaurant tables across floors.
- **Behavior**:
  - Subscribes to `private-seller.{sellerId}.tables`.
  - Instantly toggles table status colors (Green: `Free`, Red: `Occupied`, Yellow: `Reserved`, Blue: `Dirty/Cleaning`).

### 2.5 Kitchen Queue Management
- **Purpose**: Manages order prioritization (FIFO vs VIP / Expedited).
- **Behavior**:
  - Automatically queues incoming kitchen order tickets by timestamp.
  - Broadcasts `KitchenTicketUrgentEvent` when preparation time exceeds target thresholds.

### 2.6 Real-Time Notifications Engine
- **Purpose**: Audio chimes and visual toast alerts across all client stations.
- **Behavior**:
  - Triggers sound effects (`kds_new_order.mp3`, `dish_ready.mp3`) and Alpine.js toast popups.

---

## 3. Broadcast Event Specification

### 3.1 `OrderPlacedEvent`
- **Interface**: `ShouldBroadcastNow`
- **Channels**: `private-seller.{seller_id}.kds`, `private-seller.{seller_id}.pos`
- **Payload**:
  ```json
  {
    "order_id": "INV-2607241015-88",
    "sale_id": 142,
    "table_id": 4,
    "table_name": "Table 4 (Ground Floor)",
    "order_type": "dine_in",
    "waiter_name": "Rahim",
    "items": [
      {
        "product_id": 12,
        "name": "Beef Burger",
        "quantity": 2,
        "modifiers": ["Extra Cheese", "No Onions"],
        "special_instructions": "Well done"
      }
    ],
    "created_at": "2026-07-24T22:50:00Z"
  }
  ```

### 3.2 `KitchenStatusUpdatedEvent`
- **Interface**: `ShouldBroadcastNow`
- **Channels**: `private-seller.{seller_id}.pos`, `private-table.{table_token}`
- **Payload**:
  ```json
  {
    "ticket_id": 58,
    "sale_id": 142,
    "table_id": 4,
    "status": "ready",
    "updated_at": "2026-07-24T22:58:00Z"
  }
  ```

### 3.3 `TableStatusChangedEvent`
- **Interface**: `ShouldBroadcastNow`
- **Channels**: `private-seller.{seller_id}.tables`
- **Payload**:
  ```json
  {
    "table_id": 4,
    "status": "occupied",
    "current_sale_id": 142,
    "elapsed_seconds": 480
  }
  ```

### 3.4 `KitchenTicketUrgentEvent`
- **Interface**: `ShouldBroadcast` (Queued)
- **Channels**: `private-seller.{seller_id}.kds`
- **Payload**:
  ```json
  {
    "ticket_id": 58,
    "elapsed_minutes": 16,
    "urgency_level": "CRITICAL"
  }
  ```

---

## 4. Broadcast Channel Authorization (`routes/channels.php`)

| Channel Pattern | Type | Authorization Logic | Purpose |
| :--- | :--- | :--- | :--- |
| `private-seller.{sellerId}.kds` | Private | `$user->seller_id === (int) $sellerId \|\| $user->id === (int) $sellerId` | Authorizes kitchen staff & managers for KDS screen. |
| `private-seller.{sellerId}.pos` | Private | `$user->role === 'seller' && ($user->seller_id === (int) $sellerId \|\| $user->id === (int) $sellerId)` | Authorizes cashier POS access. |
| `private-seller.{sellerId}.tables` | Private | Check user belongs to target seller account. | Authorizes floor map access. |
| `presence-seller.{sellerId}.staff` | Presence| Check user belongs to seller; returns `{id, name, role}`. | Tracks online staff members. |
| `private-table.{tableToken}` | Private | Validates `$tableToken` matches active encrypted table token. | Authorizes QR customer tracking. |

---

## 5. Queue & Infrastructure Architecture
- **Queue Drivers**: Redis / Database.
- **Queue Workers**:
  - High-priority real-time broadcasts utilize `ShouldBroadcastNow` to bypass queue delays.
  - Background alert tasks (e.g. ticket SLA violation monitoring) execute on the `default` Redis queue processed via `php artisan queue:work --queue=high,default`.
