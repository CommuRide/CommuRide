<?php
require_once 'config/config.php';
require_once 'config/functions.php';
require_once 'includes/activity-logger.php';

if (isLoggedIn()) {
    switch($_SESSION['role']) {
        case 'admin':
            redirect('/admin/dashboard.php');
            exit;
        case 'manager':
            redirect('/manager/dashboard.php');
            exit;
        case 'user':
            redirect('/user/dashboard.php');
            exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_verified = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];
        
        // Log successful login
        logActivity($pdo, $user['id'], $user['email'], 'login', 'success');
        
        // Redirect based on role
        switch($user['role']) {
            case 'admin':
                redirect('/admin/dashboard.php');
                break;
            case 'manager':
                redirect('/manager/dashboard.php');
                break;
            case 'user':
                redirect('/user/dashboard.php');
                break;
        }
        
        exit;
        
    } else {
        $error = "Invalid credentials or email not verified";
        
        // Log failed login attempt
        logActivity($pdo, null, $email, 'login', 'failed');
    }
}

renderHeader('Login');
?>

<h1>Login</h1>

<?php if ($error): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <button type="submit">Login</button>
</form>

<div class="info-box">
    <strong>Test Accounts (password: password123):</strong><br>
    admin@example.com | manager@example.com | user@example.com
</div>

<?php renderFooter(); ?>
