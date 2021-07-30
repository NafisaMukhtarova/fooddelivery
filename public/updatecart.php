<?php

//var_dump($_POST);
require_once ("classes.php");

if (isset($_POST['ordernumber']))
{
    foreach($_POST['ordernumber'] as $key=>$ordernumber)
    {
        $order = (int) $_POST['ordernumber'][$key];
        $dish = (int) $_POST['dishid'][$key];
        $quantity = intval($_POST['quantity'][$key]);

        OrderClasses\WorkWithOrderList::updateList($order,$dish,$quantity);

    } 
}

header('Location: /cart.php');