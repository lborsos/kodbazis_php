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
    'POST' => [
        '/register' => 'registrationHandler',
        '/login' => 'loginHandler',
        '/logout' => 'logoutHandler'
    ],
];

$handlerFunction = $routes[$method][$path] ?? "notFoundHandler";

$handlerFunction();

function getPathWithId($url) { 
    $parsed = parse_url($url);
    if(!isset($parsed['query'])) {
        return $url;
    }
    $queryParams = []; 
    parse_str($parsed['query'], $queryParams);
    return $parsed['path'] . "?id=" . $queryParams['id'];
}

function logoutHandler() 
{    
    session_start();
    $params = session_get_cookie_params(); 
    setcookie(session_name(),  '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
    session_destroy(); 
    header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']));   
}

function loginHandler()
{
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $statement->execute([$_POST["email"]]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']) . '&info=invalidCredentials');  
        return;
    }

    $isVerified = password_verify($_POST['password'], $user["password"]);

    if (!$isVerified) {
        header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']) . '&info=invalidCredentials'); 
        return;
    }

    session_start();
    $_SESSION['userId'] = $user['id'];
    header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']));
}

function registrationHandler()
{

    $pdo = getConnection();
    $statment = $pdo->prepare(
        "INSERT INTO `users` (`email`, `password`, `createdAt`) 
        VALUES (?, ?, ?);"
    );
    $statment->execute([
        $_POST["email"],
        password_hash($_POST["password"], PASSWORD_DEFAULT),
        time()
    ]);

    header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']) . '&info=registrationSuccessful'); 
}

function isLoggedIn(): bool
{
    if (!isset($_COOKIE[session_name()])) {
        return false;
    }

    session_start();

    if (!isset($_SESSION['userId'])) {
        return false;
    }

    return true;
}

function singleCountryHandler()
{

    if (!isLoggedIn()) {  
        echo compileTemplate("wrapper.phtml", [ 
            'content' => compileTemplate('subscriptionForm.phtml', [
                'info' => $_GET['info'] ?? '',
                'isRegistration' => isset($_GET['isRegistration']),
                'url' => getPathWithId($_SERVER['REQUEST_URI']), 
            ]),  
            'isAuthorized' => false,
        ]);
        return;
    }



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
        ]),
        'isAuthorized' => true,
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
        ]),
        'isAuthorized' => isLoggedIn()
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
