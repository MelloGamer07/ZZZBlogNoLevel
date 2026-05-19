<?php   

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        exit;
    }

    $postId = intval($_POST['postId'] ?? 0);

    if ($postId <= 0) {
        exit;
    }

    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "my_zzzblog";

    $conn = mysqli_connect($hostname, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = "
    SELECT 
        a.Id AS ArticoloId,
        a.Title,
        a.Img,
        a.Descrizione,
        a.IdUtente AS AutoreId, 
        u.Username AS AutoreUsername,
        u.Avatar AS AutoreAvatar
    FROM Articolo a
    JOIN Utente u ON a.IdUtente = u.Id
    WHERE a.Id = $postId
    ";

    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $articleId = $row['ArticoloId'];

        $commentQuery = "
            SELECT c.Id, c.Content, u.Id AS CommentoAutoreId, u.Username, u.Avatar 
            FROM Commento c 
            JOIN Utente u ON c.IdUtente = u.Id 
            WHERE c.IdArticolo = $articleId 
            ORDER BY c.DataCreazione ASC
        ";
        
        $commentResult = mysqli_query($conn, $commentQuery);

        echo '
        <div class="post-modal-container" id="modal-post">
            <div class="post-modal">
                <div class="post-header">
                    <img id="post-user-pfp" src="ASSETS/IMG/Avatars/Avatar' . htmlspecialchars($row['AutoreAvatar']) . '.png">
                    <h2 id="post-user-name">' . htmlspecialchars($row['AutoreUsername']) . '</h2>
                    ';
                    include 'formLikeArticolo.php';
                    echo'
                    <div class="vertical-line"></div>
                    ';
                    include 'formFunzioniPost.php';
                    echo'
                    <div class="post-exit-button" onclick="closeModal()"><img class="post-exit-button-img" src="ASSETS/IMG/UI/CancelIMG.png"></div>
                </div>
                <div class="post-main">
                    <div class="post-image-container"><img id="post-image" src="' . htmlspecialchars($row['Img']) . '"></div>
                    <div class="post-data">
                        <div id="post-content">
                            <h3 id="post-title">' . htmlspecialchars($row['Title']) . '</h3>
                            <p class="post-desc" id="post-text">' . htmlspecialchars($row['Descrizione']) . '</p>
                            <div class="post-comments">';
                                while ($comment = mysqli_fetch_assoc($commentResult)) {
                                    $IdCommento = htmlspecialchars($comment['Id']);
                                    $commentAuthorId = htmlspecialchars($comment['CommentoAutoreId']);
                                    
                                    $nameClick = ($IDUser !== -1)
                                        ? 'onclick = goToProfile(' . $commentAuthorId . '); #user/' . $commentAuthorId . '\';" style="cursor:pointer;"'
                                        : 'style="cursor:default;"';

                                    echo '
                                        <div class="comment">
                                            <div class="comment-body">
                                                <div class="comment-header">
                                                    <img id="comment-user-pfp" src="ASSETS/IMG/Avatars/Avatar' . htmlspecialchars($comment['Avatar']) . '.png">
                                                    <h5 class="comment-user-name" ' . $nameClick . '>' . htmlspecialchars($comment['Username']) . '</h5>
                                                    <div class="right-actions">
                                                    ';
                                                include 'formLikeCommento.php';
                                                echo'
                                                <div class="vertical-line"></div>
                                                ';
                                                include 'formFunzioniCommento.php';
                                                echo'
                                                </div>
                                                </div>
                                                <p class="comment-user-text">' . htmlspecialchars($comment['Content']) . '</p>
                                            </div>
                                        </div>
                                    ';
                                }

                                echo '
                            </div> <!-- post-comments -->
                        </div>
                        ';
                        include 'formComment.php';
                        echo'
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ModalCheckCommento" id="ModalCheckCommentoDestroy">
            <h5 class="XModalCheck">X</h5>
            <p>Are you sure you want to eliminate it?</p>
            <button class="confirm"> Destroy </button>
        </div>

        <div class="Background">
        </div>

        <div class="ModalCheckCommento" id="ModalCheckCommentoReport">
            <h5 class="XModalCheck">X</h5>
            <p>What is the reason for the report?</p>
            <div class="textarea-container">
                <textarea class="input" id="reportReason"
                        maxlength="160"
                        placeholder="Enter reason pretty please..."></textarea>
                <span class="edit-char-count" id="reportCharCount">0/160</span>
            </div>
            <button class="confirm"> Report </button>
        </div>
        ';
    }

    mysqli_close($conn);
?>

