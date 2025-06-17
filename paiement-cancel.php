<?php
session_start();
require_once __DIR__ . '/cnx.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

$_SESSION['error_message'] = "Le paiement a été annulé. Vous pouvez réessayer ultérieurement.";
header('Location: mon-compte.php');
exit();
?>
