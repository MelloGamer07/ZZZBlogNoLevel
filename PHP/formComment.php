<?php

    $IDUser = $_SESSION['IdUsername'];

    if($IDUser == -1){
        return;
    }
    echo '
    <form class="send-comment-form" id="comment-form" action="PHP/uploadComment.php" method="POST">
        <input type="hidden" name="article_id" value="' . $articleId . '">
        <input type="text" name="post_comment" id="add-post-comment" placeholder="Comment...">
        <button type="submit" class="add-comment-send">Send</button>
    </form>
    ';

?>