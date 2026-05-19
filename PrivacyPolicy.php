<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>ZZZ Privacy Policy</title>
    <link rel="icon" type="image/x-icon" href="ASSETS/IMG/dumbJaneDoe.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/PrivacyPolicy.css">
</head>
<body>

<header>
    <div class="back-btn" onclick="window.location.href = 'home.php';">
        <img class="back-btn-img" src="ASSETS/IMG/UI/BackButton.png">
    </div>
</header>

<img class="background" id="Img">


<div class="login">
    <div class="header-login">
        <p style="font-size: 30px;">HOYOVERSE (ZZZ Blog)</p>
    </div>
    <div class="body-PrivacyPolicy">
        <?php include 'PrivacyPolicy.html'?>
    </div>
</div>

<div class="blur1"></div> 

<!-- FOOTER -->
<footer class="footer">
    <h2>CONTATTI</h2>
    <p>
        <strong>Email</strong>: <a>zzz.blog.support@protonmail.com</a> |
        <strong>Tel</strong>: <a>+39 6868686420</a> |
        <strong>Stazione Principale</strong>: <a>Via Borzoli 21, 16153 Genova</a>
    </p>
</footer>

<script>
    window.onload = function ChangeImage() {
    const Img = document.getElementById("Img");
    let i = 0;
    const maxImages = 90;
    Img.src = "ASSETS/IMG/LoadingScreens/" + i + ".jpg";
    setInterval(() => {
        i++;
        Img.style.opacity = 0;
        setTimeout(() => {
        Img.src = "ASSETS/IMG/LoadingScreens/" + i + ".jpg";
        Img.style.opacity = 1;
        }, 1000);
        if (i > maxImages) {
            i = 1;
        }
    }, 5000);
};
</script>

<!-- <audio id="Audio" loop>
  <source src="ASSETS/Music!/CameliaGoldenWeek-Night.mp3" type="audio/mpeg">
</audio>-->

</body>
</html>