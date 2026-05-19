<?php   

    session_start();
    $userId = isset($_SESSION['IdUsername']) ? intval($_SESSION['IdUsername']) : -1;

    if($userId == -1){
        die("You must have an account to enter");
    }

    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "my_zzzblog";

    $conn = mysqli_connect($hostname, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $stmt = $conn->prepare("SELECT *, DATE_FORMAT(DataCreazione, '%d/%m/%Y') AS DataFormattata FROM Notifica WHERE IdDestinatario = ?");
    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        die("Query failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    echo '
    <!DOCTYPE html>
    <html>
    <head>
    <link rel="stylesheet" href="CSS/mailPage.css">
    </head>
    <body>

        <header>
            <div class="back-btn" onclick="window.location.href=\'home.php\'">
                <img class="back-btn-img" src="ASSETS/IMG/UI/BackButton.png">
            </div>
        </header>

        <div class="containerNotifiche">';

    while ($row = $result->fetch_assoc()) {

        switch($row['Tipo']){
            case 'follow':
                $img = "ASSETS/IMG/UI/Polychrome.png";
                break;
            case 'post_eliminato':
                $img = "ASSETS/IMG/UI/PostDeleted.png";
                break;
            case 'commento_eliminato':
                $img = "ASSETS/IMG/UI/CommentDeleted.png";
                break;
            case 'sospensione_account':
                $img = "ASSETS/IMG/UI/Suspended.png"; 
                break;
            default:
                $img = "ASSETS/IMG/UI/Denny.png";
        }
    
        echo '
            <div class="containerNotifica" data-id="'. $row['Id'] .'">
                '.(!$row['Letta'] ? '<div class="nonLettaNotifica"></div>' : '').'
                <img src="'.$img.'">
                <div class="contentNotifica">
                    <div class="titoloNotifica">'. htmlspecialchars($row['Titolo']) .'</div>
                    <div class="sottoTitoloNotifica">Inter-Knot</div>
                    <div class="dataNotifica">'. htmlspecialchars($row['DataFormattata']) .'</div>
                </div>
            </div>';
    }

    /*for($i=0; $i<20; $i++){
    echo '
        <div class="containerNotifica" data-id="dummy'.$i.'">
            <img src="ASSETS/IMG/default.png">
            <div class="contentNotifica">
                <div class="titoloNotifica">Dummy Notification '.$i.'</div>
                <div class="sottoTitoloNotifica">Test Content</div>
                <div class="dataNotifica">29/03/2026</div>
            </div>
        </div>';
    }*/

    echo '
    </div>

    <div class="background"></div>
    <div class="blur1"></div>

    <script src="JS/mailPage.js"></script>
    </body>
    </html>';

    $stmt->close();
    $conn->close();
    session_write_close();

?>

