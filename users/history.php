<?php
require_once '../config/config.php';
require_once '../config/functions.php';
requireRole('user');

$title = 'Weather Search History';
renderHeader($title);

$user_id = $_SESSION['user_id'];



// Fetch history
$stmt = $pdo->prepare("SELECT * FROM user_weather_history WHERE id = ? ORDER BY searched_at DESC");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* ===== GENERAL ===== */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f9fb;
    margin: 0;
}

/* ===== NAVIGATION BAR ===== */
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

/* ===== MAIN CONTAINER ===== */
.main-container {
    display: flex;
    min-height: calc(100vh - 60px);
    flex-direction: column;
    padding: 20px;
}

/* ===== HISTORY TABLE CARD ===== */
.history-card {
    background-color: #fff;
    padding: 25px;
    border-radius: 6px;
    border-left: 6px solid #1e88e5;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.history-card h2 {
    color: #1565c0;
    margin-bottom: 15px;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
    min-width: 600px;
}

thead {
    background-color: #1e88e5;
    color: #fff;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

tr:hover {
    background-color: #f1f1f1;
}

/* ===== MOBILE RESPONSIVE TABLE ===== */
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr { display: block; }

    thead { display: none; }

    td {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    td::before {
        content: attr(data-label);
        font-weight: bold;
        width: 50%;
    }
}
</style>

<div class="nav">
    <a href="<?php echo BASE_URL; ?>/user/dashboard.php">Dashboard |</a>
    <a href="<?php echo BASE_URL; ?>/users/history.php">History |</a>
    <a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<div style="padding:20px;">
    <h2>Your Weather Search History</h2>
    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse: collapse;">
        <thead style="background:#1e88e5; color:#fff;">
            <tr>
                <th>Date</th>
                <th>Location</th>
                <th>Temperature (°C)</th>
                <th>Condition</th>
                <th>Humidity (%)</th>
                <th>Wind Speed (m/s)</th>
                <th>Clothing Recommendation</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($history as $h): ?>
            <tr>
                <td><?php echo $h['searched_at']; ?></td>
                <td><?php echo htmlspecialchars($h['location']); ?></td>
                <td><?php echo $h['temperature']; ?></td>
                <td><?php echo htmlspecialchars($h['cond']); ?></td>
                <td><?php echo $h['humidity']; ?></td>
                <td><?php echo $h['wind_speed']; ?></td>
                <td><?php echo htmlspecialchars($h['cloth_rec']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php renderFooter(); ?>