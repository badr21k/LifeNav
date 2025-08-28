<?php
define('VERSION', '0.9.0');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));
define('APPS', ROOT . DS . 'app');
define('CORE', ROOT . DS . 'core');
define('MODELS', ROOT . DS . 'models');
define('VIEWS', ROOT . DS . 'views');
define('CONTROLLERS', ROOT . DS . 'controllers');
define('LOGS', ROOT . DS . 'logs');
define('FILES', ROOT . DS . 'files');

/* DB (from your Files.io instance) */
define('DB_HOST',     'cyop7a.h.filess.io');
define('DB_USER',     'LifeNav_schoolnest');
define('DB_PASS',     $_ENV['DB_PASS']);   // set this in Replit secrets
define('DB_DATABASE', 'LifeNav_schoolnest');
define('DB_PORT',     '3306');
