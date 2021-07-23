<?php
//корзина

require_once ("bootstrap.php");

session_start();

use Illuminate\Database\Capsule\Manager as Capsule;

if (!isset($_SESSION['user_id'])) {
    
    echo "Для заказ пройдите авторизацию!";
    require 'authentication.php';
}

//  Работа в содержимым корзиной: добаление/удаление блюда 
class WorkWithOrderList
{
    public static function addDish($cart_id,$dish_id,$quantity,$price)
    {
        //добавляем блюдо
        Capsule::insert('INSERT INTO fdorderlist(created_at,ordernumber,dish,quantity,price) 
                            VALUES(NOW(),?,?,?,?)',
                            [$cart_id,$dish_id,$quantity,$price]);
        //обновляем общую сумму
        self::calcCost($cart_id);
    }

    public static function addQuantity($cart_id,$dish_id,$quantity,$price)
    {
        //обновляем количество и цену для блюда
        Capsule::update('UPDATE fdorderlist SET updated_at = NOW(), price = ?,quantity = quantity + ? WHERE ordernumber = ? AND dish = ?',[$price,$quantity,$cart_id,$dish_id]);
        //обновляем общую сумму
        self::calcCost($cart_id);
    }

    //обновление суммы 
    private static function calcCost($cart_id)
    {
        Capsule::update('UPDATE fdorderlist SET cost = quantity * price WHERE ordernumber = ?',[$cart_id]);
        WorkWithCart::calcTotalCost($cart_id);
    }
}

//  Работа с корзиной: общая стоимость, перевод статуса
class WorkWithCart
{
    public static function calcTotalCost($cart_id)
    {
        Capsule::update('UPDATE fdorders 
                            SET totalcost = (SELECT SUM(cost) FROM fdorderlist WHERE ordernumber = ?) 
                            WHERE id = ? ',[$cart_id,$cart_id]);
    }

    public static function addOrder($user_id)
    {
        $cartstatus = Capsule::table('fdorderstatus')->where('statusname','=','cart')->first(); //status-cart
        //var_dump($cartstatus);
        Capsule::insert('insert into fdorders(created_at,orderdate,client,status) values (NOW(),CURRENT_DATE(),?,?)',
                                    [$user_id,$cartstatus->id]);

    }
}


$user_id = $_SESSION['user_id'];
//var_dump($_GET);
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
            WorkWithOrderList::addQuantity($cart[0]->orderid,$dish_id,$quantity,$price);    
        } else {
            //echo "Добавляем блюдо в корзину";
            WorkWithOrderList::addDish($cart[0]->orderid,$dish_id,$quantity,$price);
        }

    } else {
        // если нет коризины - создаем
       // echo "создаем корзину и добавляем блюдо!";
       WorkWithCart::addOrder($user_id);

        $cart = Capsule::select('SELECT O.id AS orderid FROM fdorders O, fdorderstatus S 
                                    WHERE O.status = S.id 
                                        AND S.statusname = "cart" 
                                        AND O.client = ? ',
                                        [$user_id]);

        if (isset($cart[0])) {
            //и добавляем блюдо в коризину
            WorkWithOrderList::addDish($cart[0]->orderid,$dish_id,$quantity,$price);
        }
                                    
    }
}

$order = Capsule::select('SELECT O.id AS orderid,
                                O.totalcost, O.orderdate
                            FROM fdorders O, fdorderstatus S
                            WHERE O.status = S.id
                            AND S.statusname = "cart" 
                            AND O.client = ? ',
                                [$user_id]);
$client = Capsule::select('SELECT C.clientsname, C.clientsphonenumber,C.clientsaddress
                            FROM fdorders O,  fdclients C
                            WHERE  O.client = C.id
                            AND O.id = ?',
                            [$order[0]->orderid]);
$orderlist = Capsule::select('SELECT D.dishname, L.price,L.quantity,L.cost
                            FROM fdorderlist L, fddishes D
                            WHERE  L.ordernumber = ?
                            AND L.dish = D.id',
                            [$order[0]->orderid]);



var_dump($order);    
var_dump($client);          
var_dump($orderlist); 
//переходим в корзину
//header('Location: /');
$result_user = Capsule::table('fdclients')->where('id','=',$user_id)->first();

$model = ['title'=>'Cart','order'=>$order,'client'=>$client,'orderlist'=>$orderlist,'user'=>$result_user->clientsname];

$template = $twig->load('cart.html');

echo $template->render($model);