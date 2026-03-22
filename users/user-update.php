<?php
require_once '../config/config.php';
require_once '../config/functions.php';
require_once '../includes/activity-logger.php';
requireLogin();

$userId  = $_GET['user_id'] ?? 0;
$message = '';
$success = false;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email']    ?? '';
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role']     ?? '';

    $updates = []; $params = []; $changes = [];

    if ($email && $email !== $user['email']) {
        $updates[] = "email = ?";    $params[] = $email;    $changes[] = 'email';
    }
    if ($password) {
        $updates[] = "password = ?"; $params[] = password_hash($password, PASSWORD_DEFAULT); $changes[] = 'password';
    }
    if ($role && in_array($role, ['admin','manager','user']) && $role !== $user['role']) {
        $updates[] = "role = ?";     $params[] = $role;     $changes[] = 'role';
    }

    if (!empty($updates)) {
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = "User updated successfully!";
            $success = true;
            logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'user_updated',    'success');
            logActivity($pdo, $userId,              $user['email'],      'profile_updated', 'success');
            // Refresh
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "Error updating user: " . $e->getMessage();
            logActivity($pdo, $_SESSION['user_id'], $_SESSION['email'], 'user_updated', 'failed');
        }
    } else {
        $message = "No changes detected.";
    }
}

renderHeader('Update User');
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
  <div class="card card-update">

    <div class="card-body">

      <!-- Header -->
      <div class="card-header">
        <h2>✏️ Update User</h2>
        <?php if ($user): ?>
          <span class="badge badge-<?php echo $user['role']; ?>">
            <?php echo ucfirst($user['role']); ?>
          </span>
        <?php endif; ?>
      </div>

      <!-- Feedback -->
      <?php if ($message): ?>
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <?php if ($user): ?>

        <form method="POST" class="create-form">

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email"
                   id="email"
                   name="email"
                   value="<?php echo htmlspecialchars($user['email']); ?>">
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input type="password"
                   id="password"
                   name="password"
                   placeholder="Leave blank to keep current password">
            <span class="field-hint">Only fill this in if you want to change the password.</span>
          </div>

          <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
              <option value="user"    <?php echo $user['role'] === 'user'    ? 'selected' : ''; ?>>User</option>
              <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
              <option value="admin"   <?php echo $user['role'] === 'admin'   ? 'selected' : ''; ?>>Admin</option>
            </select>
          </div>

          <div class="btn-row" style="margin-top:8px;">
            <button type="submit" class="btn-action-lg btn-warning" style="flex:1;">
              ✏️ Save Changes
            </button>
            <a href="user-view.php?user_id=<?php echo $user['id']; ?>"
               class="btn-action-lg btn-neutral">
              👁 View Profile
            </a>
            <a href="<?php echo BASE_URL; ?>/users/dashboard.php"
               class="btn-action-lg btn-neutral">
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