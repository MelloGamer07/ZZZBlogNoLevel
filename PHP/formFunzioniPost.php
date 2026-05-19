<?php

    $IDUser = $_SESSION['IdUsername'];
    $IDUserArticolo = htmlspecialchars($row['AutoreId']);
    

    $queryCommentLikeCount = "SELECT COUNT(*) AS like_count FROM LikeArticolo WHERE IdArticolo = $articleId";
    $resultLikeCount = mysqli_query($conn, $queryCommentLikeCount);
    
    if (!$resultLikeCount) {
        die("Query failed: " . mysqli_error($conn));
    }
    
    $rowLikeCount = mysqli_fetch_assoc($resultLikeCount);
    $likeCount = $rowLikeCount['like_count'];

    if($IDUser != -1){
        echo '<div class="container-vertical-for-actions">';
        if($IDUser != $IDUserArticolo){
        echo '<form method="POST" action="PHP/SegnalaPost.php">
                    <input type="hidden" name="article_id" value="' . $articleId . '">
                    <input type="hidden" name="ragione" value="">
                    <button class="post-segnala"><p> <span>Segnala</span></p></button>
                </form>';
        }
        else{
            echo '<form method="POST" action="PHP/EliminaPost.php">
                    <input type="hidden" name="article_id" value="' . $articleId . '">
                    <button class="post-elimina" ><p> <span>Elimina</span></p></button>
                </form>';
        }
        
        echo '</div>';
        
    }
    return;
?>