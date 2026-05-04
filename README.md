# 🌤️ CommuRide

> A web-based weather forecasting and clothing recommendation system that helps users make smarter daily decisions.

---

## 📖 Overview

CommuRide integrates real-time weather data retrieval with a smart recommendation engine to provide users with:

- 🌦️ **Real-time weather forecasts**
- 📍 **Location-based weather search**
- 👕 **Smart clothing suggestions** based on weather conditions

By combining weather insights with practical outfit recommendations, CommuRide enhances user convenience and day-to-day planning.

---

## ✨ Key Features
 
- 🔍 **Search weather by location** — Look up any city or region instantly
- 🌡️ **Current temperature and conditions** — Live temperature, humidity, and sky conditions
- 👕 **Clothing recommendations** — Outfit suggestions tailored to the weather
- 📊 **Simple, user-friendly interface** — Clean layout designed for fast access
- ⚡ **Real-time data updates** — Always-fresh data pulled from live sources

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Database | MySQL |
| Local Server | XAMPP (Apache + MySQL) |

---

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/CommuRide/CommuRide.git
```

### 2. Move to Server Directory

Copy the project folder into your XAMPP `htdocs` directory:

```
C:/xampp/htdocs/CommuRide
```

### 3. Setup the Database

1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`)
2. Create a new database named:
   ```
   commuride_db
   ```
3. Import the provided `.sql` file from `/db/schema.sql`

### 4. Configure Database Connection

Edit `/config/config.php` with your local credentials:

```php
$host     = "localhost";
$user     = "root";
$password = "";
$database = "commuride_db";
```

### 5. Run the Project

1. Start **Apache** and **MySQL** in XAMPP Control Panel
2. Open your browser and navigate to:
   ```
   http://localhost/CommuRide
   ```

---

## 📊 System Workflow

```
🔍 User enters a location
        ↓
🌐 System fetches weather data from API
   (temperature, condition, humidity, etc.)
        ↓
🌡️ Weather data is processed & categorized
   (hot / cold / rainy / humid)
        ↓
👕 Clothing recommendation is generated
   Hot     → Light clothing
   Rainy   → Jacket / Umbrella
   Cold    → Layered clothing
        ↓
📊 Results displayed to the user
        ↓
🔁 User can search again for another location
```

---

## 🔐 Security

- Passwords are hashed using PHP's `password_hash()` with `PASSWORD_DEFAULT`
- Session-based authentication enforced across all pages
- Role-based access restrictions for Admin, Manager, and User routes

---

## 📁 Project Structure

```
CommuRide/
│
├── admin/
│   └── dashboard.php
│
├── auth/
│   ├── signout.php
│   └── verify-email.php
│
├── manager/
│   └── dashboard.php
│
├── user/
│   ├── dashboard.php
│   └── save-history.php
│
├── users/
│   ├── dashboard.php
│   ├── history.php
│   ├── user-create.php
│   ├── user-delete.php
│   ├── user-update.php
│   └── user-view.php
│
├── assets/
│   └── css/
│       ├── style.css
│       ├── admin.css
│       ├── history.css
│       ├── index.css
│       ├── user-create.css
│       ├── user-forms.css
│       └── user.css
│
├── config/
│   ├── config.php
│   └── functions.php
│
├── db/
│   └── schema.sql
│
├── includes/
│   └── activity-logger.php
│
├── tests/
│   ├── test-access.php
│   ├── test-login.php
│   └── test-mail.php
│
└── index.php          ← Login page (entry point)
```

---

## 🤝 Contributing

Contributions are welcome! Follow these steps:

1. **Fork** the repository
2. **Create** a feature branch
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Commit** your changes
   ```bash
   git commit -m "Add: your feature description"
   ```
4. **Push** to your branch
   ```bash
   git push origin feature/your-feature-name
   ```
5. **Open a Pull Request**

---

## 📄 License

This project is intended for **educational purposes** and may be freely modified or reused.

---

## 👩‍💻 Author

**CommuRide Team**
🔗 GitHub: [https://github.com/CommuRide](https://github.com/CommuRide)
