<?php
require_once '../config/config.php';
require_once '../config/functions.php';

requireRole('user');

$title = 'User Dashboard';
renderHeader($title);

$user_id = $_SESSION['user_id'];
?>

<style>
body { 
font-family: Arial, sans-serif; 
background-color: #f4f9fb; 
margin: 0; 
}

/* NAV */
.nav { 
padding: 15px 25px; 
background-color: #1e88e5; 
}

.nav a { 
color: #fff; 
text-decoration: none; 
margin-right: 15px; 
font-weight: 500; 
}

/* MAIN */
.main-container { 
display: flex; 
min-height: calc(100vh - 60px); 
}

/* SIDEBAR */
.sidebar { 
width: 280px;
background-color: #fff; 
border-right: 4px solid #26a69a; 
padding: 15px; 
}

.sidebar-content h2 { 
color: #1565c0; 
margin-bottom: 15px; 
}

/* ===== INPUTS ===== */
/* INPUT AND SELECT SAME SIZE */

input[type="text"],
select {
    width: 100%;
    padding: 9px;
    margin-bottom: 15px;
    border: 1px solid #b2dfdb;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

input:focus, select:focus { 
outline: none; 
border-color: #26a69a; 
}
/* BUTTON */
button[type="submit"] { 
background-color: #1e88e5; 
color: #fff; 
border: none; 
padding: 10px; 
width: 100%; 
cursor: pointer; 
}

/* RESULT */
#weatherResult { 
flex: 1; 
background-color: #fff; 
margin: 20px; 
padding: 25px; 
border-radius: 6px; 
border-left: 6px solid #1e88e5; 
text-align: center;
}

.weather-icon {
    width: 100px;
}

</style>

<div class="nav">
<a href="<?php echo BASE_URL; ?>/users/dashboard.php">Dashboard |</a>
<a href="<?php echo BASE_URL; ?>/users/history.php">History |</a>
<a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<div class="main-container">

<!-- SIDEBAR -->
<div class="sidebar">
<div class="sidebar-content">

<h2>Search Weather</h2>

<form id="weatherForm">

<label>Location</label>
<input type="text" id="location" placeholder="Enter city" required>

<label>Day</label>
<select id="day">
<option value="today">Today</option>
<option value="tomorrow">Tomorrow</option>
<option value="7days">Next 7 Days</option>
</select>

<button type="submit">Search</button>

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

const apiKey = "4f673f2e68b9bac6f6c679d9dcee250e"; // 🔥 replace with your real key

const weatherForm = document.getElementById("weatherForm");
const locationInput = document.getElementById("location");
const daySelect = document.getElementById("day");
const weatherResult = document.getElementById("weatherResult");

/* ===== FORM SUBMIT ===== */
weatherForm.addEventListener("submit", function(e){

e.preventDefault();

const city = locationInput.value.trim();
const day = daySelect.value;

if(!city){
weatherResult.innerHTML = "<p>Please enter a location.</p>";
return;
}

weatherResult.innerHTML = "<p>Loading weather...</p>";

/* ===== TODAY ===== */
if(day === "today"){

fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`)
.then(res => res.json())
.then(data => {

if(data.cod != 200){
weatherResult.innerHTML = "<p>Location not found.</p>";
return;
}

displayWeather(
data.name,
data.sys.country,
data.main.temp,
data.weather[0].description,
data.main.humidity,
data.wind.speed,
data.weather[0].icon // ✅ ICON
);

saveHistory(
data.name,
data.main.temp,
data.weather[0].description,
data.main.humidity,
data.wind.speed,
data.weather[0].icon // ✅ ICON
);

});

}

/* ===== TOMORROW ===== */
else if(day === "tomorrow"){

fetch(`https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric`)
.then(res => res.json())
.then(data => {

const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate()+1);
const tomorrowDate = tomorrow.toISOString().split("T")[0];

const forecast = data.list.find(item =>
item.dt_txt.includes("12:00:00") &&
item.dt_txt.startsWith(tomorrowDate)
);

if(!forecast){
weatherResult.innerHTML = "<p>Forecast unavailable.</p>";
return;
}

displayWeather(
data.city.name,
data.city.country,
forecast.main.temp,
forecast.weather[0].description,
forecast.main.humidity,
forecast.wind.speed,
forecast.weather[0].icon // ✅ ICON
);

});

}

/* ===== 7 DAYS ===== */
else {

fetch(`https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric`)
.then(res => res.json())
.then(data => {

let html = `<h2>7 Day Forecast for ${data.city.name}</h2>`;
const daily = {};

data.list.forEach(item => {
const date = item.dt_txt.split(" ")[0];
if(!daily[date]) daily[date] = item;
});

let count = 0;

for(let date in daily){

if(count === 7) break;

const d = daily[date];
const iconUrl = `https://openweathermap.org/img/wn/${d.weather[0].icon}@2x.png`;

html += `
<p>
<strong>${date}</strong><br>
<img src="${iconUrl}" width="60"><br>
Temp: ${d.main.temp}°C<br>
${d.weather[0].description}
</p>
<hr>
`;

count++;
}

weatherResult.innerHTML = html;

});

}

});

/* ===== DISPLAY ===== */
function displayWeather(city,country,temp,cond,humidity,wind,icon){

let recommendation = "";

if(temp >= 30){
recommendation = "Light clothes (T-shirt, shorts)";
}
else if(temp >= 20){
recommendation = "Comfortable clothes (T-shirt, jeans)";
}
else if(temp >= 10){
recommendation = "Wear a jacket or sweater";
}
else{
recommendation = "Wear warm clothes";
}

const iconUrl = `https://openweathermap.org/img/wn/${icon}@2x.png`;

weatherResult.innerHTML = `
<h2>${city}, ${country}</h2>
<img src="${iconUrl}" class="weather-icon">
<p><strong>${temp} °C</strong></p>
<p>${cond}</p>
<p>Humidity: ${humidity}%</p>
<p>Wind: ${wind} m/s</p>
<p><strong>👕 ${recommendation}</strong></p>
`;

}

/* ===== SAVE HISTORY ===== */
function saveHistory(location,temp,cond,humidity,wind,icon){

let recommendation = "";

if(temp >= 30){
recommendation = "Light clothes (T-shirt, shorts)";
}
else if(temp >= 20){
recommendation = "Comfortable clothes (T-shirt, jeans)";
}
else if(temp >= 10){
recommendation = "Wear a jacket or sweater";
}
else{
recommendation = "Wear warm clothes";
}

fetch('save-history.php',{

method:'POST',

headers:{
'Content-Type':'application/json'
},

body: JSON.stringify({
location:location,
temperature:temp,
cond:cond,
humidity:humidity,
wind_speed:wind,
cloth_rec:recommendation,
icon:icon // ✅ SAVED
})

});

}

</script>

<?php renderFooter(); ?>