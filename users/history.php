<?php
require_once '../config/config.php';
require_once '../config/functions.php';

requireRole('user');

$user_id = $_SESSION['user_id'];

/* ===== DELETE SELECTED ===== */
if (isset($_POST['delete_selected']) && !empty($_POST['selected'])) {

    $ids = $_POST['selected'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql = "DELETE FROM user_weather_history 
            WHERE search_id IN ($placeholders) 
            AND id = ?";

    $stmt = $pdo->prepare($sql);
    $params = array_merge($ids, [$user_id]);
    $stmt->execute($params);
}

/* ===== CLEAR ALL HISTORY ===== */
if (isset($_POST['clear_all'])) {
    $stmt = $pdo->prepare("
        DELETE FROM user_weather_history
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
}

/* ===== GET HISTORY ===== */
$stmt = $pdo->prepare("
    SELECT * 
    FROM user_weather_history
    WHERE id = ?
    ORDER BY searched_at DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Weather History";
renderHeader($title);
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
    color: white;
    margin-right: 15px;
    text-decoration: none;
}

/* CONTAINER */
.history-container {
    margin: 20px;
    padding: 25px;
    background: white;
    border-left: 6px solid #1e88e5;
    border-radius: 6px;
}

/* SEARCH BAR */
.search-bar {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    margin-top: 10px;
}

/* GRID */
.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

/* CARD */
.card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

/* ICON (API) */
.weather-icon {
    display: block;
    margin: 15px auto 0;
    width: 80px;
}

/* CONTENT */
.card-content {
    padding: 15px;
    text-align: center;
}

.card-content h3 {
    margin: 5px 0;
    color: #1e88e5;
}

/* CHECKBOX */
.card-checkbox {
    position: absolute;
    top: 10px;
    left: 10px;
}

/* BUTTONS */
button {
    padding: 10px 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 15px;
}

.delete-btn {
    background: #e53935;
    color: white;
}

.clear-btn {
    background: #ef6c00;
    color: white;
}

</style>

<div class="nav">
<a href="<?php echo BASE_URL; ?>/user/dashboard.php">Dashboard</a>
<a href="<?php echo BASE_URL; ?>/users/history.php">History</a>
<a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<div class="history-container">

<h2>Your Weather Search History</h2>

<!-- SEARCH BAR -->
<input type="text" id="searchInput" class="search-bar" placeholder="Search location or condition...">

<form method="POST">

<div class="card-grid" id="cardGrid">

<?php if ($history): ?>

<?php foreach ($history as $row): 

    // 🔥 Use stored icon if you have one, otherwise default
    $icon = !empty($row['icon']) ? $row['icon'] : "01d"; 
    $iconUrl = "https://openweathermap.org/img/wn/" . $icon . "@2x.png";

?>

<div class="card" data-search="<?php echo strtolower($row['location'] . ' ' . $row['cond']); ?>">

<input type="checkbox" 
       class="card-checkbox"
       name="selected[]" 
       value="<?php echo $row['search_id']; ?>">

<img src="<?php echo $iconUrl; ?>" class="weather-icon">

<div class="card-content">

<h3><?php echo htmlspecialchars($row['location']); ?></h3>

<p><strong>Date:</strong> <?php echo $row['searched_at']; ?></p>
<p><strong>Temp:</strong> <?php echo $row['temperature']; ?> °C</p>
<p><strong><?php echo htmlspecialchars($row['cond']); ?></strong></p>
<p>Humidity: <?php echo $row['humidity']; ?> %</p>
<p>Wind: <?php echo $row['wind_speed']; ?> m/s</p>
<p><strong>👕 <?php echo htmlspecialchars($row['cloth_rec']); ?></strong></p>

</div>

</div>

<?php endforeach; ?>

<?php else: ?>

<p>No search history found.</p>

<?php endif; ?>

</div>

<br>

<button type="submit" name="delete_selected" class="delete-btn">
Delete Selected
</button>

<button type="submit" name="clear_all" class="clear-btn"
onclick="return confirm('Delete ALL history?');">
Clear All History
</button>

</form>

</div>

<!-- 🔍 SEARCH SCRIPT -->
<script>
document.getElementById('searchInput').addEventListener('keyup', function() {

    let value = this.value.toLowerCase();
    let cards = document.querySelectorAll('.card');

    cards.forEach(card => {
        let text = card.getAttribute('data-search');

        if (text.includes(value)) {
            card.style.display = "block";
        } else {
            card.style.display = "none";
        }
    });

});
</script>

<?php renderFooter(); ?>