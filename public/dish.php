<?php
// карточка блюда

require_once ("bootstrap.php");

session_start();

use Illuminate\Database\Capsule\Manager as Capsule;

$id = $_GET['id'];

$dish = Capsule::select('select D.dishname AS dishname,
                                    D.id AS dishid, 
                                    D.dishphoto AS dishphoto, 
                                    D.dishdescription AS dishdescription,
                                    D.dishingredients AS dishingredients,
                                    P.price AS price 
                                        from fddishes D, fdprices P 
                                        where D.id = P.dish and P.active = true and D.id =?',[$id]);

//var_dump($dish);

$model_dish =[];
foreach ($dish as $dish)
{
    $model_dish[] = [
        'id'=> $dish->dishid,
        'name'=> $dish->dishname,
        'photo'=>$dish->dishphoto,
        'price'=>$dish->price,
        'description'=>$dish->dishdescription,
        'ingredients'=>$dish->dishingredients
    ];
}
//var_dump($model_dishes);

$model = ['title'=>$model_dish[0]['name'],'dish'=>$model_dish];

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

$template = $twig->load('dish.html');

echo $template->render($model);