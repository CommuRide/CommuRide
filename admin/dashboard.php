
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
    SELECT 
        COALESCE(u.role, 'unknown') as role, 
        COUNT(*) as count 
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY role
");
$roleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* ================= WEATHER SEARCH HISTORY ================= */

$stmt = $pdo->query("
    SELECT 
        uwh.search_id,
        u.email,
        uwh.location,
        uwh.temperature,
        uwh.cond,
        uwh.humidity,
        uwh.wind_speed,
        uwh.cloth_rec,
        uwh.searched_at
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

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

<style>
.stat-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:15px;
margin:20px 0;
}

.stat-card{
color:white;
padding:20px;
border-radius:8px;
box-shadow:0 4px 6px rgba(0,0,0,0.1);
}

.stat-card h3{
margin:0;
font-size:32px;
}

.chart-container{
background:white;
padding:20px;
border-radius:8px;
box-shadow:0 2px 4px rgba(0,0,0,0.1);
margin:20px 0;
}

.chart-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(400px,1fr));
gap:20px;
margin:20px 0;
}

table.dataTable{
width:100%!important;
}

.badge-success{background:#28a745;}
.badge-failed{background:#dc3545;}
</style>


<div class="nav" style="padding-bottom:15px;">
<a href="<?php echo BASE_URL; ?>/users/dashboard.php">User Management |</a>
<a href="<?php echo BASE_URL; ?>/users/user-create.php">Create User |</a>
<a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<h1>Admin Dashboard</h1>

<div class="info-box">
<strong>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?></strong><br>
Role: <span class="badge badge-admin">Admin</span>
</div>


<h2>System Statistics</h2>

<div class="stat-grid">

<div class="stat-card" style="background:linear-gradient(135deg,#667eea,#764ba2);">
<h3><?php echo $totalUsers; ?></h3>
<p>Total Users</p>
</div>

<div class="stat-card" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
<h3><?php echo $totalAdmins; ?></h3>
<p>Admins</p>
</div>

<div class="stat-card" style="background:linear-gradient(135deg,#fad0c4,#ffd1ff);color:#333;">
<h3><?php echo $totalManagers; ?></h3>
<p>Managers</p>
</div>

<div class="stat-card" style="background:linear-gradient(135deg,#a1c4fd,#c2e9fb);color:#333;">
<h3><?php echo $totalRegularUsers; ?></h3>
<p>Regular Users</p>
</div>

<div class="stat-card" style="background:linear-gradient(135deg,#ffecd2,#fcb69f);color:#333;">
<h3><?php echo $unverifiedUsers; ?></h3>
<p>Unverified</p>
</div>

</div>


<h2>Activity Analytics</h2>

<div class="chart-grid">

<div class="chart-container">
<h3>Daily Activity Trend</h3>
<canvas id="dailyActivityChart"></canvas>
</div>

<div class="chart-container">
<h3>Top Actions</h3>
<canvas id="actionChart"></canvas>
</div>

<div class="chart-container">
<h3>Most Searched Locations</h3>
<canvas id="locationChart"></canvas>
</div>

</div>


<h2>User Weather Search History</h2>

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

<?php foreach($searchHistory as $row): ?>

<tr>
<td><?php echo $row['search_id']; ?></td>
<td><?php echo htmlspecialchars($row['email']); ?></td>
<td><?php echo htmlspecialchars($row['location']); ?></td>
<td><?php echo $row['temperature']; ?>°C</td>
<td><?php echo htmlspecialchars($row['cond']); ?></td>
<td><?php echo $row['humidity']; ?>%</td>
<td><?php echo $row['wind_speed']; ?> km/h</td>
<td><?php echo htmlspecialchars($row['cloth_rec']); ?></td>
<td><?php echo $row['searched_at']; ?></td>
</tr>

<?php endforeach; ?>

</tbody>
</table>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>

/* DAILY ACTIVITY */

const dailyData = <?php echo json_encode($dailyActivity); ?>;

new Chart(document.getElementById('dailyActivityChart'),{
type:'line',
data:{
labels:dailyData.map(d=>d.date),
datasets:[{
label:'Activities',
data:dailyData.map(d=>d.count),
borderColor:'rgb(102,126,234)',
backgroundColor:'rgba(102,126,234,0.1)',
fill:true
}]
}
});


/* ACTION CHART */

const actionData = <?php echo json_encode($actionStats); ?>;

new Chart(document.getElementById('actionChart'),{
type:'bar',
data:{
labels:actionData.map(d=>d.action),
datasets:[{
data:actionData.map(d=>d.count),
backgroundColor:'rgba(75,192,192,0.7)'
}]
}
});


/* LOCATION ANALYTICS */

const locationData = <?php echo json_encode($locationStats); ?>;

new Chart(document.getElementById('locationChart'),{
type:'bar',
data:{
labels:locationData.map(d=>d.location),
datasets:[{
label:'Search Count',
data:locationData.map(d=>d.total),
backgroundColor:'rgba(255,159,64,0.7)'
}]
}
});


/* DATATABLE */

$(document).ready(function(){
$('#searchHistoryTable').DataTable({
responsive:true,
pageLength:10,
order:[[0,'desc']]
});
});

</script>

<?php renderFooter(); ?>