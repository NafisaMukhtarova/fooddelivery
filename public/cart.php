<?php
//корзина

require_once ("bootstrap.php");

session_start();

use Illuminate\Database\Capsule\Manager as Capsule;

if (!isset($_SESSION['user_id'])) {
    
    echo "Для заказ пройдите авторизацию!";
    require 'authentication.php';
}
//var_dump($_SESSION);
var_dump($_GET);


class WorkWithOrderList
{
    public static function addDish($cart_id,$dish_id,$quantity,$price)
    {
        Capsule::insert('insert into fdorderlist(created_at,ordernumber,dish,quantity,price) values(NOW(),?,?,?,?)',[$cart_id,$dish_id,$quantity,$price]);
    }

    public static function addqQuantity()
    {

    }
}

// проверяем наличие заказа со статусом = "корзина"
$user_id = $_SESSION['user_id'];
$dish_id = $_GET['id'];
$quantity = $_GET['quantity'];
$price = $_GET['price'];

$cart = Capsule::select('select O.id AS orderid from fdorders O, fdorderstatus S 
                            where O.status = S.id 
                                AND S.statusname = "cart" 
                                AND O.client = ? ',
                                [$user_id]);
//var_dump($cart);
if (isset($cart[0])) {
    //добавляем в корзину
    
    $dishinthecart = Capsule::select('SELECT * FROM fdorderlist WHERE ordernumber = ? AND dish = ?',[$cart[0]->orderid,$dish_id]);
    
    if (isset($dishinthecart[0])) {
        echo "Такое блюдо уже есть в корзине";
    } else {
        echo "Добавляем в корзину";
    WorkWithOrderList::addDish($cart[0]->orderid,$dish_id,$quantity,$price);
    }

} else {
    // если нет коризины - создаем
    echo "создаем корзину и добавляем блюдо!";
    $cartstatus = Capsule::table('fdorderstatus')->where('statusname','=','cart')->first();
    var_dump($cartstatus);
    Capsule::insert('insert into fdorders(created_at,orderdate,client,status) values (NOW(),CURRENT_DATE(),?,?)',
                                [$user_id,$cartstatus->id]);

    $cart = Capsule::select('select O.id AS orderid from fdorders O, fdorderstatus S 
                                where O.status = S.id 
                                    AND S.statusname = "cart" 
                                    AND O.client = ? ',
                                    [$user_id]);
    

    WorkWithOrderList::addDish($cart[0]->orderid,$dish_id,$quantity,$price);
                                
}

//корзина существует либо создана
//добавляем товар и количество в корзину
 


//переходим в корзину