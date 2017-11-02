<?php

ob_start();
session_start();
error_reporting( error_reporting() & ~E_NOTICE );

$lPath = '../Admin/Core/Classes/Loader/Loader.php';
$sPath = '\Core\Classes\Loader\Loader::run';
$cPath = '../Admin/Core/Config/constants.php';


/** @noinspection PhpIncludeInspection */
require_once $lPath;

spl_autoload_register ($sPath);

/** @noinspection PhpIncludeInspection */
require_once $cPath;

\Controllers\FrontController::run();