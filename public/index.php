<?php

require __DIR__ . '/../vendor/autoload.php';
require '../helpers.php';

use Framework\Router;
use Framework\Session;

Session::start();





// require basePath('Framework/Router.php');
// require basePath('Framework/Database.php');

// spl_autoload_register(function ($class) {
//     $path = basePath('Framework' . '/' . $class . '.php');
//     if (file_exists($path)) {
//         require $path;
//     };
// });

// Instantiate routes
$router = new Router();

// Get routes
$routes = require basePath('routes.php');

// Get current uri and http method
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// Route to the request
$router->route($uri);
