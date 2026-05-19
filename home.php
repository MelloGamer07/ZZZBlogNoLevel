<?php 
    require_once __DIR__ . '/PHP/auth_check.php';

    $Username = $_SESSION['Username'] ?? "Guest";
    $IDUser = intval($_SESSION['IdUsername'] ?? -1);
    $_SESSION['IdUsername'] = $IDUser;
    $IDAvatar = intval($_SESSION['IdAvatar'] ?? 0);
    $UserRole = intval($_SESSION['UserRole'] ?? "user");
    if (!isset($_COOKIE['backgroundPreference'])) {
        setcookie("backgroundPreference", 1, time() + (86400 * 30), "/");
        $backgroundPreference = 1;
    } else {
        $backgroundPreference = $_COOKIE['backgroundPreference'];
    }

    $showTerms = !isset($_COOKIE['termsAccepted']);

    session_write_close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>ZZZ Home Page</title>
    <link rel="icon" type="image/x-icon" href="ASSETS/IMG/dumbJaneDoe.png">
    <link rel="stylesheet" href="CSS/header.css">
    <link rel="stylesheet" href="CSS/home.css">
</head>
<body>
    <video autoplay muted loop id="DynamicWallpaper">
       <source id="videoSource" src="ASSETS/DynamicWallpapers/DynamicWallpaper<?php echo $backgroundPreference?>.mp4" type="video/mp4">
    </video>

    <div id="LoadingScreen"></div>

    <?php if ($showTerms): ?>
    <div class="terms-overlay" id="termsOverlay">
        <div class="terms-modal">
            <h2 class="terms-title">Terms & Conditions</h2>
            <div class="terms-body">
                <p>Welcome to <strong>ZZZ</strong>. Before continuing, please read and accept our terms.</p>
                <p>By using this site you agree to the following:</p>
                <ul>
                    <li>You will not post offensive, hateful, or harmful content.</li>
                    <li>You are responsible for the content you publish.</li>
                    <li>We reserve the right to remove content or ban users who violate these rules.</li>
                    <li>Your data may be used to improve the service.</li>
                    <li>You must be 13 years or older to use this site.</li>
                </ul>
                <p>We may update these terms at any time. Continued use of the site means you accept any changes.</p>
            </div>
            <div class="terms-actions">
                <div class="terms-decline-btn" onclick="declineTerms();">Decline</div>
                <div class="terms-accept-btn" onclick="acceptTerms();">Accept</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="page-wrapper">

      <?php include 'header.php' ?>

      <main class="content" id="posts">
        <?php include 'main.php' ?>
      </main>

    <div class="IMG-Modal-Container" id="IMG-Modal-Container">
        <img id="IMG-Modal" src="">
    </div>

    <div class="IMG-Modal-Container-Background" id="IMG-Modal-Container-Background"></div>

    <?php if ($IDUser === -1): ?>
    <div class="auth-modal-overlay" id="authModalOverlay">
        <div class="auth-modal">
            <div class="modal-exit-button" onclick="closeAuthModal()">
                <img class="modal-exit-button-img" src="ASSETS/IMG/UI/CancelIMG.png">
            </div>
            <img src="ASSETS/IMG/dumbJaneDoe.png" class="auth-modal-avatar" alt="">
            <h2 class="auth-modal-title">You're not logged in</h2>
            <p class="auth-modal-subtitle">Join ZZZ to post, comment and interact with the community.</p>
            <div class="auth-modal-actions">
                <div class="auth-modal-btn auth-login-btn" onclick="window.location.href='loginIndex.php'">Login</div>
                <div class="auth-modal-btn auth-register-btn" onclick="window.location.href='Register.php'">Register</div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php include 'PHP/plusBtn.php'; ?>

    <div id="footer-container">
        <?php include 'footer.php'; ?>
    </div>
    <script>const isLoggedIn = <?php echo ($IDUser !== -1) ? 'true' : 'false'; ?>;</script>
    <script src="JS/home.js"></script>
    <script>
        function acceptTerms() {
            document.cookie = "termsAccepted=1; max-age=" + (60*60*24*365) + "; path=/";
            const overlay = document.getElementById('termsOverlay');
            overlay.classList.add('terms-closing');
            setTimeout(() => overlay.remove(), 400);
        }

        function declineTerms() {
            window.location.href = 'https://www.google.com';
        }

        const ratio = window.innerWidth / window.innerHeight;
        const is16by9 = ratio >= 1.7 && ratio <= 1.85; // 16:9 = 1.777...

        if (is16by9) {
            document.getElementById('videoSource').src = 
                "ASSETS/DynamicWallpapers/DynamicWallpaper<?php echo $backgroundPreference?>.mp4";
            document.getElementById('DynamicWallpaper').load();
        }
    </script>
</body>
</html>
