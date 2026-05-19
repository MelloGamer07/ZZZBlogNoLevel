<?php 
session_start();

$Email = $_POST["Email1"];
$Username = $_POST["Username1"];
$Password = $_POST["Password1"];

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

$hostname = "localhost";
$username = "root";
$database = "my_zzzblog";

$emailExists = false;
$usernameExists = false;

$conn = mysqli_connect($hostname, $username, "", $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "SELECT * FROM Utente";
$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)){
    if($row["Email"] === $Email){
        $emailExists = true;
    }
    if($row["Username"] === $Username){
        $usernameExists = true;
    }
}

if ($emailExists || $usernameExists) {
    $params = [];
    if ($emailExists) $params[] = "emailError=1";
    if ($usernameExists) $params[] = "usernameError=1";
    $queryString = implode("&", $params);
    header("Location: ../Register.php?" . $queryString);  
    exit;
}

if (!passwordChecks($Password)) {
    header("Location: ../Register.php?passwordError=1");
    exit;
}

$PasswordHash = password_hash($Password, PASSWORD_DEFAULT);
$sql = "INSERT INTO Utente (Email, Username, PasswordHash)
        VALUES ('$Email', '$Username', '$PasswordHash')";

$resultInsert = mysqli_query($conn, $sql);

if (!$resultInsert) {
    echo "Errore: " . mysqli_error($conn);
    exit;
}

mysqli_close($conn);

header("Location: ../loginIndex.php");
exit;
?>
