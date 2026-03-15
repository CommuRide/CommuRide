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

/* ===== NAV ===== */
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

.nav a:hover { 
text-decoration: underline; 
}

/* ===== MAIN LAYOUT ===== */
.main-container { 
display: flex; 
min-height: calc(100vh - 60px); 
}

/* ===== SIDEBAR ===== */
.sidebar { 
width: 280px;
background-color: #fff; 
border-right: 4px solid #26a69a; 
padding: 15px; 
}

/* Sidebar content always visible */
.sidebar-content {
display: block;
}

.sidebar-content h2 { 
color: #1565c0; 
margin-bottom: 15px; 
}

.sidebar-content label { 
display: block; 
margin-bottom: 5px; 
font-weight: bold; 
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

/* ===== BUTTON ===== */
button[type="submit"] { 
background-color: #1e88e5; 
color: #fff; 
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
background-color: #fff; 
margin: 20px; 
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
margin: 15px; 
}

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

<label for="location">Location</label>
<input type="text" id="location" placeholder="Enter city" required>

<label for="day">Day</label>
<select id="day">
<option value="today">Today</option>
<option value="tomorrow">Tomorrow</option>
<option value="7days">Next 7 Days</option>
</select>

<button type="submit">Search</button>

</form>

</div>

</div>

<!-- WEATHER RESULT -->
<div id="weatherResult">
<h2>Weather Information</h2>
<p>Search a location to see weather details and clothing recommendations.</p>
</div>

</div>

<script>

const weatherForm = document.getElementById("weatherForm");
const locationInput = document.getElementById("location");
const daySelect = document.getElementById("day");
const weatherResult = document.getElementById("weatherResult");

const apiKey = "4f673f2e68b9bac6f6c679d9dcee250e";

weatherForm.addEventListener("submit", function(e){

e.preventDefault();

const city = locationInput.value.trim();
const day = daySelect.value;

if(!city){
weatherResult.innerHTML = "<p>Please enter a location.</p>";
return;
}

weatherResult.innerHTML = "<p>Loading weather...</p>";

/* ===== TODAY WEATHER ===== */

if(day === "today"){

fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`)
.then(res => res.json())
.then(data => {

if(data.cod != 200){
weatherResult.innerHTML = "<p>Location not found.</p>";
return;
}

displayWeather(data.name,data.sys.country,data.main.temp,data.weather[0].description,data.main.humidity,data.wind.speed);

saveHistory(data.name,data.main.temp,data.weather[0].description,data.main.humidity,data.wind.speed);

});

}

/* ===== TOMORROW FORECAST ===== */

else if(day === "tomorrow"){

fetch(`https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric`)
.then(res => res.json())
.then(data => {

const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate()+1);
const tomorrowDate = tomorrow.toISOString().split("T")[0];

const forecast = data.list.find(item => item.dt_txt.includes("12:00:00") && item.dt_txt.startsWith(tomorrowDate));

if(!forecast){
weatherResult.innerHTML = "<p>Forecast unavailable.</p>";
return;
}

displayWeather(data.city.name,data.city.country,forecast.main.temp,forecast.weather[0].description,forecast.main.humidity,forecast.wind.speed);

});

}

/* ===== NEXT 7 DAYS ===== */

else if(day === "7days"){

fetch(`https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric`)
.then(res => res.json())
.then(data => {

let html = `<h2>7 Day Forecast for ${data.city.name}</h2>`;

const daily = {};

data.list.forEach(item => {

const date = item.dt_txt.split(" ")[0];

if(!daily[date]){
daily[date] = item;
}

});

let count = 0;

for(let date in daily){

if(count === 7) break;

const d = daily[date];

const temp = d.main.temp;
const cond = d.weather[0].description;

html += `
<p>
<strong>${date}</strong><br>
Temp: ${temp}°C<br>
Condition: ${cond}
</p>
<hr>
`;

count++;

}

weatherResult.innerHTML = html;

});

}

});

/* ===== WEATHER DISPLAY FUNCTION ===== */

function displayWeather(city,country,temp,cond,humidity,wind){

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
recommendation = "Wear warm clothes (coat, sweater)";
}

weatherResult.innerHTML = `
<h2>${city}, ${country}</h2>
<p><strong>Temperature:</strong> ${temp} °C</p>
<p><strong>Condition:</strong> ${cond}</p>
<p><strong>Humidity:</strong> ${humidity}%</p>
<p><strong>Wind Speed:</strong> ${wind} m/s</p>
<p><strong>Clothing Recommendation:</strong> ${recommendation}</p>
`;

}

/* ===== SAVE SEARCH HISTORY ===== */

function saveHistory(location,temp,cond,humidity,wind){

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
recommendation = "Wear warm clothes (coat, sweater)";
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
cloth_rec:recommendation

})

});

}

</script>

<?php renderFooter(); ?>