<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$postId = intval($_POST['article_id']);
session_start();

if (!isset($_SESSION['IdUsername'])) {
    exit;
}

$IDUser = intval($_SESSION['IdUsername'] ?? -1);


$hostname = "localhost";
$username = "root";
$password = "";
$database = "my_zzzblog";

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$stmt = $conn->prepare("SELECT IdUtente FROM Articolo WHERE Id = $postId");
$stmt->execute();
$stmt->bind_result($authorId);
$stmt->fetch();
$stmt->close();

session_write_close();

$query = "
SELECT l.IdArticolo, l.IdUtente
FROM LikeArticolo l
WHERE IdUtente='$IDUser' AND IdArticolo='$postId' 
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) === 0) {
    $sql = "INSERT INTO LikeArticolo (IdUtente, IdArticolo)
    VALUES ('$IDUser', '$postId')";
    mysqli_query($conn, $sql);

}
else{
    $sql = "DELETE FROM LikeArticolo WHERE IdUtente='$IDUser' AND IdArticolo='$postId'";
    mysqli_query($conn, $sql);

}

header("Location: ../home.php#InterKnot/idArticle=" . $postId);
?>