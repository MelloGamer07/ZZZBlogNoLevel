<?php

    session_start();
    $IDUser = $_SESSION['IdUsername'];

    $queryCommentLikeCount = "SELECT COUNT(*) AS like_count FROM LikeArticolo WHERE IdArticolo = $articleId";
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

    <form method="POST" action="PHP/isLike.php">
        <input type="hidden" name="article_id" value="' . $articleId . '">
        <button class="post-likes" id="likes"><p> <span id="num-likes">♥  ' . $likeCount . '</span></p></button>
    </form>';

    
?>