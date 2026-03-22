<?php
require_once '../config/config.php';
require_once '../config/functions.php';
require_once '../includes/activity-logger.php';
requireLogin();

$currentRole = $_SESSION['role'];

if ($currentRole !== 'admin' && $currentRole !== 'manager') {
    die("Access denied. Only administrators and managers can create users.");
}

$message         = '';
$success         = false;
$verificationLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = $_POST['email']    ?? '';
    $password   = $_POST['password'] ?? '';
    $role       = $_POST['role']     ?? 'user';
    $send_email = isset($_POST['send_verification_email']);

    // Role restrictions
    if ($currentRole === 'manager') {
        $role = 'user';
    } elseif ($currentRole === 'admin') {
        if (!in_array($role, ['admin', 'manager', 'user'])) $role = 'user';
    }

    try {
        $email_verified       = 1;
        $verification_token   = null;
        $verification_expires = null;

        if ($send_email) {
            $email_verified       = 0;
            $verification_token   = bin2hex(random_bytes(32));
            $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        }

        $stmt = $pdo->prepare("
            INSERT INTO users
                (email, password, role, verification_token, is_verified, email_verification_expires, created_at)
            VALUES
                (:email, :password, :role, :token, :verified, :expires, NOW())
        ");
        $stmt->execute([
            ':email'    => $email,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role'     => $role,
            ':token'    => $verification_token,
            ':verified' => $email_verified,
            ':expires'  => $verification_expires
        ]);

        $new_user_id = $pdo->lastInsertId();

        logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'user_created',    'success');
        logActivity($pdo, $new_user_id,         $email,            'account_created',  'success');

        if ($send_email) {
            $verificationLink = BASE_URL . "/auth/verify-email.php?token=" . $verification_token;

            $email_subject = "Verify Your Email Address";
            $email_body    = "
            <html><head>
            <style>
              body{font-family:Arial,sans-serif;line-height:1.6;color:#333;}
              .container{max-width:600px;margin:0 auto;padding:20px;}
              .header{background:#15719f;color:white;padding:20px;text-align:center;border-radius:8px 8px 0 0;}
              .content{background:#f9f9f9;padding:30px;}
              .button{display:inline-block;padding:12px 30px;background:#62a1c7;color:white;text-decoration:none;border-radius:6px;margin:20px 0;}
              .footer{text-align:center;margin-top:20px;color:#666;font-size:12px;}
            </style></head>
            <body>
              <div class='container'>
                <div class='header'><h2>⛅ CommuRide — Email Verification</h2></div>
                <div class='content'>
                  <p>Hello,</p>
                  <p>An account was created for this email address on CommuRide. Please verify your email to get started.</p>
                  <p style='text-align:center;'><a href='{$verificationLink}' class='button'>Verify Email Address</a></p>
                  <p>If the button doesn't work, copy and paste this link into your browser:</p>
                  <p style='word-break:break-all;color:#15719f;'>{$verificationLink}</p>
                  <p><em>This link expires in 24 hours.</em></p>
                </div>
                <div class='footer'><p>&copy; " . date('Y') . " CommuRide</p></div>
              </div>
            </body></html>";

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: noreply@ics-dev.io\r\n";

            if (mail($email, $email_subject, $email_body, $headers)) {
                $message = "User created successfully! Verification email sent to <strong>" . htmlspecialchars($email) . "</strong>.";
                logActivity($pdo, $new_user_id, $email, 'verification_email_sent', 'success');
            } else {
                $message = "User created, but the verification email failed to send.";
                logActivity($pdo, $new_user_id, $email, 'verification_email_sent', 'failed');
            }
        } else {
            $message = "User created successfully! Email verification was skipped — account is active immediately.";
        }

        $success = true;

    } catch (PDOException $e) {
        $message = "Error creating user: " . $e->getMessage();
        logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'user_created', 'failed');
    }
}

$title = 'Create User';
renderHeader($title);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/users.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-create.css">

<!-- NAV -->
<div class="nav">
  <a href="<?php echo BASE_URL; ?>/users/dashboard.php" class="nav-back">Back to Users</a>
  <a href="<?php echo BASE_URL; ?>/<?php echo $currentRole; ?>/dashboard.php">Dashboard</a>
  <a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<!-- PAGE -->
<div class="create-page">

  <div class="card">

    <!-- Accent stripe via CSS ::before -->

    <div class="card-body">

      <!-- Header -->
      <div class="card-header">
        <h2>Create New User</h2>
        <span class="badge badge-<?php echo $currentRole; ?>"><?php echo ucfirst($currentRole); ?></span>
      </div>

      <!-- Manager notice -->
      <?php if ($currentRole === 'manager'): ?>
        <div class="alert alert-warning">
          <strong>Manager Access:</strong> You can only create regular users.
        </div>
      <?php endif; ?>

      <!-- Success / error feedback -->
      <?php if ($message): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <!-- Verification link (dev/testing) -->
      <?php if ($verificationLink): ?>
        <div class="alert alert-info">
          <div>
            <strong>Verification Link (for testing)</strong>
            <div class="verify-link-box">
              <a href="<?php echo $verificationLink; ?>" target="_blank"><?php echo $verificationLink; ?></a>
              <p class="hint">In production this link is sent via email. It expires in 24 hours.</p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" class="create-form">

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email"
                 id="email"
                 name="email"
                 placeholder="user@example.com"
                 required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password"
                 id="password"
                 name="password"
                 placeholder="Enter a secure password"
                 required>
        </div>

        <div class="form-group">
          <label for="role">Role</label>
          <select id="role"
                  name="role"
                  <?php echo $currentRole === 'manager' ? 'disabled' : ''; ?>>
            <option value="user">User</option>
            <?php if ($currentRole === 'admin'): ?>
              <option value="manager">Manager</option>
              <option value="admin">Admin</option>
            <?php endif; ?>
          </select>
          <?php if ($currentRole === 'manager'): ?>
            <input type="hidden" name="role" value="user">
            <span class="field-hint">Managers can only create regular users.</span>
          <?php endif; ?>
        </div>

        <label class="checkbox-group">
          <input type="checkbox"
                 name="send_verification_email"
                 value="1"
                 checked>
          <div class="checkbox-label">
            <span>Send email verification</span>
            <small>Recommended — user must verify their email before logging in. Uncheck to activate the account immediately.</small>
          </div>
        </label>

        <button type="submit" class="btn-submit">
          ＋ Create User
        </button>

      </form>

    </div><!-- /.card-body -->
  </div><!-- /.card -->

</div><!-- /.create-page -->

<?php renderFooter(); ?>