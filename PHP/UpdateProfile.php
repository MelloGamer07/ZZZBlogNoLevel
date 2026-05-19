<?php
session_start();
header('Content-Type: application/json');

$IDUser = intval($_SESSION['IdUsername'] ?? -1);
if ($IDUser === -1) {
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}

$username  = trim($_POST['username']  ?? '');
$desc      = trim($_POST['desc']      ?? '');
$avatarId  = intval($_POST['avatar_id'] ?? 0);

if (mb_strlen($username) === 0) {
    echo json_encode(['error' => 'Username cannot be empty.']);
    exit;
}
if (mb_strlen($username) > 30) {
    echo json_encode(['error' => 'Username must be 30 characters or fewer.']);
    exit;
}
if (mb_strlen($desc) > 160) {
    echo json_encode(['error' => 'Description must be 160 characters or fewer.']);
    exit;
}
if ($avatarId < 0 || $avatarId > 57) {
    echo json_encode(['error' => 'Invalid avatar selection.']);
    exit;
}

$hostname = "localhost";
$dbUser   = "root";
$password = "";
$database = "my_zzzblog";

$conn = mysqli_connect($hostname, $dbUser, $password, $database);
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

$checkStmt = $conn->prepare(
    "SELECT Id FROM Utente WHERE Username = ? AND Id != ?"
);
$checkStmt->bind_param("si", $username, $IDUser);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    mysqli_close($conn);
    echo json_encode(['error' => 'Username is already taken.']);
    exit;
}
$checkStmt->close();

$updateStmt = $conn->prepare(
    "UPDATE Utente SET Username = ?, Avatar = ?, Descrizione = ? WHERE Id = ?"
);
$updateStmt->bind_param("sisi", $username, $avatarId, $desc, $IDUser);

if (!$updateStmt->execute()) {
    $updateStmt->close();
    mysqli_close($conn);
    echo json_encode(['error' => 'Failed to save changes. Please try again.']);
    exit;
}
$updateStmt->close();

$_SESSION['Username'] = $username;
$_SESSION['IdAvatar'] = $avatarId;

mysqli_close($conn);
echo json_encode(['success' => true]);