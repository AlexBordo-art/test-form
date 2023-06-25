<?php
require_once 'sdbh.php';
$dbh = new sdbh();

$products = $dbh->query_exc("SELECT * FROM a25_products");

echo json_encode($products);
?>
