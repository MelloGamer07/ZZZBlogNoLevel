<?php

session_start();
$userId = intval($_SESSION['IdUsername']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$accountId = intval($_POST['user_id']);

$hostname = "localhost";
$username = "root";
$password = "";
$database = "my_zzzblog";

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($userId !== $accountId) {
    mysqli_close($conn);
    die("You silly punk, don't try it again");
}

$cleanupQueries = [
    "DELETE FROM Follow WHERE IdUtente = $userId",
    "DELETE FROM Follow WHERE IDUtenteFollow = $userId",
    "DELETE FROM LikeArticolo WHERE IdUtente = $userId",
    "DELETE FROM LikeCommento WHERE IdUtente = $userId",
    "DELETE FROM Segnalazione WHERE IdUtente = $userId",
    "DELETE FROM Ban WHERE UtenteId = $userId",
    "UPDATE AdminLogs SET IdAdmin = NULL WHERE IdAdmin = $userId",
];

foreach ($cleanupQueries as $query) {
    if (!mysqli_query($conn, $query)) {
        mysqli_close($conn);
        die("Cleanup error: " . mysqli_error($conn));
    }
}

if (!mysqli_query($conn, "DELETE FROM Utente WHERE Id = $userId")) {
    mysqli_close($conn);
    die("Delete error: " . mysqli_error($conn));
}

mysqli_close($conn);
session_write_close();

header("Location: logout.php");
exit;
?>