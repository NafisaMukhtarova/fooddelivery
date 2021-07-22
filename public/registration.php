<?php

require_once 'bootstrap.php';

use Illuminate\Database\Capsule\Manager as Capsule;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $model = ["title"=>"Регистрация"];

    $template = $twig->load('reg_form.html');

    echo $template->render($model);

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    $name = filter_var(trim($_POST['name']),FILTER_SANITIZE_STRING);
    $login = filter_var(trim($_POST['login']),FILTER_SANITIZE_STRING);
    $pass = filter_var(trim($_POST['pass']),FILTER_SANITIZE_STRING);
    $address = filter_var(trim($_POST['address']),FILTER_SANITIZE_STRING);
    $phone = filter_var(trim($_POST['phone-number']),FILTER_SANITIZE_STRING);

    if(mb_strlen($name) < 3 || mb_strlen($name)>90) {
        echo "Недопустимая длина логина";
        exit();
    } elseif(mb_strlen($login) < 3 || mb_strlen($login)>90) {
        echo "Недопустимая длина имени";
        exit();
    } elseif(mb_strlen($pass) < 2 || mb_strlen($pass)>50) {
        echo "Недопустимая длина пароля";
        exit();
    } 

    $pass = md5($pass);

    $data = [
        ':login'=>$login,
        ':name'=>$name,
        ':pass'=>$pass,
        ':address'=>$address,
        ':phone'=>$phone
    ];
    //var_dump ($data);

    $sqlinsert = 'INSERT INTO fdclients(login,clientsname,password,clientsaddress,clientsphonenumber,created_at) 
    VALUES (:login,:name,:pass,:address,:phone,NOW())';
    //echo $sqlinsert;
    try {
        Capsule::select($sqlinsert,$data);
        $log->debug('Добавлен пользователь', ['name' => $data[':name']]);
        echo "Client added";
    } catch(PDOException $e) {
            $log->error('Ошибка добавления пользователя', ['message' => $e->getMessage()]);
            echo $e->getMessage();
    }

//header('Location: /');

}
