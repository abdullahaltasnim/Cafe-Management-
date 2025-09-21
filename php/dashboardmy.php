<?php
// dashboardmy.php — PHP + validation version of your Dashboard page
// Preserves the original markup and styles, adds CSRF + email validation for Newsletter form.

session_start();

/* ---------------- Helpers ---------------- */
function h($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
function clean($v)
{
    return trim((string)($v ?? ''));
}

// Simple CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* ---------------- State ---------------- */
$email = '';
$email_err = '';
$form_success = '';

/* ---------------- Handle POST (Newsletter) ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newsletter_submit'])) {
    // CSRF check
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        $email_err = 'Security check failed. Please refresh the page and try again.';
    } else {
        $email = clean($_POST['newsletter_email'] ?? '');

        // Basic validation
        if ($email === '') {
            $email_err = 'Please enter an email address.';
        } elseif (strlen($email) > 120) {
            $email_err = 'Email is too long (max 120 characters).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = 'Please enter a valid email address.';
        }

        // Optional: super-simple rate limit (per session)
        if (!$email_err) {
            $last = $_SESSION['last_newsletter_submit'] ?? 0;
            if (time() - $last < 5) { // 5 seconds
                $email_err = 'You are submitting too fast. Please wait a moment and try again.';
            }
        }

        // If OK, pretend to save/subscribe
        if (!$email_err) {
            // TODO: Insert into DB or send to your email service
            // Example (PDO): INSERT INTO newsletter_subscribers (email, created_at) VALUES (?, NOW())
            $_SESSION['last_newsletter_submit'] = time();
            $form_success = 'Thanks! You have been subscribed.';
            // Clear the field after success
            $email = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/dashhome.css" />
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

        <!-- middle part -->
        <h2 class="title">DASHBOARD</h2>

        <div class="box">
            <div class="btn-dash">
                <h2 class="middleprice">$20/-</h2>
                <div class="btn-cc">
                    <span class="middletotal">Total Pendings</span>
                    <button class="middleorder"><a href="../HTML/dashorder.html" class="zx">See Orders</a></button>
                </div>
            </div>

            <div class="btn-dash">
                <h2 class="middleprice">$22/-</h2>
                <div class="btn-cc">
                    <span class="middletotal">Completed Orders</span>
                    <button class="middleorder"><a href="../HTML/dashorder.html" class="zx">See Orders</a></button>
                </div>
            </div>

            <div class="btn-dash">
                <h2 class="middleprice">3</h2>
                <div class="btn-cc">
                    <span class="middletotal">Orders Placed</span>
                    <button class="middleorder"><a href="../HTML/dashorder.html" class="zx">See Orders</a></button>
                </div>
            </div>

            <div class="btn-dash">
                <h2 class="middleprice">9</h2>
                <div class="btn-cc">
                    <span class="middletotal">Product Added</span>
                    <button class="middleorder"><a href="../HTML/dashproduct.html" class="zx">See Products</a></button>
                </div>
            </div>

            <div class="btn-dash">
                <h2 class="middleprice">5</h2>
                <div class="btn-cc">
                    <span class="middletotal">Total Users</span>
                    <button class="middleorder"><a href="../HTML/dashuser.html" class="zx">See Accounts</a></button>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section" id="contact-section">
                <h3>Contact Us</h3>
                <p>
                    Email:
                    <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a>
                </p>
                <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
                <p>Address: 123 Skyline Avenue, Dhaka</p>
            </div>

            <div class="footer-section" id="about-section">
                <h3>About Us</h3>
                <p>
                    We are passionate about serving the finest coffee, crafted with
                    love and expertise. Join us for a unique coffee experience!
                </p>
            </div>

            <!-- Newsletter: converted to PHP form with validation -->
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe for exclusive offers!</p>

                <?php if ($form_success): ?>
                    <div class="success" style="color:#2bb34a; font-weight:700; margin:6px 0 10px;">
                        <?php echo h($form_success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($email_err): ?>
                    <div class="error" style="color:#e34d4d; margin:6px 0 10px;">
                        <?php echo h($email_err); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post" novalidate>
                    <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>">
                    <input
                        type="email"
                        name="newsletter_email"
                        placeholder="Enter your email"
                        class="newsletter-input<?php echo $email_err ? ' is-invalid' : ''; ?>"
                        value="<?php echo h($email); ?>"
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

</body>

</html>