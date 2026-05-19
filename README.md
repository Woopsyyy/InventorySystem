# 🎓 Smart School & Enterprise Inventory System (TI)

A complete, high-end, and production-ready School Inventory and Asset Management system. Built using a robust local stack of **PHP**, **MySQL**, **HTML5**, **Vanilla CSS3**, and **Vanilla JS**.

This system features a premium **glassmorphism user interface**, dynamic real-time responsive analytics, activity audit logs, and a completely **dynamic routing system** designed to run seamlessly on any machine or device on your network without hardcoded directories.

---

## ✨ Features
- **📊 Real-time Dashboard:** Track items, total inventory value, low-stock alerts, and pending borrow requests at a glance.
- **🏷️ Item & Category Management:** Create categories, track suppliers, manage item specifications, and monitor item conditions.
- **🔄 Dynamic Stock Movements:** Log stock-in and stock-out histories with custom explanations and quantities.
- **📋 Asset Borrowing & Returns:** Integrated borrowing workflow, request status tracking, and returns processing.
- **🔔 Low Stock & System Notifications:** Dynamic automatic warning flags when stock falls below warning thresholds.
- **🔒 Multi-Role Authentication:** Dynamic permissions for Administrator, Warehouse Manager, and Staff roles.
- **🖥️ Premium Glassmorphism UI:** Stunning aesthetic featuring smooth dark/light visual contrasts, fluid transitions, and responsive layout.

---

## 🛠️ Installation & Setup (XAMPP)

Follow these simple steps to host this project locally using XAMPP and run it across any device in your local area network (LAN).

### Step 1: Install XAMPP
1. Download and install **XAMPP** for your operating system from the official [Apache Friends website](https://www.apachefriends.org/).
2. During installation, make sure both **Apache** and **MySQL** are checked.

### Step 2: Copy the Project to `htdocs`
For the project to connect properly and run, place the `InventorySystem` directory directly inside the web server root (`htdocs`):
*   **Windows**: `C:\xampp\htdocs\InventorySystem`
*   **macOS**: `/Applications/XAMPP/htdocs/InventorySystem`
*   **Linux**: `/opt/lampp/htdocs/InventorySystem`

> [!IMPORTANT]
> The dynamic path routing in `includes/config.php` automatically resolves the correct URL paths. If you rename the project folder, it will still work perfectly without editing any files.

### Step 3: Run the Services
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

### Step 4: Import the Database
1. Open your web browser and navigate to the local database manager: `http://localhost/phpmyadmin`
2. Click **New** on the left menu to create a new database.
3. Name the database **`school_inventory`** and click **Create**.
4. With the `school_inventory` database selected, click the **Import** tab in the top navigation bar.
5. Click **Choose File** (or Browse) and select the **`database.sql`** file located inside the `InventorySystem` project folder.
6. Scroll to the bottom and click **Import** (or **Go**). All tables and demo data will be created automatically.

---

## 📱 Cross-Device Compatibility & LAN Setup

One of the key features of this codebase is the **Zero Hardcoded Paths** design. The system dynamically computes its base URL path (e.g. `/InventorySystem/`) and maps all assets case-insensitively, meaning you can access this system from **any device** (phones, tablets, other PCs) connected to the same Wi-Fi or Local Network!

### How to Access the System from Other Devices:

1. **Find your Host IP Address**:
   * **On Windows**: Open Command Prompt, type `ipconfig`, press Enter, and look for your **IPv4 Address** (e.g., `192.168.1.15`).
   * **On macOS/Linux**: Open Terminal, type `ifconfig` (or `ip a`), and look for the local IP (e.g., `192.168.1.15` under `en0` or `wlan0`).

2. **Allow Apache through the Firewall**:
   Ensure that the host machine's firewall allows incoming connections on Port 80 (standard HTTP port used by Apache).

3. **Access from Other Devices**:
   Open a web browser on any other device (like your phone or another PC) connected to the same Wi-Fi network and type:
   ```text
   http://<YOUR_HOST_IP>/InventorySystem/
   ```
   *Example:* `http://192.168.1.15/InventorySystem/`

---

## 🔑 Default Credentials

The database comes pre-loaded with three users of different access levels. The password for all accounts is: **`password`**

| Role | Username | Permissions |
| :--- | :--- | :--- |
| **Administrator** | `admin` | Full system control: Manage users, departments, categories, settings, audit logs, and reports. |
| **Warehouse Manager** | `manager` | Manage stock, suppliers, approve borrow requests, log returns, and register damaged items. |
| **Staff** | `staff` | Read-only inventory catalog, request assets, and view borrowing logs. |

> [!TIP]
> After logging in for the first time, it is highly recommended to go to the **Settings** or **Users** page and change the password for security.

---

## 🐳 Docker Deployment (Alternative)

If you prefer using Docker instead of XAMPP:
1. Open a terminal in the project directory.
2. Build and run the containers: `docker-compose up -d`
3. Access the system at: `http://localhost:8080`
