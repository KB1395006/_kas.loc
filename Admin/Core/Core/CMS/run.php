<?php

ob_start();
session_start();
error_reporting( error_reporting() & ~E_NOTICE );

$lPath = '../Core/Classes/Loader/Loader.php';
$sPath = '\Core\Classes\Loader\Loader::run';
$cPath = '../Core/Config/constants.php';

// Подключить автозагрузчик.
/** @noinspection PhpIncludeInspection */
require_once $lPath;

// Зарегистрировать класс в качестве автозагрузчика.
spl_autoload_register ($sPath);

// Подключение констант.
/** @noinspection PhpIncludeInspection */
require_once $cPath;

// Запуск SQL
\Core\Config\SQL::run();

// Запуск главного контроллера.
\Controllers\FrontController::run();