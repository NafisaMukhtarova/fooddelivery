<?php
//корзина

require_once ("bootstrap.php");
require_once ("classes.php");

session_start();

use Illuminate\Database\Capsule\Manager as Capsule;


if (!isset($_SESSION['user_id'])) {
    
    echo "Для заказ пройдите авторизацию!";
    require 'authentication.php';
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{ 
if (isset($_GET['id'])) {

        ///
        $dish_id = $_GET['id'];
        $quantity = $_GET['quantity'];
        $price = $_GET['price'];
    

    // проверяем наличие заказа со статусом = "корзина"
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
            //echo "Такое блюдо уже есть в корзине, обновляем количество";
            OrderClasses\WorkWithOrderList::addQuantity($cart[0]->orderid,$dish_id,$quantity,$price);    
        } else {
            //echo "Добавляем блюдо в корзину";
            OrderClasses\WorkWithOrderList::addDish($cart[0]->orderid,$dish_id,$quantity,$price);
        }

    } else {
        // если нет коризины - создаем
       // echo "создаем корзину и добавляем блюдо!";
       OrderClasses\WorkWithCart::addOrder($user_id);

        $cart = Capsule::select('SELECT O.id AS orderid FROM fdorders O, fdorderstatus S 
                                    WHERE O.status = S.id 
                                        AND S.statusname = "cart" 
                                        AND O.client = ? ',
                                        [$user_id]);

        if (isset($cart[0])) {
            //и добавляем блюдо в коризину
            OrderClasses\WorkWithOrderList::addDish($cart[0]->orderid,$dish_id,$quantity,$price);
        }
                                    
    }
}
    $template = $twig->load('cart.html');

}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{ 
    $template = $twig->load('cart_update.html');
}

$order = Capsule::select('SELECT O.id AS orderid,
                                O.totalcost, O.orderdate
                            FROM fdorders O, fdorderstatus S
                            WHERE O.status = S.id
                            AND S.statusname = "cart" 
                            AND O.client = ? ',
                                [$user_id]);

$client = Capsule::select('SELECT C.clientsname, C.clientsphonenumber,C.clientsaddress
                            FROM  fdclients C
                            WHERE  C.id = ?',
                            [$user_id]);                          
$orderlist = Capsule::select('SELECT D.dishname, L.price,L.quantity,L.cost, D.id As dishid, L.ordernumber
                            FROM fdorderlist L, fddishes D
                            WHERE  L.ordernumber = ?
                            AND L.dish = D.id',
                            [$order[0]->orderid]);



var_dump($order);    
//var_dump($client);          
//var_dump($orderlist); 
//переходим в корзину
//header('Location: /');

$model = ['title'=>'Cart','order'=>$order,'client'=>$client,'orderlist'=>$orderlist,'user'=>$client[0]->clientsname];


echo $template->render($model);