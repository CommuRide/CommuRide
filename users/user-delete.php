<?php
require_once '../config/config.php';
require_once '../config/functions.php';
require_once '../includes/activity-logger.php';
requireLogin();

$userId  = $_GET['user_id'] ?? 0;
$message = '';
$success = false;

$stmt = $pdo->prepare("SELECT id, email, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user) {
        try {
            logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'user_deleted',   'success');
            logActivity($pdo, $userId,              $user['email'],      'account_deleted','success');

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            if ($stmt->rowCount() > 0) {
                redirect('/users/dashboard.php');
            } else {
                $message = "User not found.";
            }
        } catch (PDOException $e) {
            $message = "Error deleting user: " . $e->getMessage();
            logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'user_deleted', 'failed');
        }
    }
}

renderHeader('Delete User');
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
  <div class="card card-delete">

    <div class="card-body">

      <!-- Header -->
      <div class="card-header">
        <h2>🗑 Delete User</h2>
      </div>

      <!-- Error feedback -->
      <?php if ($message): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>

      <?php if ($user): ?>

        <!-- Danger confirmation box -->
        <div class="danger-box">
          <div class="danger-title">⚠️ This action cannot be undone</div>

          <div class="detail-row">
            <span class="detail-label">Email</span>
            <strong><?php echo htmlspecialchars($user['email']); ?></strong>
          </div>
          <div class="detail-row">
            <span class="detail-label">Role</span>
            <span class="badge badge-<?php echo $user['role']; ?>">
              <?php echo ucfirst($user['role']); ?>
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">ID</span>
            <span style="color:var(--text-3);font-size:13px;">#<?php echo $user['id']; ?></span>
          </div>
        </div>

        <p style="font-size:14px;color:var(--text-2);margin-bottom:20px;line-height:1.6;">
          You are about to permanently delete this user account. All associated data will be removed
          and this cannot be reversed. Please confirm you want to proceed.
        </p>

        <form method="POST">
          <div class="btn-row">
            <button type="submit" class="btn-action-lg btn-danger">
              🗑 Yes, Delete User
            </button>
            <a href="<?php echo BASE_URL; ?>/users/dashboard.php" class="btn-action-lg btn-neutral">
              ← Cancel
            </a>
          </div>
        </form>

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