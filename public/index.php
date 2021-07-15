<?php

require_once ("bootstrap.php");

use Illuminate\Database\Capsule\Manager as Capsule;

//$dishes = Capsule::table('fddishes')->get();
/* ПРОБА не прошло
$dishes = Capsule::table('fddishes')
                    ->join('fdprices',function($join){
                    join->on('fddishes.id','=','fdprices.dish')
                        ->where('fdprices.active','=',true);
                    })
                    ->get();
*/
$dishes = Capsule::select('select * from fddishes D, fdprices P where D.id = P.dish and P.active = true');
//var_dump($dishes);

$model_dishes =[];
foreach ($dishes as $dish)
{
    $model_dishes[] = [
        'name'=> $dish->dishname,
        'photo'=>$dish->dishphoto,
        'price'=>$dish->price
    ];
}

$model = ['title'=>'Food delivery','dishes'=>$model_dishes];
//var_dump($model);
$template = $twig->load('index.html');

echo $template->render($model);