<?php
require_once '../config/config.php';
require_once '../config/functions.php';

requireRole('user');

$title = 'User Dashboard';
renderHeader($title);

$user_id = $_SESSION['user_id'];
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user.css">

<div class="nav">
  <a href="<?php echo BASE_URL; ?>/user/dashboard.php">Dashboard</a>
  <a href="<?php echo BASE_URL; ?>/users/history.php">History</a>
  <a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<div class="main-container">

  <!-- SIDEBAR -->
  <div class="sidebar">
    <div class="sidebar-content">

      <h2>Search Weather</h2>

      <form id="weatherForm">

        <label for="location">Location</label>
        <input type="text" id="location" placeholder="Enter city" required>

        <label for="day">Day</label>
        <select id="day">
          <option value="today">Today</option>
          <option value="tomorrow">Tomorrow</option>
          <option value="7days">Next 7 Days</option>
        </select>

        <button type="submit">Search Weather</button>

      </form>

    </div>
  </div>

  <!-- RESULT -->
  <div id="weatherResult">
    <h2>Weather Information</h2>
    <p>Search a location to see weather details.</p>
  </div>

</div>

<script>

const apiKey = "4f673f2e68b9bac6f6c679d9dcee250e";

const weatherForm    = document.getElementById("weatherForm");
const locationInput  = document.getElementById("location");
const daySelect      = document.getElementById("day");
const weatherResult  = document.getElementById("weatherResult");

/* ===== FORM SUBMIT ===== */
weatherForm.addEventListener("submit", function(e) {
  e.preventDefault();

  const city = locationInput.value.trim();
  const day  = daySelect.value;

  if (!city) {
    weatherResult.innerHTML = "<p>Please enter a location.</p>";
    return;
  }

  // Loading state
  weatherResult.innerHTML = `
    <div class="loading-pulse">
      <span></span><span></span><span></span>
    </div>
    <p style="margin-top:16px;opacity:0.6;">Fetching weather data…</p>
  `;

  /* ===== TODAY ===== */
  if (day === "today") {

    fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`)
      .then(res => res.json())
      .then(data => {

        if (data.cod != 200) {
          weatherResult.innerHTML = "<p>Location not found. Please try another city.</p>";
          return;
        }

        displayWeather(
          data.name,
          data.sys.country,
          data.main.temp,
          data.weather[0].description,
          data.main.humidity,
          data.wind.speed,
          data.weather[0].icon
        );

        saveHistory(
          data.name,
          data.main.temp,
          data.weather[0].description,
          data.main.humidity,
          data.wind.speed,
          data.weather[0].icon
        );

      });

  }

  /* ===== TOMORROW ===== */
  else if (day === "tomorrow") {

    fetch(`https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric`)
      .then(res => res.json())
      .then(data => {

        const tomorrow     = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowDate = tomorrow.toISOString().split("T")[0];

        const forecast = data.list.find(item =>
          item.dt_txt.includes("12:00:00") &&
          item.dt_txt.startsWith(tomorrowDate)
        );

        if (!forecast) {
          weatherResult.innerHTML = "<p>Forecast unavailable for tomorrow.</p>";
          return;
        }

        displayWeather(
          data.city.name,
          data.city.country,
          forecast.main.temp,
          forecast.weather[0].description,
          forecast.main.humidity,
          forecast.wind.speed,
          forecast.weather[0].icon
        );

      });

  }

  /* ===== 7 DAYS ===== */
  else {

    fetch(`https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric`)
      .then(res => res.json())
      .then(data => {

        const daily = {};
        data.list.forEach(item => {
          const date = item.dt_txt.split(" ")[0];
          if (!daily[date]) daily[date] = item;
        });

        let cards = '';
        let count = 0;

        for (let date in daily) {
          if (count === 7) break;
          const d       = daily[date];
          const iconUrl = `https://openweathermap.org/img/wn/${d.weather[0].icon}@2x.png`;
          const label   = new Date(date).toLocaleDateString('en-US', { weekday:'short', month:'short', day:'numeric' });

          cards += `
            <div class="forecast-card result-animate" style="animation-delay:${count * 0.06}s">
              <div class="date">${label}</div>
              <img src="${iconUrl}" alt="${d.weather[0].description}">
              <div class="temp">${Math.round(d.main.temp)}°C</div>
              <div class="desc">${d.weather[0].description}</div>
            </div>
          `;
          count++;
        }

        weatherResult.innerHTML = `
          <h2 style="margin-bottom:20px;">7-Day Forecast — ${data.city.name}</h2>
          <div class="forecast-grid">${cards}</div>
        `;

      });

  }

});

/* ===== DISPLAY (Today / Tomorrow) ===== */
function displayWeather(city, country, temp, cond, humidity, wind, icon) {

  const recommendation = getRecommendation(temp);
  const iconUrl        = `https://openweathermap.org/img/wn/${icon}@2x.png`;
  const capCond        = cond.charAt(0).toUpperCase() + cond.slice(1);

  weatherResult.innerHTML = `
    <div class="result-animate">
      <h2>${city}, ${country}</h2>
      <img src="${iconUrl}" class="weather-icon" alt="${cond}">
      <p><strong>${Math.round(temp)}°C</strong></p>
      <p style="font-size:16px;opacity:0.85;text-transform:capitalize;margin-bottom:16px;">${capCond}</p>
      <div class="weather-stats">
        <div class="stat-chip">💧 Humidity: ${humidity}%</div>
        <div class="stat-chip">🌬 Wind: ${wind} m/s</div>
      </div>
      <div class="recommendation">👕 ${recommendation}</div>
    </div>
  `;

}

/* ===== RECOMMENDATION ===== */
function getRecommendation(temp) {
  if (temp >= 30) return "Light clothes — T-shirt & shorts";
  if (temp >= 20) return "Comfortable clothes — T-shirt & jeans";
  if (temp >= 10) return "Bring a jacket or sweater";
  return "Bundle up — wear warm layers";
}

/* ===== SAVE HISTORY ===== */
function saveHistory(location, temp, cond, humidity, wind, icon) {

  fetch('save-history.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      location:    location,
      temperature: temp,
      cond:        cond,
      humidity:    humidity,
      wind_speed:  wind,
      cloth_rec:   getRecommendation(temp),
      icon:        icon
    })
  });

}

</script>

<?php renderFooter(); ?>