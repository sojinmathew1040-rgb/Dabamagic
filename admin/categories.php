<?php
/**
 * Daba Magic - Admin Category Management Page
 */

require_once __DIR__ . '/includes/auth_check.php';

$page_title = "Category Management - Daba Magic Admin Panel";

$msg = "";
$err = "";

// Helper function to generate slug
function generate_category_slug($name) {
    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    return trim($slug, '-');
}

// Process CRUD Operations for Categories
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = generate_category_slug($name);
        } else {
            $slug = generate_category_slug($slug);
        }
        $description = trim($_POST['description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);
        $status = $_POST['status'] ?? 'Active';

        if (!empty($name)) {
            // Check for duplicates
            $chk = $con->prepare("SELECT id FROM tbl_categories WHERE name = ? OR slug = ?");
            $chk->bind_param("ss", $name, $slug);
            $chk->execute();
            $res = $chk->get_result();
            if ($res->num_rows > 0) {
                $err = "A category with the name '{$name}' or slug '{$slug}' already exists.";
            } else {
                $stmt = $con->prepare("INSERT INTO tbl_categories (name, slug, description, status, display_order) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssi", $name, $slug, $description, $status, $display_order);
                if ($stmt->execute()) {
                    $msg = "New category '{$name}' created successfully!";
                } else {
                    $err = "Database error: Unable to add category.";
                }
                $stmt->close();
            }
            $chk->close();
        } else {
            $err = "Category name is required.";
        }

    } elseif ($action === 'edit_category') {
        $cat_id = intval($_POST['cat_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = generate_category_slug($name);
        } else {
            $slug = generate_category_slug($slug);
        }
        $description = trim($_POST['description'] ?? '');
        $display_order = intval($_POST['display_order'] ?? 0);
        $status = $_POST['status'] ?? 'Active';

        if ($cat_id > 0 && !empty($name)) {
            // Get original name to sync tbl_menu_items if category name changes
            $orig_query = $con->prepare("SELECT name FROM tbl_categories WHERE id = ?");
            $orig_query->bind_param("i", $cat_id);
            $orig_query->execute();
            $orig_res = $orig_query->get_result();
            $old_name = "";
            if ($orig_row = $orig_res->fetch_assoc()) {
                $old_name = $orig_row['name'];
            }
            $orig_query->close();

            // Check duplicate name/slug (excluding current ID)
            $chk = $con->prepare("SELECT id FROM tbl_categories WHERE (name = ? OR slug = ?) AND id != ?");
            $chk->bind_param("ssi", $name, $slug, $cat_id);
            $chk->execute();
            if ($chk->get_result()->num_rows > 0) {
                $err = "Another category with the name '{$name}' or slug '{$slug}' already exists.";
            } else {
                $stmt = $con->prepare("UPDATE tbl_categories SET name = ?, slug = ?, description = ?, status = ?, display_order = ? WHERE id = ?");
                $stmt->bind_param("ssssii", $name, $slug, $description, $status, $display_order, $cat_id);
                if ($stmt->execute()) {
                    // Update category name in tbl_menu_items if changed
                    if (!empty($old_name) && $old_name !== $name) {
                        $upd_menu = $con->prepare("UPDATE tbl_menu_items SET category = ? WHERE category = ?");
                        $upd_menu->bind_param("ss", $name, $old_name);
                        $upd_menu->execute();
                        $upd_menu->close();
                    }
                    $msg = "Category '{$name}' updated successfully.";
                } else {
                    $err = "Database error: Unable to update category.";
                }
                $stmt->close();
            }
            $chk->close();
        }

    } elseif ($action === 'toggle_status') {
        $cat_id = intval($_POST['cat_id'] ?? 0);
        $new_status = $_POST['status'] ?? 'Active';

        if ($cat_id > 0) {
            $stmt = $con->prepare("UPDATE tbl_categories SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $cat_id);
            if ($stmt->execute()) {
                $msg = "Category status updated to '{$new_status}'.";
            }
            $stmt->close();
        }

    } elseif ($action === 'delete_category') {
        $cat_id = intval($_POST['cat_id'] ?? 0);

        if ($cat_id > 0) {
            // Get category name
            $c_query = $con->prepare("SELECT name FROM tbl_categories WHERE id = ?");
            $c_query->bind_param("i", $cat_id);
            $c_query->execute();
            $c_res = $c_query->get_result();
            if ($c_row = $c_res->fetch_assoc()) {
                $cat_name = $c_row['name'];

                // Check linked menu items
                $chk_items = $con->prepare("SELECT COUNT(*) AS cnt FROM tbl_menu_items WHERE category = ?");
                $chk_items->bind_param("s", $cat_name);
                $chk_items->execute();
                $item_cnt = $chk_items->get_result()->fetch_assoc()['cnt'];
                $chk_items->close();

                if ($item_cnt > 0) {
                    $err = "Cannot delete category '{$cat_name}' because it contains {$item_cnt} dish(es). Reassign or remove dishes first.";
                } else {
                    $del_stmt = $con->prepare("DELETE FROM tbl_categories WHERE id = ?");
                    $del_stmt->bind_param("i", $cat_id);
                    if ($del_stmt->execute()) {
                        $msg = "Category '{$cat_name}' has been deleted.";
                    }
                    $del_stmt->close();
                }
            }
            $c_query->close();
        }
    }
}

// KPI Stats Computation
$total_cats = 0;
$active_cats = 0;
$inactive_cats = 0;
$total_dishes = 0;

$r1 = $con->query("SELECT COUNT(*) AS cnt FROM tbl_categories");
if ($r1) { $total_cats = $r1->fetch_assoc()['cnt']; }

$r2 = $con->query("SELECT COUNT(*) AS cnt FROM tbl_categories WHERE status = 'Active'");
if ($r2) { $active_cats = $r2->fetch_assoc()['cnt']; }

$r3 = $con->query("SELECT COUNT(*) AS cnt FROM tbl_categories WHERE status = 'Inactive'");
if ($r3) { $inactive_cats = $r3->fetch_assoc()['cnt']; }

$r4 = $con->query("SELECT COUNT(*) AS cnt FROM tbl_menu_items");
if ($r4) { $total_dishes = $r4->fetch_assoc()['cnt']; }

// Filter & Search Logic
$filter_status = $_GET['status'] ?? 'all';
$search_query = trim($_GET['search'] ?? '');

$conditions = [];
if ($filter_status !== 'all') {
    $safe_st = $con->real_escape_string($filter_status);
    $conditions[] = "c.status = '$safe_st'";
}
if (!empty($search_query)) {
    $safe_sq = $con->real_escape_string($search_query);
    $conditions[] = "(c.name LIKE '%$safe_sq%' OR c.slug LIKE '%$safe_sq%' OR c.description LIKE '%$safe_sq%')";
}

$where_clause = "";
if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// Query Categories with Linked Item Count
$categories_list = [];
$cat_sql = "SELECT c.*, (SELECT COUNT(*) FROM tbl_menu_items m WHERE m.category = c.name) AS item_count 
            FROM tbl_categories c 
            $where_clause 
            ORDER BY c.display_order ASC, c.name ASC";
$res_cats = $con->query($cat_sql);
if ($res_cats) {
    while ($row = $res_cats->fetch_assoc()) {
        $categories_list[] = $row;
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<!-- Page Title Header -->
<div class="page-header">
  <div>
    <h1 class="page-title">Category Management</h1>
    <p class="page-subtitle">Create, organize, and manage food categories for Daba Magic dynamic menu catalog.</p>
  </div>
  <div>
    <button type="button" class="btn-admin-primary" onclick="openModal('add-category-modal')">
      <i class="fa-solid fa-folder-plus"></i>
      <span>Add New Category</span>
    </button>
  </div>
</div>

<!-- Flash Notifications -->
<?php if (!empty($msg)): ?>
  <div style="background: rgba(92,148,51,0.18); border: 1px solid var(--clr-green); color: var(--clr-green-bright); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem;">
    <i class="fa-solid fa-circle-check"></i>
    <span><?php echo htmlspecialchars($msg); ?></span>
  </div>
<?php endif; ?>

<?php if (!empty($err)): ?>
  <div class="alert-danger" style="margin-bottom: 1.5rem;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <span><?php echo htmlspecialchars($err); ?></span>
  </div>
<?php endif; ?>

<!-- KPI Stat Cards Grid -->
<div class="stats-grid">
  
  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Total Categories</span>
      <span class="stat-value"><?php echo number_format($total_cats); ?></span>
      <span class="stat-trend up"><i class="fa-solid fa-layer-group"></i> Dynamic Taxonomy</span>
    </div>
    <div class="stat-icon-wrap">
      <i class="fa-solid fa-tags"></i>
    </div>
  </div>

  <div class="stat-card green">
    <div class="stat-info">
      <span class="stat-label">Active Categories</span>
      <span class="stat-value"><?php echo number_format($active_cats); ?></span>
      <span class="stat-trend up"><i class="fa-solid fa-eye"></i> Visible to Customers</span>
    </div>
    <div class="stat-icon-wrap">
      <i class="fa-solid fa-circle-check"></i>
    </div>
  </div>

  <div class="stat-card gold">
    <div class="stat-info">
      <span class="stat-label">Inactive / Draft</span>
      <span class="stat-value"><?php echo number_format($inactive_cats); ?></span>
      <span class="stat-trend neutral"><i class="fa-solid fa-eye-slash"></i> Hidden Categories</span>
    </div>
    <div class="stat-icon-wrap">
      <i class="fa-solid fa-folder-closed"></i>
    </div>
  </div>

  <div class="stat-card purple">
    <div class="stat-info">
      <span class="stat-label">Total Linked Dishes</span>
      <span class="stat-value"><?php echo number_format($total_dishes); ?></span>
      <span class="stat-trend up" style="color: #BA68C8;"><i class="fa-solid fa-utensils"></i> Active Catalog</span>
    </div>
    <div class="stat-icon-wrap">
      <i class="fa-solid fa-bowl-food"></i>
    </div>
  </div>

</div>

<!-- Filter & Search Controls -->
<div class="filter-bar">
  <div class="filter-tabs">
    <a href="categories.php?status=all&search=<?php echo urlencode($search_query); ?>" class="filter-tab <?php echo ($filter_status == 'all') ? 'active' : ''; ?>">All Statuses</a>
    <a href="categories.php?status=Active&search=<?php echo urlencode($search_query); ?>" class="filter-tab <?php echo ($filter_status == 'Active') ? 'active' : ''; ?>">Active Only</a>
    <a href="categories.php?status=Inactive&search=<?php echo urlencode($search_query); ?>" class="filter-tab <?php echo ($filter_status == 'Inactive') ? 'active' : ''; ?>">Inactive</a>
  </div>

  <form action="categories.php" method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
    <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
    <div class="header-search" style="width: 260px;">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search category name..." style="width: 100%;">
    </div>
    <?php if (!empty($search_query)): ?>
      <a href="categories.php?status=<?php echo urlencode($filter_status); ?>" class="btn-action-icon danger" title="Clear search">
        <i class="fa-solid fa-xmark"></i>
      </a>
    <?php endif; ?>
  </form>
</div>

<!-- Categories Table -->
<div class="content-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th style="width: 70px;">Order</th>
          <th>Category Details</th>
          <th>Slug / Identifier</th>
          <th>Description</th>
          <th>Dishes Count</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($categories_list)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
              <i class="fa-solid fa-layer-group" style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--clr-terracotta);"></i><br>
              No categories found. Click "Add New Category" to create one.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($categories_list as $cat): ?>
            <tr>
              <td>
                <span style="background: var(--bg-admin-surface); border: 1px solid var(--border-subtle); color: var(--clr-gold-bright); font-weight: 700; padding: 0.2rem 0.6rem; border-radius: var(--radius-sm); font-size: 0.85rem;">
                  #<?php echo $cat['display_order']; ?>
                </span>
              </td>
              <td>
                <strong style="color: var(--text-primary); font-size: 0.95rem; font-family: var(--font-heading);">
                  <?php echo htmlspecialchars($cat['name']); ?>
                </strong>
              </td>
              <td>
                <code style="background: rgba(200, 99, 56, 0.12); color: var(--clr-terracotta-bright); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.825rem;">
                  <?php echo htmlspecialchars($cat['slug']); ?>
                </code>
              </td>
              <td style="max-width: 280px;">
                <small style="color: var(--text-muted); display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                  <?php echo !empty($cat['description']) ? htmlspecialchars($cat['description']) : '<em>No description provided</em>'; ?>
                </small>
              </td>
              <td>
                <a href="menu.php?cat=<?php echo urlencode($cat['name']); ?>" style="display: inline-flex; align-items: center; gap: 0.4rem; background: rgba(212,160,23,0.12); color: var(--clr-gold-bright); padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 600; text-decoration: none;" title="Manage dishes in this category">
                  <i class="fa-solid fa-utensils"></i>
                  <span><?php echo $cat['item_count']; ?> dish<?php echo ($cat['item_count'] == 1 ? '' : 'es'); ?></span>
                </a>
              </td>
              <td>
                <?php
                  $st = strtolower($cat['status']);
                  echo "<span class='badge-status {$st}'><i class='fa-solid fa-circle' style='font-size: 6px;'></i> " . htmlspecialchars($cat['status']) . "</span>";
                ?>
              </td>
              <td>
                <div class="table-actions">
                  
                  <!-- Edit Button Trigger -->
                  <button type="button" class="btn-action-icon" title="Edit Category"
                          onclick="openEditModal(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>

                  <!-- Toggle Status Form -->
                  <form action="categories.php?status=<?php echo urlencode($filter_status); ?>" method="POST" style="display: inline;">
                    <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">
                    <input type="hidden" name="action" value="toggle_status">
                    <?php if ($cat['status'] === 'Active'): ?>
                      <button type="submit" name="status" value="Inactive" class="btn-action-icon danger" title="Deactivate Category">
                        <i class="fa-solid fa-eye-slash"></i>
                      </button>
                    <?php else: ?>
                      <button type="submit" name="status" value="Active" class="btn-action-icon success" title="Activate Category">
                        <i class="fa-solid fa-eye"></i>
                      </button>
                    <?php endif; ?>
                  </form>

                  <!-- Delete Category Form -->
                  <form action="categories.php?status=<?php echo urlencode($filter_status); ?>" method="POST" style="display: inline;" 
                        onsubmit="return confirm('Are you sure you want to delete category \'<?php echo htmlspecialchars($cat['name']); ?>\'?');">
                    <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">
                    <input type="hidden" name="action" value="delete_category">
                    <button type="submit" class="btn-action-icon danger" title="Delete Category">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </form>

                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Add New Category -->
<div class="admin-modal-overlay" id="add-category-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('add-category-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-folder-plus"></i> Add New Category
    </h3>
    <form action="categories.php?status=<?php echo urlencode($filter_status); ?>" method="POST">
      <input type="hidden" name="action" value="add_category">
      
      <div class="form-group">
        <label class="form-label">Category Name</label>
        <input type="text" name="name" id="add-cat-name" class="form-control" required placeholder="e.g. Chef's Tasting Menu" onkeyup="autoGenerateSlug('add-cat-name', 'add-cat-slug')">
      </div>

      <div class="form-group">
        <label class="form-label">Slug / Identifier</label>
        <input type="text" name="slug" id="add-cat-slug" class="form-control" placeholder="e.g. chefs-tasting-menu">
        <small style="color: var(--text-muted); font-size: 0.775rem;">Used for frontend tabs and URL parameters. Auto-generated if left empty.</small>
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Short description of items in this category..."></textarea>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Display Order</label>
          <input type="number" name="display_order" class="form-control" value="0" min="0" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="Active" selected>Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin-primary">
          <span>Create Category</span>
          <i class="fa-solid fa-plus"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit Category -->
<div class="admin-modal-overlay" id="edit-category-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('edit-category-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-pen-to-square"></i> Edit Category
    </h3>
    <form action="categories.php?status=<?php echo urlencode($filter_status); ?>" method="POST">
      <input type="hidden" name="action" value="edit_category">
      <input type="hidden" name="cat_id" id="edit-cat-id">
      
      <div class="form-group">
        <label class="form-label">Category Name</label>
        <input type="text" name="name" id="edit-cat-name" class="form-control" required placeholder="e.g. Starters & Appetizers">
      </div>

      <div class="form-group">
        <label class="form-label">Slug / Identifier</label>
        <input type="text" name="slug" id="edit-cat-slug" class="form-control" required placeholder="e.g. starters">
      </div>

      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" id="edit-cat-desc" class="form-control" rows="3"></textarea>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Display Order</label>
          <input type="number" name="display_order" id="edit-cat-order" class="form-control" min="0" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" id="edit-cat-status" class="form-control">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin-primary">
          <span>Save Changes</span>
          <i class="fa-solid fa-check"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(id) {
    document.getElementById(id).classList.add('active');
  }

  function closeModal(id) {
    document.getElementById(id).classList.remove('active');
  }

  function autoGenerateSlug(nameId, slugId) {
    const nameVal = document.getElementById(nameId).value;
    const slugVal = nameVal.toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
    document.getElementById(slugId).value = slugVal;
  }

  function openEditModal(category) {
    document.getElementById('edit-cat-id').value = category.id;
    document.getElementById('edit-cat-name').value = category.name;
    document.getElementById('edit-cat-slug').value = category.slug;
    document.getElementById('edit-cat-desc').value = category.description || '';
    document.getElementById('edit-cat-order').value = category.display_order;
    document.getElementById('edit-cat-status').value = category.status;
    openModal('edit-category-modal');
  }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
