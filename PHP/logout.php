<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $conn = mysqli_connect("localhost", "root", "", "my_zzzblog");
    if ($conn) {
        $stmt = $conn->prepare("DELETE FROM RememberTokens WHERE Token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();
        mysqli_close($conn);
    }
    setcookie('remember_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

session_unset();
session_destroy();

header("Location: ../loginIndex.php");
exit();
?>