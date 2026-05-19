<?php
    if($IDUser == -1){
        return;
    }
    echo '
    <div class="change-background-btn" id="change-footer-btn">
        <div class="change-background-btn-extra">
            <img class="change-background-img" src="ASSETS/IMG/UI/ChangeWallpaper.png">
        </div>
    </div>
    <div class="plus-btn" id="plus-btn">
        <div class="plus-btn-extra">
            <img class="plus-img" src="ASSETS/IMG/UI/plus.png">
        </div>
    </div>
    <script src="JS/plusBtn.js"></script>
    ';
?>