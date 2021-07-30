<?php

require_once ("bootstrap.php");
require_once ("classes.php");

use Illuminate\Database\Capsule\Manager as Capsule;
use OrderClasses\Mailer as Mailer;

if ($_SERVER['REQUEST_METHOD'] == 'GET') { 
//var_dump($_GET);

$order_id = $_GET['ordernumber'];



$order = Capsule::select('SELECT O.id AS orderid,
                                O.totalcost, O.orderdate
                            FROM fdorders O, fdorderstatus S
                            WHERE O.status = S.id
                            AND S.statusname = "cart"
                            AND O.id = ?',
                                [$order_id]);
$client = Capsule::select('SELECT C.clientsname, C.clientsphonenumber,C.clientsaddress
                            FROM fdorders O,  fdclients C
                            WHERE  O.client = C.id
                            AND O.id = ?',
                            [$order_id]);
$orderlist = Capsule::select('SELECT D.dishname, L.price,L.quantity,L.cost, D.id As dishid, L.ordernumber
                            FROM fdorderlist L, fddishes D
                            WHERE  L.ordernumber = ?
                            AND L.dish = D.id',
                            [$order_id]);
                            
$template = $twig->load('confirmorder.html');

$model = ['title'=>'Confirm Order','order'=>$order,'client'=>$client,'orderlist'=>$orderlist,'user'=>$client[0]->clientsname];


echo $template->render($model);



}

if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    
    $order_id = $_POST['orderid'];

    OrderClasses\WorkWithCart::moveToAccepted($order_id);

    $mail = new Mailer($order_id);
    $mail->sendMailConfirmOrder();
    $mail->mailOrder();
}
 