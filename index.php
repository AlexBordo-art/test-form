<?php
require_once 'backend/sdbh.php';
$dbh = new sdbh();

function getDataFromDB($dbh)
{
    $products = $dbh->query_exc("SELECT * FROM a25_products");
    $services = $dbh->query_exc("SELECT * FROM a25_settings WHERE set_key = 'services'");

    $servicesData = array();
    foreach ($services as $service) {
        $servicesData = unserialize($service['set_value']);
    }

    $productsData = array();
    foreach ($products as $product) {
        $tariff = unserialize($product['TARIFF']);
        $product['TARIFF'] = $tariff;
        $productsData[] = $product;
    }

    $data = array(
        'products' => $productsData,
        'services' => $servicesData
    );

    return $data;
}

$data = getDataFromDB($dbh);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $product_id = $_POST['product'];
    $days = $_POST['days'];
    $services = isset($_POST['services']) ? $_POST['services'] : [];

    // Находим выбранный продукт
    $selected_product = null;
    foreach ($data['products'] as $product) {
        if ($product['ID'] == $product_id) {
            $selected_product = $product;
            break;
        }
    }

    // Расчет стоимости продукта
    $product_price = $selected_product['PRICE'];
    if ($selected_product['TARIFF'] !== NULL) {
        foreach ($selected_product['TARIFF'] as $days_threshold => $price) {
            if ($days >= $days_threshold) {
                $product_price = $price;
            }
        }
    }
    $product_cost = $product_price * $days;

    // Расчет стоимости услуг
    $services_cost = 0;
    foreach ($services as $service_name) {
        $service_price = $data['services'][$service_name];
        $services_cost += $service_price * $days;
    }

    // Итоговая стоимость
    $total_cost = $product_cost + $services_cost;

    // Выводим результат
    //echo "Итоговая стоимость: " . $total_cost;
    
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="style_form.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link href="./assets/css/style.css" rel="stylesheet" />
</head>

<body>
    <div class="container">
        <div class="row row-body">
            <div class="col-3">
                <span style="text-align: center">Форма обратной связи</span>
                <i class="bi bi-activity"></i>
            </div>
            <div class="col-9">
                <form action="" method="POST" id="form">
                    <label class="form-label" for="product">Выберите продукт:</label>
                    <select class="form-select" name="product" id="product">
                        <?php foreach ($data['products'] as $product) : ?>
                            <option value="<?php echo $product['ID']; ?>"><?php echo "{$product['NAME']} за {$product['PRICE']}"; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="days" class="form-label">Количество дней:</label>
                    <input type="text" class="form-control" id="customRange1" name="days" min="1" max="30">

                    <label for="customRange1" class="form-label">Дополнительно:</label>
                    <?php foreach ($data['services'] as $key => $value) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="<?php echo $key; ?>" id="<?php echo $key; ?>" name="services[]" checked>
                            <label class="form-check-label" for="<?php echo $key; ?>">
                                <?php echo "{$key} за {$value}"; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary">Рассчитать</button>
                    <?php if ($total_cost !== null) : ?> <!-- Если результат был расчитан, отображаем его -->
                        <div>Итоговая стоимость: <?php echo $total_cost; ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>

</html>