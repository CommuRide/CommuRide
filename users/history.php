<?php
require_once '../config/config.php';
require_once '../config/functions.php';

requireRole('user');

$user_id = $_SESSION['user_id'];

/* ===== DELETE SELECTED ===== */
if (isset($_POST['delete_selected']) && !empty($_POST['selected'])) {

    $ids          = $_POST['selected'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $sql  = "DELETE FROM user_weather_history 
             WHERE search_id IN ($placeholders) 
             AND id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($ids, [$user_id]));
}

/* ===== CLEAR ALL HISTORY ===== */
if (isset($_POST['clear_all'])) {
    $stmt = $pdo->prepare("DELETE FROM user_weather_history WHERE id = ?");
    $stmt->execute([$user_id]);
}

/* ===== GET HISTORY ===== */
$stmt = $pdo->prepare("
    SELECT * FROM user_weather_history
    WHERE id = ?
    ORDER BY searched_at DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Weather History";
renderHeader($title);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/history.css">

<div class="nav">
  <a href="<?php echo BASE_URL; ?>/user/dashboard.php">Dashboard</a>
  <a href="<?php echo BASE_URL; ?>/users/history.php">History</a>
  <a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<div class="history-container">

  <h2>Your Weather Search History</h2>

  <input type="text"
         id="searchInput"
         class="search-bar"
         placeholder="Search location or condition…">

  <form method="POST">

    <div class="card-grid" id="cardGrid">

      <?php if ($history): ?>

        <?php foreach ($history as $i => $row):
          $icon    = !empty($row['icon']) ? $row['icon'] : '01d';
          $iconUrl = "https://openweathermap.org/img/wn/{$icon}@2x.png";
          $delay   = $i * 0.05;
        ?>

        <div class="card"
             data-search="<?php echo strtolower($row['location'] . ' ' . $row['cond']); ?>"
             style="animation-delay: <?php echo $delay; ?>s">

          <input type="checkbox"
                 class="card-checkbox"
                 name="selected[]"
                 value="<?php echo $row['search_id']; ?>">

          <img src="<?php echo $iconUrl; ?>"
               class="weather-icon"
               alt="<?php echo htmlspecialchars($row['cond']); ?>">

          <div class="card-content">

            <h3><?php echo htmlspecialchars($row['location']); ?></h3>

            <div class="date-badge">
              <?php echo date('M d, Y · g:i A', strtotime($row['searched_at'])); ?>
            </div>

            <p><strong><?php echo $row['temperature']; ?>°C</strong></p>
            <p><?php echo htmlspecialchars($row['cond']); ?></p>
            <p>💧 Humidity: <?php echo $row['humidity']; ?>%</p>
            <p>🌬 Wind: <?php echo $row['wind_speed']; ?> m/s</p>

            <div class="cloth-rec">
              👕 <?php echo htmlspecialchars($row['cloth_rec']); ?>
            </div>

          </div>

        </div>

        <?php endforeach; ?>

      <?php else: ?>

        <div class="empty-state">
          No search history yet.<br>Head to the dashboard to look up your first city!
        </div>

      <?php endif; ?>

    </div>

    <div class="btn-row">

      <button type="submit" name="delete_selected" class="delete-btn">
        🗑 Delete Selected
      </button>

      <button type="submit" name="clear_all" class="clear-btn"
              onclick="return confirm('Delete ALL history? This cannot be undone.');">
        ✕ Clear All History
      </button>

    </div>

  </form>

</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
  const value = this.value.toLowerCase();
  document.querySelectorAll('.card').forEach(card => {
    const text = card.getAttribute('data-search');
    card.style.display = text.includes(value) ? '' : 'none';
  });
});
</script>

<?php renderFooter(); ?>