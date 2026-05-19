<?php 
    include("login.html");
?>  

<div class="register">
    <div class="header-login">
        <p>HOYOVERSE (ZZZ Blog)</p>
    </div>
    <form id="Form1" class="body-login" action="PHP/RegisterPHP.php" method="POST">
        <input class="input" type="text" placeholder="Enter email" id="Email1" name="Email1" >
        <input class="input" type="text" placeholder="Enter username" id="Username1" name="Username1" >
        <input class="input" type="password" placeholder="Enter password" id="Password1" name="Password1" >
        <input class="input" type="password" placeholder="Re-enter password" id="ReEnterPassword1" name="ReEnterPassword1" >
        <div class="Reg-Pas">
            <p class="L">Go back to login</p>
            <p class="P">Forgot password?</p>
        </div>
        <button class="buttonLogin" type="submit">Create account</button>
    </form>
</div>

<div class="blur2"></div> 

<script src="JS/register.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);

    const emailErrorDiv = document.getElementById("AttentionInputsEmail");
    const emailErrorText = document.getElementById("AttentionEmailText");
    const usernameErrorDiv = document.getElementById("AttentionInputsUsername");
    const usernameErrorText = document.getElementById("AttentionUsernameText");

    if (urlParams.has('emailError')) {
        emailErrorText.textContent = "Email already exists";
        emailErrorDiv.style.display = "block";
        setTimeout(() => emailErrorDiv.style.opacity = 1, 50);
    }

    if (urlParams.has('usernameError')) {
        usernameErrorText.textContent = "Username already exists";
        usernameErrorDiv.style.display = "block";
        setTimeout(() => usernameErrorDiv.style.opacity = 1, 50);
    }
});
</script>
