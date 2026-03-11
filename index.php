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
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];
        
        logActivity($pdo, $user['id'], $user['email'], 'login', 'success');
        
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
        logActivity($pdo, null, $email, 'login', 'failed');
    }
}

renderHeader('Login');
?>

<!-- ===== INLINE CSS ===== -->
<style>
body {
    background-color: #f4f9fb;
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

.login-container {
    background-color: #ffffff;
    padding: 30px 40px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
}

h1 {
    color: #1565c0;
    margin-bottom: 20px;
    text-align: center;
}

.error {
    background-color: #ffcdd2;
    color: #b71c1c;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    text-align: center;
}

.form-group {
    margin-bottom: 15px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #b2dfdb;
    border-radius: 4px;
    font-size: 14px;
}

input[type="email"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: #26a69a;
}

button[type="submit"] {
    background-color: #1e88e5;
    color: #ffffff;
    border: none;
    padding: 12px;
    width: 100%;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    border-radius: 4px;
    margin-top: 10px;
}

button[type="submit"]:hover {
    background-color: #26a69a;
}

.info-box {
    background-color: #e0f7fa;
    border-left: 6px solid #1e88e5;
    padding: 15px;
    margin-top: 20px;
    font-size: 14px;
    border-radius: 4px;
}
</style>

<!-- ===== LOGIN FORM ===== -->
<div class="login-container">
    <h1>Login</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        
        <button type="submit">Login</button>
    </form>

    <div class="info-box">
        <strong>Test Accounts (password: password123):</strong><br>
        admin@example.com | manager@example.com | user@example.com
    </div>
</div>

<?php renderFooter(); ?>