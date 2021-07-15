<?php

require_once ("../vendor/autoload.php");

$appdir = dirname(__DIR__);

//ENV
$dotenv = Dotenv\Dotenv::createImmutable($appdir);
$dotenv->load();

//twig
$loader = new \Twig\Loader\FilesystemLoader($appdir."/templates");
$twig = new \Twig\Environment($loader);

//db illuminate
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

//настройка подключения  DB
$db = $_ENV['CONFIG_DB'];
$us = $_ENV['CONFIG_USER'];
$pw = $_ENV['CONFIG_PASSWORD'];
$ht = $_ENV['CONFIG_HOST'];
$dr = $_ENV['CONFIG_DRIVER'];

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'my_db',
    'username' => 'my_dbuser',
    'password' => 'Mba25fly!',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

