<?php
session_start();
$userId = intval($_SESSION['IdUsername']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$postId = intval($_POST['article_id']);
$ragione = trim(strip_tags(htmlspecialchars($_POST['ragione'])));

if (empty($ragione)) {
    die("You must provide a reason!");
}

$hostname = "localhost";
$username = "root";
$password = "";
$database = "my_zzzblog";

$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$stmt = $conn->prepare("SELECT IdUtente FROM Articolo WHERE Id = ?");
$stmt->bind_param("i", $postId);
$stmt->execute();
$stmt->bind_result($authorId);
$stmt->fetch();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM Segnalazione WHERE IdUtente = ? AND IdArticolo = ?");
$stmt->bind_param("ii", $userId, $postId);
$stmt->execute();
$stmt->bind_result($alreadyReport);
$stmt->fetch();
$stmt->close();

session_write_close();
if ($authorId === $userId) {
    die("You can't report your own post silly punk! How did you even manage to do that?..");
}

if ($alreadyReport) {
    die("You already reported this post! Don't kill the servers :(");
}

$stmt = $conn->prepare("INSERT INTO Segnalazione (Ragione, IdUtente, IdArticolo) VALUES (?, ?, ?)");
$stmt->bind_param("sii", $ragione, $userId, $postId);
$stmt->execute();
$stmt->close();

mysqli_close($conn);

header("Location: ../home.php#InterKnot/");
?>