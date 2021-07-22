<?php

require_once 'bootstrap.php';

session_start();

$log->debug(' Пользователь вышел из системы: ', ['user_id' => $_SESSION['user_id']]);

session_destroy();


header('Location: /');