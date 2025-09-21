<?php
session_start();

/*
  Prefill values (GET): If you want to load from DB, do it here.
  For now we start empty to match your "registration style" format.
*/
$username = $email = $pic = $old_password = $new_password = $confirm_password = "";
$username_err = $email_err = $pic_err = $old_password_err = $new_password_err = $confirm_password_err = "";
$success_msg = "";

/* ---------------------------
   RESET (PRG pattern)
   ---------------------------
   When Reset is clicked, we submit with name=do value=reset,
   then redirect to the same page as GET so fields are cleared.
*/
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['do']) && $_POST['do'] === 'reset') {
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

/* ---------------------------
   VALIDATION (same style as your sample)
   --------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && (!isset($_POST['do']) || $_POST['do'] !== 'reset')) {

    // Username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $email = trim($_POST["email"]);
    }


    // pic
    if (empty(trim($_POST["pic"]))) {
        $pic_err = "Please enter your username.";
    } else {
        $pic = trim($_POST["pic"]);
    }

    // Old password
    if (empty(trim($_POST["old_password"]))) {
        $old_password_err = "Please enter your old password.";
    } else {
        $old_password = trim($_POST["old_password"]);
    }

    // New password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 4) {
        $new_password_err = "Password must have at least 4 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm new password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // If no errors, you would update the DB here
    if (
        empty($username_err) &&
        empty($email_err) &&
        empty($pic_err) &&
        empty($old_password_err) &&
        empty($new_password_err) &&
        empty($confirm_password_err)
    ) {
        // EXAMPLE: DB update (pseudo)
        // require '../config.php';
        // $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
        // $stmt->execute([$username, $email, password_hash($new_password, PASSWORD_DEFAULT), $_SESSION['admin_id']]);

        $success_msg = "Profile updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin Profile</title>
    <!-- Link your uploaded CSS -->
    <link rel="stylesheet" href="../css/dashprofile.css">
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

        <h2 class="middletitle">UPDATE PROFILE</h2>

        <?php if (!empty($success_msg)): ?>
            <p style="color: green; font-weight: bold; text-align:center;"><?php echo $success_msg; ?></p>
        <?php endif; ?>

        <section class="update-product">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <!-- Profile image preview (static). Replace with DB image if needed -->
                <img src="../Images/userphoto.jpg" alt="profile picture">

                <div class="flex">
                    <div class="inputBox">
                        <span>Username :</span>
                        <input type="text" name="username" class="box" value="<?php echo $username; ?>">
                        <p style="color: red;"><?php echo $username_err; ?></p>

                        <span>Email :</span>
                        <input type="email" name="email" class="box" value="<?php echo $email; ?>">
                        <p style="color: red;"><?php echo $email_err; ?></p>

                        <span>Update Picture :</span>
                        <input type="file" name="pic" class="box" accept="image/jpg, image/jpeg, image/png">
                    </div>

                    <div class="inputBox">
                        <span>Old Password :</span>
                        <input type="password" name="old_password" class="box">
                        <p style="color: red;"><?php echo $old_password_err; ?></p>

                        <span>New Password :</span>
                        <input type="password" name="new_password" class="box">
                        <p style="color: red;"><?php echo $new_password_err; ?></p>

                        <span>Confirm Password :</span>
                        <input type="password" name="confirm_password" class="box">
                        <p style="color: red;"><?php echo $confirm_password_err; ?></p>
                    </div>
                </div>

                <div class="btn">
                    <input type="submit" value="Update Profile" class="btn">
                    <!-- Reset posts a flag so PHP redirects to a clean GET -->
                    <button type="submit" name="do" value="reset" class="btn">Reset</button>
                </div>
            </form>
        </section>

        <!-- FOOTER -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: <a href="mailto:info@skylinecoffee.com">info@skylinecoffee.com</a></p>
                    <p>Phone: <a href="tel:+8801234567890">+880 123 456 7890</a></p>
                    <p>Address: 123 Skyline Avenue, Dhaka</p>
                </div>
                <div class="footer-section">
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
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook" class="social-logo" />
                        </a>
                        <a href="https://instagram.com" class="social-icon" aria-label="Instagram">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/instagram-new.png" alt="Instagram" class="social-logo" />
                        </a>
                        <a href="https://x.com" class="social-icon" aria-label="X">
                            <img src="https://img.icons8.com/ios-filled/50/ffffff/x.png" alt="X" class="social-logo" />
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