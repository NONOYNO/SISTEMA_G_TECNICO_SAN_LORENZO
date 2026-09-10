<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Router;

$router = new Router();

require APP_PATH . '/routes/web.php';

$router->dispatch();
