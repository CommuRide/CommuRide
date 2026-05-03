🌤️ About

CommuRide is a web-based application that allows users to forecast weather conditions and receive clothing recommendations based on the current weather of a selected location. The system helps users make better daily decisions by suggesting appropriate outfits according to temperature and weather conditions.

📖 Overview

This application integrates weather data retrieval with a recommendation system to provide users with:

🌦️ Real-time weather forecasts
📍 Location-based weather search
👕 Smart clothing suggestions based on weather conditions

By combining weather insights and practical recommendations, the system enhances user convenience and day-to-day planning.
✨ Features
👤 User Registration and Login System
🔐 Secure Password Hashing using bcrypt
🚘 Ride Request / Booking System
🧑‍💼 Role-Based Access Control
Admin
Manager
User
📊 Dashboard for monitoring activity
📝 Activity Logging System
⚙️ User and Ride Management
🛠️ Tech Stack
Frontend: HTML, CSS, JavaScript
Backend: PHP
Database: MySQL
⚙️ Installation
1. Clone the Repository
git clone https://github.com/CommuRide/CommuRide.git
2. Move to Server Directory
XAMPP → htdocs/
3. Setup Database
Open phpMyAdmin
Create a database:
commuride_db
Import the provided .sql file
4. Configure Database

Edit:

/config/config.php
$host = "localhost";
$user = "root";
$password = "";
$database = "commuride_db";
5. Run the Project
Start Apache & MySQL
Open browser:
http://localhost/CommuRide
🔐 Security
Passwords are hashed using password_default
Session-based authentication
Role-based access restrictions
📁 Project Structure
Commuride/
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
│   └── dashboard.php
│   ├── save-history.php
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
│       └── style.css
│       └── admin.css
│       └── history.css
│       └── index.css
│       └── user-create.css
│       └── user-forms.css
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
└── index.php   (login page)
📊 System Workflow
🔍 User enters a location
The user searches for a city or place they want to check
🌐 System fetches weather data
The application sends a request to a weather API
Retrieves real-time data (temperature, condition, humidity, etc.)
🌡️ Weather data is processed
The system analyzes the current weather conditions
Identifies categories (e.g., hot, cold, rainy, humid)
👕 Clothing recommendation is generated
Based on predefined rules (e.g.,
Hot → light clothing
Rainy → jacket/umbrella
Cold → layered clothing)
📊 Results are displayed to the user
Weather details are shown
Suggested outfit recommendations are presented
🔁 User can search again
The process repeats for another location

Contributions are welcome!

Fork the repository
Create a feature branch
Commit your changes
Open a Pull Request
📄 License

This project is for educational purposes and can be modified or reused.

👩‍💻 Author

CommuRide Team
GitHub: https://github.com/CommuRide
