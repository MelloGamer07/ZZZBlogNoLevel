<?php
    session_start();

    $Username = $_SESSION['Username'] ?? "Guest";
    $IDUser = intval($_SESSION['IdUsername'] ?? 0);
    $IDAvatar = intval($_SESSION['IdAvatar'] ?? 0);

    echo '
    <div class="post-modal-container" id="modal-post">
        <div class="post-modal">
            <div class="post-header">

                <img id="post-user-pfp" src="ASSETS/IMG/Avatars/Avatar'. $IDAvatar .'.png">
                <h2 id="post-user-name">' . $Username . '</h2>
                <div class="post-exit-button" onclick="closeModal()"><img class="post-exit-button-img"  src="ASSETS/IMG/UI/CancelIMG.png"></div>
            </div>

            <div class="post-main">
                <form id="send-post-form" action="PHP/uploadPost.php" method="POST" enctype="multipart/form-data">
                <div class="post-image-container" id="fileDiv"><input type="file" name="post_image" id="add-post-img" accept=".jpg,.png"></div>

                <div class="post-data">
                    <div id="post-content">
                        <input type="text" name="post_title" id="add-post-title" placeholder="Post Title">
                        <textarea name="post_text" id="add-post-text" maxlength="1000" placeholder="Type here..."></textarea>
                        <button type="submit" class="add-post-send">Pubblica</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
';
    session_write_close()
?>
