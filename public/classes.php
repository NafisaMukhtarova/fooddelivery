<?php

namespace OrderClasses;

require_once ("bootstrap.php");

use Illuminate\Database\Capsule\Manager as Capsule;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


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

    public static function updateList($ordernumber,$dishid,$quantity)
    {
        if ($quantity > 0) {
            Capsule::update('UPDATE fdorderlist SET updated_at = NOW(),quantity = ? WHERE ordernumber = ? AND dish = ?',
                                    [$quantity,$ordernumber,$dishid]);
        } else {
            Capsule::delete('DELETE FROM fdorderlist WHERE ordernumber = ? AND dish = ?',[$ordernumber,$dishid]);
        }

        self::calcCost($ordernumber);
        
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

    public static function moveToAccepted($order_id)
    {
        Capsule::update('UPDATE fdorders SET status = (SELECT MIN(id) FROM fdorderstatus WHERE statusname like "accepted") WHERE id = ?',[$order_id]);

    }

}

class Mailer
{

    private $order;
    private $orderlist;

    public function __construct($order_id)
    {
        $orderselect = Capsule::select('SELECT O.id AS orderid, O.totalcost, O.orderdate,
                            C.clientsname,C.clientsphonenumber,C.clientsaddress,C.clientsmail
                            FROM fdorders O, fdclients C
                            WHERE O.id = ?
                            AND C.id = O.client',
                                [$order_id]);
        $this->order = $orderselect;
                            
        $orderlistselect = Capsule::select('SELECT D.dishname, L.price,L.quantity,L.cost
                            FROM fdorderlist L, fddishes D
                            WHERE  L.ordernumber = ?
                            AND L.dish = D.id',
                            [$order_id]);
        $this->orderlist = $orderlistselect;

        var_dump($this->orderlist);
    }

    public function sendMailConfirmOrder() // sending mail to customer
    {
        $mail = new PHPMailer(true);

        $host = $_ENV['MAIL_HOST'];
        $username = $_ENV['MAIL_USERNAME'];
        $pass = $_ENV['MAIL_PASSWORD'];

        if(!is_null($this->order[0]->clientsmail)) {

        try {
            //Server settings
            $mail->CharSet = "utf-8";
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $host;                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $username;                     //SMTP username
            $mail->Password   = $pass;                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        
            //Recipients
            
            $mail_address = $this->order[0]->clientsmail;
        
            $mail->setFrom('nafisa@ufa-lanka.com', 'fooddelivery');
            $mail->addAddress($mail_address);     //Add a recipient
             
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Подтверждение заказа fooddelivery';

            $body="Confirm order #";
            foreach($this->order as $order) {
                $body .= $order->orderid.' <br />';
                $body .= "Client: " .$order->clientsname.' <br />';
                $body .= "Phone number: " .$order->clientsphonenumber.' <br />';
                $body .= "Delivery address: " .$order->clientsaddress.' <br />';
                $body .= "Date: " .$order->orderdate.' <br />';
                $body .= "Total cost: " .$order->totalcost.' <br />';
            }
            $body .= 'Order list'.' <br />';

            foreach($this->orderlist as $orderlist) {
                $body .=  'Dish: '.$orderlist->dishname.'; quantity: '.$orderlist->quantity.'; price: '.$orderlist->price.'; <br />';
            }

            $mail->Body    = $body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Confirmation has been sent';
        
            //$log->debug('Message has been sent. Order confirmation ', ['mail' => $mail_address]);
        } catch (Exception $e) {
            echo "Message could not be sent. Order confirmation. Mailer Error: {$mail->ErrorInfo}";
        
                    //$log = new Logger('send_mail.php');
                    // $log->pushHandler(new StreamHandler(__DIR__ .'/logs/debug/log', Logger::DEBUG));
                    
                  //  $log->error('Message could not be sent.Order confirmation. Mailer Error: ', ['message' => $mail->ErrorInfo]);
        }
        } else {
            echo 'Клиент не указал свой e-mail. Подтверждение НЕ отправлено!';
        }
    }

    public function mailOrder() // sending mail to owner about confirmed order
    {
        $mail = new PHPMailer(true);

        $host = $_ENV['MAIL_HOST'];
        $username = $_ENV['MAIL_USERNAME'];
        $pass = $_ENV['MAIL_PASSWORD'];

        try {
            //Server settings
            $mail->CharSet = "utf-8";
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $host;                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = $username;                     //SMTP username
            $mail->Password   = $pass;                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
        
            //Recipients
            
            $mail_address = 'nafisa@ufa-lanka.com';
        
            $mail->setFrom('nafisa@ufa-lanka.com', 'fooddelivery');
            $mail->addAddress($mail_address);     //Add a recipient
             
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Подтверждение заказа fooddelivery';

            $body="Accepted order on the website #";
            foreach($this->order as $order) {
                $body .= $order->orderid.' <br />';
                $body .= "Client: " .$order->clientsname.' <br />';
                $body .= "Phone number: " .$order->clientsphonenumber.' <br />';
                $body .= "Delivery address: " .$order->clientsaddress.' <br />';
                $body .= "Date: " .$order->orderdate.' <br />';
                $body .= "Total cost: " .$order->totalcost.' <br />';
            }
            $body .= 'Order list'.' <br />';

            foreach($this->orderlist as $orderlist) {
                $body .=  'Dish: '.$orderlist->dishname.'; quantity: '.$orderlist->quantity.'; price: '.$orderlist->price.'; <br />';
            }

            $mail->Body    = $body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Order has been sent to the owner';
        
           // $log->debug('Message has been sent. Order confirmation ', ['mail' => $mail_address]);
        } catch (Exception $e) {
            echo "Message could not be sent. Order to the owner. Mailer Error: {$mail->ErrorInfo}";
        
                    //$log = new Logger('send_mail.php');
                    // $log->pushHandler(new StreamHandler(__DIR__ .'/logs/debug/log', Logger::DEBUG));
                    
                  //  $log->error('Message could not be sent.Order confirmation. Mailer Error: ', ['message' => $mail->ErrorInfo]);
        
         }
    }

}