<?php 
    include("login.html");
?>  

<div class="button">
    <p>Enter</p>
</div>

<div class="login">
    <div class="header-login">
        <p>HOYOVERSE (ZZZ Blog)</p>
        <div class="ImgHeaderBehind">
            <img src="ASSETS/IMG/UI/CancelIMG.png" id="IMG">
        </div>
    </div>
    <form id="Form1" class="body-login" action="PHP/LoginPHP.php" method="POST">
        <input class="input" type="text" placeholder="Enter username" id="Username1" name="Username1" >
        <input class="input" type="password" placeholder="Enter password" id="Password1" name="Password1" >
        <div class="Reg-Pas">
            <p class="R">Register now</p>
            <p class="P">Forgot password?</p>
        </div>
        <button class="buttonLogin" type="submit">Start blog</button>
    </form>
</div>

<div class="blur1"></div> 

<script src="JS/index.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);

    const usernameErrorDiv = document.getElementById("AttentionInputsUsername");
    const usernameErrorText = document.getElementById("AttentionUsernameText");
    const passwordErrorDiv = document.getElementById("AttentionInputsPassword");
    const passwordErrorText = document.getElementById("AttentionPasswordText");

    if (urlParams.has('bannedError')) {
        usernameErrorText.textContent = "Your account has been banned. Login is not allowed.";
        usernameErrorDiv.style.display = "block";
        setTimeout(() => usernameErrorDiv.style.opacity = 1, 50);
        return;
    }

    if (urlParams.has('usernameError')) {
        usernameErrorText.textContent = "Username doesn't exist";
        usernameErrorDiv.style.display = "block";
        setTimeout(() => usernameErrorDiv.style.opacity = 1, 50);
    }

    if (urlParams.has('passwordError')) {
        passwordErrorText.textContent = "Password doesn't exist";
        passwordErrorDiv.style.display = "block";
        setTimeout(() => passwordErrorDiv.style.opacity = 1, 50);
    }
});
</script>