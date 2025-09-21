<?php
// ------------------------------
// Initialize values & errors
// ------------------------------
$name = $category = $price = $details = "";
$name_err = $category_err = $price_err = $details_err = $image_err = "";
$success_msg = "";

// Allowed <select> categories
$allowed_categories = ["smoothies", "ice-cream", "chocolate", "sandwiches"];

// Helper
function clean($v)
{
    return trim($v ?? "");
}

// ------------------------------
// Handle POST
// ------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_product"])) {

    // Product Name
    $name = clean($_POST["name"] ?? "");
    if ($name === "") {
        $name_err = "Please enter product name.";
    } elseif (mb_strlen($name) > 100) {
        $name_err = "Name must be 100 characters or fewer.";
    }

    // Category
    $category = clean($_POST["category"] ?? "");
    if ($category === "") {
        $category_err = "Please select a category.";
    } elseif (!in_array($category, $allowed_categories, true)) {
        $category_err = "Invalid category selected.";
    }

    // Price
    $price = clean($_POST["price"] ?? "");
    if ($price === "") {
        $price_err = "Please enter product price.";
    } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
        $price_err = "Price must be a number (up to 2 decimals).";
    } elseif ((float)$price < 0) {
        $price_err = "Price cannot be negative.";
    }

    // Details
    $details = clean($_POST["details"] ?? "");
    if ($details === "") {
        $details_err = "Please enter product details.";
    } elseif (mb_strlen($details) < 5) {
        $details_err = "Details must be at least 5 characters.";
    } elseif (mb_strlen($details) > 1000) {
        $details_err = "Details must be 1000 characters or fewer.";
    }

    // Image validation (optional)
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
            $image_err = "Image upload failed. Please try again.";
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $_FILES["image"]["tmp_name"]);
            finfo_close($finfo);

            $allowed_mime = ["image/jpeg" => "jpg", "image/png" => "png", "image/jpg" => "jpg"];
            if (!isset($allowed_mime[$mime])) {
                $image_err = "Only JPG/PNG images are allowed.";
            } elseif ($_FILES["image"]["size"] > 2_000_000) {
                $image_err = "Image must be 2MB or smaller.";
            }
            // To save: move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/filename.jpg");
        }
    }

    // If no errors
    if ($name_err === "" && $category_err === "" && $price_err === "" && $details_err === "" && $image_err === "") {
        $success_msg = "Product added successfully!";
        // Reset fields if needed
        // $name = $category = $price = $details = "";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="../css/dashproduct.css">
    <style>
        /* Center Add/Reset buttons */
        .form-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- NAV -->
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="../php/dashboardmy.php">Home</a></li>
                <li><a href="../php/dashorder.php">Orders</a></li>
                <li><a href="../php/dashproduct.php">Products</a></li>
                <li><a href="../php/dashuser.php">Users</a></li>
                <li><a href="../php/adminProfile.php" class="active">Profile</a></li>
            </ul>
        </nav>

        <!-- Title -->
        <h2 class="middletitle">ADD PRODUCTS</h2>

        <!-- Add Product Form -->
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="product-form" enctype="multipart/form-data" novalidate>
            <div class="form-row">
                <div class="inputBox">
                    <label class="label" for="name">Product Name</label>
                    <input id="name" type="text" name="name" class="box" placeholder="enter product name"
                        value="<?php echo htmlspecialchars($name); ?>">
                    <p style="color:red;margin:6px 0;"><?php echo htmlspecialchars($name_err); ?></p>
                </div>

                <div class="inputBox">
                    <label class="label" for="category">Category</label>
                    <select id="category" name="category" class="box">
                        <option value="" <?php echo $category === "" ? "selected" : ""; ?> disabled>select category</option>
                        <option value="smoothies" <?php echo $category === "smoothies"  ? "selected" : ""; ?>>Smoothies</option>
                        <option value="ice-cream" <?php echo $category === "ice-cream"  ? "selected" : ""; ?>>Ice-Cream</option>
                        <option value="chocolate" <?php echo $category === "chocolate"  ? "selected" : ""; ?>>Chocolate</option>
                        <option value="sandwiches" <?php echo $category === "sandwiches" ? "selected" : ""; ?>>Sandwiches</option>
                    </select>
                    <p style="color:red;margin:6px 0;"><?php echo htmlspecialchars($category_err); ?></p>
                </div>
            </div>

            <div class="form-row">
                <div class="inputBox">
                    <label class="label" for="price">Price</label>
                    <input id="price" type="number" min="0" step="0.01" name="price" class="box" placeholder="enter product price"
                        value="<?php echo htmlspecialchars($price); ?>">
                    <p style="color:red;margin:6px 0;"><?php echo htmlspecialchars($price_err); ?></p>
                </div>

                <div class="inputBox">
                    <label class="label" for="image">Image</label>
                    <input id="image" type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
                    <p style="color:red;margin:6px 0;"><?php echo htmlspecialchars($image_err); ?></p>
                </div>
            </div>

            <div class="inputBox full">
                <label class="label" for="details">Details</label>
                <textarea id="details" name="details" class="box textarea" placeholder="enter product details"
                    rows="8"><?php echo htmlspecialchars($details); ?></textarea>
                <p style="color:red;margin:6px 0;"><?php echo htmlspecialchars($details_err); ?></p>
            </div>

            <!-- Centered buttons -->
            <div class="form-buttons">
                <input type="submit" class="btn" value="Add Product" name="add_product">
                <input type="reset" class="btn" value="Reset">
            </div>

            <?php if (!empty($success_msg)): ?>
                <p style="color:green;font-weight:700;margin-top:10px;"><?php echo htmlspecialchars($success_msg); ?></p>
            <?php endif; ?>
        </form>

        <h2 class="middletitle">PRODUCTS VIEW</h2>

        <!-- Products Grid -->
        <section class="products-grid">
            <article class="product-card">
                <span class="price-tag">$8/-</span>
                <img src="../Images/coffee.png" alt="Coffee" class="prod-img">
                <h3 class="prod-title">Coffee</h3>
                <p class="prod-desc">Best coffee</p>
                <div class="btn-cc">
                    <button type="button" class="middletotal"
                        onclick="location.href='../HTML/dashupdates.html'">Update</button>
                    <button class="middleorder">Delete</button>
                </div>
            </article>

            <article class="product-card">
                <span class="price-tag">$5/-</span>
                <img src="../Images/Tea.png" alt="Tea" class="prod-img">
                <h3 class="prod-title">Tea</h3>
                <p class="prod-desc">Best tea</p>
                <div class="btn-cc">
                    <button type="button" class="middletotal"
                        onclick="location.href='../HTML/dashupdates.html'">Update</button>
                    <button class="middleorder">Delete</button>
                </div>
            </article>

            <article class="product-card">
                <span class="price-tag">$12/-</span>
                <img src="../Images/Latte.png" alt="Latte" class="prod-img">
                <h3 class="prod-title">Latte</h3>
                <p class="prod-desc">Best latte</p>
                <div class="btn-cc">
                    <button type="button" class="middletotal"
                        onclick="location.href='../HTML/dashupdates.html'">Update</button>
                    <button class="middleorder">Delete</button>
                </div>
            </article>

            <article class="product-card">
                <span class="price-tag">$5/-</span>
                <img src="../Images/Croissant.png" alt="Croissant" class="prod-img">
                <h3 class="prod-title">Croissant</h3>
                <p class="prod-desc">Best croissant</p>
                <div class="btn-cc">
                    <button type="button" class="middletotal"
                        onclick="location.href='../HTML/dashupdates.html'">Update</button>
                    <button class="middleorder">Delete</button>
                </div>
            </article>

            <article class="product-card">
                <span class="price-tag">$3/-</span>
                <img src="../Images/cookies.jpg" alt="Cookies" class="prod-img">
                <h3 class="prod-title">Cookie</h3>
                <p class="prod-desc">Best cookies</p>
                <div class="btn-cc">
                    <button type="button" class="middletotal"
                        onclick="location.href='../HTML/dashupdates.html'">Update</button>
                    <button class="middleorder">Delete</button>
                </div>
            </article>

            <article class="product-card">
                <span class="price-tag">$10/-</span>
                <img src="../Images/hotchocolate.jpg" alt="Hot Chocolate" class="prod-img">
                <h3 class="prod-title">Hot Chocolate</h3>
                <p class="prod-desc">Best chocolate</p>
                <div class="btn-cc">
                    <button type="button" class="middletotal"
                        onclick="location.href='../HTML/dashupdates.html'">Update</button>
                    <button class="middleorder">Delete</button>
                </div>
            </article>
        </section>

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
                    <p>We are passionate about serving the finest coffee, crafted with love and expertise. Join us for a
                        unique coffee experience!</p>
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
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png"
                                alt="Instagram Logo" class="social-logo" />
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
    </div>
</body>

</html>