<?php

namespace Okay\Modules\OkayCMS\Cloudpayments;

use Okay\Core\EntityFactory;
use Okay\Core\Modules\AbstractModule;
use Okay\Core\Modules\Interfaces\PaymentFormInterface;
use Okay\Core\Money;
use Okay\Core\Router;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;
use Okay\Entities\PurchasesEntity;

class PaymentForm extends AbstractModule implements PaymentFormInterface
{	
    private $entityFactory;
    private $money;
    
    public function __construct(EntityFactory $entityFactory, Money $money)
    {
        parent::__construct();
        $this->entityFactory = $entityFactory;
        $this->money = $money;
    }

    /**
     * @inheritDoc
     */
    public function checkoutForm($orderId)
	{
		/** @var OrdersEntity $ordersEntity */
	    $ordersEntity = $this->entityFactory->get(OrdersEntity::class);
	    
		/** @var PaymentsEntity $paymentsEntity */
	    $paymentsEntity = $this->entityFactory->get(PaymentsEntity::class);
	    
		$order = $ordersEntity->get((int)$orderId);
		
		$paymentMethod = $paymentsEntity->get($order->payment_method_id);
		
		$paymentCurrency = $paymentMethod->currency_id;
		//получаем валюту
		if ( $paymentCurrency == 1 ) {
		    $currency='USD'; 
		}
		elseif ( $paymentCurrency == 2) {
		    $currency='RUB'; 
		}
		elseif ( $paymentCurrency == 4 ) {
		    $currency='UAH'; 
		};
		
		//получаем id плательщика
		if ($order-> user_id !=0 ) {
		    $accountId = $order-> user_id;
		}
		else {$accountId = $order->email;};
		
		$settings = $paymentsEntity->getPaymentSettings($paymentMethod->id);
		
		$amount = round($this->money->convert($order->total_price, $paymentMethod->currency_id, false), 2);	
		
		$language = $settings['language'];
		//получаем назначение заказа на языке виджета
		if ($language=='ru-RU')
		    $description = 'Оплата заказа №'.$order->id;
		    
		if ($language=='kk')
		    $description = 'Оплата заказа №'.$order->id;
		    
		if ($language=='en-US')
		    $description = 'Order payment №'.$order->id;
		    
		if ($language=='uk')
		    $description = 'Оплата замовлення №'.$order->id;
		    
		if ($language=='lv')
		    $description = 'Pasūtījuma apmaksa №'.$order->id;
		    
		if ($language=='az')
		    $description = 'Ödəniş əməliyyatı №'.$order->id;
		    
		if ($language=='kk-KZ')
		    $description = 'Тапсырысты төлеу №'.$order->id;
		    
		if ($language=='pl')
		    $description = 'Płatności za zamówienia №'.$order->id;
		    
		if ($language=='pt')
		    $description = 'O pagamento do pedido №'.$order->id;
		    
		    
			//формируем чек
        if ($settings['kassa'] == '1')
        {	
            /** @var PurchasesEntity $purchasesEntity */
            $purchasesEntity = $this->entityFactory->get(PurchasesEntity::class);
            $purchases = $purchasesEntity->find(['order_id' => (int)$orderId]);
            foreach ($purchases as $purchase)
            {      
                        $price  = $this->money->convert($purchase->price, $currency_id, false);
                        $Items[] = array(
                        'label'           =>trim($purchase->product_name),
                        'price'           =>number_format($price, 2, '.', ''),
                        'quantity'        =>number_format($purchase-> amount, 2, '.', ''),
                        'amount'          =>number_format($purchase->amount*$price, 2, '.', ''),
                        'vat'             =>intval($settings['vat']),
                        'method'          =>0,
    	                'object'          =>0,
                        'measurementUnit' =>$purchase->units,
                        );
            };        
     
            $delivery_price = $order->delivery_price;
            if ($delivery_price>0)
            {   
                $delivery_price = $this->money->convert($delivery_price, $currency_id, false);
                $array =array(
                    'label'           =>'Доставка',
                    'price'           =>number_format($delivery_price, 2, '.', ''),
                    'quantity'        =>1,
                    'amount'          =>number_format($delivery_price, 2, '.', ''),
                    'vat'             =>intval($settings['vatd']),
                    'method'          =>0,
    	            'object'          =>0,
                    'measurementUnit' =>'',
                );
                $Items[] = $array;
            };
            $receipt = array(
                'Items'           =>$Items,
                'taxationSystem'  =>intval($settings['taxationSystem']),
                'calculationPlace'=>'www.'.$_SERVER['SERVER_NAME'],
	            'email'           =>$order-> email,
	            'phone'           =>$order-> phone,
	            'amounts'         =>array (
	                'electronic'     => number_format($amount, 2, '.', ''),
	                )
	            ,);
            $data = array('cloudPayments' => array(
                        'customerReceipt'=>$receipt
                    )
                );
       };   
        $resultUrl = Router::generateUrl('order', ['url' => $order->url], true);
        //$resultUrl = Router::generateUrl();
        
        $this->design->assign('language', $language);
        $this->design->assign('currency', $currency);
        $this->design->assign('payment_scheme', $settings['payment_scheme']);
        $this->design->assign('public_id', $settings['publicId']);
        $this->design->assign('description', $description);
        $this->design->assign('amount', $amount);
        $this->design->assign('invoiceId', $order->id);
        $this->design->assign('accountId', $accountId);
        $this->design->assign('skin', $settings['skin']);
        $this->design->assign('data', json_encode($data,JSON_UNESCAPED_UNICODE));
        $this->design->assign('result_url', $resultUrl);

        return $this->design->fetch('form.tpl');
	}
}