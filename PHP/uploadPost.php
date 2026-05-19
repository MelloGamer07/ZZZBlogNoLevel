<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "my_zzzblog";

$conn = mysqli_connect($hostname, $username, "", $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

session_start();
$IDUsername = $_SESSION['IdUsername'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['post_title']));
    $text = htmlspecialchars(trim($_POST['post_text']));
    if($title !== "" && strlen($text) <= 200){
        if($text !== "" && strlen($text) <= 1000){
        if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === 0) {

            $maxSize = 10 * 1024 * 1024; // 10MB

            if ($_FILES['post_image']['size'] > $maxSize) {
                echo "File is too large. Maximum size is 10MB.";
                exit;
            }

            $fileTmp = $_FILES['post_image']['tmp_name'];
            $fileName = time() . "_" . $_FILES['post_image']['name'];
            $filePath = "ASSETS/uploads/" . $fileName;
            $saveFilePath = "../ASSETS/uploads/" . $fileName;

            if (!is_dir('../ASSETS/uploads')) {
                mkdir('../ASSETS/uploads', 0777, true);
            }

            if (move_uploaded_file($fileTmp, $saveFilePath)) {
                $query = "INSERT INTO Articolo (IDUtente, Title, Descrizione, Img, Pubblicato) VALUES ('$IDUsername','$title', '$text', '$filePath', 0)";
                if ($conn->query($query) === TRUE) {
                    header("Location: ../home.php?");
                } else {
                    echo "Database error: " . $conn->error;
                }
            } else {
                echo "Failed to upload file.";
            }
        } else {
            echo "No file uploaded or upload error.";
        }
        } else {
            echo "Empty text";
        }
    } else {
        echo "Empty title";
    }
}

session_write_close();
$conn->close();

?>
