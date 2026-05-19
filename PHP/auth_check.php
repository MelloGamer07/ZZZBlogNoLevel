<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['IdUsername'])) {
    // Even with an active session, verify the user isn't currently banned.
    // This catches users who were banned while already logged in.
    $hostname = "localhost";
    $dbuser   = "root";
    $database = "my_zzzblog";

    $conn = mysqli_connect($hostname, $dbuser, "", $database);
    if ($conn) {
        $sessionUserId = intval($_SESSION['IdUsername']);

        $banStmt = $conn->prepare("
            SELECT Id FROM Ban
            WHERE UtenteId = ?
              AND (DataFine IS NULL OR DataFine > NOW())
            LIMIT 1
        ");
        $banStmt->bind_param("i", $sessionUserId);
        $banStmt->execute();
        $banStmt->store_result();

        if ($banStmt->num_rows > 0) {
            $banStmt->close();

            // Log the forced logout
            $logStmt = $conn->prepare("
                INSERT INTO AdminLogs (IdAdmin, AzionePresa, IdTargetUtente)
                VALUES (?, ?, ?)
            ");
            $logAction = "Sessione terminata forzatamente: account bannato.";
            $logStmt->bind_param("isi", $sessionUserId, $logAction, $sessionUserId);
            $logStmt->execute();
            $logStmt->close();

            // Wipe the remember-me token from DB and cookie
            if (!empty($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                $delStmt = $conn->prepare("DELETE FROM RememberTokens WHERE Token = ?");
                $delStmt->bind_param("s", $token);
                $delStmt->execute();
                $delStmt->close();
                setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
            }

            mysqli_close($conn);

            // Destroy the session
            session_unset();
            session_destroy();

            header("Location: loginIndex.php?bannedError=1");
            exit();
        }

        $banStmt->close();
        mysqli_close($conn);
    }

    return;
}

if (!empty($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];

    $hostname = "localhost";
    $dbuser   = "root";
    $database = "my_zzzblog";

    $conn = mysqli_connect($hostname, $dbuser, "", $database);
    if ($conn) {
        $stmt = $conn->prepare("
            SELECT u.Id, u.Username, u.Avatar, u.Ruolo
            FROM RememberTokens rt
            JOIN Utente u ON u.Id = rt.IdUtente
            WHERE rt.Token = ? AND rt.DataScadenza > NOW()
            LIMIT 1
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($id, $username, $avatar, $ruolo);

        if ($stmt->fetch()) {
            $stmt->close();

            // Check for an active ban before restoring the session
            $banStmt = $conn->prepare("
                SELECT Id FROM Ban
                WHERE UtenteId = ?
                  AND (DataFine IS NULL OR DataFine > NOW())
                LIMIT 1
            ");
            $banStmt->bind_param("i", $id);
            $banStmt->execute();
            $banStmt->store_result();

            if ($banStmt->num_rows > 0) {
                $banStmt->close();

                // Log the blocked auto-login attempt
                $logStmt = $conn->prepare("
                    INSERT INTO AdminLogs (IdAdmin, AzionePresa, IdTargetUtente)
                    VALUES (NULL, ?, ?)
                ");
                $logAction = "Tentativo di accesso automatico (cookie) bloccato: account bannato.";
                $logStmt->bind_param("si", $logAction, $id);
                $logStmt->execute();
                $logStmt->close();

                // Invalidate the token in the DB so it can't be reused
                $delStmt = $conn->prepare("DELETE FROM RememberTokens WHERE IdUtente = ?");
                $delStmt->bind_param("i", $id);
                $delStmt->execute();
                $delStmt->close();

                // Wipe the cookie client-side
                setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);

                mysqli_close($conn);
                return;
            }
            $banStmt->close();

            // Not banned — restore the session
            $_SESSION['IdUsername'] = $id;
            $_SESSION['Username']   = $username;
            $_SESSION['IdAvatar']   = $avatar;
            $_SESSION['UserRole']   = $ruolo;

            $newToken = bin2hex(random_bytes(32));
            $expires  = date('Y-m-d H:i:s', strtotime('+30 days'));

            $upd = $conn->prepare("UPDATE RememberTokens SET Token = ?, DataScadenza = ? WHERE Token = ?");
            $upd->bind_param("sss", $newToken, $expires, $token);
            $upd->execute();
            $upd->close();

            setcookie('remember_token', $newToken, [
                'expires'  => time() + (30 * 24 * 60 * 60),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        } else {
            $stmt->close();
            setcookie('remember_token', '', ['expires' => time() - 3600, 'path' => '/']);
        }

        mysqli_close($conn);
    }
}