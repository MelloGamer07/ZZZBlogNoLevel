<?php 
    $Email = $_POST["Email1"];
    $Password = $_POST["Password1"];

    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "my_zzzblog";

    $emailExists = false;
    $passwordMatches = false; 
    $passwordParameters = passwordChecks($Password);
    
    function passwordChecks($Password){
    return (
        strlen($Password) >= 8 &&
        preg_match('/[A-Z]/', $Password) &&
        preg_match('/[a-z]/', $Password) &&
        preg_match('/\d/', $Password) &&
        preg_match('/[!@#$%^&*]/', $Password) &&
        !preg_match('/\s/', $Password)
    );
    }

    $conn = mysqli_connect($hostname, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = "SELECT * FROM Utente";
    $result = mysqli_query($conn, $query);

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        if($row["Email"] === $Email){
            $emailExists = true;
            if (password_verify($Password, $row["PasswordHash"])) {
                $passwordMatches = true;  
            }
            break; 
        }
    }

    if (!$emailExists || $passwordMatches) {
        $params = [];
        if (!$emailExists) {$params[] = "emailError=1";}
        if(!$passwordParameters) {$params[] = "passwordParametersError=1";}
        else if ($passwordMatches) {$params[] = "passwordMatchesError=1";}
        $queryString = implode("&", $params);
        header("Location: ../ForgotPassword.php?" . $queryString);
        exit;
    }

    $PasswordHash = password_hash($Password, PASSWORD_DEFAULT);
    $sql = "UPDATE Utente SET PasswordHash='$PasswordHash' WHERE Email='$Email'";
    mysqli_query($conn, $sql);

    if (!mysqli_query($conn, $sql)) {
        echo "Errore: " . mysqli_error($conn);
    }

    mysqli_close($conn);
    header("Location: ../loginIndex.php");
    exit;
?>