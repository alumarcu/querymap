<?php

if (!defined('QM_ROOT_PATH'))
    define('QM_ROOT_PATH', realpath(dirname(__DIR__)));

if (!defined('QM_TESTS_PATH'))
    define('QM_TESTS_PATH', QM_ROOT_PATH . '/tests/');

if (!defined('COMPOSER_AUTOLOAD_PATH'))
    define('COMPOSER_AUTOLOAD_PATH', '/vendor/autoload.php');

$includePath = array(get_include_path());
$includePath[] = QM_TESTS_PATH;

set_include_path(implode(PATH_SEPARATOR, $includePath));

// Load the autoloader
$loader = require_once(QM_ROOT_PATH . COMPOSER_AUTOLOAD_PATH);
