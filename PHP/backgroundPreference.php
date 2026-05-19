<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$backgroundPreference = intval($_POST['backgroundPreference'] ?? 1);


$cookie_name = "backgroundPreference";
$cookie_value = $backgroundPreference;
setcookie("backgroundPreference", $cookie_value, time() + (86400 * 30), "/"); 

?>