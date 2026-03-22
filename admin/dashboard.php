<?php
require_once '../config/config.php';
require_once '../config/functions.php';
require_once '../includes/activity-logger.php';
requireRole('admin');

/* ================= USER STATISTICS ================= */
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'manager'");
$totalManagers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$totalRegularUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE is_verified = 0");
$unverifiedUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

/* ================= ACTIVITY ANALYTICS ================= */
$stmt = $pdo->query("
    SELECT DATE(created_at) as date, COUNT(*) as count
    FROM activity_logs
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$dailyActivity = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT action, COUNT(*) as count
    FROM activity_logs
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY action
    ORDER BY count DESC
    LIMIT 10
");
$actionStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT COALESCE(u.role, 'unknown') as role, COUNT(*) as count
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY role
");
$roleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= WEATHER SEARCH HISTORY ================= */
$stmt = $pdo->query("
    SELECT
        uwh.search_id, u.email, uwh.location,
        uwh.temperature, uwh.cond, uwh.humidity,
        uwh.wind_speed, uwh.cloth_rec, uwh.searched_at
    FROM user_weather_history uwh
    LEFT JOIN users u ON uwh.id = u.id
    ORDER BY uwh.searched_at DESC
    LIMIT 100
");
$searchHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= LOCATION ANALYTICS ================= */
$stmt = $pdo->query("
    SELECT location, COUNT(*) as total
    FROM user_weather_history
    GROUP BY location
    ORDER BY total DESC
    LIMIT 10
");
$locationStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

renderHeader('Admin Dashboard');
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<!-- NAV -->
<div class="nav">
  <a href="<?php echo BASE_URL; ?>/users/dashboard.php">User Management</a>
  <a href="<?php echo BASE_URL; ?>/users/user-create.php">Create User</a>
  <a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<!-- PAGE CONTENT -->
<div class="admin-page">

  <h1>Admin Dashboard</h1>

  <div class="info-box">
    Welcome, <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong>
    &nbsp;·&nbsp; Role: <span class="badge badge-admin">Admin</span>
  </div>

  <!-- ── STAT CARDS ── -->
  <h2>System Statistics</h2>

  <div class="stat-grid">

    <div class="stat-card stat-card-total">
      <h3><?php echo $totalUsers; ?></h3>
      <p>Total Users</p>
    </div>

    <div class="stat-card stat-card-admin">
      <h3><?php echo $totalAdmins; ?></h3>
      <p>Admins</p>
    </div>

    <div class="stat-card stat-card-manager">
      <h3><?php echo $totalManagers; ?></h3>
      <p>Managers</p>
    </div>

    <div class="stat-card stat-card-user">
      <h3><?php echo $totalRegularUsers; ?></h3>
      <p>Regular Users</p>
    </div>

    <div class="stat-card stat-card-unverify">
      <h3><?php echo $unverifiedUsers; ?></h3>
      <p>Unverified</p>
    </div>

  </div>

  <!-- ── CHARTS ── -->
  <h2>Activity Analytics</h2>

  <div class="chart-grid">

    <div class="chart-container">
      <h3>📈 Daily Activity Trend (Last 7 Days)</h3>
      <canvas id="dailyActivityChart"></canvas>
    </div>

    <div class="chart-container">
      <h3>⚡ Top Actions (Last 30 Days)</h3>
      <canvas id="actionChart"></canvas>
    </div>

    <div class="chart-container">
      <h3>📍 Most Searched Locations</h3>
      <canvas id="locationChart"></canvas>
    </div>

  </div>

  <!-- ── HISTORY TABLE ── -->
  <h2>User Weather Search History</h2>

  <div class="table-wrapper">
    <table id="searchHistoryTable" class="display responsive nowrap">
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Location</th>
          <th>Temp</th>
          <th>Condition</th>
          <th>Humidity</th>
          <th>Wind</th>
          <th>Clothing</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($searchHistory as $row):
          // Temperature colour class
          $temp = (float) $row['temperature'];
          $tempClass = $temp < 10 ? 'temp-cold' : ($temp < 20 ? 'temp-cool' : ($temp < 30 ? 'temp-warm' : 'temp-hot'));
        ?>
        <tr>
          <td><?php echo $row['search_id']; ?></td>
          <td><?php echo htmlspecialchars($row['email']); ?></td>
          <td><?php echo htmlspecialchars($row['location']); ?></td>
          <td class="<?php echo $tempClass; ?>"><?php echo $row['temperature']; ?>°C</td>
          <td><?php echo htmlspecialchars($row['cond']); ?></td>
          <td><?php echo $row['humidity']; ?>%</td>
          <td><?php echo $row['wind_speed']; ?> km/h</td>
          <td><?php echo htmlspecialchars($row['cloth_rec']); ?></td>
          <td><?php echo date('M d, Y · g:i A', strtotime($row['searched_at'])); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div><!-- /.admin-page -->


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
/* ── SHARED CHART DEFAULTS ── */
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.font.size   = 13;
Chart.defaults.color       = '#4a6a7d';

const gridColor  = 'rgba(21,113,159,0.08)';
const tickColor  = '#8aa3b0';

/* ── DAILY ACTIVITY LINE CHART ── */
const dailyData = <?php echo json_encode($dailyActivity); ?>;

new Chart(document.getElementById('dailyActivityChart'), {
  type: 'line',
  data: {
    labels: dailyData.map(d => {
      const dt = new Date(d.date);
      return dt.toLocaleDateString('en-US', { month:'short', day:'numeric' });
    }),
    datasets: [{
      label: 'Activities',
      data: dailyData.map(d => d.count),
      borderColor: '#528ab4',
      backgroundColor: 'rgba(82,138,180,0.10)',
      borderWidth: 2.5,
      pointBackgroundColor: '#15719f',
      pointRadius: 5,
      pointHoverRadius: 7,
      fill: true,
      tension: 0.4
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: tickColor } },
      y: { grid: { color: gridColor }, ticks: { color: tickColor }, beginAtZero: true }
    }
  }
});

/* ── TOP ACTIONS BAR CHART ── */
const actionData = <?php echo json_encode($actionStats); ?>;

new Chart(document.getElementById('actionChart'), {
  type: 'bar',
  data: {
    labels: actionData.map(d => d.action),
    datasets: [{
      label: 'Count',
      data: actionData.map(d => d.count),
      backgroundColor: [
        'rgba(82,138,180,0.75)',
        'rgba(98,161,199,0.75)',
        'rgba(123,199,221,0.75)',
        'rgba(149,214,234,0.75)',
        'rgba(21,113,159,0.75)',
        'rgba(245,158,11,0.75)',
        'rgba(16,185,129,0.75)',
        'rgba(239,68,68,0.75)',
        'rgba(124,58,237,0.75)',
        'rgba(59,130,246,0.75)',
      ],
      borderRadius: 8,
      borderSkipped: false
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { color: tickColor } },
      y: { grid: { color: gridColor }, ticks: { color: tickColor }, beginAtZero: true }
    }
  }
});

/* ── LOCATION CHART ── */
const locationData = <?php echo json_encode($locationStats); ?>;

new Chart(document.getElementById('locationChart'), {
  type: 'bar',
  data: {
    labels: locationData.map(d => d.location),
    datasets: [{
      label: 'Searches',
      data: locationData.map(d => d.total),
      backgroundColor: 'rgba(245,158,11,0.75)',
      borderRadius: 8,
      borderSkipped: false,
      hoverBackgroundColor: 'rgba(245,158,11,1)'
    }]
  },
  options: {
    indexAxis: 'y',   /* horizontal bar — easier to read location names */
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { color: gridColor }, ticks: { color: tickColor }, beginAtZero: true },
      y: { grid: { display: false }, ticks: { color: tickColor } }
    }
  }
});

/* ── DATATABLE ── */
$(document).ready(function () {
  $('#searchHistoryTable').DataTable({
    responsive: true,
    pageLength: 10,
    order: [[0, 'desc']],
    language: {
      search: '',
      searchPlaceholder: 'Search records…',
      lengthMenu: 'Show _MENU_ entries',
      info: 'Showing _START_–_END_ of _TOTAL_ records',
      paginate: { previous: '‹', next: '›' }
    }
  });
});
</script>

<?php renderFooter(); ?>