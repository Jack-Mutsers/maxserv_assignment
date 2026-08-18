<?php

declare(strict_types=1);

DEFINE('APPLICATION_ROOT', dirname(__DIR__));

require_once APPLICATION_ROOT . '/vendor/autoload.php';

$bootstrap = new \MaxServ\Core\Bootstrap();
$bootstrap->boot();
