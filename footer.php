<?php 

    $IDUser = intval($_SESSION['IdUsername'] ?? -1);
    $isOwnProfileAdmin = false;

    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "my_zzzblog";

    $conn = mysqli_connect($hostname, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $stmt = $conn->prepare("SELECT Ruolo FROM Utente WHERE Id = $IDUser");
    $stmt->execute();
    $stmt->bind_result($isAdmin);
    $stmt->fetch();
    $stmt->close();

    session_write_close();
    if ($isAdmin === 'admin') {
        $isOwnProfileAdmin = true;
    }

?>


<html>
<head>
<link rel="stylesheet" href="CSS/footer.css">
</head>
<body>
<footer>
    <div class="footer-container">
        <div class="left-border"></div>
        <div class="btn" id="mail">
            <img class="btn-img" src="ASSETS/IMG/UI/MailIMG2.png">
            <p class="btn-text">Mail</p>
        </div>

        <?php if ($isOwnProfileAdmin): ?>
            <div class="btn" onclick="window.location.href='AdminDashboard.php'">
                <img class="btn-img" src="ASSETS/IMG/UI/Options.png">
                <p class="btn-text">Admin</p>
            </div>
        <?php endif; ?>

        <div class="btn" id="interKnot">
            <img class="btn-img" src="ASSETS/IMG/UI/InterKnotIMG.png">
            <p class="btn-text">Inter-Knot</p>
        </div>
        
        <div class="right-border"></div>
    </div>
</footer>
</body>
</html>
