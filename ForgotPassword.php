<?php 
    include("login.html");
?>  

<div class="forgotPassword">
    <div class="header-login">
        <p>HOYOVERSE (ZZZ Blog)</p>
    </div>
    <form id="Form1" class="body-login" action="PHP/ForgotPasswordPHP.php" method="POST">
        <input class="input" type="text" placeholder="Enter email" id="Email1" name="Email1" >
        <input class="input" type="password" placeholder="Enter new password" id="Password1" name="Password1" >
        <input class="input" type="password" placeholder="Re-enter new password" id="ReEnterPassword1" name="ReEnterPassword1" >
        <div class="Reg-Pas">
            <p class="L">Go back to login</p>
            <p class="R">Register now</p>
        </div>
        <button class="buttonLogin" type="submit">Change password</button>
    </form>
</div>

<div class="blur2"></div> 

<script src="JS/forgotPsw.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);

    const emailErrorDiv = document.getElementById("AttentionInputsEmail");
    const emailErrorText = document.getElementById("AttentionEmailText");
    const passwordErrorDiv = document.getElementById("AttentionInputsPassword");
    const passwordErrorText = document.getElementById("AttentionPasswordText");

    if (urlParams.has('emailError')) {
        emailErrorText.textContent = "Email doesn't exist";
        emailErrorDiv.style.display = "block";
        setTimeout(() => emailErrorDiv.style.opacity = 1, 50);
    }
    if (urlParams.has('passwordParametersError')) {
        passwordErrorText.textContent = "You naughty boy, dont try to mess the js code";
        passwordErrorDiv.style.display = "block";
        setTimeout(() => passwordErrorDiv.style.opacity = 1, 50);
    }
    else if(urlParams.has('passwordMatchesError')){
        passwordErrorText.textContent = "Password already exist";
        passwordErrorDiv.style.display = "block";
        setTimeout(() => passwordErrorDiv.style.opacity = 1, 50);
    }
});
</script>
