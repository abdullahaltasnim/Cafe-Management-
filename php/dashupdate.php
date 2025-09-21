<?php
// dashupdate.php — validated forms with +5 integer price bump
session_start();

/* ---------- Helpers ---------- */
function h($s)
{
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
function clean($v)
{
    return trim($v ?? '');
}

/* ---------- CSRF ---------- */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* ---------- State ---------- */
$upd_name = $upd_price = $upd_desc = '';
$upd_image_err = $upd_name_err = $upd_price_err = $upd_desc_err = '';
$upd_success = '';

$offer_code = '';
$offer_code_err = '';
$offer_success = '';

/* ---------- Handle POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        $upd_image_err = $offer_code_err = 'Security check failed. Please reload the page.';
    } else {

        /* ===== Product Update form ===== */
        if (isset($_POST['update_submit'])) {
            $upd_name  = clean($_POST['upd_name'] ?? '');
            $upd_price = clean($_POST['upd_price'] ?? '');
            $upd_desc  = clean($_POST['upd_desc'] ?? '');

            // Name
            if ($upd_name === '') {
                $upd_name_err = 'Please enter product name.';
            } elseif (mb_strlen($upd_name) > 100) {
                $upd_name_err = 'Name is too long (max 100 chars).';
            }

            // Price (+5 bump as integer)
            if ($upd_price === '' || !is_numeric($upd_price)) {
                $upd_price_err = 'Enter a valid price.';
            } else {
                $price_val = (float)$upd_price;
                if ($price_val < 0) {
                    $upd_price_err = 'Price cannot be negative.';
                } else {
                    // bump: add 5 then keep 2 decimals
                    $price_val = $price_val + 5;
                    $upd_price = number_format($price_val, 2, '.', '');
                }
            }

            // Description
            if ($upd_desc === '') {
                $upd_desc_err = 'Please write a short description.';
            } elseif (mb_strlen($upd_desc) > 500) {
                $upd_desc_err = 'Description too long (max 500 chars).';
            }

            // Image (optional)
            if (!empty($_FILES['upd_image']['name'])) {
                $allowed = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png'];
                $mime = mime_content_type($_FILES['upd_image']['tmp_name']);
                if (!isset($allowed[$mime])) {
                    $upd_image_err = 'Only JPG/PNG images are allowed.';
                } elseif ($_FILES['upd_image']['size'] > 2_000_000) {
                    $upd_image_err = 'Image must be under 2MB.';
                } else {
                    // Move file
                    $ext = $allowed[$mime];
                    if (!is_dir(__DIR__ . '/uploaded_img')) {
                        @mkdir(__DIR__ . '/uploaded_img', 0775, true);
                    }
                    $safeBase = preg_replace('/[^a-z0-9\-]+/i', '-', pathinfo($_FILES['upd_image']['name'], PATHINFO_FILENAME));
                    $fname = $safeBase . '-' . time() . '.' . $ext;
                    $dest = __DIR__ . '/uploaded_img/' . $fname;
                    if (!move_uploaded_file($_FILES['upd_image']['tmp_name'], $dest)) {
                        $upd_image_err = 'Failed to save the image.';
                    }
                }
            }

            if (!$upd_image_err && !$upd_name_err && !$upd_price_err && !$upd_desc_err) {
                // TODO: Save to DB here (name, price, desc, image path if uploaded)
                $upd_success = 'Product updated successfully (price bumped by +5).';
                // Optional: clear fields after success
                // $upd_name = $upd_price = $upd_desc = '';
            }
        }

        /* ===== Offer Code form ===== */
        if (isset($_POST['offer_submit'])) {
            $offer_code = strtoupper(clean($_POST['offer_code'] ?? ''));
            // Example rule: 5–12 chars, A–Z, 0–9, dashes/underscores allowed
            if ($offer_code === '') {
                $offer_code_err = 'Enter an offer code.';
            } elseif (!preg_match('/^[A-Z0-9\-\_]{5,12}$/', $offer_code)) {
                $offer_code_err = 'Offer code must be 5–12 chars: A–Z, 0–9, - or _.';
            } else {
                // TODO: validate against DB table or business rules
                $offer_success = 'Offer code applied!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Update & Offers</title>
    <!-- Keep your existing CSS path; change if your folders differ -->
    <link rel="stylesheet" href="../CSS/dashupdate.css" />

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

        <h2 class="title">Update Product</h2>

        <form action="" method="post" enctype="multipart/form-data" class="card" novalidate>
            <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>" />

            <div class="grid">
                <div class="full">
                    <label for="upd_image">Product Image</label>
                    <input id="upd_image" name="upd_image" type="file" accept="image/png, image/jpg, image/jpeg"
                        class="<?php echo $upd_image_err ? 'is-invalid' : ''; ?>">
                    <?php if ($upd_image_err): ?><div class="error"><?php echo h($upd_image_err); ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="upd_name">Name</label>
                    <input id="upd_name" name="upd_name" type="text" placeholder="Enter product name"
                        value="<?php echo h($upd_name); ?>"
                        class="<?php echo $upd_name_err ? 'is-invalid' : ''; ?>">
                    <?php if ($upd_name_err): ?><div class="error"><?php echo h($upd_name_err); ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="upd_price">Price</label>
                    <input id="upd_price" name="upd_price" type="number" step="0.01" placeholder="10.00"
                        value="<?php echo h($upd_price); ?>"
                        class="<?php echo $upd_price_err ? 'is-invalid' : ''; ?>">
                    <?php if ($upd_price_err): ?><div class="error"><?php echo h($upd_price_err); ?></div><?php endif; ?>
                </div>

                <div class="full">
                    <label for="upd_desc">Description</label>
                    <textarea id="upd_desc" name="upd_desc" placeholder="Short product description"
                        class="<?php echo $upd_desc_err ? 'is-invalid' : ''; ?>"><?php echo h($upd_desc); ?></textarea>
                    <?php if ($upd_desc_err): ?><div class="error"><?php echo h($upd_desc_err); ?></div><?php endif; ?>
                </div>
            </div>

            <div class="row-actions">
                <button class="btn primary" type="submit" name="update_submit">Update</button>
                <button class="btn secondary" type="reset">Reset</button>
            </div>

            <?php if ($upd_success): ?><div class="success"><?php echo h($upd_success); ?></div><?php endif; ?>
        </form>

        <h2 class="title">Apply Offer</h2>

        <form action="" method="post" class="card" novalidate>
            <input type="hidden" name="csrf" value="<?php echo h($csrf); ?>" />

            <label for="offer_code">Offer Code</label>
            <input id="offer_code" name="offer_code" type="text" placeholder="DEAL20"
                value="<?php echo h($offer_code); ?>"
                class="<?php echo $offer_code_err ? 'is-invalid' : ''; ?>">
            <?php if ($offer_code_err): ?><div class="error"><?php echo h($offer_code_err); ?></div><?php endif; ?>

            <div class="row-actions">
                <button class="btn primary" type="submit" name="offer_submit">Apply</button>
            </div>

            <?php if ($offer_success): ?><div class="success"><?php echo h($offer_success); ?></div><?php endif; ?>
        </form>


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
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook Logo"
                                class="social-logo" />
                        </a>
                        <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram Logo"
                                class="social-logo" />
                        </a>
                        <a href="https://x.com" class="social-icon" aria-label="X">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" class="social-logo" />
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