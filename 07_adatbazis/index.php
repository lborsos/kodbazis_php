<?php

// útvonalválasztó
// https://kodbazis.hu/php-az-alapoktol/termek-listazo-website

$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

$routes = [
    'GET' => [
        '/' => 'countryListHandler',
        '/orszag-megtekintese' => 'singleCountryHandler'
    ],
    'POST' => [],
];

$handlerFunction = $routes[$method][$path] ?? "notFoundHandler";

$handlerFunction();

function singleCountryHandler()
{
    $countryId = $_GET['id'] ?? '';
    $pdo = getConnection();
    $statement = $pdo->prepare('SELECT * FROM countries WHERE id = ?');
    $statement->execute([$countryId]);
    $country = $statement->fetch(PDO::FETCH_ASSOC);

    $statement = $pdo->prepare('SELECT * FROM `cities` WHERE countryId = ?');
    $statement->execute([$countryId]);
    $cities = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $pdo->prepare(
        'SELECT * FROM `countryLanguages` 
        JOIN languages ON languageId = languages.id 
        WHERE countryId = ?'
    );
    $statement->execute([$countryId]);
    $languages = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo compileTemplate('wrapper.phtml', [
        'content' => compileTemplate('countrySingle.phtml', [
            'country' => $country,
            'cities' => $cities,
            'languages' => $languages,
        ])
    ]);
}

function countryListHandler()
{
    $pdo = getConnection();

    $statement = $pdo->prepare('SELECT * FROM countries');
    $statement->execute();
    $countries = $statement->fetchAll(PDO::FETCH_ASSOC);

    echo compileTemplate('wrapper.phtml', [
        'content' => compileTemplate('countryList.phtml', [
            'countries' => $countries
        ])
    ]);
}

function getConnection()
{
    return new PDO(
        'mysql:host=' . $_SERVER['DB_HOST'] . ';dbname=' . $_SERVER['DB_NAME'],
        $_SERVER['DB_USER'],
        $_SERVER['DB_PASSWORD']
    );
}

// https://kodbazis.hu/php-az-alapoktol/sablonok-szoftvertervezes-es-kompozicio
function compileTemplate($filePath, $params = []): string
{
    ob_start();
    require __DIR__ . "/views/" . $filePath;
    return ob_get_clean();
}

function notFoundHandler()
{
    echo "Oldal nem található";
}
