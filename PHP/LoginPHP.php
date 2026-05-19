<?php 

function passwordChecks($Password){
    return (
        strlen($Password) >= 8 &&
        preg_match('/[A-Z]/', $Password) &&
        preg_match('/[a-z]/', $Password) &&
        preg_match('/\d/', $Password) &&
        preg_match('/[!@#$%^&*]/', $Password) &&
        !preg_match('/\s/', $Password)
    );
}

$Username = $_POST["Username1"];
$Password = $_POST["Password1"];

$usernameExists = false;
$passwordExists = false;

session_start();

$hostname = "localhost";
$username = "root";
$database = "my_zzzblog";

$conn = mysqli_connect($hostname, $username, "", $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "SELECT * FROM Utente";
$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)){

    if($row["Username"] === $Username){
        $usernameExists = true;

        $userId = intval($row['Id']);
        $banStmt = $conn->prepare("
            SELECT Id, Motivo, DataFine 
            FROM Ban 
            WHERE UtenteId = ? 
              AND (DataFine IS NULL OR DataFine > NOW()) 
            LIMIT 1
        ");
        $banStmt->bind_param("i", $userId);
        $banStmt->execute();
        $banResult = $banStmt->get_result();

        if ($banResult->num_rows > 0) {
            $ban = $banResult->fetch_assoc();
            $banStmt->close();

            $logStmt = $conn->prepare("
                INSERT INTO AdminLogs (IdAdmin, AzionePresa, IdTargetUtente) 
                VALUES (?, ?, ?)
            ");
            $logAction = "Tentativo di login bloccato: account bannato. Motivo: " . ($ban['Motivo'] ?? 'N/A');
            $logStmt->bind_param("isi", $userId, $logAction, $userId);
            $logStmt->execute();
            $logStmt->close();

            mysqli_close($conn);
            header("Location: ../loginIndex.php?bannedError=1");
            exit;
        }
        $banStmt->close();

        if(password_verify($Password, $row["PasswordHash"]) && passwordChecks($Password)) {
            $_SESSION['Username'] = $Username;
            $_SESSION['IdUsername'] = $row['Id'];
            $_SESSION['IdAvatar'] = $row['Avatar'];
            $_SESSION['UserRole'] = $row['Ruolo'];

            $passwordExists = true;

            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            $userId  = intval($row['Id']);

            $stmt = $conn->prepare("DELETE FROM RememberTokens WHERE IdUtente = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO RememberTokens (IdUtente, Token, DataScadenza) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $userId, $token, $expires);
            $stmt->execute();
            $stmt->close();

            $cookieExpiry = time() + (30 * 24 * 60 * 60);
            setcookie('remember_token', $token, [
                'expires'  => $cookieExpiry,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_write_close();
            break;
        }
    }
}

mysqli_close($conn);

if (!$usernameExists || !$passwordExists) {
    $params = [];
    if (!$usernameExists) $params[] = "usernameError=1";
    if (!$passwordExists) $params[] = "passwordError=1";
    $queryString = implode("&", $params);
    header("Location: ../loginIndex.php?" . $queryString); 
    exit;
}

header("Location: ../home.php");
exit;

?>