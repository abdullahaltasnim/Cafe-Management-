<?php
// dashuser.php — PHP + Validation version of your Users page
// Preserves your layout; adds CSRF, newsletter email validation, and secure delete actions.

session_start();

/* -------- Helpers -------- */
function h($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
function clean($v)
{
    return trim((string)($v ?? ''));
}

/* -------- CSRF -------- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* -------- State (messages) -------- */
$newsletter_email = '';
$newsletter_err = '';
$newsletter_ok  = '';

$action_msg = '';   // for delete feedback
$action_err = '';

/* -------- Fake users (static for this view) --------
   In real app you would fetch from DB and render dynamically.
----------------------------------------------------- */
$users = [
    ['id' => 10001, 'date' => '01-Sept-2025', 'name' => 'Monkey D. Luffy', 'email' => 'luffymonkeyd07@gmail.com', 'type' => 'CUSTOMER'],
    ['id' => 20001, 'date' => '05-Sept-2025', 'name' => 'Nami',             'email' => 'nami.staff@skyline.com',    'type' => 'STAFF'],
    ['id' => 20002, 'date' => '06-Sept-2025', 'name' => 'Sanji',            'email' => 'sanji.staff@skyline.com',   'type' => 'STAFF'],
];

/* -------- Handle POST -------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        $action_err = 'Security check failed. Please refresh and try again.';
        $newsletter_err = $action_err;
    } else {

        /* ---- Newsletter submit ---- */
        if (isset($_POST['newsletter_submit'])) {
            $newsletter_email = clean($_POST['newsletter_email'] ?? '');

            if ($newsletter_email === '') {
                $newsletter_err = 'Please enter an email address.';
            } elseif (strlen($newsletter_email) > 120) {
                $newsletter_err = 'Email is too long (max 120 characters).';
            } elseif (!filter_var($newsletter_email, FILTER_VALIDATE_EMAIL)) {
                $newsletter_err = 'Please enter a valid email address.';
            }

            // Simple per-session rate limit (5s)
            if (!$newsletter_err) {
                $last = $_SESSION['last_newsletter_submit'] ?? 0;
                if (time() - $last < 5) {
                    $newsletter_err = 'You are submitting too fast. Please try again in a moment.';
                }
            }

            if (!$newsletter_err) {
                // TODO: Insert into DB or call your email service
                // e.g., INSERT INTO newsletter_subscribers (email, created_at) VALUES (?, NOW())
                $_SESSION['last_newsletter_submit'] = time();
                $newsletter_ok = 'Thanks! You have been subscribed.';
                $newsletter_email = '';
            }
        }

        /* ---- Delete user action ---- */
        if (isset($_POST['delete_submit'])) {
            $uid  = clean($_POST['user_id'] ?? '');
            $utyp = clean($_POST['user_type'] ?? '');

            if ($uid === '' || !ctype_digit($uid)) {
                $action_err = 'Invalid user id.';
            } elseif (!in_array($utyp, ['CUSTOMER', 'STAFF'], true)) {
                $action_err = 'Invalid user type.';
            } else {
                // Example business rule: allow deleting CUSTOMER and STAFF (block ADMIN if present)
                // TODO: Replace with DB DELETE using prepared statements
                // e.g., DELETE FROM users WHERE id = :id LIMIT 1
                $action_msg = "User #" . h($uid) . " Deleted Successfully.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="../css/dashuser.css">
</head>

<body>
    <div class="container">
        <!-- NAV — same as template -->
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="../php/dashboardmy.php">Home</a></li>
                <li><a href="../php/dashorder.php">Orders</a></li>
                <li><a href="../php/dashproduct.php">Products</a></li>
                <li><a href="../php/dashuser.php">Users</a></li>
                <li><a href="../php/adminProfile.php" class="active">Profile</a></li>
            </ul>
        </nav>

        <h2 class="middletitle">USER ACCOUNTS</h2>

        <?php if ($action_msg): ?>
            <div class="success" style="margin:10px 0;"><?php echo $action_msg; ?></div>
        <?php endif; ?>
        <?php if ($action_err): ?>
            <div class="error" style="margin:10px 0;"><?php echo $action_err; ?></div>
        <?php endif; ?>

        <!-- USERS GRID -->
        <section class="users-grid">
            <?php foreach ($users as $u): ?>
                <article class="user-card">
                    <img src="../Images/userphoto.jpg" alt="user" class="avatar">
                    <h5>User ID: <?php echo h($u['id']); ?></h5>
                    <h5><?php echo ($u['type'] === 'CUSTOMER') ? 'Placed On' : 'Joined On'; ?>: <?php echo h($u['date']); ?></h5>
                    <h5>User Name: <?php echo h($u['name']); ?></h5>
                    <h5>User Email: <?php echo h($u['email']); ?></h5>
                    <h5>User Type: <?php echo h($u['type']); ?></h5>

                    <!-- Delete button wrapped in a secure form -->
                    <div class="btn-cc">
                        <form action="" method="post" onsubmit="return confirm('Delete user #<?php echo h($u['id']); ?>?');" style="display:inline;">
                            <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">
                            <input type="hidden" name="user_id" value="<?php echo h($u['id']); ?>">
                            <input type="hidden" name="user_type" value="<?php echo h($u['type']); ?>">
                            <button class="middleorder" type="submit" name="delete_submit">Delete</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- FOOTER — same as template -->
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
                    <p>We are passionate about serving the finest coffee, crafted with
                        love and expertise. Join us for a unique coffee experience!</p>
                </div>

                <!-- Newsletter: converted to PHP form with validation -->
                <div class="footer-section">
                    <h3>Newsletter</h3>
                    <p>Subscribe for exclusive offers!</p>

                    <?php if ($newsletter_ok): ?>
                        <div class="success" style="margin:6px 0 10px;"><?php echo h($newsletter_ok); ?></div>
                    <?php endif; ?>
                    <?php if ($newsletter_err): ?>
                        <div class="error" style="margin:6px 0 10px;"><?php echo h($newsletter_err); ?></div>
                    <?php endif; ?>

                    <form action="" method="post" novalidate>
                        <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">
                        <input
                            type="email"
                            name="newsletter_email"
                            placeholder="Enter your email"
                            class="newsletter-input<?php echo $newsletter_err ? ' is-invalid' : ''; ?>"
                            value="<?php echo h($newsletter_email); ?>"
                            required />
                        <button class="btn newsletter-btn" type="submit" name="newsletter_submit">Subscribe</button>
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
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" class="social-logo" alt="X (formerly Twitter)" />
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