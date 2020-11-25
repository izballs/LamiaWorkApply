<?php
require_once "../classes/server.php";
header('Content-Type: application/json');

$server = new Server();
$server->handleTransactionBook($_POST);
$server->printResponse();

?>
