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
    // если есть уже корзина - выводим количество уже добавленных блюд, если корзины нет - выводим 0
    $cart = Capsule::select('SELECT SUM(L.quantity) as SUM
                                    FROM fdorders O, fdorderlist L, fdorderstatus S 
                                    WHERE O.id = L.ordernumber 
                                        AND O.status = S.id 
                                        AND S.statusname = ? 
                                        AND O.client = ?',['cart',$_SESSION['user_id']]);
    $quantity = is_null($cart[0]->SUM) ? 0 : $cart[0]->SUM;
    //var_dump($quantity);
    $model += ['cart'=>$quantity];
} else {
    $model += ['user'=>NULL,'cart'=>NULL];
    $log->debug('Вход без авторизации');
}
//var_dump($model);


$template = $twig->load('index.html');

echo $template->render($model);