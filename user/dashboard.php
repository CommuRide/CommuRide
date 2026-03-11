<?php
require_once '../config/config.php';
require_once '../config/functions.php';

requireRole('user');

$title = 'Weather Dashboard';
renderHeader($title);
?>

<!-- ===== INLINE CSS ===== -->
<style>
/* ===== RESET ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

/* ===== BODY ===== */
body {
    background-color: #f4f9fb;
    color: #263238;
}

/* ===== NAV ===== */
.nav {
    padding: 15px 25px;
    background-color: #1e88e5;
}

.nav a {
    color: #ffffff;
    text-decoration: none;
    font-weight: 500;
    margin-right: 15px;
}

.nav a:hover {
    text-decoration: underline;
}

/* ===== MAIN LAYOUT ===== */
.main-container {
    display: flex;
    min-height: calc(100vh - 60px);
    gap: 20px;
    padding: 20px;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 280px;
    background-color: #ffffff;
    border-right: 4px solid #26a69a;
    padding: 15px;
    transition: transform 0.3s ease;
}

.sidebar:not(.open) .sidebar-content {
    display: none;
}

.sidebar.open .sidebar-content {
    display: block;
}

/* ===== SIDEBAR TOGGLE BUTTON ===== */
.sidebar-toggle {
    background-color: #26a69a;
    color: #ffffff;
    border: none;
    width: 100%;
    padding: 12px;
    cursor: pointer;
    font-size: 15px;
    font-weight: bold;
    border-radius: 4px;
    margin-bottom: 15px;
}

.sidebar-toggle:hover {
    background-color: #1e88e5;
}

/* ===== SIDEBAR CONTENT ===== */
.sidebar-content h2 {
    color: #1565c0;
    margin-bottom: 15px;
}

.sidebar-content label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

/* ===== INPUTS & SELECT ===== */
input[type="text"],
select {
    width: 100%;
    padding: 9px;
    margin-bottom: 15px;
    border: 1px solid #b2dfdb;
    border-radius: 4px;
}

input:focus,
select:focus {
    outline: none;
    border-color: #26a69a;
}

/* ===== SEARCH BUTTON ===== */
button[type="submit"] {
    background-color: #1e88e5;
    color: #ffffff;
    border: none;
    padding: 10px;
    width: 100%;
    font-size: 14px;
    cursor: pointer;
    border-radius: 4px;
}

button[type="submit"]:hover {
    background-color: #26a69a;
}

/* ===== WEATHER RESULT ===== */
#weatherResult {
    flex: 1;
    background-color: #ffffff;
    padding: 25px;
    border-radius: 6px;
    border-left: 6px solid #1e88e5;
}

#weatherResult h2 {
    color: #1565c0;
    margin-bottom: 10px;
}

#weatherResult p {
    margin-bottom: 8px;
    font-size: 15px;
}

/* ===== WEATHER CARD ===== */
.weather-card {
    background-color: #e0f7fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .main-container {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 4px solid #26a69a;
    }

    #weatherResult {
        margin: 15px 0 0 0;
    }
}
</style>

<!-- ===== DASHBOARD CONTENT ===== -->
<div class="nav">
    <a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<div class="main-container">
    <div class="sidebar">
        <button class="sidebar-toggle">Know your Weather</button>

        <form id="weatherForm" class="sidebar-content">
            <h2>Search Weather</h2>

            <label for="location">Location</label>
            <input type="text" id="location" placeholder="Enter city or campus" required>

            <label for="day">Day</label>
            <select id="day">
                <option value="today">Today</option>
            </select>

            <button type="submit">Search</button>
        </form>
    </div>

    <div id="weatherResult"></div>
</div>

<!-- ===== INLINE JS ===== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.querySelector(".sidebar-toggle");
    const sidebar = document.querySelector(".sidebar");

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener("click", () => {
            sidebar.classList.toggle("open");
        });
    }

    const weatherForm = document.getElementById("weatherForm");
    const locationInput = document.getElementById("location");
    const weatherResult = document.getElementById("weatherResult");

    const apiKey = "4f673f2e68b9bac6f6c679d9dcee250e";

    weatherForm.addEventListener("submit", (event) => {
        event.preventDefault();

        const city = locationInput.value.trim();
        if (!city) {
            weatherResult.innerHTML = "<p>Please enter a location.</p>";
            return;
        }

        weatherResult.innerHTML = "<p>Loading weather...</p>";

        fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`)
            .then(response => response.json())
            .then(data => {
                if (Number(data.cod) !== 200) {
                    weatherResult.innerHTML = "<p>Location not found.</p>";
                    return;
                }

                // Clothing recommendation logic
                let recommendation = "";
                const temp = data.main.temp;

                if (temp >= 30) recommendation = "Light clothes: t-shirt & shorts";
                else if (temp >= 20) recommendation = "Casual clothes, light jacket optional";
                else if (temp >= 10) recommendation = "Wear a jacket";
                else if (temp >= 0) recommendation = "Warm coat, scarf, gloves";
                else recommendation = "Heavy winter coat, gloves, hat";

                const condition = data.weather[0].main.toLowerCase();
                if (condition.includes("rain")) recommendation += " + carry an umbrella";

                // Render weather + recommendation
                weatherResult.innerHTML = `
                    <div class="weather-card">
                        <h2>${data.name}, ${data.sys.country}</h2>
                        <p><strong>Temperature:</strong> ${temp} °C</p>
                        <p><strong>Condition:</strong> ${data.weather[0].description}</p>
                        <p><strong>Humidity:</strong> ${data.main.humidity}%</p>
                        <p><strong>Wind Speed:</strong> ${data.wind.speed} m/s</p>
                        <p><strong>Clothing Recommendation:</strong> ${recommendation}</p>
                    </div>
                `;
            })
            .catch(err => {
                weatherResult.innerHTML = "<p>Error fetching weather data.</p>";
                console.error(err);
            });
    });
});
</script>

<?php renderFooter(); ?>