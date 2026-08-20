<?php
/**
 * Daba Magic - Admin Operator & Role Management Page
 */

require_once __DIR__ . '/includes/auth_check.php';

$page_title = "Operator & Role Management - Daba Magic Admin Panel";

$msg = "";
$err = "";
$active_tab = $_GET['tab'] ?? 'operators';

// Process Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- OPERATOR ACTIONS ---
    if ($action === 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'Restaurant Manager');
        $status = $_POST['status'] ?? 'Active';

        if (empty($username) || empty($password) || empty($full_name) || empty($email)) {
            $err = "Please fill in all operator fields.";
        } elseif (strlen($password) < 6) {
            $err = "Password must be at least 6 characters in length.";
        } else {
            // Check username / email uniqueness
            $check_stmt = $con->prepare("SELECT id FROM tbl_admin WHERE username = ? OR email = ?");
            $check_stmt->bind_param("ss", $username, $email);
            $check_stmt->execute();
            $c_res = $check_stmt->get_result();

            if ($c_res && $c_res->num_rows > 0) {
                $err = "An operator with that username or email already exists.";
            } else {
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                $ins_stmt = $con->prepare("INSERT INTO tbl_admin (username, password, full_name, email, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                $ins_stmt->bind_param("ssssss", $username, $pass_hash, $full_name, $email, $role, $status);
                if ($ins_stmt->execute()) {
                    $msg = "New operator account '{$username}' created successfully!";
                } else {
                    $err = "Database error: Could not create operator.";
                }
                $ins_stmt->close();
            }
            $check_stmt->close();
        }
        $active_tab = 'operators';

    } elseif ($action === 'edit_user') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'Restaurant Manager');
        $status = $_POST['status'] ?? 'Active';
        $new_password = $_POST['new_password'] ?? '';

        if ($user_id <= 0 || empty($full_name) || empty($email)) {
            $err = "Valid operator ID, full name, and email are required.";
        } elseif ($user_id === intval($_SESSION['admin_id'] ?? 0) && $status === 'Disabled') {
            $err = "Safety safeguard: You cannot disable your own active logged-in account.";
        } else {
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $err = "New password must be at least 6 characters in length.";
                } else {
                    $pass_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $up_stmt = $con->prepare("UPDATE tbl_admin SET full_name = ?, email = ?, role = ?, status = ?, password = ? WHERE id = ?");
                    $up_stmt->bind_param("sssssi", $full_name, $email, $role, $status, $pass_hash, $user_id);
                    if ($up_stmt->execute()) {
                        $msg = "Operator account details and password updated successfully!";
                    } else {
                        $err = "Database error: Could not update operator.";
                    }
                    $up_stmt->close();
                }
            } else {
                $up_stmt = $con->prepare("UPDATE tbl_admin SET full_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
                $up_stmt->bind_param("ssssi", $full_name, $email, $role, $status, $user_id);
                if ($up_stmt->execute()) {
                    $msg = "Operator account details updated successfully!";
                } else {
                    $err = "Database error: Could not update operator.";
                }
                $up_stmt->close();
            }
        }
        $active_tab = 'operators';

    } elseif ($action === 'toggle_user_status') {
        $user_id = intval($_POST['user_id'] ?? 0);
        $new_status = $_POST['status'] ?? 'Active';

        if ($user_id === intval($_SESSION['admin_id'] ?? 0) && $new_status === 'Disabled') {
            $err = "Safety safeguard: You cannot disable your own active logged-in account.";
        } else {
            // Check if user is primary admin
            $chk = $con->query("SELECT username FROM tbl_admin WHERE id = $user_id");
            $u_name = ($chk && $chk->num_rows > 0) ? $chk->fetch_assoc()['username'] : '';

            if ($u_name === 'admin' && $new_status === 'Disabled') {
                $err = "Safety safeguard: The primary Super Admin account cannot be disabled.";
            } else {
                $up_stmt = $con->prepare("UPDATE tbl_admin SET status = ? WHERE id = ?");
                $up_stmt->bind_param("si", $new_status, $user_id);
                if ($up_stmt->execute()) {
                    $msg = "Operator account status changed to '{$new_status}'.";
                }
                $up_stmt->close();
            }
        }
        $active_tab = 'operators';

    } elseif ($action === 'delete_user') {
        $user_id = intval($_POST['user_id'] ?? 0);

        if ($user_id === intval($_SESSION['admin_id'] ?? 0)) {
            $err = "Safety safeguard: You cannot delete your own active logged-in account.";
        } else {
            $chk = $con->query("SELECT username FROM tbl_admin WHERE id = $user_id");
            $u_name = ($chk && $chk->num_rows > 0) ? $chk->fetch_assoc()['username'] : '';

            if ($u_name === 'admin') {
                $err = "Safety safeguard: The primary Super Admin account cannot be deleted.";
            } else {
                $del_stmt = $con->prepare("DELETE FROM tbl_admin WHERE id = ?");
                $del_stmt->bind_param("i", $user_id);
                if ($del_stmt->execute()) {
                    $msg = "Operator account removed successfully.";
                }
                $del_stmt->close();
            }
        }
        $active_tab = 'operators';

    // --- ROLE ACTIONS ---
    } elseif ($action === 'add_role') {
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions_arr = $_POST['permissions'] ?? ['dashboard'];
        $permissions = implode(',', $permissions_arr);
        $status = $_POST['status'] ?? 'Active';

        if (empty($role_name)) {
            $err = "Role name is required.";
        } else {
            $role_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $role_name));
            $role_slug = trim($role_slug, '_');

            $stmt_r = $con->prepare("INSERT INTO tbl_roles (role_name, role_slug, description, permissions, status) VALUES (?, ?, ?, ?, ?)");
            $stmt_r->bind_param("sssss", $role_name, $role_slug, $description, $permissions, $status);
            if ($stmt_r->execute()) {
                $msg = "New operator role '{$role_name}' created successfully!";
            } else {
                $err = "Role name or slug already exists in database.";
            }
            $stmt_r->close();
        }
        $active_tab = 'roles';

    } elseif ($action === 'edit_role') {
        $role_id = intval($_POST['role_id'] ?? 0);
        $role_name = trim($_POST['role_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions_arr = $_POST['permissions'] ?? ['dashboard'];
        $permissions = implode(',', $permissions_arr);
        $status = $_POST['status'] ?? 'Active';

        if ($role_id <= 0 || empty($role_name)) {
            $err = "Valid role ID and role name are required.";
        } else {
            $stmt_r = $con->prepare("UPDATE tbl_roles SET role_name = ?, description = ?, permissions = ?, status = ? WHERE id = ?");
            $stmt_r->bind_param("ssssi", $role_name, $description, $permissions, $status, $role_id);
            if ($stmt_r->execute()) {
                $msg = "Role '{$role_name}' updated successfully!";
            } else {
                $err = "Database error: Could not update role.";
            }
            $stmt_r->close();
        }
        $active_tab = 'roles';

    } elseif ($action === 'delete_role') {
        $role_id = intval($_POST['role_id'] ?? 0);

        // Fetch role name
        $chk_role = $con->query("SELECT role_name FROM tbl_roles WHERE id = $role_id");
        if ($chk_role && $chk_role->num_rows > 0) {
            $r_name = $chk_role->fetch_assoc()['role_name'];
            if ($r_name === 'Super Admin') {
                $err = "Safety safeguard: The Super Admin role cannot be deleted.";
            } else {
                // Check if any operators have this role
                $op_chk = $con->prepare("SELECT COUNT(*) AS cnt FROM tbl_admin WHERE role = ?");
                $op_chk->bind_param("s", $r_name);
                $op_chk->execute();
                $cnt = $op_chk->get_result()->fetch_assoc()['cnt'];
                $op_chk->close();

                if ($cnt > 0) {
                    $err = "Cannot delete role '{$r_name}': It is currently assigned to {$cnt} operator account(s). Reassign them first.";
                } else {
                    $del_r = $con->prepare("DELETE FROM tbl_roles WHERE id = ?");
                    $del_r->bind_param("i", $role_id);
                    if ($del_r->execute()) {
                        $msg = "Role '{$r_name}' deleted successfully.";
                    }
                    $del_r->close();
                }
            }
        }
        $active_tab = 'roles';
    }
}

// Fetch Operators Filter
$filter_status = $_GET['status'] ?? 'all';
$search_q = trim($_GET['q'] ?? '');

$where_parts = [];
if ($filter_status !== 'all') {
    $safe_st = $con->real_escape_string($filter_status);
    $where_parts[] = "status = '$safe_st'";
}
if (!empty($search_q)) {
    $safe_q = $con->real_escape_string($search_q);
    $where_parts[] = "(username LIKE '%$safe_q%' OR full_name LIKE '%$safe_q%' OR email LIKE '%$safe_q%' OR role LIKE '%$safe_q%')";
}
$where_sql = !empty($where_parts) ? "WHERE " . implode(" AND ", $where_parts) : "";

// Fetch Operators
$all_operators = [];
$users_query = $con->query("SELECT * FROM tbl_admin $where_sql ORDER BY id ASC");
if ($users_query) {
    while ($row = $users_query->fetch_assoc()) {
        $all_operators[] = $row;
    }
}

// Fetch Roles
$all_roles = [];
$roles_query = $con->query("SELECT r.*, (SELECT COUNT(*) FROM tbl_admin a WHERE a.role = r.role_name) AS operator_count FROM tbl_roles r ORDER BY id ASC");
if ($roles_query) {
    while ($r_row = $roles_query->fetch_assoc()) {
        $all_roles[] = $r_row;
    }
}

// Metrics
$total_operators = count($all_operators);
$active_operators_cnt = 0;
$disabled_operators_cnt = 0;

$m_query = $con->query("SELECT status, COUNT(*) AS cnt FROM tbl_admin GROUP BY status");
if ($m_query) {
    while ($m_row = $m_query->fetch_assoc()) {
        if ($m_row['status'] === 'Active') $active_operators_cnt = $m_row['cnt'];
        if ($m_row['status'] === 'Disabled') $disabled_operators_cnt = $m_row['cnt'];
    }
}
$total_roles_cnt = count($all_roles);

include_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Operator & Role Management</h1>
    <p class="page-subtitle">Manage administrative staff accounts, configure role permissions, and toggle access.</p>
  </div>
  <div style="display: flex; gap: 0.75rem;">
    <button type="button" class="btn-admin-sec" onclick="openModal('add-role-modal')">
      <i class="fa-solid fa-user-shield"></i>
      <span>Create New Role</span>
    </button>
    <button type="button" class="btn-admin-primary" onclick="openModal('add-operator-modal')">
      <i class="fa-solid fa-user-plus"></i>
      <span>Add New Operator</span>
    </button>
  </div>
</div>

<?php if (!empty($msg)): ?>
  <div style="background: rgba(92,148,51,0.18); border: 1px solid var(--clr-green); color: var(--clr-green-bright); padding: 0.9rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem;">
    <i class="fa-solid fa-circle-check" style="font-size: 1.15rem;"></i>
    <span><?php echo htmlspecialchars($msg); ?></span>
  </div>
<?php endif; ?>

<?php if (!empty($err)): ?>
  <div class="alert-danger" style="margin-bottom: 1.5rem;">
    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem;"></i>
    <span><?php echo htmlspecialchars($err); ?></span>
  </div>
<?php endif; ?>

<!-- KPI Metrics Overview Cards -->
<div class="dashboard-grid" style="margin-bottom: 1.75rem;">
  
  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(212, 160, 23, 0.15); color: var(--clr-gold-bright); border: 1px solid var(--border-gold);">
      <i class="fa-solid fa-users-gear"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Total Operators</span>
      <h3 class="kpi-value"><?php echo $active_operators_cnt + $disabled_operators_cnt; ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(92, 148, 51, 0.15); color: var(--clr-green-bright); border: 1px solid rgba(92, 148, 51, 0.3);">
      <i class="fa-solid fa-user-check"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Active Accounts</span>
      <h3 class="kpi-value"><?php echo $active_operators_cnt; ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(235, 87, 87, 0.15); color: var(--clr-red-bright); border: 1px solid rgba(235, 87, 87, 0.3);">
      <i class="fa-solid fa-user-slash"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Disabled Accounts</span>
      <h3 class="kpi-value"><?php echo $disabled_operators_cnt; ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(200, 99, 56, 0.15); color: var(--clr-terracotta-bright); border: 1px solid rgba(200, 99, 56, 0.3);">
      <i class="fa-solid fa-shield-halved"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Configured Roles</span>
      <h3 class="kpi-value"><?php echo $total_roles_cnt; ?></h3>
    </div>
  </div>

</div>

<!-- Navigation Tabs (Operators vs Roles) -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.75rem;">
  <a href="users.php?tab=operators" class="media-tab-btn <?php echo ($active_tab === 'operators') ? 'active' : ''; ?>">
    <i class="fa-solid fa-users"></i>
    <span>Operators List (<?php echo count($all_operators); ?>)</span>
  </a>
  <a href="users.php?tab=roles" class="media-tab-btn <?php echo ($active_tab === 'roles') ? 'active' : ''; ?>">
    <i class="fa-solid fa-user-shield"></i>
    <span>Role Configuration (<?php echo $total_roles_cnt; ?>)</span>
  </a>
</div>

<?php if ($active_tab === 'operators'): ?>

  <!-- Operators Filter Bar -->
  <div class="filter-bar">
    <div class="filter-tabs">
      <a href="users.php?tab=operators&status=all" class="filter-tab <?php echo ($filter_status === 'all') ? 'active' : ''; ?>">All Status</a>
      <a href="users.php?tab=operators&status=Active" class="filter-tab <?php echo ($filter_status === 'Active') ? 'active' : ''; ?>">Active Only</a>
      <a href="users.php?tab=operators&status=Disabled" class="filter-tab <?php echo ($filter_status === 'Disabled') ? 'active' : ''; ?>">Disabled Only</a>
    </div>

    <form action="users.php" method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
      <input type="hidden" name="tab" value="operators">
      <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
      <div class="header-search" style="width: 260px;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" value="<?php echo htmlspecialchars($search_q); ?>" placeholder="Search operator name...">
      </div>
      <?php if (!empty($search_q)): ?>
        <a href="users.php?tab=operators&status=<?php echo urlencode($filter_status); ?>" class="btn-cropper-action" title="Clear Search">
          <i class="fa-solid fa-xmark"></i>
        </a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Operators Data Table -->
  <div class="content-card">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Operator</th>
            <th>Email Contact</th>
            <th>Assigned Role</th>
            <th>Account Status</th>
            <th>Last Login</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($all_operators)): ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                <i class="fa-solid fa-users-slash" style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--clr-terracotta);"></i><br>
                No operator accounts found matching the current filters.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($all_operators as $op): ?>
              <?php 
                $is_current_user = ($op['id'] == ($_SESSION['admin_id'] ?? 0)); 
                $is_primary_admin = ($op['username'] === 'admin');
              ?>
              <tr>
                <td>
                  <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <div class="user-avatar" style="width: 42px; height: 42px; font-size: 1.05rem; flex-shrink: 0;">
                      <?php echo strtoupper(substr($op['username'], 0, 1)); ?>
                    </div>
                    <div>
                      <a href="javascript:void(0)" 
                         onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($op)); ?>)"
                         style="color: var(--text-primary); text-decoration: none; font-weight: 600; font-size: 0.95rem; font-family: var(--font-heading); display: inline-flex; align-items: center; gap: 0.4rem; transition: var(--transition-fast);"
                         onmouseover="this.style.color='var(--clr-gold)'" 
                         onmouseout="this.style.color='var(--text-primary)'" 
                         title="Click to edit operator">
                        <span><?php echo htmlspecialchars($op['full_name']); ?></span>
                        <i class="fa-solid fa-pen-to-square" style="font-size: 0.75rem; color: var(--clr-gold); opacity: 0.7;"></i>
                      </a><br>
                      <small style="color: var(--text-muted); font-size: 0.775rem;">@<?php echo htmlspecialchars($op['username']); ?> <?php if ($is_current_user): ?><strong style="color: var(--clr-gold-bright);">(You)</strong><?php endif; ?></small>
                    </div>
                  </div>
                </td>
                <td>
                  <a href="mailto:<?php echo htmlspecialchars($op['email']); ?>" style="color: var(--text-secondary); text-decoration: none; font-size: 0.85rem;" onmouseover="this.style.color='var(--clr-gold)'" onmouseout="this.style.color='var(--text-secondary)'">
                    <i class="fa-regular fa-envelope" style="color: var(--clr-gold); font-size: 0.8rem; margin-right: 0.25rem;"></i>
                    <?php echo htmlspecialchars($op['email']); ?>
                  </a>
                </td>
                <td>
                  <span style="background: rgba(212,160,23,0.12); color: var(--clr-gold-bright); border: 1px solid var(--border-gold); padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 600;">
                    <i class="fa-solid fa-shield"></i> <?php echo htmlspecialchars($op['role']); ?>
                  </span>
                </td>
                <td>
                  <?php if (strtolower($op['status']) === 'active'): ?>
                    <span class="badge-status active">
                      <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Active
                    </span>
                  <?php else: ?>
                    <span class="badge-status cancelled" style="background: rgba(235, 87, 87, 0.15); color: var(--clr-red-bright); border: 1px solid rgba(235, 87, 87, 0.3);">
                      <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Disabled
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <small style="color: var(--text-muted); font-size: 0.8rem;">
                    <?php echo !empty($op['last_login']) ? date('d M Y, H:i', strtotime($op['last_login'])) : 'Never logged in'; ?>
                  </small>
                </td>
                <td>
                  <div class="table-actions">
                    
                    <!-- Edit Button -->
                    <button type="button" class="btn-action-icon success" title="Edit Operator Details"
                            onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($op)); ?>)">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </button>

                    <!-- Toggle Enable / Disable Status Form -->
                    <?php if (!$is_current_user && !$is_primary_admin): ?>
                      <form action="users.php?tab=operators&status=<?php echo urlencode($filter_status); ?>" method="POST" style="display: flex;">
                        <input type="hidden" name="action" value="toggle_user_status">
                        <input type="hidden" name="user_id" value="<?php echo $op['id']; ?>">
                        
                        <?php if (strtolower($op['status']) === 'active'): ?>
                          <button type="submit" name="status" value="Disabled" class="btn-action-icon danger" title="Disable Operator Account" onclick="return confirm('Disable operator account for <?php echo htmlspecialchars($op['username']); ?>?');">
                            <i class="fa-solid fa-user-slash"></i>
                          </button>
                        <?php else: ?>
                          <button type="submit" name="status" value="Active" class="btn-action-icon success" title="Enable Operator Account">
                            <i class="fa-solid fa-user-check"></i>
                          </button>
                        <?php endif; ?>
                      </form>
                    <?php endif; ?>

                    <!-- Delete Button Form -->
                    <?php if (!$is_current_user && !$is_primary_admin): ?>
                      <form action="users.php?tab=operators&status=<?php echo urlencode($filter_status); ?>" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete operator <?php echo htmlspecialchars($op['username']); ?>?');">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" value="<?php echo $op['id']; ?>">
                        <button type="submit" class="btn-action-icon danger" title="Delete Operator">
                          <i class="fa-solid fa-trash-can"></i>
                        </button>
                      </form>
                    <?php endif; ?>

                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php else: ?>

  <!-- Roles Configuration Tab -->
  <div class="content-card">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Role Name</th>
            <th>Description</th>
            <th>Accessible Modules</th>
            <th>Assigned Staff</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($all_roles as $role_item): ?>
            <tr>
              <td>
                <strong style="color: var(--text-primary); font-size: 0.95rem; font-family: var(--font-heading);">
                  <i class="fa-solid fa-shield-halved" style="color: var(--clr-gold); margin-right: 0.35rem;"></i>
                  <?php echo htmlspecialchars($role_item['role_name']); ?>
                </strong><br>
                <code style="color: var(--clr-terracotta-bright); font-size: 0.775rem;"><?php echo htmlspecialchars($role_item['role_slug']); ?></code>
              </td>
              <td style="max-width: 260px; color: var(--text-secondary); font-size: 0.85rem;">
                <?php echo htmlspecialchars($role_item['description'] ?? 'Standard operational permissions.'); ?>
              </td>
              <td>
                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                  <?php 
                    $perm_list = explode(',', $role_item['permissions'] ?? 'dashboard');
                    foreach ($perm_list as $p_tag):
                      $p_tag = trim($p_tag);
                      if ($p_tag === 'all') {
                          echo "<span class='badge-img-status system'><i class='fa-solid fa-star'></i> Full Access (All)</span>";
                      } else {
                          echo "<span class='badge-img-status used'>" . ucfirst($p_tag) . "</span>";
                      }
                    endforeach;
                  ?>
                </div>
              </td>
              <td>
                <span style="background: var(--bg-admin-surface); border: 1px solid var(--border-subtle); color: var(--clr-gold-bright); font-weight: 700; padding: 0.2rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.85rem;">
                  <?php echo $role_item['operator_count']; ?> Staff
                </span>
              </td>
              <td>
                <?php if (strtolower($role_item['status']) === 'active'): ?>
                  <span class="badge-status active"><i class="fa-solid fa-circle" style="font-size: 6px;"></i> Active</span>
                <?php else: ?>
                  <span class="badge-status cancelled"><i class="fa-solid fa-circle" style="font-size: 6px;"></i> Inactive</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="table-actions">
                  <button type="button" class="btn-action-icon success" title="Edit Role"
                          onclick="openEditRoleModal(<?php echo htmlspecialchars(json_encode($role_item)); ?>)">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>

                  <?php if ($role_item['role_name'] !== 'Super Admin'): ?>
                    <form action="users.php?tab=roles" method="POST" onsubmit="return confirm('Delete role <?php echo htmlspecialchars($role_item['role_name']); ?>?');">
                      <input type="hidden" name="action" value="delete_role">
                      <input type="hidden" name="role_id" value="<?php echo $role_item['id']; ?>">
                      <button type="submit" class="btn-action-icon danger" title="Delete Role">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php endif; ?>

<!-- ==========================================================================
     MODALS
     ========================================================================== -->

<!-- Modal: Add Operator -->
<div class="admin-modal-overlay" id="add-operator-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('add-operator-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-user-plus"></i> Add New Operator
    </h3>
    <form action="users.php?tab=operators" method="POST" autocomplete="off">
      <input type="hidden" name="action" value="add_user">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required placeholder="e.g. Vikram Singh">
        </div>
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required placeholder="e.g. vikram_staff">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required placeholder="vikram@dabamagic.com">
        </div>
        <div class="form-group">
          <label class="form-label">Login Password</label>
          <input type="password" name="password" class="form-control" required minlength="6" placeholder="Min. 6 characters">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Assign Role</label>
          <select name="role" class="form-control" required>
            <?php foreach ($all_roles as $r_opt): ?>
              <option value="<?php echo htmlspecialchars($r_opt['role_name']); ?>">
                <?php echo htmlspecialchars($r_opt['role_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Account Status</label>
          <select name="status" class="form-control">
            <option value="Active" selected>Active (Enabled)</option>
            <option value="Disabled">Disabled (No Login Access)</option>
          </select>
        </div>
      </div>

      <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
        <button type="submit" class="btn-admin-primary">
          <i class="fa-solid fa-check"></i>
          <span>Create Operator Account</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit Operator -->
<div class="admin-modal-overlay" id="edit-operator-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('edit-operator-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-user-pen"></i> Edit Operator Account
    </h3>
    <form action="users.php?tab=operators" method="POST" autocomplete="off">
      <input type="hidden" name="action" value="edit_user">
      <input type="hidden" name="user_id" id="edit-user-id">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" id="edit-user-fullname" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" id="edit-user-username" class="form-control" disabled style="opacity: 0.6; cursor: not-allowed;">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" id="edit-user-email" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Reset Password <small style="color: var(--text-muted);">(Leave blank to keep current)</small></label>
          <input type="password" name="new_password" class="form-control" minlength="6" placeholder="Enter new password">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Assign Role</label>
          <select name="role" id="edit-user-role" class="form-control" required>
            <?php foreach ($all_roles as $r_opt): ?>
              <option value="<?php echo htmlspecialchars($r_opt['role_name']); ?>">
                <?php echo htmlspecialchars($r_opt['role_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Account Status</label>
          <select name="status" id="edit-user-status" class="form-control">
            <option value="Active">Active (Enabled)</option>
            <option value="Disabled">Disabled (No Login Access)</option>
          </select>
        </div>
      </div>

      <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
        <button type="submit" class="btn-admin-primary">
          <i class="fa-solid fa-check"></i>
          <span>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Add Role -->
<div class="admin-modal-overlay" id="add-role-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('add-role-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-shield-halved"></i> Create New Role
    </h3>
    <form action="users.php?tab=roles" method="POST">
      <input type="hidden" name="action" value="add_role">

      <div class="form-group">
        <label class="form-label">Role Name</label>
        <input type="text" name="role_name" class="form-control" required placeholder="e.g. Reservation Supervisor">
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Brief outline of responsibilities..."></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Module Permissions</label>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; background: var(--bg-admin-surface); padding: 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="dashboard" checked style="accent-color: var(--clr-gold);"> Dashboard Analytics
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="reservations" checked style="accent-color: var(--clr-gold);"> Reservations
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="categories" style="accent-color: var(--clr-gold);"> Categories
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="menu" style="accent-color: var(--clr-gold);"> Menu Items Catalog
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="users" style="accent-color: var(--clr-gold);"> Operator Management
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="all" style="accent-color: var(--clr-gold);"> Full Admin Access (All)
          </label>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Role Status</label>
        <select name="status" class="form-control">
          <option value="Active" selected>Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>

      <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
        <button type="submit" class="btn-admin-primary">
          <i class="fa-solid fa-check"></i>
          <span>Save Role</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit Role -->
<div class="admin-modal-overlay" id="edit-role-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('edit-role-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-shield-halved"></i> Edit Role Configuration
    </h3>
    <form action="users.php?tab=roles" method="POST">
      <input type="hidden" name="action" value="edit_role">
      <input type="hidden" name="role_id" id="edit-role-id">

      <div class="form-group">
        <label class="form-label">Role Name</label>
        <input type="text" name="role_name" id="edit-role-name" class="form-control" required>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" id="edit-role-desc" class="form-control" rows="2"></textarea>
      </div>

      <div class="form-group">
        <label class="form-label">Module Permissions</label>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; background: var(--bg-admin-surface); padding: 0.85rem; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="dashboard" id="perm-dashboard" style="accent-color: var(--clr-gold);"> Dashboard Analytics
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="reservations" id="perm-reservations" style="accent-color: var(--clr-gold);"> Reservations
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="categories" id="perm-categories" style="accent-color: var(--clr-gold);"> Categories
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="menu" id="perm-menu" style="accent-color: var(--clr-gold);"> Menu Items Catalog
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="users" id="perm-users" style="accent-color: var(--clr-gold);"> Operator Management
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-primary); font-size: 0.85rem;">
            <input type="checkbox" name="permissions[]" value="all" id="perm-all" style="accent-color: var(--clr-gold);"> Full Admin Access (All)
          </label>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Role Status</label>
        <select name="status" id="edit-role-status" class="form-control">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>

      <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
        <button type="submit" class="btn-admin-primary">
          <i class="fa-solid fa-check"></i>
          <span>Save Role Changes</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(id) { document.getElementById(id).classList.add('active'); }
  function closeModal(id) { document.getElementById(id).classList.remove('active'); }

  function openEditUserModal(user) {
    document.getElementById('edit-user-id').value = user.id;
    document.getElementById('edit-user-fullname').value = user.full_name;
    document.getElementById('edit-user-username').value = user.username;
    document.getElementById('edit-user-email').value = user.email;
    document.getElementById('edit-user-role').value = user.role;
    document.getElementById('edit-user-status').value = user.status;
    openModal('edit-operator-modal');
  }

  function openEditRoleModal(role) {
    document.getElementById('edit-role-id').value = role.id;
    document.getElementById('edit-role-name').value = role.role_name;
    document.getElementById('edit-role-desc').value = role.description || '';
    document.getElementById('edit-role-status').value = role.status;

    // Reset checkboxes
    const permBoxes = ['dashboard', 'reservations', 'categories', 'menu', 'users', 'all'];
    permBoxes.forEach(p => {
      const el = document.getElementById('perm-' + p);
      if (el) el.checked = false;
    });

    // Check saved permissions
    if (role.permissions) {
      const savedPerms = role.permissions.split(',');
      savedPerms.forEach(p => {
        const el = document.getElementById('perm-' + p.trim());
        if (el) el.checked = true;
      });
    }

    openModal('edit-role-modal');
  }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
