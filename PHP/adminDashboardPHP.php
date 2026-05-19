<?php
session_start();

$hostname = "localhost";
$username = "root";
$password = "";
$database = "my_zzzblog";
$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) die("Connection failed: " . mysqli_connect_error());

if (!isset($_SESSION['IdUsername']) || !isset($_SESSION['UserRole']) || $_SESSION['UserRole'] !== 'admin') {
    header('Location: home.php'); exit;
}

$admin_id = (int) $_SESSION['IdUsername'];

function logAdminAction(mysqli $conn, int $adminId, string $action, ?int $targetUserId = null): int {
    $stmt = $conn->prepare("INSERT INTO AdminLogs (IdAdmin, AzionePresa, IdTargetUtente) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $adminId, $action, $targetUserId);
    $stmt->execute();
    $logId = (int) $conn->insert_id;
    $stmt->close();
    return $logId;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['success'=>false,'error'=>'Invalid ID']); exit; }

    switch ($action) {

        case 'approve_post': {
            $r   = $conn->query("SELECT IdUtente FROM Articolo WHERE Id=$id");
            $row = $r ? $r->fetch_assoc() : null;
            $stmt = $conn->prepare("UPDATE Articolo SET Pubblicato=TRUE WHERE Id=?");
            $stmt->bind_param("i",$id);
            if ($stmt->execute()) {
                logAdminAction($conn,$admin_id,"Approved post #$id",$row['IdUtente']??null);
                echo json_encode(['success'=>true]);
            } else { echo json_encode(['success'=>false,'error'=>$conn->error]); }
            $stmt->close(); break;
        }

        case 'reject_post': {
            $r   = $conn->query("SELECT IdUtente FROM Articolo WHERE Id=$id");
            $row = $r ? $r->fetch_assoc() : null;
            $stmt = $conn->prepare("DELETE FROM Articolo WHERE Id=? AND Pubblicato=FALSE");
            $stmt->bind_param("i",$id);
            if ($stmt->execute()) {
                logAdminAction($conn,$admin_id,"Rejected pending post #$id",$row['IdUtente']??null);
                echo json_encode(['success'=>true]);
            } else { echo json_encode(['success'=>false,'error'=>$conn->error]); }
            $stmt->close(); break;
        }

        case 'dismiss_post_report': {
            $stmt = $conn->prepare("DELETE FROM Segnalazione WHERE IdArticolo=? AND IdCommento IS NULL");
            $stmt->bind_param("i",$id);
            if ($stmt->execute()) {
                logAdminAction($conn,$admin_id,"Dismissed reports on post #$id");
                echo json_encode(['success'=>true]);
            } else { echo json_encode(['success'=>false,'error'=>$conn->error]); }
            $stmt->close(); break;
        }

        case 'delete_reported_post': {
            $r        = $conn->query("SELECT IdUtente FROM Articolo WHERE Id=$id");
            $row      = $r ? $r->fetch_assoc() : null;
            $authorId = $row['IdUtente'] ?? null;

            $conn->query("DELETE FROM Segnalazione WHERE IdArticolo=$id");

            $stmt = $conn->prepare("DELETE FROM Articolo WHERE Id=?");
            $stmt->bind_param("i",$id);
            if ($stmt->execute()) {
                $logId = logAdminAction($conn,$admin_id,"Deleted reported post #$id",$authorId);
                if ($authorId) {
                    $ns = $conn->prepare("INSERT INTO Notifica (IdDestinatario,IdAdminLogs,Tipo,Messaggio) VALUES (?,?,'post_eliminato','Your post was removed by an administrator.')");
                    $ns->bind_param("ii",$authorId,$logId); $ns->execute(); $ns->close();
                }
                echo json_encode(['success'=>true]);
            } else { echo json_encode(['success'=>false,'error'=>$conn->error]); }
            $stmt->close(); break;
        }

        case 'dismiss_comment_report': {
            $stmt = $conn->prepare("DELETE FROM Segnalazione WHERE IdCommento=?");
            $stmt->bind_param("i",$id);
            if ($stmt->execute()) {
                logAdminAction($conn,$admin_id,"Dismissed reports on comment #$id");
                echo json_encode(['success'=>true]);
            } else { echo json_encode(['success'=>false,'error'=>$conn->error]); }
            $stmt->close(); break;
        }

        case 'delete_comment': {
            $r        = $conn->query("SELECT IdUtente,IdArticolo FROM Commento WHERE Id=$id");
            $row      = $r ? $r->fetch_assoc() : null;
            $authorId  = $row['IdUtente']   ?? null;
            $articleId = $row['IdArticolo'] ?? null;

            $conn->query("DELETE FROM Segnalazione WHERE IdCommento=$id");

            $stmt = $conn->prepare("DELETE FROM Commento WHERE Id=?");
            $stmt->bind_param("i",$id);
            if ($stmt->execute()) {
                $logId = logAdminAction($conn,$admin_id,"Deleted reported comment #$id",$authorId);
                if ($authorId) {
                    $ns = $conn->prepare("INSERT INTO Notifica (IdDestinatario,IdAdminLogs,Tipo,IdArticolo,Messaggio) VALUES (?,?,'commento_eliminato',?,'Your comment was removed by an administrator.')");
                    $ns->bind_param("iii",$authorId,$logId,$articleId); $ns->execute(); $ns->close();
                }
                echo json_encode(['success'=>true]);
            } else { echo json_encode(['success'=>false,'error'=>$conn->error]); }
            $stmt->close(); break;
        }

        case 'ban_user': {
            $r = $conn->query("SELECT Ruolo FROM Utente WHERE Id=$id");
            $row = $r ? $r->fetch_assoc() : null;
            if (!$row || $row['Ruolo'] === 'admin') {
                echo json_encode(['success'=>false,'error'=>'Cannot ban an admin.']); break;
            }
            // Clear ALL existing ban rows before inserting the new permanent ban
            $conn->query("DELETE FROM Ban WHERE UtenteId=$id");
            $stmt = $conn->prepare("INSERT INTO Ban (UtenteId, Motivo) VALUES (?, 'Banned by admin')");
            $stmt->bind_param("i",$id);
            if ($stmt->execute()) {
                logAdminAction($conn,$admin_id,"Banned user #$id",$id);
                echo json_encode(['success'=>true,'newRole'=>'banned']);
            } else { echo json_encode(['success'=>false,'error'=>$conn->error]); }
            $stmt->close(); break;
        }

        case 'suspend_user': {
            $r = $conn->query("SELECT Ruolo FROM Utente WHERE Id=$id");
            $row = $r ? $r->fetch_assoc() : null;

            if (!$row || $row['Ruolo'] === 'admin') {
                echo json_encode(['success'=>false,'error'=>'Cannot suspend an admin.']);
                break;
            }

            $until = null;
            if (!empty($_POST['suspend_until'])) {
                $until = $_POST['suspend_until'];
            }

            $stmt = $conn->prepare("UPDATE Utente SET Ruolo='onThinIce' WHERE Id=? AND Ruolo!='admin'");
            $stmt->bind_param("i",$id);

            if ($stmt->execute()) {
                $detail = $until ? " until $until" : " indefinitely";

                $logId = logAdminAction($conn,$admin_id,"Suspended user #$id$detail",$id);

                if ($until) {
                    $conn->query("DELETE FROM Ban WHERE UtenteId=$id AND DataFine IS NOT NULL");
                    $ins = $conn->prepare("INSERT INTO Ban (UtenteId, Motivo, DataFine) VALUES (?, 'Suspension', ?)");
                    $ins->bind_param("is",$id,$until);
                    $ins->execute();
                    $ins->close();
                }

                $msg = $until
                    ? "Your account has been suspended until $until."
                    : "Your account has been suspended.";

                $ns = $conn->prepare("
                    INSERT INTO Notifica (
                        IdDestinatario,
                        Tipo,
                        IdAdminLogs,
                        Titolo,
                        Messaggio
                    ) VALUES (?, 'sospensione_account', ?, 'Account Suspended', ?)
                ");
                $ns->bind_param("iis", $id, $logId, $msg);
                $ns->execute();
                $ns->close();

                echo json_encode(['success'=>true,'newRole'=>'onThinIce']);
            } else {
                echo json_encode(['success'=>false,'error'=>$conn->error]);
            }

            $stmt->close();
            break;
        }

        case 'unban_user':
        case 'unsuspend_user': {
            if ($action === 'unban_user') {
                // Delete ALL active ban rows (both permanent DataFine IS NULL and timed DataFine > NOW())
                $stmt = $conn->prepare("DELETE FROM Ban WHERE UtenteId=? AND (DataFine IS NULL OR DataFine > NOW())");
                $stmt->bind_param("i",$id);
                if ($stmt->execute()) {
                    logAdminAction($conn,$admin_id,"Unbanned user #$id",$id);
                    echo json_encode(['success'=>true,'newRole'=>'user']);
                } else { echo json_encode(['success'=>false,'error'=>$conn->error]); }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("UPDATE Utente SET Ruolo='user' WHERE Id=? AND Ruolo='onThinIce'");
                $stmt->bind_param("i",$id);

                if ($stmt->execute()) {
                    $logId = logAdminAction($conn,$admin_id,"Unsuspended user #$id",$id);

                    $ns = $conn->prepare("
                        INSERT INTO Notifica (
                            IdDestinatario,
                            Tipo,
                            IdAdminLogs,
                            Titolo,
                            Messaggio
                        ) VALUES (?, 'sospensione_account', ?, 'Account Restored', 'Your account suspension has been removed.')
                    ");
                    $ns->bind_param("ii", $id, $logId);
                    $ns->execute();
                    $ns->close();

                    echo json_encode(['success'=>true,'newRole'=>'user']);
                } else {
                    echo json_encode(['success'=>false,'error'=>$conn->error]);
                }

                $stmt->close();
            }
            break;
        }

        default: echo json_encode(['success'=>false,'error'=>'Unknown action']);
    }
    mysqli_close($conn); exit;
}

$section = $_GET['section'] ?? 'approve';
$sort    = $_GET['sort']    ?? 'date';
$section = in_array($section, ['approve','post_reports','comment_reports','users']) ? $section : 'approve';

$usersPerPage  = 30;
$userPage      = max(1, (int)($_GET['page'] ?? 1));
$userOffset    = ($userPage - 1) * $usersPerPage;
$userSort      = in_array($_GET['usort'] ?? '', ['username','xp','posts','joined','reports']) ? ($_GET['usort'] ?? 'joined') : 'joined';
$userSortDir   = ($_GET['udir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
$userSearch    = trim($_GET['usearch'] ?? '');

$pending_posts   = [];
$post_reports    = [];
$comment_reports = [];
$users_list      = [];
$users_total     = 0;
$users_pages     = 1;

if ($section === 'approve') {
    $sql = "SELECT a.Id,a.Title,a.Img,a.DataCreazione,a.IdUtente AS author_id,
                   u.Username AS author,u.Avatar AS author_avatar,
                   GROUP_CONCAT(DISTINCT c.Nome ORDER BY c.Nome SEPARATOR '|||') AS tags
            FROM Articolo a
            JOIN Utente u ON u.Id=a.IdUtente
            LEFT JOIN CategoriaArticolo ca ON ca.IdArticolo=a.Id
            LEFT JOIN Categoria c ON c.Id=ca.IdCategoria
            WHERE a.Pubblicato=FALSE
            GROUP BY a.Id
            ORDER BY a.DataCreazione DESC";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $row['tags'] = $row['tags'] ? explode('|||',$row['tags']) : [];
        $pending_posts[] = $row;
    }
}

if ($section === 'post_reports') {
    $order = match($sort) { 'likes'=>'post_likes DESC','reports'=>'report_count DESC',default=>'last_report DESC' };
    $sql = "SELECT a.Id AS post_id,a.Title AS post_title,a.Img AS post_image,
                   a.IdUtente AS author_id,u.Username AS post_author,
                   COUNT(DISTINCT s.Id) AS report_count,
                   COUNT(DISTINCT la.IdUtente) AS post_likes,
                   MAX(s.DataCreazione) AS last_report,
                   GROUP_CONCAT(DISTINCT s.Ragione ORDER BY s.DataCreazione DESC SEPARATOR ' | ') AS reasons
            FROM Segnalazione s
            JOIN Articolo a ON a.Id=s.IdArticolo
            JOIN Utente u ON u.Id=a.IdUtente
            LEFT JOIN LikeArticolo la ON la.IdArticolo=a.Id
            WHERE s.IdCommento IS NULL
            GROUP BY a.Id
            ORDER BY $order";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) $post_reports[] = $row;
}

if ($section === 'comment_reports') {
    $order = match($sort) { 'comment_likes'=>'comment_likes DESC','reports'=>'report_count DESC',default=>'last_report DESC' };
    $sql = "SELECT c.Id AS comment_id,c.Content AS comment_text,
                   c.IdUtente AS comment_author_id,c.IdArticolo AS post_id,
                   uc.Username AS comment_author,
                   a.Title AS post_title,a.Img AS post_image,
                   COUNT(DISTINCT s.Id) AS report_count,
                   COUNT(DISTINCT lc.IdUtente) AS comment_likes,
                   MAX(s.DataCreazione) AS last_report
            FROM Segnalazione s
            JOIN Commento c ON c.Id=s.IdCommento
            JOIN Utente uc ON uc.Id=c.IdUtente
            JOIN Articolo a ON a.Id=c.IdArticolo
            LEFT JOIN LikeCommento lc ON lc.IdCommento=c.Id
            WHERE s.IdCommento IS NOT NULL
            GROUP BY c.Id
            ORDER BY $order";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) $comment_reports[] = $row;
}

if ($section === 'users') {
    $dir = strtoupper($userSortDir);
    $orderMap = [
        'username' => "u.Username $dir",
        'xp'       => "u.XP $dir",
        'posts'    => "post_count $dir",
        'joined'   => "u.DataCreazione $dir",
        'reports'  => "report_count $dir",
    ];
    $orderSql = $orderMap[$userSort] ?? "u.DataCreazione DESC";

    $searchSql = '';
    if ($userSearch !== '') {
        $safe = $conn->real_escape_string($userSearch);
        $searchSql = "AND (u.Username LIKE '%$safe%' OR u.Email LIKE '%$safe%')";
    }

    $countSql = "SELECT COUNT(*) c FROM Utente u WHERE u.Ruolo != 'admin' $searchSql";
    $users_total = (int)$conn->query($countSql)->fetch_assoc()['c'];
    $users_pages = max(1, (int)ceil($users_total / $usersPerPage));
    $userPage    = min($userPage, $users_pages);
    $userOffset  = ($userPage - 1) * $usersPerPage;

    $sql = "SELECT u.Id, u.Username, u.Email, u.Avatar, u.Ruolo, u.XP, u.DataCreazione,
                   COUNT(DISTINCT a.Id) AS post_count,
                   (SELECT COUNT(*) FROM Ban b WHERE b.UtenteId = u.Id AND b.DataFine IS NULL) AS is_banned,
                   (
                       SELECT COUNT(*) FROM Segnalazione s
                       LEFT JOIN Articolo ua ON ua.Id = s.IdArticolo
                       WHERE ua.IdUtente = u.Id OR s.IdCommento IN (
                           SELECT c.Id FROM Commento c WHERE c.IdUtente = u.Id
                       )
                   ) AS report_count
            FROM Utente u
            LEFT JOIN Articolo a ON a.IdUtente = u.Id AND a.Pubblicato = TRUE
            WHERE u.Ruolo != 'admin' $searchSql
            GROUP BY u.Id
            ORDER BY $orderSql
            LIMIT $usersPerPage OFFSET $userOffset";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) $users_list[] = $row;
}

$count_pending   = (int)$conn->query("SELECT COUNT(*) c FROM Articolo WHERE Pubblicato=FALSE")->fetch_assoc()['c'];
$count_preports  = (int)$conn->query("SELECT COUNT(DISTINCT IdArticolo) c FROM Segnalazione WHERE IdCommento IS NULL")->fetch_assoc()['c'];
$count_creports  = (int)$conn->query("SELECT COUNT(DISTINCT IdCommento) c FROM Segnalazione WHERE IdCommento IS NOT NULL")->fetch_assoc()['c'];
$count_restricted = (int)$conn->query("SELECT COUNT(DISTINCT u.Id) c FROM Utente u LEFT JOIN Ban b ON b.UtenteId=u.Id AND b.DataFine IS NULL WHERE u.Ruolo='onThinIce' OR b.Id IS NOT NULL")->fetch_assoc()['c'];

mysqli_close($conn);
?>