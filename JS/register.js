/*document.addEventListener("click", () => {
  const Audio = document.getElementById("Audio");
  Audio.play();
}, { once: true });*/


/* -------------------------------
   NAVIGATION LINKS
-------------------------------- */

document.querySelector('.P')?.addEventListener('click', () => {
    window.location.href = "ForgotPassword.php";
});

document.querySelector('.L')?.addEventListener('click', () => {
    window.location.href = "loginIndex.php";
});


/* -------------------------------
   DOM ELEMENTS
-------------------------------- */

const form = document.getElementById("Form1");

const Email = document.getElementById("Email1");
const Username = document.getElementById("Username1");
const Password = document.getElementById("Password1");
const RePassword = document.getElementById("ReEnterPassword1");

const AttentionInputsEmail = document.getElementById("AttentionInputsEmail");
const AttentionEmailText = document.getElementById("AttentionEmailText");

const AttentionInputsUsername = document.getElementById("AttentionInputsUsername");
const AttentionUsernameText = document.getElementById("AttentionUsernameText");

const AttentionInputsPassword = document.getElementById("AttentionInputsPassword");


/* -------------------------------
   FORM SUBMISSION
-------------------------------- */

form.addEventListener('submit', (e) => {
    const isEmailValid = validateEmail();
    const isUsernameValid = validateUsername();
    const isPasswordValid = validatePassword();

    if (!isEmailValid || !isUsernameValid || !isPasswordValid) {
        e.preventDefault();
    }
});


/* -------------------------------
   EMAIL VALIDATION
-------------------------------- */

function validateEmail() {
    if (Email.value.trim() === '') {
        showEmailError("Please enter email");
        return false;
    }

    if(!/[@]/.test(Email.value)){
        showEmailError("Please enter a '@' in your email");
        return false;
    }

    hideEmailError();
    return true;
}

function showEmailError(message) {
    AttentionEmailText.textContent = message;
    AttentionInputsEmail.style.display = "block";
    setTimeout(() => AttentionInputsEmail.style.opacity = 1, 50);
}

function hideEmailError() {
    AttentionInputsEmail.style.opacity = 0;
    setTimeout(() => AttentionInputsEmail.style.display = "none", 200);
}

/* -------------------------------
   USERNAME VALIDATION
-------------------------------- */

function validateUsername() {
    if (Username.value.trim() === '') {
        showUsernameError("Please enter username");
        return false;
    }

    hideUsernameError();
    return true;
}

function showUsernameError(message) {
    AttentionUsernameText.textContent = message;
    AttentionInputsUsername.style.display = "block";
    setTimeout(() => AttentionInputsUsername.style.opacity = 1, 50);
}

function hideUsernameError() {
    AttentionInputsUsername.style.opacity = 0;
    setTimeout(() => AttentionInputsUsername.style.display = "none", 200);
}


/* -------------------------------
   PASSWORD VALIDATION
-------------------------------- */

function validatePassword() {
    const password = Password.value;
    const repassword = RePassword.value;

    if (password.trim() === '') {
        showPasswordError("Password cannot be empty");
        return false;
    }

    if (!passwordRules(password)) {
        showPasswordError(
            "Password must be at least 8 characters long, contain uppercase, lowercase, number, symbol (!@#$%^&*), and no spaces"
        );
        return false;
    }

    if(repassword !== password){
        showPasswordError("Re-enter your password correctly");
        return false;
    }

    hidePasswordError();
    return true;
}

function passwordRules(password) {
    return (
        password.length >= 8 &&
        /[A-Z]/.test(password) &&
        /[a-z]/.test(password) &&
        /\d/.test(password) &&
        /[!@#$%^&*]/.test(password) &&
        !/\s/.test(password)
    );
}

function showPasswordError(message) {
    AttentionInputsPassword.textContent = message;
    AttentionInputsPassword.style.display = "block";
    setTimeout(() => AttentionInputsPassword.style.opacity = 1, 50);
}

function hidePasswordError() {
    AttentionInputsPassword.style.opacity = 0;
    setTimeout(() => AttentionInputsPassword.style.display = "none", 200);
}