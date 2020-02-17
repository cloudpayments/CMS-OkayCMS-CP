<?php
namespace Okay\Modules\OkayCMS\Cloudpayments\Controllers;

use Okay\Controllers\AbstractController;
use Okay\Core\Money;
use Okay\Core\Notify;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;

class CallbackController extends AbstractController
{
    public function payOrder(
        OrdersEntity $ordersEntity,
        PaymentsEntity $paymentsEntity,
        CurrenciesEntity $currenciesEntity,
        Money $money,
        Notify $notify
    ) {
        $this->response->setContentType(RESPONSE_TEXT);
        
        $action        = $this->request->get('action');
        $transactionId = $this->request->post('TransactionId');
        $amount        = $this->request->post('Amount');
        $currency      = $this->request->post('Currency');
        $status        = $this->request->post('Status');
        $orderId       = $this->request->post('InvoiceId');
        
        $order = $ordersEntity->get(intval($orderId));
        if(empty($order))
        {
            $this->response->setContent(json_encode(array('code'=>10)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        };
        
        if ($currency=='USD')     {$currency=1;}
		elseif ($currency=='RUB') {$currency=2;} 
		elseif ($currency=='UAH') {$currency=4;};
		$paymentMethod = $paymentsEntity->get($order->payment_method_id);
		if(empty($paymentMethod))
        {
            $this->response->setContent(json_encode(array('code'=>13)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        }  
        $order_currency = $paymentMethod->currency_id;
        if ($amount != round($money->convert($order->total_price, $paymentMethod->currency_id, false), 2) || $amount<=0) {
            $this->response->setContent(json_encode(array('code'=>12)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        };
        $settings = $paymentsEntity->getPaymentSettings($paymentMethod->id);
        $hash   = file_get_contents('php://input');
        $sign   = hash_hmac('sha256', $hash, $settings['API'], true);
        $sign   = base64_encode($sign);
        $signs  = isset($_SERVER['HTTP_CONTENT_HMAC']) ? $_SERVER['HTTP_CONTENT_HMAC'] : '';
        
        //проверяем контрольную подпись
        if ($signs!= $sign) {
            $this->response->setContent(json_encode(array('code'=>13)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        };
        
        if ($action == 'check' && $status == 'Authorized') {
            $note = $order->note;
            if (!empty($note)){
                $this->response->setContent(json_encode(array('code'=>13)))->setStatusCode(200);
                $this->response->sendContent();
                exit;
            }
        };
        
        if ($action == 'pay' && $status == 'Authorized') {
            $note = $order->note;
            $ordersEntity->update(intval($order->id), ['note' => $note.' Платеж авторизован. Транзакция № '.$transactionId.'.']);
            $this->response->setContent(json_encode(array('code'=>0)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        };
        
        if ($action == 'refund') {
            $ordersEntity->update(intval($order->id), ['paid'=>0]);
            $note = $order->note;
            $ordersEntity->update(intval($order->id), ['note' => $note.' Совершён полный возврат денежных средств. Транзакция № '.$transactionId.'.']);
            $this->response->setContent(json_encode(array('code'=>0)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        };
        
        if ($action == 'cancel') {
            $note = $order->note;
            $ordersEntity->update(intval($order->id), ['note' => $note.' Платеж отменен. Транзакция № '.$transactionId.'.']);
            $this->response->setContent(json_encode(array('code'=>0)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        };
        
        if($order->paid)
        {
            $this->response->setContent(json_encode(array('code'=>13)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        };  
        
        if ($currency != $order_currency) {
            $this->response->setContent(json_encode(array('code'=>13)))->setStatusCode(200);
            $this->response->sendContent();
            exit;
        };
        
        if (($action == 'pay' && $status == 'Completed') || $action == 'confirm' ) {
             // Установим статус оплачен
            $ordersEntity->update(intval($order->id), ['paid'=>1]);

            // Отправим уведомление на email
            $notify->emailOrderUser(intval($order->id));
            $notify->emailOrderAdmin(intval($order->id));

            // Спишем товары  
            $ordersEntity->close(intval($order->id));
        };
        
        $this->response->setContent(json_encode(array('code'=>0)))->setStatusCode(200);
        $this->response->sendContent();
        exit;
    }
}
