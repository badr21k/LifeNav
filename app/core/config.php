<?php

define('VERSION', '0.7.0');

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__DIR__));
define('APPS', ROOT . DS . 'app');
define('CORE', ROOT . DS . 'core');
define('LIBS', ROOT . DS . 'lib');
define('MODELS', ROOT . DS . 'models');
define('VIEWS', ROOT . DS . 'views');
define('CONTROLLERS', ROOT . DS . 'controllers');
define('LOGS', ROOT . DS . 'logs');	
define('FILES', ROOT . DS. 'files');

// ---------------------  DATABASE CONFIG -------------------------
// Defaults target TiDB Cloud; all can be overridden via environment variables.
// Required: set DB_PASS in your environment (Replit Secret or .env).

define('DB_HOST',     $_ENV['DB_HOST']     ?? 'gateway01.us-east-1.prod.aws.tidbcloud.com');
define('DB_PORT',     $_ENV['DB_PORT']     ?? '4000');
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'test');
define('DB_USER',     $_ENV['DB_USER']     ?? '2kE5MtL1evUmi3g.root');
define('DB_PASS',     $_ENV['DB_PASS']     ?? '');

// Optional SSL settings (recommended for TiDB Cloud public endpoint)
// Default to system bundle used by Replit containers; override via env if needed.
define('DB_SSL_CA',   $_ENV['DB_SSL_CA']   ?? '/etc/ssl/cert.pem');