<?php 

    session_start();
    $userId = intval($_SESSION['IdUsername']);
    session_write_close();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        exit;
    }

    $notificaId = intval($_POST['notificaId'] ?? 1);

    if ($notificaId <= 0) {
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

    $stmt = $conn->prepare("SELECT n.IdDestinatario, n.Tipo, n.Titolo, n.Messaggio, n.Letta, DATE_FORMAT(n.DataCreazione, '%d/%m/%y %H:%i'), u.Username, u.Id FROM Notifica n LEFT JOIN Utente u ON u.Id = n.IdMittente WHERE n.IdDestinatario = ? AND n.Id = ?");
    $stmt->bind_param("ii", $userId, $notificaId);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($destinatarioId, $Tipo, $titolo, $messaggio, $letta, $dataCreazione, $mittenteUsername , $mittenteId);

    if ($stmt->fetch()) {

        if ($destinatarioId !== $userId) {
            die("You silly punk, don't try it again");
        }

        if (!$letta) {
            $update = $conn->prepare("UPDATE Notifica SET Letta = 1 WHERE Id = ? AND Letta = 0");
            $update->bind_param("i", $notificaId);
            if (!$update->execute()) {
                die("Update failed: " . $update->error);
            }
            $update->close();
        }

        if ($Tipo == 'follow') {
            $nomeClick = "onclick=\"goToProfile($mittenteId)\" style='cursor:pointer; color:#4ea1ff;'";
            $nomeSpan = "<span $nomeClick>". htmlspecialchars($mittenteUsername) ."</span>";
            $messaggio = $nomeSpan . " ha iniziato a seguirti.";
        }
        else {
            $messaggio = htmlspecialchars($messaggio);
        }

        echo "
        <div class='containerNotificona'> 
            <div class='NotificonaTitolo'>".htmlspecialchars($titolo)."</div>
            <div class='NotificonaSottoTitoloContainer'>Inter-Knot<br>Time ".htmlspecialchars($dataCreazione)."</div>
            <div class='NotificonaMessaggio'>".$messaggio."</div>
        </div>";
    }

    $stmt->close();
    mysqli_close($conn);
?>