<?php
require_once '../config/config.php';
require_once '../config/functions.php';
requireLogin();

$currentRole   = $_SESSION['role'];
$currentUserId = $_SESSION['user_id'];

/* ── ROLE-BASED QUERY ─────────────────────────────────── */
if ($currentRole === 'admin') {
    $stmt = $pdo->prepare("
        SELECT id, email, role, is_verified, created_at FROM users
        ORDER BY
            CASE role WHEN 'admin' THEN 1 WHEN 'manager' THEN 2 WHEN 'user' THEN 3 END,
            created_at DESC
    ");
    $stmt->execute();
} elseif ($currentRole === 'manager') {
    $stmt = $pdo->prepare("
        SELECT id, email, role, is_verified, created_at FROM users
        WHERE role = 'user' ORDER BY created_at DESC
    ");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT id, email, role, is_verified, created_at FROM users
        WHERE id = ? ORDER BY created_at DESC
    ");
    $stmt->execute([$currentUserId]);
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

function canEdit($targetRole, $targetUserId) {
    global $currentRole, $currentUserId;
    if ($targetUserId == $currentUserId) return true;
    if ($currentRole === 'admin')   return true;
    if ($currentRole === 'manager') return $targetRole === 'user';
    return false;
}

function canDelete($targetRole, $targetUserId) {
    global $currentRole, $currentUserId;
    if ($targetUserId == $currentUserId) return false;
    if ($currentRole === 'admin')   return true;
    if ($currentRole === 'manager') return $targetRole === 'user';
    return false;
}

renderHeader('User Management');
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/users.css">

<!-- ── NAV ── -->
<div class="nav">
  <a href="<?php echo BASE_URL; ?>/<?php echo $currentRole; ?>/dashboard.php">Dashboard</a>
  <?php if ($currentRole === 'admin' || $currentRole === 'manager'): ?>
    <a href="<?php echo BASE_URL; ?>/users/user-create.php">Create User</a>
  <?php endif; ?>
  <a href="<?php echo BASE_URL; ?>/auth/signout.php">Logout</a>
</div>

<!-- ── PAGE CONTENT ── -->
<div class="admin-page">

  <!-- Page header + Create button -->
  <div class="page-header">
    <h1>User Management</h1>
    <?php if ($currentRole === 'admin' || $currentRole === 'manager'): ?>
    <?php endif; ?>
  </div>

  <!-- Info / role context box -->
  <div class="info-box" style="margin-bottom:14px;">
    Your Role: <span class="badge badge-<?php echo $currentRole; ?>"><?php echo ucfirst($currentRole); ?></span>
    &nbsp;·&nbsp;
    <?php if ($currentRole === 'admin'): ?>
      Full access — all users visible.
    <?php elseif ($currentRole === 'manager'): ?>
      You can manage regular users only.
    <?php else: ?>
      Viewing your own profile.
    <?php endif; ?>
  </div>

  <!-- Count line -->
  <p class="context-line">
    <?php if ($currentRole === 'admin'): ?>
      Showing <strong><?php echo count($users); ?></strong> total users
    <?php elseif ($currentRole === 'manager'): ?>
      Showing <strong><?php echo count($users); ?></strong> regular user<?php echo count($users) !== 1 ? 's' : ''; ?> you can manage
    <?php else: ?>
      Showing your profile
    <?php endif; ?>
  </p>

  <!-- ── TABLE ── -->
  <?php if (!empty($users)): ?>

    <div class="table-wrapper">
      <table class="user-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Role</th>
            <th>Verified</th>
            <th>Created</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $user):
            $isSelf = ($user['id'] == $currentUserId);
          ?>
          <tr class="<?php echo $isSelf ? 'current-user' : ''; ?>">

            <td data-label="ID"><?php echo $user['id']; ?></td>

            <td data-label="Email" class="cell-email">
              <?php echo htmlspecialchars($user['email']); ?>
            </td>

            <td data-label="Role">
              <span class="badge badge-<?php echo $user['role']; ?>">
                <?php echo ucfirst($user['role']); ?>
              </span>
            </td>

            <td data-label="Verified">
              <span class="badge badge-<?php echo $user['is_verified'] ? 'verified' : 'unverified'; ?>">
                <?php echo $user['is_verified'] ? '✓ Yes' : '✗ No'; ?>
              </span>
            </td>

            <td data-label="Created" class="cell-date">
              <?php echo date('M d, Y · g:i A', strtotime($user['created_at'])); ?>
            </td>

            <td data-label="Actions">
              <div class="action-group">

                <!-- View -->
                <a href="user-view.php?user_id=<?php echo $user['id']; ?>"
                   class="btn-action btn-view">
                  👁 View
                </a>

                <!-- Edit -->
                <?php if (canEdit($user['role'], $user['id'])): ?>
                  <a href="user-update.php?user_id=<?php echo $user['id']; ?>"
                     class="btn-action btn-edit">
                    ✏️ Edit
                  </a>
                <?php endif; ?>

                <!-- Delete -->
                <?php if (canDelete($user['role'], $user['id'])): ?>
                  <a href="user-delete.php?user_id=<?php echo $user['id']; ?>"
                     class="btn-action btn-delete"
                     onclick="return confirm('Delete this user? This cannot be undone.')">
                    🗑 Delete
                  </a>
                <?php elseif ($isSelf): ?>
                  <span class="btn-delete-disabled" title="You cannot delete your own account">
                    🔒 Delete
                  </span>
                <?php endif; ?>

              </div>
            </td>

          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  <?php else: ?>

    <div class="table-wrapper">
      <div class="empty-state">
        <p>No users to display based on your role permissions.</p>
      </div>
    </div>

  <?php endif; ?>

</div><!-- /.admin-page -->

<?php renderFooter(); ?>