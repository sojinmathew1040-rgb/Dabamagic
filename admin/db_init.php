<?php
/**
 * Daba Magic - Admin Database Auto-Initializer
 * Creates necessary database tables and populates default seed data.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db_connection.php';

function init_admin_database($con) {
    // 1. Create tbl_admin
    $sql_admin = "CREATE TABLE IF NOT EXISTS tbl_admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        role VARCHAR(20) DEFAULT 'Admin',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $con->query($sql_admin);

    // Check if default admin exists
    $check_admin = $con->query("SELECT id FROM tbl_admin WHERE username = 'admin'");
    if ($check_admin && $check_admin->num_rows === 0) {
        $default_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $con->prepare("INSERT INTO tbl_admin (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
        $uname = 'admin';
        $fname = 'Daba Magic Admin';
        $email = 'admin@dabamagic.com';
        $role = 'Super Admin';
        $stmt->bind_param("sssss", $uname, $default_pass, $fname, $email, $role);
        $stmt->execute();
        $stmt->close();
    }

    // 2. Create tbl_reservations
    $sql_res = "CREATE TABLE IF NOT EXISTS tbl_reservations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_code VARCHAR(20) NOT NULL UNIQUE,
        guest_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        guests INT DEFAULT 2,
        reservation_date DATE NOT NULL,
        time_slot VARCHAR(30) NOT NULL,
        special_requests TEXT,
        status VARCHAR(20) DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $con->query($sql_res);

    // Seed reservations if empty
    $check_res = $con->query("SELECT id FROM tbl_reservations LIMIT 1");
    if ($check_res && $check_res->num_rows === 0) {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $dayAfter = date('Y-m-d', strtotime('+2 days'));

        $seed_reservations = [
            ['DM-849201', 'Rahul Sharma', 'rahul.s@example.com', '+353 87 123 4567', 4, $today, '19:30 (Dinner)', 'Anniversary table by the window please', 'Confirmed'],
            ['DM-739102', 'Priya Patel', 'priya.p@example.com', '+353 89 456 7890', 2, $today, '20:30 (Dinner)', 'Vegetarian options preferred', 'Pending'],
            ['DM-628403', 'Liam O\'Connor', 'liam.oc@example.com', '+353 86 987 6543', 6, $tomorrow, '18:30 (Dinner)', 'High chair required for toddler', 'Confirmed'],
            ['DM-517304', 'Aarav Mehta', 'aarav.m@example.com', '+353 87 555 1234', 3, $tomorrow, '13:30 (Lunch)', 'Mild spice level for kids', 'Pending'],
            ['DM-406205', 'Sophie Martin', 'sophie.m@example.com', '+353 85 333 4455', 2, $dayAfter, '19:30 (Dinner)', 'Quiet booth preferred', 'Cancelled'],
            ['DM-395106', 'Vikram Singh', 'vikram.v@example.com', '+353 87 888 9900', 8, $dayAfter, '20:00 (Dinner)', 'Birthday celebration setup', 'Confirmed']
        ];

        $stmt_r = $con->prepare("INSERT INTO tbl_reservations (booking_code, guest_name, email, phone, guests, reservation_date, time_slot, special_requests, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($seed_reservations as $r) {
            $stmt_r->bind_param("ssssissss", $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7], $r[8]);
            $stmt_r->execute();
        }
        $stmt_r->close();
    }

    // 3. Create tbl_menu_items
    $sql_menu = "CREATE TABLE IF NOT EXISTS tbl_menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        category VARCHAR(50) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image VARCHAR(255) DEFAULT 'default_dish.png',
        is_special TINYINT(1) DEFAULT 0,
        status VARCHAR(20) DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $con->query($sql_menu);

    // Seed menu items if empty
    $check_menu = $con->query("SELECT id FROM tbl_menu_items LIMIT 1");
    if ($check_menu && $check_menu->num_rows === 0) {
        $seed_menu = [
            ['Royal Hyderabadi Dum Biryani', 'Biryani & Rice', 'Slow-cooked fragrant basmati rice layered with aromatic spices, fresh saffron, and tender marinated lamb.', 18.95, 'hero_biryani.png', 1, 'Active'],
            ['Clay-Oven Tandoori Kebab Platter', 'Tandoor Specials', 'Juicy chicken tikka, seekh kebabs, and paneer tikka charred to perfection in an authentic clay tandoor.', 21.50, 'tandoori_kebab.png', 1, 'Active'],
            ['Old Delhi Velvet Butter Chicken', 'Curries', 'Succulent chicken breast morsels cooked in a velvety tomato, cashew nut, and butter sauce with fenugreek leaves.', 16.95, 'butter_chicken.png', 1, 'Active'],
            ['Golden Crisp Masala Dosa', 'South Indian', 'Thin crispy fermented rice crepe stuffed with spiced potato masala, served with coconut chutney and sambar.', 13.50, 'crisp_dosa.png', 0, 'Active'],
            ['Malabar Coastal Fish Curry', 'Curries', 'Fresh kingfish simmered in a tangy coconut gravy infused with kokum, curry leaves, and green chillies.', 19.50, 'default_dish.png', 1, 'Active'],
            ['Paneer Butter Masala', 'Vegetarian', 'Cottage cheese cubes tossed in rich tomato butter gravy, seasoned with aromatic garam masala.', 14.95, 'default_dish.png', 0, 'Active'],
            ['Mango Cardamom Lassi', 'Beverages', 'Refreshing churned yogurt smoothie infused with Alphonso mango pulp and freshly ground cardamom.', 5.50, 'default_dish.png', 0, 'Active'],
            ['Gulab Jamun with Vanilla Ice Cream', 'Desserts', 'Warm golden milk dumplings soaked in rose-cardamom syrup, served alongside premium vanilla bean gelato.', 7.25, 'default_dish.png', 0, 'Active']
        ];

        $stmt_m = $con->prepare("INSERT INTO tbl_menu_items (name, category, description, price, image, is_special, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($seed_menu as $m) {
            $stmt_m->bind_param("sssdsis", $m[0], $m[1], $m[2], $m[3], $m[4], $m[5], $m[6]);
            $stmt_m->execute();
        }
        $stmt_m->close();
    }
}

// Auto-run schema check
if (isset($con) && !$con->connect_error) {
    init_admin_database($con);
}
