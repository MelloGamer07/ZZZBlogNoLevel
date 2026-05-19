<?php
declare(strict_types=1);

require_once __DIR__ . '/CommentValidator.php';


$conn = mysqli_connect("localhost", "root", "", "my_zzzblog");
if (!$conn) {
    http_response_code(500);
    die(json_encode(['error' => 'Errore di connessione al database.']));
}


session_start();

if (empty($_SESSION['IdUsername'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Devi essere loggato per commentare.']));
}

$idUtente   = (int) $_SESSION['IdUsername'];
$idArticolo = isset($_POST['article_id']) ? (int) $_POST['article_id'] : 0;
$rawComment = $_POST['post_comment'] ?? '';


$validator = new CommentValidator($conn);
$result    = $validator->validate($rawComment);

if (!$result['ok']) {
    $_SESSION['comment_error'] = $result['error'];
    session_write_close();
    $conn->close();

    header("Location: ../home.php#InterKnot/idArticle=" . $idArticolo);
    exit;
}

$comment = trim($rawComment);

$stmt = $conn->prepare(
    "INSERT INTO Commento (Content, IdUtente, IdArticolo) VALUES (?, ?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    die("Errore nella preparazione della query: " . $conn->error);
}

$stmt->bind_param('sii', $comment, $idUtente, $idArticolo);

if ($stmt->execute()) {
    $stmt->close();
    session_write_close();
    $conn->close();

    header("Location: ../home.php#InterKnot/idArticle=" . $idArticolo);
    exit;
} else {
    $stmt->close();
    session_write_close();
    $conn->close();

    http_response_code(500);
    echo "Errore nel database: " . $conn->error;
}