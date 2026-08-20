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
        role VARCHAR(50) DEFAULT 'Super Admin',
        status VARCHAR(20) DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $con->query($sql_admin);

    // Auto-migration: check if status column exists in tbl_admin
    $col_check = $con->query("SHOW COLUMNS FROM tbl_admin LIKE 'status'");
    if ($col_check && $col_check->num_rows === 0) {
        $con->query("ALTER TABLE tbl_admin ADD COLUMN status VARCHAR(20) DEFAULT 'Active' AFTER role");
    }

    // Check if default admin exists
    $check_admin = $con->query("SELECT id FROM tbl_admin WHERE username = 'admin'");
    if ($check_admin && $check_admin->num_rows === 0) {
        $default_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $con->prepare("INSERT INTO tbl_admin (username, password, full_name, email, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        $uname = 'admin';
        $fname = 'Daba Magic Admin';
        $email = 'admin@dabamagic.com';
        $role = 'Super Admin';
        $status = 'Active';
        $stmt->bind_param("ssssss", $uname, $default_pass, $fname, $email, $role, $status);
        $stmt->execute();
        $stmt->close();
    }

    // 1b. Create tbl_roles
    $sql_roles = "CREATE TABLE IF NOT EXISTS tbl_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) NOT NULL UNIQUE,
        role_slug VARCHAR(50) NOT NULL UNIQUE,
        description TEXT NULL,
        permissions TEXT NULL,
        status VARCHAR(20) DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $con->query($sql_roles);

    // Seed default roles if empty
    $check_roles = $con->query("SELECT id FROM tbl_roles LIMIT 1");
    if ($check_roles && $check_roles->num_rows === 0) {
        $seed_roles = [
            ['Super Admin', 'super_admin', 'Full unrestricted control, user & operator management, system settings', 'all', 'Active'],
            ['Restaurant Manager', 'manager', 'Manage food menu catalog, categories, reservations, and analytics', 'dashboard,reservations,categories,menu', 'Active'],
            ['Kitchen Operator', 'kitchen_operator', 'Monitor orders, view reservations, and toggle dish availability', 'dashboard,menu,reservations', 'Active'],
            ['Front Desk Staff', 'staff', 'Manage table reservations, guest bookings, and confirmation status', 'dashboard,reservations', 'Active']
        ];

        $stmt_role = $con->prepare("INSERT INTO tbl_roles (role_name, role_slug, description, permissions, status) VALUES (?, ?, ?, ?, ?)");
        foreach ($seed_roles as $r) {
            $stmt_role->bind_param("sssss", $r[0], $r[1], $r[2], $r[3], $r[4]);
            $stmt_role->execute();
        }
        $stmt_role->close();
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

    // 4. Create tbl_categories
    $sql_cat = "CREATE TABLE IF NOT EXISTS tbl_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT NULL,
        status VARCHAR(20) DEFAULT 'Active',
        display_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $con->query($sql_cat);

    // Seed categories if empty
    $check_cat = $con->query("SELECT id FROM tbl_categories LIMIT 1");
    if ($check_cat && $check_cat->num_rows === 0) {
        $seed_categories = [
            ['Biryani & Rice', 'biryani', 'Aromatic dum biryanis and authentic fragrant rice specialties.', 'Active', 1],
            ['Tandoor Specials', 'tandoori', 'Juicy kebabs, tikka platters, and clay-oven grilled delicacies.', 'Active', 2],
            ['Curries', 'curries', 'Rich, velvety gravies simmered with aromatic whole spices.', 'Active', 3],
            ['South Indian', 'south', 'Crispy dosas, fluffy idlis, and coastal South Indian flavors.', 'Active', 4],
            ['Vegetarian', 'vegetarian', 'Wholesome pure-vegetarian specialties and cottage cheese dishes.', 'Active', 5],
            ['Beverages', 'beverages', 'Refreshing churned lassis, spiced chai, and cold drinks.', 'Active', 6],
            ['Desserts', 'desserts', 'Traditional warm milk dumplings and decadent sweet treats.', 'Active', 7]
        ];

        $stmt_c = $con->prepare("INSERT INTO tbl_categories (name, slug, description, status, display_order) VALUES (?, ?, ?, ?, ?)");
        foreach ($seed_categories as $c) {
            $stmt_c->bind_param("ssssi", $c[0], $c[1], $c[2], $c[3], $c[4]);
            $stmt_c->execute();
        }
        $stmt_c->close();
    }

    // 5. Create tbl_orders
    $sql_orders = "CREATE TABLE IF NOT EXISTS tbl_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(30) NOT NULL UNIQUE,
        customer_name VARCHAR(100) NOT NULL,
        customer_email VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(30) NOT NULL,
        order_type VARCHAR(20) DEFAULT 'Delivery',
        table_number VARCHAR(20) NULL,
        delivery_address TEXT NULL,
        order_notes TEXT NULL,
        subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total_amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) DEFAULT 'Razorpay',
        payment_status VARCHAR(20) DEFAULT 'Pending',
        order_status VARCHAR(30) DEFAULT 'Received',
        razorpay_order_id VARCHAR(100) NULL,
        razorpay_payment_id VARCHAR(100) NULL,
        razorpay_signature VARCHAR(255) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $con->query($sql_orders);

    // 6. Create tbl_order_items
    $sql_order_items = "CREATE TABLE IF NOT EXISTS tbl_order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        dish_id INT NULL,
        dish_name VARCHAR(150) NOT NULL,
        dish_price DECIMAL(10,2) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        item_total DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES tbl_orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $con->query($sql_order_items);

    // Seed sample orders if empty
    $check_orders = $con->query("SELECT id FROM tbl_orders LIMIT 1");
    if ($check_orders && $check_orders->num_rows === 0) {
        $sample_orders = [
            [
                'DM-ORD-849102', 'Aarav Sharma', 'aarav.sharma@example.com', '+353 87 912 3456', 
                'Delivery', '', '14 Patrick Street, Cork City, Apt 3B', 'Extra mint chutney please', 
                35.90, 3.23, 3.50, 42.63, 'Razorpay', 'Paid', 'Preparing', 
                'order_Rzp849102', 'pay_Rzp849102abc', 'sig_849102xyz',
                [
                    ['Royal Hyderabadi Dum Biryani', 18.95, 1, 18.95],
                    ['Old Delhi Velvet Butter Chicken', 16.95, 1, 16.95]
                ]
            ],
            [
                'DM-ORD-739201', 'Chloe Murphy', 'chloe.m@example.com', '+353 89 543 2109', 
                'DineIn', 'Table 4', '', 'Please serve desserts after main course', 
                34.25, 3.08, 0.00, 37.33, 'Razorpay', 'Paid', 'Ready', 
                'order_Rzp739201', 'pay_Rzp739201def', 'sig_739201uvw',
                [
                    ['Clay-Oven Tandoori Kebab Platter', 21.50, 1, 21.50],
                    ['Mango Cardamom Lassi', 5.50, 1, 5.50],
                    ['Gulab Jamun with Vanilla Ice Cream', 7.25, 1, 7.25]
                ]
            ],
            [
                'DM-ORD-628305', 'Karthik Rao', 'karthik.rao@example.com', '+353 86 333 7788', 
                'Takeaway', '', '', 'Packaging for travel', 
                28.45, 2.56, 0.00, 31.01, 'Razorpay', 'Paid', 'Delivered', 
                'order_Rzp628305', 'pay_Rzp628305ghi', 'sig_628305rst',
                [
                    ['Golden Crisp Masala Dosa', 13.50, 1, 13.50],
                    ['Paneer Butter Masala', 14.95, 1, 14.95]
                ]
            ]
        ];

        foreach ($sample_orders as $ord) {
            $stmt_ord = $con->prepare("INSERT INTO tbl_orders (order_number, customer_name, customer_email, customer_phone, order_type, table_number, delivery_address, order_notes, subtotal, tax, delivery_fee, total_amount, payment_method, payment_status, order_status, razorpay_order_id, razorpay_payment_id, razorpay_signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_ord->bind_param("ssssssssddddssssss", $ord[0], $ord[1], $ord[2], $ord[3], $ord[4], $ord[5], $ord[6], $ord[7], $ord[8], $ord[9], $ord[10], $ord[11], $ord[12], $ord[13], $ord[14], $ord[15], $ord[16], $ord[17]);
            if ($stmt_ord->execute()) {
                $order_id = $stmt_ord->insert_id;
                $stmt_item = $con->prepare("INSERT INTO tbl_order_items (order_id, dish_name, dish_price, quantity, item_total) VALUES (?, ?, ?, ?, ?)");
                foreach ($ord[18] as $item) {
                    $stmt_item->bind_param("isdid", $order_id, $item[0], $item[1], $item[2], $item[3]);
                    $stmt_item->execute();
                }
                $stmt_item->close();
            }
            $stmt_ord->close();
        }
    }
}

// Auto-run schema check
if (isset($con) && !$con->connect_error) {
    init_admin_database($con);
}
