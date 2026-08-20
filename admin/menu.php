<?php
/**
 * Daba Magic - Admin Menu Management Page
 */

require_once __DIR__ . '/includes/auth_check.php';

// Handle AJAX Cropped Image Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_cropped_image') {
    header('Content-Type: application/json');
    $image_data = $_POST['image_data'] ?? '';
    $original_name = trim($_POST['original_name'] ?? 'dish');

    if (!empty($image_data)) {
        // Strip data URI header if present
        if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $type)) {
            $image_data = substr($image_data, strpos($image_data, ',') + 1);
            $ext = strtolower($type[1]);
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $ext = 'png';
            }
        } else {
            $ext = 'png';
        }

        $decoded_data = base64_decode($image_data);
        if ($decoded_data === false) {
            echo json_encode(['success' => false, 'error' => 'Invalid image encoding.']);
            exit;
        }

        // Clean filename
        $clean_name = preg_replace('/[^a-z0-9]+/i', '_', strtolower(pathinfo($original_name, PATHINFO_FILENAME)));
        $clean_name = trim($clean_name, '_');
        if (empty($clean_name)) { $clean_name = 'dish'; }
        $filename = $clean_name . '_' . time() . '.' . $ext;
        $target_path = __DIR__ . '/../assets/images/' . $filename;

        if (file_put_contents($target_path, $decoded_data)) {
            echo json_encode([
                'success' => true, 
                'filename' => $filename, 
                'url' => '../assets/images/' . $filename
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to save cropped image to disk.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No image data provided.']);
    }
    exit;
}

$page_title = "Menu Catalog Management - Daba Magic Admin Panel";

$msg = "";
$err = "";

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
            } else {
                $err = "Database error: Unable to add dish.";
            }
            $stmt->close();
        } else {
            $err = "Dish name and a valid price (> 0) are required.";
        }

    } elseif ($action === 'edit_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Curries');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0.00);
        $image = trim($_POST['image'] ?? 'default_dish.png');
        $is_special = isset($_POST['is_special']) ? 1 : 0;
        $status = $_POST['status'] ?? 'Active';

        if ($item_id > 0 && !empty($name) && $price > 0) {
            $stmt = $con->prepare("UPDATE tbl_menu_items SET name = ?, category = ?, description = ?, price = ?, image = ?, is_special = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssdsisi", $name, $category, $description, $price, $image, $is_special, $status, $item_id);
            if ($stmt->execute()) {
                $msg = "Dish '{$name}' details updated successfully!";
            } else {
                $err = "Database error: Unable to update dish.";
            }
            $stmt->close();
        } else {
            $err = "Valid dish ID, name, and price are required.";
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

// Dynamic Categories from Database
$categories = [];
$cat_query = $con->query("SELECT name FROM tbl_categories WHERE status = 'Active' ORDER BY display_order ASC, name ASC");
if ($cat_query && $cat_query->num_rows > 0) {
    while ($crow = $cat_query->fetch_assoc()) {
        $categories[] = $crow['name'];
    }
} else {
    $categories = ['Biryani & Rice', 'Tandoor Specials', 'Curries', 'South Indian', 'Vegetarian', 'Beverages', 'Desserts'];
}

// Scan Existing Dish Images on Server
$existing_images = [];
$images_dir = __DIR__ . '/../assets/images';
if (is_dir($images_dir)) {
    $files = scandir($images_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && preg_match('/\.(jpg|jpeg|png|webp|gif|svg)$/i', $file)) {
            $existing_images[] = [
                'filename' => $file,
                'url' => '../assets/images/' . $file,
                'size' => round(filesize($images_dir . '/' . $file) / 1024, 1) . ' KB'
            ];
        }
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<!-- Include Cropper.js for Interactive Image Cropping -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<div class="page-header">
  <div>
    <h1 class="page-title">Menu Catalog Management</h1>
    <p class="page-subtitle">Add, edit, or toggle availability of dishes served at Daba Magic.</p>
  </div>
  <div style="display: flex; gap: 0.75rem;">
    <a href="categories.php" class="btn-admin-sec" title="Manage and Edit Categories">
      <i class="fa-solid fa-layer-group"></i>
      <span>Manage Categories</span>
    </a>
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

<?php if (!empty($err)): ?>
  <div class="alert-danger" style="margin-bottom: 1.5rem;">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <span><?php echo htmlspecialchars($err); ?></span>
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
                    <img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 48 48\'%3E%3Crect width=\'48\' height=\'48\' fill=\'%23221613\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' fill=\'%23D4A017\' font-size=\'20\' dominant-baseline=\'middle\' text-anchor=\'middle\'%3E🍲%3C/text%3E%3C/svg%3E';">
                  </div>
                  <div>
                    <a href="javascript:void(0)" 
                       onclick="openEditMenuModal(<?php echo htmlspecialchars(json_encode($item)); ?>)"
                       style="color: var(--text-primary); text-decoration: none; font-size: 0.95rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; transition: var(--transition-fast);"
                       onmouseover="this.style.color='var(--clr-gold)'" 
                       onmouseout="this.style.color='var(--text-primary)'" 
                       title="Click to edit dish details">
                      <span><?php echo htmlspecialchars($item['name']); ?></span>
                      <i class="fa-solid fa-pen-to-square" style="font-size: 0.75rem; color: var(--clr-gold); opacity: 0.7;"></i>
                    </a><br>
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
                  
                  <!-- Edit Dish Button -->
                  <button type="button" class="btn-action-icon success" title="Edit Dish Details"
                          onclick="openEditMenuModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </button>

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
                        <i class="fa-solid fa-ban"></i>
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

      <!-- Dish Image Picker Input -->
      <div class="form-group">
        <label class="form-label">Dish Image</label>
        <div class="image-input-wrap">
          <div class="image-preview-thumb" id="add-dish-preview-box" onclick="openMediaPicker('add-dish-image', 'add-dish-preview-img')" title="Click to select or upload image">
            <img src="../assets/images/hero_biryani.png" id="add-dish-preview-img" alt="Dish Preview" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 48 48\'%3E%3Crect width=\'48\' height=\'48\' fill=\'%23221613\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' fill=\'%23D4A017\' font-size=\'20\' dominant-baseline=\'middle\' text-anchor=\'middle\'%3E🍲%3C/text%3E%3C/svg%3E';">
          </div>
          <input type="text" name="image" id="add-dish-image" class="form-control" value="hero_biryani.png" placeholder="hero_biryani.png" onchange="updateLivePreview(this.value, 'add-dish-preview-img')">
          <button type="button" class="btn-media-picker" onclick="openMediaPicker('add-dish-image', 'add-dish-preview-img')" title="Browse gallery or upload and crop image">
            <i class="fa-solid fa-images"></i>
            <span>Browse / Crop</span>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
          <option value="Active" selected>Active</option>
          <option value="Draft">Draft</option>
          <option value="SoldOut">Sold Out</option>
        </select>
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

<!-- Modal: Edit Dish -->
<div class="admin-modal-overlay" id="edit-menu-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('edit-menu-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-pen-to-square"></i> Edit Dish Details
    </h3>
    <form action="menu.php?cat=<?php echo urlencode($filter_cat); ?>" method="POST">
      <input type="hidden" name="action" value="edit_item">
      <input type="hidden" name="item_id" id="edit-item-id">
      
      <div class="form-group">
        <label class="form-label">Dish Name</label>
        <input type="text" name="name" id="edit-item-name" class="form-control" required placeholder="e.g. Royal Hyderabadi Biryani">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category" id="edit-item-category" class="form-control" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Price (€)</label>
          <input type="number" step="0.01" name="price" id="edit-item-price" class="form-control" placeholder="15.50" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Description / Ingredients</label>
        <textarea name="description" id="edit-item-desc" class="form-control" rows="3"></textarea>
      </div>

      <!-- Edit Dish Image Picker Input -->
      <div class="form-group">
        <label class="form-label">Dish Image</label>
        <div class="image-input-wrap">
          <div class="image-preview-thumb" id="edit-item-preview-box" onclick="openMediaPicker('edit-item-image', 'edit-item-preview-img')" title="Click to select or upload image">
            <img src="../assets/images/default_dish.png" id="edit-item-preview-img" alt="Dish Preview" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'48\' height=\'48\' viewBox=\'0 0 48 48\'%3E%3Crect width=\'48\' height=\'48\' fill=\'%23221613\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' fill=\'%23D4A017\' font-size=\'20\' dominant-baseline=\'middle\' text-anchor=\'middle\'%3E🍲%3C/text%3E%3C/svg%3E';">
          </div>
          <input type="text" name="image" id="edit-item-image" class="form-control" placeholder="hero_biryani.png" onchange="updateLivePreview(this.value, 'edit-item-preview-img')">
          <button type="button" class="btn-media-picker" onclick="openMediaPicker('edit-item-image', 'edit-item-preview-img')" title="Browse gallery or upload and crop image">
            <i class="fa-solid fa-images"></i>
            <span>Browse / Crop</span>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Status</label>
        <select name="status" id="edit-item-status" class="form-control">
          <option value="Active">Active</option>
          <option value="Draft">Draft</option>
          <option value="SoldOut">Sold Out</option>
        </select>
      </div>

      <div class="form-group" style="margin-top: 0.5rem;">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-secondary);">
          <input type="checkbox" name="is_special" id="edit-item-special" value="1" style="accent-color: var(--clr-gold);">
          <span>Feature on Home Page "Chef's Specials"</span>
        </label>
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin-primary">
          <span>Save Dish Changes</span>
          <i class="fa-solid fa-check"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Media Manager & Image Cropper Popup -->
<div class="admin-modal-overlay" id="dish-media-modal" style="z-index: 1100;">
  <div class="admin-modal-box media-modal-box">
    <button class="modal-close-btn" onclick="closeModal('dish-media-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
      <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin: 0;">
        <i class="fa-solid fa-photo-film"></i> Dish Image Manager
      </h3>
    </div>

    <!-- Media Tabs Navigation -->
    <div class="media-tabs-header">
      <button type="button" class="media-tab-btn active" id="tab-btn-gallery" onclick="switchMediaTab('gallery')">
        <i class="fa-solid fa-images"></i>
        <span>Existing Images (<?php echo count($existing_images); ?>)</span>
      </button>
      <button type="button" class="media-tab-btn" id="tab-btn-crop" onclick="switchMediaTab('crop')">
        <i class="fa-solid fa-crop-simple"></i>
        <span>Upload & Crop New Image</span>
      </button>
    </div>

    <!-- Tab 1: Existing Images Gallery -->
    <div class="media-tab-content active" id="tab-content-gallery">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; gap: 1rem;">
        <div class="header-search" style="width: 100%; max-width: 320px;">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="gallery-search-input" placeholder="Filter existing image names..." onkeyup="filterGalleryCards(this.value)">
        </div>
        <small style="color: var(--text-muted); white-space: nowrap;">Click any image to select</small>
      </div>

      <div class="media-thumb-list" id="media-gallery-container">
        <?php if (empty($existing_images)): ?>
          <div style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
            <i class="fa-solid fa-image" style="font-size: 2.5rem; color: var(--clr-terracotta); margin-bottom: 0.5rem;"></i><br>
            No existing images found in assets/images. Switch to "Upload & Crop" to add one.
          </div>
        <?php else: ?>
          <?php foreach ($existing_images as $img): ?>
            <div class="media-thumb-item" data-filename="<?php echo htmlspecialchars($img['filename']); ?>" onclick="selectExistingImage('<?php echo htmlspecialchars($img['filename']); ?>')">
              <div class="media-thumb-img-wrap">
                <img src="<?php echo htmlspecialchars($img['url']); ?>" alt="<?php echo htmlspecialchars($img['filename']); ?>" loading="lazy" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'52\' height=\'52\'%3E%3Crect width=\'100%25\' height=\'100%25\' fill=\'%23221613\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' fill=\'%23D4A017\' font-size=\'18\' dominant-baseline=\'middle\' text-anchor=\'middle\'%3E🍲%3C/text%3E%3C/svg%3E';">
              </div>
              <div class="media-thumb-info">
                <strong class="media-thumb-name" title="<?php echo htmlspecialchars($img['filename']); ?>">
                  <?php echo htmlspecialchars($img['filename']); ?>
                </strong>
                <span class="media-thumb-meta">
                  <i class="fa-regular fa-image" style="color: var(--clr-gold);"></i> <?php echo strtoupper(pathinfo($img['filename'], PATHINFO_EXTENSION)); ?> • <?php echo $img['size']; ?>
                </span>
              </div>
              <button type="button" class="btn-select-thumb" title="Use this image">
                <i class="fa-solid fa-check"></i>
                <span>Select</span>
              </button>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Tab 2: Upload & Interactive Cropper -->
    <div class="media-tab-content" id="tab-content-crop">
      
      <!-- Upload Drop Zone (Visible initially before file is chosen) -->
      <div id="cropper-upload-zone" class="upload-drop-zone" onclick="document.getElementById('cropper-file-input').click()">
        <input type="file" id="cropper-file-input" accept="image/*" style="display: none;" onchange="handleImageFileSelect(this)">
        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: var(--clr-gold); margin-bottom: 0.75rem;"></i>
        <h4 style="color: var(--text-primary); margin-bottom: 0.35rem; font-family: var(--font-heading);">Choose or Drop Food Image</h4>
        <p style="color: var(--text-muted); font-size: 0.825rem;">Supports JPG, PNG, WEBP high-resolution food photos</p>
      </div>

      <!-- Cropper Workspace (Visible after file is chosen) -->
      <div id="cropper-workspace" style="display: none;">
        <div class="cropper-container-wrapper">
          <img id="cropper-target-img" src="" alt="Crop Target" style="max-width: 100%; display: block;">
        </div>

        <!-- Cropper Controls & Aspect Ratio Toolbar -->
        <div class="cropper-toolbar">
          
          <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span style="font-size: 0.775rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Ratio:</span>
            <div class="cropper-btn-group">
              <button type="button" class="btn-cropper-action active" id="btn-ratio-sq" onclick="setCropperRatio(1/1, this)">1:1 Square</button>
              <button type="button" class="btn-cropper-action" id="btn-ratio-43" onclick="setCropperRatio(4/3, this)">4:3</button>
              <button type="button" class="btn-cropper-action" id="btn-ratio-free" onclick="setCropperRatio(NaN, this)">Free</button>
            </div>
          </div>

          <div class="cropper-btn-group">
            <button type="button" class="btn-cropper-action" onclick="cropperAction('zoom', 0.1)" title="Zoom In"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
            <button type="button" class="btn-cropper-action" onclick="cropperAction('zoom', -0.1)" title="Zoom Out"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
            <button type="button" class="btn-cropper-action" onclick="cropperAction('rotate', -90)" title="Rotate Left"><i class="fa-solid fa-rotate-left"></i></button>
            <button type="button" class="btn-cropper-action" onclick="cropperAction('rotate', 90)" title="Rotate Right"><i class="fa-solid fa-rotate-right"></i></button>
            <button type="button" class="btn-cropper-action" onclick="cropperAction('reset')" title="Reset"><i class="fa-solid fa-arrow-rotate-left"></i></button>
          </div>

          <div style="display: flex; gap: 0.5rem; margin-left: auto;">
            <button type="button" class="btn-cropper-action" onclick="resetCropSelection()">
              <i class="fa-solid fa-arrow-left"></i>
              <span>Choose Other</span>
            </button>
            <button type="button" class="btn-admin-primary" id="btn-save-cropped-img" onclick="saveCroppedImage()" style="padding: 0.55rem 1.25rem; font-size: 0.85rem;">
              <i class="fa-solid fa-check"></i>
              <span>Crop & Use Image</span>
            </button>
          </div>

        </div>
      </div>

    </div>

  </div>
</div>

<script>
  function openModal(id) { document.getElementById(id).classList.add('active'); }
  function closeModal(id) { document.getElementById(id).classList.remove('active'); }

  // Update Live Preview Image
  function updateLivePreview(filename, previewImgId) {
    const previewImg = document.getElementById(previewImgId);
    if (previewImg && filename) {
      previewImg.src = '../assets/images/' + filename;
    }
  }

  function openEditMenuModal(item) {
    document.getElementById('edit-item-id').value = item.id;
    document.getElementById('edit-item-name').value = item.name;
    document.getElementById('edit-item-category').value = item.category;
    document.getElementById('edit-item-price').value = item.price;
    document.getElementById('edit-item-desc').value = item.description || '';
    
    const imgName = item.image || 'default_dish.png';
    document.getElementById('edit-item-image').value = imgName;
    updateLivePreview(imgName, 'edit-item-preview-img');

    document.getElementById('edit-item-status').value = item.status;
    document.getElementById('edit-item-special').checked = (item.is_special == 1);
    openModal('edit-menu-modal');
  }

  <?php
    // Auto-open edit modal if edit_id parameter is passed in URL
    if (isset($_GET['edit_id'])) {
        $edit_id = intval($_GET['edit_id']);
        foreach ($all_menu_items as $m_item) {
            if ($m_item['id'] == $edit_id) {
                echo "document.addEventListener('DOMContentLoaded', function() { openEditMenuModal(" . json_encode($m_item) . "); });";
                break;
            }
        }
    }
  ?>

  /* ==========================================================================
     MEDIA MANAGER & IMAGE CROPPER LOGIC
     ========================================================================== */
  let activeTargetInputId = 'add-dish-image';
  let activeTargetPreviewId = 'add-dish-preview-img';
  let cropperInstance = null;
  let selectedFileObject = null;

  function openMediaPicker(targetInputId, targetPreviewImgId) {
    activeTargetInputId = targetInputId;
    activeTargetPreviewId = targetPreviewImgId;

    // Highlight currently selected thumbnail item if present in gallery
    const currentVal = document.getElementById(targetInputId) ? document.getElementById(targetInputId).value : '';
    const items = document.querySelectorAll('.media-thumb-item');
    items.forEach(item => {
      if (item.getAttribute('data-filename') === currentVal) {
        item.classList.add('selected');
      } else {
        item.classList.remove('selected');
      }
    });

    switchMediaTab('gallery');
    openModal('dish-media-modal');
  }

  function switchMediaTab(tabName) {
    const btnGallery = document.getElementById('tab-btn-gallery');
    const btnCrop = document.getElementById('tab-btn-crop');
    const contentGallery = document.getElementById('tab-content-gallery');
    const contentCrop = document.getElementById('tab-content-crop');

    if (tabName === 'gallery') {
      btnGallery.classList.add('active');
      btnCrop.classList.remove('active');
      contentGallery.classList.add('active');
      contentCrop.classList.remove('active');
    } else {
      btnCrop.classList.add('active');
      btnGallery.classList.remove('active');
      contentCrop.classList.add('active');
      contentGallery.classList.remove('active');
    }
  }

  // Filter existing gallery images
  function filterGalleryCards(query) {
    const q = query.toLowerCase().trim();
    const items = document.querySelectorAll('.media-thumb-item');
    items.forEach(item => {
      const filename = (item.getAttribute('data-filename') || '').toLowerCase();
      item.style.display = filename.includes(q) ? 'flex' : 'none';
    });
  }

  // Choose from Existing Gallery
  function selectExistingImage(filename) {
    if (activeTargetInputId && document.getElementById(activeTargetInputId)) {
      document.getElementById(activeTargetInputId).value = filename;
    }
    if (activeTargetPreviewId && document.getElementById(activeTargetPreviewId)) {
      updateLivePreview(filename, activeTargetPreviewId);
    }
    closeModal('dish-media-modal');
  }

  // Handle Image File Selection for Cropper
  function handleImageFileSelect(input) {
    if (input.files && input.files[0]) {
      const file = input.files[0];
      selectedFileObject = file;

      const reader = new FileReader();
      reader.onload = function(e) {
        const targetImg = document.getElementById('cropper-target-img');
        targetImg.src = e.target.result;

        document.getElementById('cropper-upload-zone').style.display = 'none';
        document.getElementById('cropper-workspace').style.display = 'block';

        if (cropperInstance) {
          cropperInstance.destroy();
        }

        // Initialize Cropper.js with 1:1 Aspect Ratio
        cropperInstance = new Cropper(targetImg, {
          aspectRatio: 1 / 1,
          viewMode: 2,
          autoCropArea: 0.9,
          responsive: true,
          guides: true,
          center: true,
          highlight: true,
          background: true
        });
      };
      reader.readAsDataURL(file);
    }
  }

  function setCropperRatio(ratio, btnEl) {
    if (!cropperInstance) return;
    cropperInstance.setAspectRatio(ratio);

    document.querySelectorAll('.btn-cropper-action').forEach(b => {
      if (b.id && b.id.startsWith('btn-ratio-')) {
        b.classList.remove('active');
      }
    });
    if (btnEl) btnEl.classList.add('active');
  }

  function cropperAction(action, param) {
    if (!cropperInstance) return;
    if (action === 'zoom') {
      cropperInstance.zoom(param);
    } else if (action === 'rotate') {
      cropperInstance.rotate(param);
    } else if (action === 'reset') {
      cropperInstance.reset();
    }
  }

  function resetCropSelection() {
    if (cropperInstance) {
      cropperInstance.destroy();
      cropperInstance = null;
    }
    document.getElementById('cropper-file-input').value = '';
    document.getElementById('cropper-workspace').style.display = 'none';
    document.getElementById('cropper-upload-zone').style.display = 'block';
  }

  // Save Cropped Image via AJAX
  function saveCroppedImage() {
    if (!cropperInstance) {
      alert('Please choose an image to crop first.');
      return;
    }

    const saveBtn = document.getElementById('btn-save-cropped-img');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;

    // Export Cropped Canvas
    const canvas = cropperInstance.getCroppedCanvas({
      width: 600,
      height: 600,
      imageSmoothingEnabled: true,
      imageSmoothingQuality: 'high'
    });

    const base64Data = canvas.toDataURL('image/png', 0.92);
    const origName = selectedFileObject ? selectedFileObject.name : 'dish_upload';

    const formData = new FormData();
    formData.append('action', 'upload_cropped_image');
    formData.append('image_data', base64Data);
    formData.append('original_name', origName);

    fetch('menu.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(result => {
      saveBtn.innerHTML = originalText;
      saveBtn.disabled = false;

      if (result.success && result.filename) {
        // Set new filename on target inputs
        selectExistingImage(result.filename);
        resetCropSelection();

        // Dynamically prepend new image item to thumbnail list
        const galleryContainer = document.getElementById('media-gallery-container');
        if (galleryContainer) {
          const newItem = document.createElement('div');
          newItem.className = 'media-thumb-item selected';
          newItem.setAttribute('data-filename', result.filename);
          newItem.onclick = () => selectExistingImage(result.filename);
          newItem.innerHTML = `
            <div class="media-thumb-img-wrap">
              <img src="${result.url}" alt="${result.filename}">
            </div>
            <div class="media-thumb-info">
              <strong class="media-thumb-name" title="${result.filename}">${result.filename}</strong>
              <span class="media-thumb-meta"><i class="fa-regular fa-image" style="color: var(--clr-gold);"></i> NEW UPLOAD</span>
            </div>
            <button type="button" class="btn-select-thumb">
              <i class="fa-solid fa-check"></i>
              <span>Select</span>
            </button>
          `;
          galleryContainer.prepend(newItem);
        }
      } else {
        alert('Error saving image: ' + (result.error || 'Unknown error occurred.'));
      }
    })
    .catch(error => {
      saveBtn.innerHTML = originalText;
      saveBtn.disabled = false;
      console.error('Cropper upload error:', error);
      alert('Upload failed. Please check server permissions or try another image.');
    });
  }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
