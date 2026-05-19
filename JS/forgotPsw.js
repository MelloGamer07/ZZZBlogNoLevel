document.querySelector('.L').onclick = () => {
    window.location.href="loginIndex.php";
};
document.querySelector('.R').onclick = () => {
    window.location.href="Register.php";
};

/* -------------------------------
   DOM ELEMENTS
-------------------------------- */

const form = document.getElementById("Form1");

const Email = document.getElementById("Email1");
const Password = document.getElementById("Password1");
const RePassword = document.getElementById("ReEnterPassword1");

const AttentionInputsEmail = document.getElementById("AttentionInputsEmail");
const AttentionEmailText = document.getElementById("AttentionEmailText");

const AttentionInputsPassword = document.getElementById("AttentionInputsPassword");


/* -------------------------------
   FORM SUBMISSION
-------------------------------- */

form.addEventListener('submit', (e) => {
    const isEmailValid = validateEmail();
    const isPasswordValid = validatePassword();

    if (!isEmailValid || !isPasswordValid) {
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