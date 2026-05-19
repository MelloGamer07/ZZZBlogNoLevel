<?php
    if($IDUser == -1){
        echo '
        <div class="enterBtn" id="enterBtn">
            <p>Sign in / Sign up</p>
        </div>
        <script src="JS/enterBtn.js"></script>
        ';
        
    }

    echo '
    <div class="enterPrivacyPolicy" id="enterPrivacyPolicy" >
        <p>Privacy Policy</p>
    </div>

    <script src="JS/enterPrivacyPolicy.js"></script>
    ';
?>