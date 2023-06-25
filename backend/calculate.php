<?php
require_once 'sdbh.php';
$dbh = new sdbh();

function calculateTotalCost($dbh, $productId, $days, $services) {
    $productData = $dbh->query_exc("SELECT * FROM a25_products WHERE ID = '$productId'");
    $product = $productData[0];
    $tariffs = unserialize($product['TARIFF']);
    $productPrice = $product['PRICE'];

    if (!empty($tariffs)) {
        foreach ($tariffs as $tariffDays => $tariffPrice) {
            if ($days >= $tariffDays) {
                $productPrice = $tariffPrice;
            }
        }
    }

    $totalCost = $productPrice * $days;

    foreach ($services as $service) {
        $serviceData = $dbh->query_exc("SELECT * FROM a25_settings WHERE set_key = 'services'");
        $servicesArray = unserialize($serviceData[0]['set_value']);
        if (isset($servicesArray[$service])) {
            $serviceCost = $servicesArray[$service] * $days;
            $totalCost += $serviceCost;
        }
    }

    return $totalCost;
}

$productId = $_POST['product'];
$days = $_POST['days'];
$services = isset($_POST['services']) ? $_POST['services'] : [];

$totalCost = calculateTotalCost($dbh, $productId, $days, $services);

echo json_encode(['total_cost' => $totalCost]);
?>
