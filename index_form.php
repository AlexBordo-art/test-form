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
                <form action="" id="form">
                    <label class="form-label" for="product">Выберите продукт:</label>
                    <select class="form-select" name="product" id="product">
                        <?php foreach ($data['products'] as $product) : ?>
                            <option value="<?php echo $product['PRICE']; ?>"><?php echo "{$product['NAME']} за {$product['PRICE']}"; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- <label for="customRange1" class="form-label">Количество дней:</label> -->
                    <!-- <input type="text" class="form-control" id="customRange1" min="1" max="30"> -->
                    <label for="days" class="form-label">Количество дней:</label>
                    <input type="text" class="form-control" id="customRange1" name="days" min="1" max="30">


                    <label for="customRange1" class="form-label">Дополнительно:</label>
                    <?php foreach ($data['services'] as $key => $value) : ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="<?php echo $value; ?>" id="flexCheckChecked1" checked>
                            <label class="form-check-label" for="flexCheckChecked1">
                                <?php echo "{$key} за {$value}"; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary">Рассчитать</button>
                </form>
            </div>
        </div>
    </div>

    <!-- <script>
            $(document).ready(function() {
                var data = <?php echo json_encode($data); ?>;
                var products = data['products'];
                var services = data['services'];
            });
        </script> -->
    <script>
        $(document).ready(function() {
            var data = <?php echo json_encode($data); ?>;
            var products = data['products'];
            var services = data['services'];

            $('#form').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: 'backend/calculate.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(data) {
                        var result = JSON.parse(data);
                        alert('Итоговая стоимость: ' + result.total_cost);
                    }
                });
            });
        });
    </script>

</body>

</html>