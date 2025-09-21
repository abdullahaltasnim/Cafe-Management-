<?php
// dashorder.php

// ---------- DB EXAMPLE (uncomment + configure) ----------
// require_once '../php/config.php';  // your PDO $conn
// Fetch orders (example schema: orders table with these fields).
// $stmt = $conn->query("SELECT user_id, placed_on, name, email, phone, address, total_order, total_price, status FROM orders ORDER BY placed_on DESC");
// $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------- Demo data (remove when using DB) ----------
$orders = [
    [
        'user_id' => '10001',
        'placed_on' => '2025-09-01',
        'name' => 'Alice Baker',
        'email' => 'alicebaker07@gmail.com',
        'phone' => '+44-2589361212',
        'address' => 'Flat 12/C, Orion Block/Eastern Chapel, 5200',
        'total_order' => 2,
        'total_price' => 22,
        'status' => 'Pending'
    ],
    [
        'user_id' => '10002',
        'placed_on' => '2025-09-02',
        'name' => 'John Smith',
        'email' => 'johnsmith08@gmail.com',
        'phone' => '+44-58742103',
        'address' => 'Block B, Sunview Apartments, London',
        'total_order' => 3,
        'total_price' => 45,
        'status' => 'Pending'
    ],
    [
        'user_id' => '10003',
        'placed_on' => '2025-09-03',
        'name' => 'Maria Lopez',
        'email' => 'marialopez@gmail.com',
        'phone' => '+44-96321478',
        'address' => '45, Green Valley, Manchester',
        'total_order' => 1,
        'total_price' => 15,
        'status' => 'Pending'
    ],
];

// ---------- View Sales (server-side sum between dates) ----------
$totalSales = null;        // will hold numeric sum
$totalSalesCurrency = 'USD';
$startDate = $_POST['start_date'] ?? '';
$endDate   = $_POST['end_date']   ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_sales'])) {
    // Validate YYYY-MM-DD
    $ok1 = preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate);
    $ok2 = preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate);

    if ($ok1 && $ok2) {
        // If using DB:
        // $sql = "SELECT SUM(total_price) AS s FROM orders WHERE placed_on BETWEEN ? AND ?";
        // $stmt = $conn->prepare($sql);
        // $stmt->execute([$startDate, $endDate]);
        // $totalSales = (float) $stmt->fetchColumn();

        // Demo sum from array:
        $totalSales = 0.0;
        foreach ($orders as $o) {
            if ($o['placed_on'] >= $startDate && $o['placed_on'] <= $endDate) {
                $totalSales += (float)$o['total_price'];
            }
        }
    } else {
        $totalSales = 0.0; // invalid input -> treat as 0; you can show an error if you want
    }
}

// tiny helper
function h($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Orders</title>
    <link rel="stylesheet" href="../css/dashorder.css" />
    <style>
        /* --- Keep buttons fixed in place: remove hover scale so layout doesn’t jump --- */
        .middletotal:hover,
        .middleorder:hover {
            transform: none !important;
        }

        .btn-cc {
            position: sticky;
            bottom: 0;
            background: inherit;
            padding-bottom: 0;
        }

        /* If you don’t want sticky, comment the line above and buttons will still not jump */
        /* Prevent anchor inside button from changing layout */
        .zx {
            color: #fff;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">

        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="../php/dashboardmy.php">Home</a></li>
                <li><a href="../php/dashorder.php">Orders</a></li>
                <li><a href="../php/dashproduct.php">Products</a></li>
                <li><a href="../php/dashuser.php">Users</a></li>
                <li><a href="../php/adminProfile.php" class="active">Profile</a></li>
            </ul>
        </nav>

        <!-- PAGE TITLE -->
        <h2 class="middletitle">PLACED ORDERS</h2>

        <!-- ORDERS GRID -->
        <section class="orders-grid">
            <?php foreach ($orders as $o): ?>
                <article class="order-card">
                    <h5>User ID: <?= h($o['user_id']) ?></h5>
                    <h5>Placed On: <?= h(date('d-M-Y', strtotime($o['placed_on']))) ?></h5>
                    <h5>Name: <?= h($o['name']) ?></h5>
                    <h5>Email: <?= h($o['email']) ?></h5>
                    <h5>Number: <?= h($o['phone']) ?></h5>
                    <h5>Address: <?= h($o['address']) ?></h5>
                    <h5>Total Order: <?= (int)$o['total_order'] ?></h5>
                    <h5>Total Price: $<?= number_format((float)$o['total_price'], 2) ?>/-</h5>

                    <h5>Status:</h5>
                    <form method="post" action="update_status.php" style="margin:0">
                        <input type="hidden" name="id" value="<?= h($o['user_id']) ?>">
                        <select name="status" required>
                            <?php
                            $opts = ['Pending', 'Processing', 'Packed', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned'];
                            foreach ($opts as $opt) {
                                $sel = ($o['status'] ?? '') === $opt ? 'selected' : '';
                                echo "<option value='" . h($opt) . "' $sel>" . h($opt) . "</option>";
                            }
                            ?>
                        </select>

                        <div class="btn-cc">
                            <!-- Edit with id so each card opens its own record -->
                            <button class="middletotal" type="button">
                                <a class="zx" href="dashedit.php?id=<?= urlencode($o['user_id']) ?>">Edit</a>
                            </button>

                            <!-- Delete (POST) -->
                            <form method="post" action="delete_order.php" style="display:inline">
                                <input type="hidden" name="id" value="<?= h($o['user_id']) ?>">
                                <button class="middleorder" type="submit" onclick="return confirm('Delete this order?')">Delete</button>
                            </form>
                        </div>
                    </form>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- VIEW SALES (server-side) -->
        <div class="container">
            <div class="product-form">
                <h2>View Sales</h2>
                <p>Check sales performance by date range.</p>

                <form id="sales-form" method="post" action="">
                    <div class="form-group">
                        <label for="start-date">Start Date</label>
                        <input type="date" id="start-date" name="start_date" required value="<?= h($startDate) ?>" />
                    </div>
                    <div class="form-group">
                        <label for="end-date">End Date</label>
                        <input type="date" id="end-date" name="end_date" required value="<?= h($endDate) ?>" />
                    </div>
                    <button type="submit" class="btn" name="view_sales" value="1">View Sales</button>
                </form>

                <div id="sales-total">
                    <?php if ($totalSales !== null): ?>
                        Total Sales: <?= number_format((float)$totalSales, 2) . ' ' . h($totalSalesCurrency) ?>
                    <?php else: ?>
                        Total Sales: 0 <?= h($totalSalesCurrency) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section" id="contact-section">
                    <h3>Contact Us</h3>
                    <p>Email: <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a></p>
                    <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
                    <p>Address: 123 Skyline Avenue, Dhaka</p>
                </div>
                <div class="footer-section" id="about-section">
                    <h3>About Us</h3>
                    <p>We are passionate about serving the finest coffee, crafted with love and expertise. Join us for a unique coffee experience!</p>
                </div>
                <div class="footer-section">
                    <h3>Newsletter</h3>
                    <p>Subscribe for exclusive offers!</p>
                    <input type="email" placeholder="Enter your email" class="newsletter-input" />
                    <button class="btn newsletter-btn">Subscribe</button>
                </div>
                <div class="footer-section">
                    <h3>Follow Us</h3>
                    <div class="social-links">
                        <a href="https://facebook.com" class="social-icon" aria-label="Facebook">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook Logo" class="social-logo" />
                        </a>
                        <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram Logo" class="social-logo" />
                        </a>
                        <a href="https://x.com" class="social-icon" aria-label="X">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" class="social-logo" alt="X" />
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>Skyline Coffee Shop - Where Every Sip Tells a Story</p>
                <p>&copy; 2025 Skyline Coffee Shop. All rights reserved.</p>
            </div>
        </footer>

    </div>
</body>

</html>