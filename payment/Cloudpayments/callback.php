<?php
chdir ('../../');
require_once('api/Okay.php');
$okay = new Okay();
//получаем из запроса необходимые параметры
$action         = $okay->request->get('action');
$OperationType  = $okay->request->post('OperationType');
$order_id   	= $okay->request->post('InvoiceId');
$amount		    = $okay->request->post('Amount');
$currency		= $okay->request->post('Currency');
        if ($currency==USD)     {$currency=1;}
		elseif ($currency==RUB) {$currency=2;} 
		elseif ($currency==UAH) {$currency=4;};
//параметры заказа
$order            = $okay->orders->get_order((int)$order_id);
$payment_method   = $okay->payment->get_payment_method($order->payment_method_id);
$payment_currency = $payment_method->currency_id;
$settings         = $okay->payment->get_payment_settings($payment_method->id);
//API из настроек
$API=$settings['API'];
//получаем контрольную подпись
$hash   = $_POST;
$sign = hash_hmac('sha256', $hash, $API, true);
$sign = base64_encode($sign);
$signs = $_SERVER['HTTPS_CONTENT_HMAC'];

if($OperationType==Payment)
{   //проверяем наличие заказа
    if(empty($order))
    {
        response(10);
    }  
    //проверяем метод оплаты
    elseif(empty($payment_method))
    {
        response(13);
    }
    //если заказ оплачен - ошибка
    elseif($order->paid)
    {
        response(13);
    }
    //проверяем сумму заказа
    elseif($amount != round($okay->money->convert($order->total_price, $method->currency_id, false), 2) || $amount<=0)
    {
        response(11);
    }
    //проверяем соответствие валюты
    elseif($currency!= $payment_currency)
    {
        response(13);
    }
    //проверяем контрольную подпись
    elseif ($signs!= $$sign)
    {
        response(13);
    }
    else 
    {
        if($action==pay)
        {   //устанавливаем статус оплачен
            $okay->orders->update_order(intval($order->id), array('paid'=>1));
            //закрываем заказ списываем товар со склада
            $okay->orders->close(intval($order->id));
            //отправляем уведомление покупателю и продавцу
            $okay->notify->email_order_user(intval($order->id));
            $okay->notify->email_order_admin(intval($order->id));
        }
    response(0);
    };
};
if($OperationType==Refund)
{   //проверяем контрольную подпись
    if ($signs!= $sign)
    {
        response(13);
    }
    //проверяем наличие заказа
    elseif (empty($order))
    {
        response(10);
    }  
    else 
    {   //заказ переводим в статус неоплачен пишем заметку в заказ    
        $okay->orders->update_order(intval($order->id), array('paid'=>0, 'note' => 'Совершён полный возврат денежных средств.'));
        response(0);
    }    
};
//ответ на запрос
function response($code)
{
    header('Content-Type:application/json');
    echo json_encode(array('code'=>$code));
}