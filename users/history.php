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

/* NAVBAR */

.nav {
padding: 15px 25px;
background-color: #1e88e5;
}

.nav a {
color: white;
text-decoration: none;
margin-right: 15px;
font-weight: 500;
}

.nav a:hover {
text-decoration: underline;
}

/* HISTORY CONTAINER */

.history-container {
margin: 20px;
padding: 25px;
background: white;
border-left: 6px solid #1e88e5;
border-radius: 6px;
}

/* TABLE */

table {
width: 100%;
border-collapse: collapse;
margin-top: 20px;
}

th {
background: #1e88e5;
color: white;
padding: 10px;
text-align: left;
}

td {
padding: 10px;
border-bottom: 1px solid #ddd;
}

tr:hover {
background: #f1f1f1;
}

/* BUTTONS */

button {
padding: 8px 12px;
border: none;
border-radius: 4px;
cursor: pointer;
}

.delete-btn {
background: #e53935;
color: white;
}

.delete-btn:hover {
background: #c62828;
}

.clear-btn {
background: #ef6c00;
color: white;
}

.clear-btn:hover {
background: #d84315;
}

</style>

<div class="nav">
<a href="<?php echo BASE_URL; ?>/user/dashboard.php">Dashboard</a>
<a href="<?php echo BASE_URL; ?>/users/history.php">History</a>
<a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<div class="history-container">

<h2>Your Weather Search History</h2>

<form method="POST">

<table>

<tr>
<th>Select</th>
<th>Date</th>
<th>Location</th>
<th>Temperature</th>
<th>Condition</th>
<th>Humidity</th>
<th>Wind Speed</th>
<th>Clothing Recommendation</th>
</tr>

<?php if ($history): ?>

<?php foreach ($history as $row): ?>

<tr>

<td>
<input type="checkbox" name="selected[]" value="<?php echo $row['search_id']; ?>">
</td>

<td><?php echo $row['searched_at']; ?></td>

<td><?php echo htmlspecialchars($row['location']); ?></td>

<td><?php echo $row['temperature']; ?> °C</td>

<td><?php echo htmlspecialchars($row['cond']); ?></td>

<td><?php echo $row['humidity']; ?> %</td>

<td><?php echo $row['wind_speed']; ?> m/s</td>

<td><?php echo htmlspecialchars($row['cloth_rec']); ?></td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="8">No search history found.</td>
</tr>

<?php endif; ?>

</table>

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

<?php renderFooter(); ?>