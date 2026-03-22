<?php
require_once 'config/config.php';
require_once 'config/functions.php';
require_once 'includes/activity-logger.php';

if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'admin':   redirect('/admin/dashboard.php');   exit;
        case 'manager': redirect('/manager/dashboard.php'); exit;
        case 'user':    redirect('/user/dashboard.php');    exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_verified = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        logActivity($pdo, $user['id'], $user['email'], 'login', 'success');

        switch ($user['role']) {
            case 'admin':   redirect('/admin/dashboard.php');   break;
            case 'manager': redirect('/manager/dashboard.php'); break;
            case 'user':    redirect('/user/dashboard.php');    break;
        }
        exit;

    } else {
        $error = "Invalid credentials or email not verified.";
        logActivity($pdo, null, $email, 'login', 'failed');
    }
}

renderHeader('Login');
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/index.css">

<!-- Floating weather bg icons -->
<div class="weather-bg-icons" aria-hidden="true">
  <span>☀️</span>
  <span>🌧️</span>
  <span>❄️</span>
  <span>⛈️</span>
  <span>⛅</span>
</div>

<div class="login-container">

  <!-- Gradient band rendered by CSS ::before -->

  <div class="login-inner">

    <div class="login-brand">
      <span class="brand-icon">⛅</span>
      <h1>CommuRide</h1>
      <p>Weather-smart commuting, every day.</p>
    </div>

    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email"
               id="email"
               name="email"
               placeholder="Enter your email"
               required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password"
               id="password"
               name="password"
               placeholder="Enter your password"
               required>
      </div>

      <button type="submit">Sign In</button>

    </form>

    <div class="info-box">
      <strong>Test Accounts (password: password123)</strong>
      admin@example.com &nbsp;·&nbsp; manager@example.com &nbsp;·&nbsp; user@example.com
    </div>

  </div>

  <div class="condition-strip" title="Weather conditions we cover">
    <span title="Sunny">☀️</span>
    <span title="Partly Cloudy">⛅</span>
    <span title="Rainy">🌧️</span>
    <span title="Snowy">❄️</span>
    <span title="Storm">⛈️</span>
  </div>

</div>

<?php renderFooter(); ?>