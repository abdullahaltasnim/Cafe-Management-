<?php
// dashedit.php  — drop this in your /HTML or /PHP folder and open in browser.
// It keeps your exact HTML structure, adds secure server-side validation,
// and re-fills the form with submitted values + inline error messages.

session_start();

function h($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// ---- CSRF token (simple) ----
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

// ---- Defaults from the screenshot ----
$defaults = [
    'user_id'     => '10001',
    'placed_on'   => '2025-09-01',
    'name'        => 'Alice Baker',
    'email'       => 'alicebaker07@gmail.com',
    'phone'       => '+44-2589361212',
    'address'     => 'Flat 12/C, Orion Block/Eastern Chapel, 5200',
    'total_order' => '2',
    'total_price' => '22',
    'status'      => '',
];

$data   = $defaults;
$errors = [];
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        $errors['form'] = "Security check failed. Please reload the page.";
    } else {
        // Collect trimmed inputs
        foreach ($defaults as $k => $_) {
            $data[$k] = isset($_POST[$k]) ? trim((string)$_POST[$k]) : '';
        }

        // ---- Validation rules ----
        if ($data['user_id'] === '' || !preg_match('/^\d{1,10}$/', $data['user_id'])) {
            $errors['user_id'] = "User ID must be 1–10 digits.";
        }

        // date (YYYY-MM-DD) and must be a real date
        if ($data['placed_on'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['placed_on'])) {
            $errors['placed_on'] = "Please pick a valid date.";
        } else {
            [$y, $m, $d] = array_map('intval', explode('-', $data['placed_on']));
            if (!checkdate($m, $d, $y)) $errors['placed_on'] = "Invalid calendar date.";
        }

        if ($data['name'] === '' || mb_strlen($data['name']) < 2) {
            $errors['name'] = "Name is required.";
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Enter a valid email.";
        }

        // Allow +, digits, dashes, spaces
        if ($data['phone'] !== '' && !preg_match('/^\+?[0-9][0-9\-\s]{6,}$/', $data['phone'])) {
            $errors['phone'] = "Enter a valid phone number.";
        }

        if ($data['address'] === '' || mb_strlen($data['address']) < 5) {
            $errors['address'] = "Address is required.";
        }

        if ($data['total_order'] === '' || !ctype_digit($data['total_order'])) {
            $errors['total_order'] = "Total Order must be a whole number.";
        }

        if (!is_numeric($data['total_price']) || $data['total_price'] < 0) {
            $errors['total_price'] = "Total Price must be a non-negative number.";
        }

        $valid_statuses = [
            'Pending',
            'Processing',
            'Packed',
            'Out for Delivery',
            'Delivered',
            'Cancelled',
            'Returned'
        ];
        if ($data['status'] === '' || !in_array($data['status'], $valid_statuses, true)) {
            $errors['status'] = "Please select a valid status.";
        }

        // ---- If valid, do your DB update here ----
        if (!$errors) {
            // Example (commented):
            // require_once '../php/config.php';
            // $stmt = $conn->prepare('UPDATE orders SET placed_on=?, name=?, email=?, phone=?, address=?, total_order=?, total_price=?, status=? WHERE user_id=?');
            // $stmt->execute([$data['placed_on'],$data['name'],$data['email'],$data['phone'],$data['address'],$data['total_order'],$data['total_price'],$data['status'],$data['user_id']]);

            $success_msg = "Order updated successfully.";
            // Optional: redirect back to orders page
            // header('Location: ../HTML/dashorder.html'); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Edit</title>
    <link rel="stylesheet" href="../css/dashedit.css" />
    <style>
        /* Small helpers for error/success */
        .error {
            color: #c62828;
            font-size: .9rem;
            margin-top: 4px
        }

        .success {
            background: #e6f6ea;
            border: 1px solid #b6e2c0;
            color: #206a3a;
            padding: 10px 12px;
            border-radius: 8px;
            margin: 10px 0
        }

        .form-error {
            background: #fff0f0;
            border: 1px solid #f3b9b9;
            color: #7a1313;
            padding: 10px 12px;
            border-radius: 8px;
            margin: 10px 0
        }

        .money {
            display: flex;
            align-items: center
        }

        .money span {
            padding: 10px;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 8px 0 0 8px;
            background: #fafafa
        }

        .money input {
            border-radius: 0 8px 8px 0
        }

        .full {
            grid-column: 1 / -1
        }

        .btns .btn a {
            color: inherit;
            text-decoration: none;
            display: block
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

        <h2 class="title">Order Details</h2>

        <?php if (!empty($errors['form'])): ?>
            <div class="form-error"><?php echo h($errors['form']); ?></div>
        <?php elseif ($success_msg): ?>
            <div class="success"><?php echo h($success_msg); ?></div>
        <?php endif; ?>

        <form class="card" action="" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>" />

            <div class="grid">
                <div>
                    <label for="user_id">User ID</label>
                    <input id="user_id" name="user_id" type="text" value="<?php echo h($data['user_id']); ?>" required>
                    <?php if (!empty($errors['user_id'])): ?><div class="error"><?php echo h($errors['user_id']); ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="placed_on">Placed On</label>
                    <input id="placed_on" name="placed_on" type="date" value="<?php echo h($data['placed_on']); ?>" required>
                    <?php if (!empty($errors['placed_on'])): ?><div class="error"><?php echo h($errors['placed_on']); ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="<?php echo h($data['name']); ?>" required>
                    <?php if (!empty($errors['name'])): ?><div class="error"><?php echo h($errors['name']); ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?php echo h($data['email']); ?>" required>
                    <?php if (!empty($errors['email'])): ?><div class="error"><?php echo h($errors['email']); ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="phone">Number</label>
                    <input id="phone" name="phone" type="tel" value="<?php echo h($data['phone']); ?>" placeholder="+Country-XXXXXXXXXX">
                    <?php if (!empty($errors['phone'])): ?><div class="error"><?php echo h($errors['phone']); ?></div><?php endif; ?>
                </div>

                <div class="full">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?php echo h($data['address']); ?></textarea>
                    <?php if (!empty($errors['address'])): ?><div class="error"><?php echo h($errors['address']); ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="total_order">Total Order</label>
                    <input id="total_order" name="total_order" type="number" min="0" value="<?php echo h($data['total_order']); ?>">
                    <?php if (!empty($errors['total_order'])): ?><div class="error"><?php echo h($errors['total_order']); ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="total_price">Total Price</label>
                    <div class="money">
                        <span>$</span>
                        <input id="total_price" name="total_price" type="number" min="0" step="0.01" value="<?php echo h($data['total_price']); ?>">
                    </div>
                    <?php if (!empty($errors['total_price'])): ?><div class="error"><?php echo h($errors['total_price']); ?></div><?php endif; ?>
                </div>

                <div class="full">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <?php
                        $opts = ['', 'Pending', 'Processing', 'Packed', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned'];
                        $labels = ['Select Status', 'Pending', 'Processing', 'Packed', 'Out for Delivery', 'Delivered', 'Cancelled', 'Returned'];
                        foreach ($opts as $i => $val) {
                            $v = $val;
                            $label = $labels[$i];
                            $sel = ($v !== '' && $data['status'] === $v) ? 'selected' : '';
                            if ($v === '') {
                                echo '<option value="" disabled ' . ($data['status'] === '' ? 'selected' : '') . '>' . $label . '</option>';
                            } else {
                                echo '<option value="' . h($v) . '" ' . $sel . '>' . h($label) . '</option>';
                            }
                        }
                        ?>
                    </select>
                    <?php if (!empty($errors['status'])): ?><div class="error"><?php echo h($errors['status']); ?></div><?php endif; ?>
                </div>
            </div>

            <div class="btns">
                <!-- Real submit button (no <a> inside) -->
                <button class="btn edit" type="submit" name="update_order" value="1">Update</button>

                <!-- Example delete button (client confirm). Replace with your real delete endpoint. -->
                <button class="btn delete" type="button" onclick="
        if(confirm('Delete this order?')){
          // TODO: submit to delete endpoint with CSRF token
          alert('Delete action here');
        }">Delete</button>
            </div>
        </form>

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
                    <form method="post" action="#">
                        <input type="email" placeholder="Enter your email" class="newsletter-input" name="newsletter_email" />
                        <button class="btn newsletter-btn" type="submit">Subscribe</button>
                    </form>
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
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" class="social-logo" alt="X Logo" />
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