<?php
require_once '../config/config.php';
require_once '../config/functions.php';
require_once '../includes/activity-logger.php';
requireLogin();

$userId  = $_GET['user_id'] ?? 0;
$message = '';
$success = false;
$verificationLink = '';

/* ── RESEND VERIFICATION ───────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_verification'])) {
    $verification_token   = bin2hex(random_bytes(32));
    $verification_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

    try {
        $stmt = $pdo->prepare("
            UPDATE users
            SET verification_token = :token, email_verification_expires = :expires
            WHERE id = :id
        ");
        $stmt->execute([':token' => $verification_token, ':expires' => $verification_expires, ':id' => $userId]);

        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $userEmail = $stmt->fetchColumn();

        if ($userEmail) {
            $verificationLink = BASE_URL . "/auth/verify-email.php?token=" . $verification_token;

            $email_subject = "Verify Your Email Address";
            $email_body    = "
            <html><head><style>
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
                  <p>You requested a new verification link. Please verify your email address to access CommuRide.</p>
                  <p style='text-align:center;'><a href='{$verificationLink}' class='button'>Verify Email Address</a></p>
                  <p>If the button doesn't work, copy and paste this link:</p>
                  <p style='word-break:break-all;color:#15719f;'>{$verificationLink}</p>
                  <p><em>This link expires in 24 hours.</em></p>
                </div>
                <div class='footer'><p>&copy; " . date('Y') . " CommuRide</p></div>
              </div>
            </body></html>";

            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: noreply@ics-dev.io\r\n";

            if (mail($userEmail, $email_subject, $email_body, $headers)) {
                $message = "Verification email resent to <strong>" . htmlspecialchars($userEmail) . "</strong>.";
                $success = true;
                logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'verification_email_resent',   'success');
                logActivity($pdo, $userId,              $userEmail,          'verification_email_received', 'success');
            } else {
                $message = "Verification email failed to send. Please try again.";
                $success = false;
                logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'verification_email_resent', 'failed');
            }
        }
    } catch (PDOException $e) {
        $message = "Error resending verification: " . $e->getMessage();
        $success = false;
        logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'verification_email_resent', 'failed');
    }
}

/* ── FETCH USER ────────────────────────────────────────── */
$stmt = $pdo->prepare("
    SELECT id, email, role, is_verified, verification_token,
           email_verification_expires, created_at
    FROM users WHERE id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'user_viewed', 'success');
}

$verificationExpired = false;
if ($user && !$user['is_verified'] && $user['email_verification_expires']) {
    $verificationExpired = strtotime($user['email_verification_expires']) < time();
}

$title = 'View User';
renderHeader($title);
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/users.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-create.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/user-forms.css">

<!-- NAV -->
<div class="nav">
  <a href="<?php echo BASE_URL; ?>/users/dashboard.php" class="nav-back">Back to Users</a>
  <a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<!-- PAGE -->
<div class="form-page">
  <div class="card card-view">

    <div class="card-body">

      <!-- Header -->
      <div class="card-header">
        <h2>👁 User Details</h2>
        <?php if ($user): ?>
          <span class="badge badge-<?php echo $user['role']; ?>">
            <?php echo ucfirst($user['role']); ?>
          </span>
        <?php endif; ?>
      </div>

      <!-- Feedback (resend result) -->
      <?php if ($message): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
          <?php echo $message; ?>
          <?php if ($verificationLink && $success): ?>
            <div class="verify-link-box" style="margin-top:8px;">
              <a href="<?php echo $verificationLink; ?>" target="_blank"><?php echo $verificationLink; ?></a>
              <p class="hint">Testing link — in production this is sent by email only.</p>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($user): ?>

        <!-- Details table -->
        <table class="details-table">
          <tr>
            <th>ID</th>
            <td>#<?php echo $user['id']; ?></td>
          </tr>
          <tr>
            <th>Email</th>
            <td><strong><?php echo htmlspecialchars($user['email']); ?></strong></td>
          </tr>
          <tr>
            <th>Role</th>
            <td>
              <span class="badge badge-<?php echo $user['role']; ?>">
                <?php echo ucfirst($user['role']); ?>
              </span>
            </td>
          </tr>
          <tr>
            <th>Verified</th>
            <td>
              <span class="badge badge-<?php echo $user['is_verified'] ? 'verified' : 'unverified'; ?>">
                <?php echo $user['is_verified'] ? '✓ Yes' : '✗ No'; ?>
              </span>
              <?php if (!$user['is_verified'] && $verificationExpired): ?>
                <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;margin-left:6px;">
                  Expired
                </span>
              <?php endif; ?>
            </td>
          </tr>

          <?php if (!$user['is_verified'] && $user['email_verification_expires']): ?>
          <tr>
            <th>Link Expires</th>
            <td>
              <?php
                $expiresTime = strtotime($user['email_verification_expires']);
                echo date('M d, Y · g:i A', $expiresTime);
                if ($verificationExpired) {
                    echo ' <span class="expiry-expired">(Expired)</span>';
                } else {
                    $hoursLeft = floor(($expiresTime - time()) / 3600);
                    echo ' <span class="expiry-ok">(' . $hoursLeft . 'h remaining)</span>';
                }
              ?>
            </td>
          </tr>
          <?php endif; ?>

          <tr>
            <th>Created</th>
            <td><?php echo date('M d, Y · g:i A', strtotime($user['created_at'])); ?></td>
          </tr>
        </table>

        <!-- Verification alert -->
        <?php if (!$user['is_verified']): ?>
          <div class="verify-alert <?php echo $verificationExpired ? 'verify-alert-danger' : 'verify-alert-warning'; ?>">
            <div class="verify-alert-title">
              <?php echo $verificationExpired ? '⚠️ Verification Link Expired' : '📧 Email Not Verified'; ?>
            </div>
            <p>
              <?php if ($verificationExpired): ?>
                This user's verification link has expired. Send a new one below.
              <?php else: ?>
                This user hasn't verified their email yet. You can resend the link if needed.
              <?php endif; ?>
            </p>
            <form method="POST">
              <button type="submit"
                      name="resend_verification"
                      class="btn-resend <?php echo $verificationExpired ? 'btn-resend-danger' : 'btn-resend-warning'; ?>">
                📧 Resend Verification Email
              </button>
            </form>
          </div>
        <?php endif; ?>

        <!-- Action buttons -->
        <div class="btn-row">
          <a href="user-update.php?user_id=<?php echo $user['id']; ?>"
             class="btn-action-lg btn-warning">
            ✏️ Edit User
          </a>
          <a href="user-delete.php?user_id=<?php echo $user['id']; ?>"
             class="btn-action-lg btn-danger"
             onclick="return confirm('Delete this user? This cannot be undone.')">
            🗑 Delete
          </a>
          <a href="<?php echo BASE_URL; ?>/users/dashboard.php"
             class="btn-action-lg btn-neutral">
            ← Back to List
          </a>
        </div>

      <?php else: ?>
        <div class="alert alert-error">User not found.</div>
        <div class="btn-row">
          <a href="<?php echo BASE_URL; ?>/users/dashboard.php" class="btn-action-lg btn-neutral">← Back to Users</a>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php renderFooter(); ?>