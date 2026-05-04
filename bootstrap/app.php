<?php

use App\Core\App;
use App\Core\Config;
use App\Core\Database;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

require_once __DIR__ . '/autoload.php';

$root = dirname(__DIR__);
Config::load($root);
Session::start();
Database::configure(Config::database());

$router = new Router();
require $root . '/routes/web.php';

return new App($router, Request::capture());
