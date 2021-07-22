<?php

require_once ("bootstrap.php");

session_start();

use Illuminate\Database\Capsule\Manager as Capsule;

//Загрузка данных - список блюд
//$dishes = Capsule::table('fddishes')->get();
/* ПРОБА не прошло
$dishes = Capsule::table('fddishes')
                    ->join('fdprices',function($join){
                    join->on('fddishes.id','=','fdprices.dish')
                        ->where('fdprices.active','=',true);
                    })
                    ->get();
*/
$dishes = Capsule::select('select D.dishname AS dishname,
                                     D.id AS dishid, 
                                    D.dishphoto AS dishphoto, 
                                    P.price AS price 
                                        from fddishes D, fdprices P 
                                        where D.id = P.dish and P.active = true');
//var_dump($dishes);

$model_dishes =[];
foreach ($dishes as $dish)
{
    $model_dishes[] = [
        'id'=> $dish->dishid,
        'name'=> $dish->dishname,
        'photo'=>$dish->dishphoto,
        'price'=>$dish->price
    ];
}

$model = ['title'=>'Food delivery','dishes'=>$model_dishes];
//var_dump($model);
//var_dump($_SESSION);
//  Авторизация клиента
if (isset($_SESSION['user_id'])) {
    
    $result_user = Capsule::table('fdclients')->where('id','=',$_SESSION['user_id'])->first();

    $model += ['user'=>$result_user->clientsname];
    $log->debug('Вход авторизованного пользователя: ', ['user' => $result_user->login,'user_id' => $result_user->id]);
} else {
    $model += ['user'=>NULL];
    $log->debug('Вход  без авторизации');
}
//var_dump($model);

$template = $twig->load('index.html');

echo $template->render($model);