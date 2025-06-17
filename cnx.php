<?php
$host = "localhost";
$base = "autoworld";
$user = "root";
$pass = "";
$conn = new PDO("mysql:host=$host;dbname=$base", $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

?>