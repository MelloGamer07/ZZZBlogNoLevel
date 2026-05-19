const enterPrivacyPolicy = document.getElementById('enterPrivacyPolicy');

enterPrivacyPolicy.addEventListener('click', function () {
    playLoadingAnimation();

    setTimeout(() => {
        window.location.href = "PrivacyPolicy.php";
    }, 1240);

});