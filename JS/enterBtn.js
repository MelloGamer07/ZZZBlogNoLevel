const enter = document.getElementById('enterBtn');

enter.addEventListener('click', function () {
    playLoadingAnimation();

    setTimeout(() => {
        window.location.href = "loginIndex.php";
    }, 1240);
});

