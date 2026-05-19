<?php
header('Content-Type: application/json');

$hostname = "localhost";
$username = "root";
$password = "";
$database = "my_zzzblog";

session_start();

if (!isset($_SESSION['IdUsername'])) {
    echo json_encode([]);
    exit;
}
else{
    $IDUser = intval($_SESSION['IdUsername']);
}

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) { echo json_encode([]); exit; }

$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, trim($_GET['q'])) : '';
$type = $_GET['type'] ?? 1;

if (strlen($q) < 1 || (strlen($type) !== 1 && strlen($type) !== 2)) {
    echo json_encode([]);
    exit;
}

if($type == 1){
    $result = mysqli_query($conn, "
    SELECT Id, Username, Avatar 
    FROM Utente 
    WHERE Username LIKE '%$q%' 
    ORDER BY Username ASC 
    LIMIT 8
    ");
}
else{
    $result = mysqli_query($conn, "
    SELECT Id, Username, Avatar 
    FROM Utente
    WHERE Username LIKE '%$q%' AND Id IN (
    SELECT IdUtenteFollow 
    FROM Follow 
    WHERE IdUtente = $IDUser
    )
    ORDER BY Username ASC 
    LIMIT 8
    ");
}

if (!$result) {
    echo json_encode([]);
    exit;
}

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

session_write_close();

echo json_encode($users);
mysqli_close($conn);
?>