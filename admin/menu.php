<?php
/**
 * Daba Magic - Admin Menu Management Page
 */

require_once __DIR__ . '/includes/auth_check.php';

$page_title = "Menu Catalog Management - Daba Magic Admin Panel";

$msg = "";

// Process CRUD Operations for Menu Items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_item') {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Curries');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0.00);
        $image = trim($_POST['image'] ?? 'default_dish.png');
        $is_special = isset($_POST['is_special']) ? 1 : 0;
        $status = $_POST['status'] ?? 'Active';

        if (!empty($name) && $price > 0) {
            $stmt = $con->prepare("INSERT INTO tbl_menu_items (name, category, description, price, image, is_special, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdsis", $name, $category, $description, $price, $image, $is_special, $status);
            if ($stmt->execute()) {
                $msg = "New dish '{$name}' added to menu catalog!";
            }
            $stmt->close();
        }
    } elseif ($action === 'update_status') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $new_status = $_POST['status'] ?? 'Active';
        if ($item_id > 0) {
            $stmt = $con->prepare("UPDATE tbl_menu_items SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $item_id);
            if ($stmt->execute()) {
                $msg = "Menu item availability set to '{$new_status}'.";
            }
            $stmt->close();
        }
    } elseif ($action === 'delete_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        if ($item_id > 0) {
            $stmt = $con->prepare("DELETE FROM tbl_menu_items WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            if ($stmt->execute()) {
                $msg = "Dish removed from catalog.";
            }
            $stmt->close();
        }
    }
}

// Category & Status Filters
$filter_cat = $_GET['cat'] ?? 'all';
$filter_status = $_GET['status'] ?? 'all';

$conditions = [];
if ($filter_cat !== 'all') {
    $safe_cat = $con->real_escape_string($filter_cat);
    $conditions[] = "category = '$safe_cat'";
}
if ($filter_status !== 'all') {
    $safe_st = $con->real_escape_string($filter_status);
    $conditions[] = "status = '$safe_st'";
}

$where_clause = "";
if (!empty($conditions)) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// Fetch Menu Items
$all_menu_items = [];
$menu_query = $con->query("SELECT * FROM tbl_menu_items $where_clause ORDER BY id DESC");
if ($menu_query) {
    while ($row = $menu_query->fetch_assoc()) {
        $all_menu_items[] = $row;
    }
}

// Distinct Categories for Filter Dropdown
$categories = ['Biryani & Rice', 'Tandoor Specials', 'Curries', 'South Indian', 'Vegetarian', 'Beverages', 'Desserts'];

include_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Menu Catalog Management</h1>
    <p class="page-subtitle">Add, edit, or toggle availability of dishes served at Daba Magic.</p>
  </div>
  <div>
    <button type="button" class="btn-admin-primary" onclick="openModal('add-menu-modal')">
      <i class="fa-solid fa-utensils"></i>
      <span>Add New Dish</span>
    </button>
  </div>
</div>

<?php if (!empty($msg)): ?>
  <div style="background: rgba(92,148,51,0.18); border: 1px solid var(--clr-green); color: var(--clr-green-bright); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem;">
    <i class="fa-solid fa-circle-check"></i>
    <span><?php echo htmlspecialchars($msg); ?></span>
  </div>
<?php endif; ?>

<!-- Filter Controls -->
<div class="filter-bar">
  <div class="filter-tabs">
    <a href="menu.php?cat=all&status=<?php echo urlencode($filter_status); ?>" class="filter-tab <?php echo ($filter_cat == 'all') ? 'active' : ''; ?>">All Categories</a>
    <?php foreach ($categories as $cat): ?>
      <a href="menu.php?cat=<?php echo urlencode($cat); ?>&status=<?php echo urlencode($filter_status); ?>" class="filter-tab <?php echo ($filter_cat == $cat) ? 'active' : ''; ?>">
        <?php echo htmlspecialchars($cat); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div style="color: var(--text-muted); font-size: 0.85rem;">
    Total Dishes: <strong style="color: var(--clr-gold-bright);"><?php echo count($all_menu_items); ?></strong>
  </div>
</div>

<!-- Menu Catalog Data Table -->
<div class="content-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Dish Info</th>
          <th>Category</th>
          <th>Price</th>
          <th>Chef Special</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($all_menu_items)): ?>
          <tr>
            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
              <i class="fa-solid fa-utensils" style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--clr-terracotta);"></i><br>
              No menu items found in this category.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($all_menu_items as $item): ?>
            <tr>
              <td>
                <div style="display: flex; align-items: center; gap: 1rem;">
                  <div style="width: 48px; height: 48px; border-radius: var(--radius-sm); overflow: hidden; background: var(--bg-admin-surface); border: 1px solid var(--border-subtle); flex-shrink: 0;">
                    <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://via.placeholder.com/48x48?text=Food'">
                  </div>
                  <div>
                    <strong style="color: var(--text-primary); font-size: 0.95rem;"><?php echo htmlspecialchars($item['name']); ?></strong><br>
                    <small style="color: var(--text-muted); display: block; max-width: 320px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                      <?php echo htmlspecialchars($item['description']); ?>
                    </small>
                  </div>
                </div>
              </td>
              <td>
                <span style="background: rgba(212,160,23,0.12); color: var(--clr-gold-bright); padding: 0.25rem 0.75rem; border-radius: var(--radius-full); font-size: 0.8rem; font-weight: 500;">
                  <?php echo htmlspecialchars($item['category']); ?>
                </span>
              </td>
              <td>
                <strong style="color: var(--clr-terracotta-bright); font-size: 1rem;">
                  €<?php echo number_format($item['price'], 2); ?>
                </strong>
              </td>
              <td>
                <?php if ($item['is_special']): ?>
                  <span style="color: var(--clr-gold-bright); font-size: 0.825rem; font-weight: 600;">
                    <i class="fa-solid fa-star"></i> Featured
                  </span>
                <?php else: ?>
                  <span style="color: var(--text-muted); font-size: 0.825rem;">Standard</span>
                <?php endif; ?>
              </td>
              <td>
                <?php
                  $st = strtolower($item['status']);
                  echo "<span class='badge-status {$st}'><i class='fa-solid fa-circle' style='font-size: 6px;'></i> " . htmlspecialchars($item['status']) . "</span>";
                ?>
              </td>
              <td>
                <div class="table-actions">
                  
                  <!-- Toggle Status Form -->
                  <form action="menu.php?cat=<?php echo urlencode($filter_cat); ?>" method="POST" style="display: flex; gap: 0.35rem;">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <input type="hidden" name="action" value="update_status">
                    
                    <?php if ($item['status'] !== 'Active'): ?>
                      <button type="submit" name="status" value="Active" class="btn-action-icon success" title="Set Active">
                        <i class="fa-solid fa-check"></i>
                      </button>
                    <?php endif; ?>

                    <?php if ($item['status'] !== 'SoldOut'): ?>
                      <button type="submit" name="status" value="SoldOut" class="btn-action-icon danger" title="Mark Sold Out">
                        <i class="fa-solid fa-slash-forward"></i>
                      </button>
                    <?php endif; ?>
                  </form>

                  <!-- Delete Dish Form -->
                  <form action="menu.php?cat=<?php echo urlencode($filter_cat); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($item['name']); ?>?');">
                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                    <input type="hidden" name="action" value="delete_item">
                    <button type="submit" class="btn-action-icon danger" title="Delete Dish">
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

<!-- Modal: Add New Dish -->
<div class="admin-modal-overlay" id="add-menu-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('add-menu-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-bowl-food"></i> Add New Dish to Menu
    </h3>
    <form action="menu.php?cat=<?php echo urlencode($filter_cat); ?>" method="POST">
      <input type="hidden" name="action" value="add_item">
      
      <div class="form-group">
        <label class="form-label">Dish Name</label>
        <input type="text" name="name" class="form-control" required placeholder="e.g. Malabar Prawn Curry">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category" class="form-control" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Price (€)</label>
          <input type="number" step="0.01" name="price" class="form-control" placeholder="15.50" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description / Ingredients</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Fragrant spices, fresh coriander..."></textarea>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Image File Name</label>
          <input type="text" name="image" class="form-control" value="hero_biryani.png" placeholder="hero_biryani.png">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="Active" selected>Active</option>
            <option value="Draft">Draft</option>
            <option value="SoldOut">Sold Out</option>
          </select>
        </div>
      </div>

      <div class="form-group" style="margin-top: 0.5rem;">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-secondary);">
          <input type="checkbox" name="is_special" value="1" style="accent-color: var(--clr-gold);">
          <span>Feature on Home Page "Chef's Specials"</span>
        </label>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin-primary">
          <span>Add Dish</span>
          <i class="fa-solid fa-plus"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(id) { document.getElementById(id).classList.add('active'); }
  function closeModal(id) { document.getElementById(id).classList.remove('active'); }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
