<?php

require_once 'bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $model = ["title"=>"Авторизация"];
    
    $template = $twig->load('auth_form.html');

    echo $template->render($model);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $login = filter_var(trim($_POST['login']),FILTER_SANITIZE_STRING);
    $pass = filter_var(trim($_POST['pass']),FILTER_SANITIZE_STRING);
    $pass = md5($pass);

    //$result = Capsule::select('SELECT * FROM fdclients WHERE login= :login and password =:pass',['login'=>$login,'pass'=>$pass]);
    $user = Capsule::table('fdclients')
                        ->where('login','=', $login)
                        ->where('password','=',$pass)
                        ->first();
    if (empty($user)) {
    echo "Такой пользователь не найден";
    $log->debug('Попытка входа пользователя: ', ['login' => $login]);
    exit();
    }
    //var_dump($user);
    session_start();
    $_SESSION['user_id']= $user->id;//ИЗМЕНИТЬ
    $log->debug('Авторизация пользователя: ', ['user' => $user->login]);

    header('Location: /');
}
