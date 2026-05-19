<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$postId = intval($_POST['article_id']);
$commentoId = intval($_POST['commento_id']);

session_start();

if (!isset($_SESSION['IdUsername'])) {
    exit;
}

$IDUser = intval($_SESSION['IdUsername'] ?? 1);


$hostname = "localhost";
$username = "root";
$password = "";
$database = "my_zzzblog";

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$query = "
SELECT l.IdCommento, l.IdUtente
FROM LikeCommento l
WHERE IdUtente='$IDUser' AND IdCommento='$commentoId' 
";

$stmt = $conn->prepare("SELECT IdUtente FROM Commento WHERE Id = $commentoId");
$stmt->execute();
$stmt->bind_result($authorId);
$stmt->fetch();
$stmt->close();

session_write_close();


$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) === 0) {
    $sql = "INSERT INTO LikeCommento (IdUtente, IdCommento)
    VALUES ('$IDUser', '$commentoId')";
    mysqli_query($conn, $sql);

}
else{
    $sql = "DELETE FROM LikeCommento WHERE IdUtente='$IDUser' AND IdCommento='$commentoId'";
    mysqli_query($conn, $sql);

}

header("Location: ../home.php#InterKnot/idArticle=" . $postId);
?>