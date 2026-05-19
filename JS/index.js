/*document.addEventListener("click", () => {
  const Audio = document.getElementById("Audio");
  Audio.play();
}, { once: true });*/



/* -------------------------------
   SHOW/HIDE LOGIN MODAL
-------------------------------- */

const login = document.querySelector('.login');
const blur = document.querySelector('.blur1');
const AttentionInputsUsername = document.getElementById("AttentionInputsUsername");
const AttentionInputsPassword = document.getElementById("AttentionInputsPassword");

document.querySelector('.button').addEventListener('click', () => {
    login.style.display = "flex";
    blur.style.display = "flex";
    blur.style.zIndex = 1;
    setTimeout(() => {
        login.style.opacity = 1;
        blur.style.opacity = 0.8;
    }, 50);
});

document.querySelector('.blur1').addEventListener('click', () => {
    Behind();
});

document.querySelector('.ImgHeaderBehind').addEventListener('click', () => {
    Behind();
});

function Behind(){
  AttentionInputsUsername.style.opacity = 0;
  AttentionInputsPassword.style.opacity = 0;
  login.style.opacity = 0;
  blur.style.opacity = 0;
  setTimeout(() => {
      login.style.display = "none";
      blur.style.display = "none";
      blur.style.zIndex = -5;
  }, 200);
}


/* -------------------------------
   NAVIGATION LINKS
-------------------------------- */

document.querySelector('.R').addEventListener('click', () => {
    window.location.href = "Register.php";
});

document.querySelector('.P').addEventListener('click', () => {
    window.location.href = "ForgotPassword.php";
});


/* -------------------------------
   FORM ELEMENTS AND VALIDATION
-------------------------------- */

const Username = document.getElementById("Username1");
const Password = document.getElementById("Password1");
const form = document.getElementById("Form1");

const AttentionUsernameText = document.getElementById("AttentionUsernameText");

form.addEventListener('submit', (e) => {
    if (!validateUsername() || !validatePassword()) {
        e.preventDefault();
    }
});


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
    AttentionInputsUsername.style.display = "block";
    AttentionUsernameText.textContent = message;
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
    if (password.trim() === '') {
        showPasswordError("Please enter your password");
        return false;
    }
    hidePasswordError();
    return true;
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