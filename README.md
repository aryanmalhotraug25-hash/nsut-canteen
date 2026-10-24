# Campus Canteen Pre-Ordering System (DBMS Project)

This project is a Database Management System (DBMS) focused web application designed to eliminate queues during peak lunch hours at the university canteen. It allows students and faculty to pre-order meals, review their cart, simulate a payment, and track order status, while providing canteen staff with a real-time dashboard to manage incoming orders.

## Tech Stack
* **Frontend:** HTML5, CSS3
* **Backend:** PHP (8.x)
* **Database:** MySQL (phpMyAdmin)
* **Server Environment:** XAMPP (Apache + MySQL)

---

## Database Architecture & ER Diagram

The database utilizes a relational structure to ensure data integrity and avoid redundancy. Below is the Entity-Relationship mapping for the four primary tables.

### Entity-Relationship (ER) Mapping

```text
[Users] 1 -------------------- ∞ [Orders]
(user_id)                        (user_id)
                                    | 1
                                    |
                                    | ∞
[Menu_Items] 1 --------------- ∞ [Order_Details]
(item_id)                        (item_id)
```

### Table Definitions & Relationships

| Entity | Primary Key | Foreign Keys | Description |
| :--- | :--- | :--- | :--- |
| **Users** | `user_id` (INT) | None | Stores user profiles. Roles include 'student', 'staff', and 'faculty'. |
| **Menu_Items** | `item_id` (INT) | None | Catalog of available food items. Includes pricing and availability flags. |
| **Orders** | `order_id` (INT) | `user_id` -> Users(`user_id`) | The parent record for a transaction. Tracks total price and overall status (Pending/Ready/Delivered). |
| **Order_Details**| `detail_id` (INT) | `order_id` -> Orders(`order_id`), `item_id` -> Menu_Items(`item_id`) | The junction table resolving the many-to-many relationship between Orders and Menu_Items. |

---

## Local Setup & Execution Guide

Follow these steps to configure the project environment on a local machine.

### 1. Prerequisites
* Download and install [XAMPP](https://www.apachefriends.org/index.html).
* Ensure Git is installed on your machine.

### 2. Server & Code Configuration
1. Open the XAMPP Control Panel and start the **Apache** and **MySQL** modules.
2. Navigate to your XAMPP web directory (`C:\xampp\htdocs` on Windows or `/Applications/XAMPP/xamppfiles/htdocs` on Mac).
3. Clone the repository into the `htdocs` folder:
   ```bash
   git clone https://github.com/YOUR-USERNAME/nsut-canteen.git
   cd nsut-canteen
   ```

### 3. Database Initialization
1. Open a web browser and navigate to `http://localhost/phpmyadmin`.
2. Click **New** in the left-hand sidebar.
3. Create a database named `nsut_canteen_db` and set the collation to `utf8mb4_general_ci`.
4. Select the new `nsut_canteen_db` database, click the **Import** tab at the top.
5. Upload the `nsut_canteen_db.sql` file located in your project folder and click **Import** to generate the schema and test data.
6. Verify the database connection by navigating to `http://localhost/nsut-canteen/db_connect.php`. The page should display "Connected successfully".

---

## Application Flow & Testing Guide

Once the server is running and the database is populated, use the following workflows to test the system's CRUD operations.

### Workflow 1: Placing an Order (Create & Read)
1. Open `http://localhost/nsut-canteen/index.php` in your browser.
2. The page executes a `SELECT` query to fetch active items from the `Menu_Items` table. 
3. Enter quantities for desired items (e.g., 2 Samosas, 1 Cold Coffee) and click **Place Order**.
4. The application routes to `cart.php`, displaying the order summary and calculating the total dynamically based on database prices.
5. Click **Pay via Dummy UPI**. 
6. The system executes `INSERT` queries into both the `Orders` and `Order_Details` tables. A success screen will display your generated Order ID.

### Workflow 2: Staff Order Management (Update)
1. Open a separate browser tab and navigate to `http://localhost/nsut-canteen/dashboard.php`.
2. The dashboard uses a relational `JOIN` query to fetch all orders where `order_status = 'Pending'`. The order placed in Workflow 1 will be visible here.
3. Click the **Mark as Delivered (Clear)** button next to the order.
4. The system executes an `UPDATE` query, changing the `order_status` from 'Pending' to 'Delivered'.
5. The page reloads automatically, and the order is removed from the active queue.

### Workflow 3: Database Verification
1. Return to phpMyAdmin (`http://localhost/phpmyadmin`).
2. Open the `Orders` table to confirm the total price, linked `user_id`, and updated 'Delivered' status.
3. Open the `Order_Details` table to confirm the specific item quantities are correctly mapped to the parent `order_id`.