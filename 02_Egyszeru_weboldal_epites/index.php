<?php

#$path_prefix = "/02_Egyszeru_weboldal_epites";
$path_prefix = "";

#$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = parse_url($_SERVER['REQUEST_URI'])["path"];

#$path = $parsed["path"];

#var_dump($path);

switch ($path) {
    case $path_prefix . "/penzvalto":
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
        require $path_prefix . "./views/converter.php";
        break;
    case $path_prefix . "/":
        require $path_prefix . "./views/home.html";
        break;
    // újabb útvonalak ....
    default:
        echo "Oldal nem található <a href='/'>Vissza a címlapra...</a>";
}