<?php

// 1. Mellékhatás (Request adatok beolvasása)
$value = (int)($_GET['mennyit'] ?? 1);
$sourceCurrency = $_GET['mirol'] ?? 'USD';
$targetCurrency = $_GET['mire'] ?? 'HUF';

// 2. Mellékhatás (Átváltási ráta adatok beolvasása)
$content = file_get_contents("https://kodbazis.hu/api/exchangerates?base=" . $sourceCurrency);
$decodedContent = json_decode($content, true);

// 3. Számítás
$vegeredmeny = $decodedContent["rates"][$targetCurrency] * $value;

// 4. Mellékhatás (Pénznem adatok beolvasása, saját fájlrendszerből)
$currencies = json_decode(file_get_contents('./currencies.json'), true);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" />
</head>

<body>
    <div class="card w-25 m-auto p-3">
        <form action="/" method="GET">
            <input class="form-control mb-2" type="number" name="mennyit" value="<?php echo $value ?>">

            <select name="mirol" class="form-control mb-2">
                <?php foreach ($currencies as $currency): ?>
                    <option value="<?php echo $currency['label'] ?>" <?php echo $sourceCurrency === $currency['label'] ? 'selected' : '' ?>>
                        <?php echo $currency['name'] ?> <?php echo $currency['symbol'] ?>
                    </option>
                <?php endforeach ?>
            </select>

            <h1 class="text-center">
                <?php echo number_format($vegeredmeny, 4, ',', '') ?>
            </h1>

            <select name="mire" class="form-control mb-2">
                <?php foreach ($currencies as $currency): ?>
                    <option value="<?php echo $currency['label'] ?>" <?php echo $targetCurrency === $currency['label'] ? 'selected' : '' ?>>
                        <?php echo $currency['name'] ?> <?php echo $currency['symbol'] ?>
                    </option>
                <?php endforeach ?>
            </select>
            <input type="submit" class="btn btn-primary form-control">
        </form>
    </div>
</body>

</html>