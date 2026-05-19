<?php

    $IDUser = $_SESSION['IdUsername'];


    $queryCommentLikeCount = "SELECT COUNT(*) AS like_count FROM LikeCommento WHERE IdCommento = $IdCommento";
    $resultLikeCount = mysqli_query($conn, $queryCommentLikeCount);
    
    if (!$resultLikeCount) {
        die("Query failed: " . mysqli_error($conn));
    }
    
    $rowLikeCount = mysqli_fetch_assoc($resultLikeCount);
    $likeCount = $rowLikeCount['like_count'];
    
    if($IDUser == -1){
        echo '<div class="post-likes"><p> <span id="num-likes">' . $likeCount . '</span></p></div>';
        return;
    }
    echo '

    <form method="POST" action="PHP/isLikeCommento.php">
        <input type="hidden" name="article_id" value="' . $articleId . '">
        <input type="hidden" name="commento_id" value="' . $IdCommento . '">
        <button style="height: 18px; margin-left: 17px;" class="post-likes" id="likes"><p> <span id="num-likes">♥  ' . $likeCount . '</span></p></button>
    </form>';

    
?>