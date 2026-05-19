<?php

session_start();
$userId = intval($_SESSION['IdUsername']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$postId = intval($_POST['article_id']);
$commentoId = intval($_POST['commento_id']);

$hostname = "localhost";
$username = "root";
$password = "";
$database = "my_zzzblog";

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$stmt = $conn->prepare("SELECT IdUtente FROM Commento WHERE Id = $commentoId");
$stmt->execute();
$stmt->bind_result($authorId);
$stmt->fetch();
$stmt->close();

session_write_close();
if ($authorId !== $userId) {
    die("You silly punk, don't try it again"); 
}

$sql = "DELETE FROM Commento WHERE Id='$commentoId'";
mysqli_query($conn, $sql);
mysqli_close($conn);

header("Location: ../home.php#InterKnot/idArticle=" . $postId);
?>