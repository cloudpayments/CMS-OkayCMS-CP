<?php

require_once('api/Okay.php');

class Cloudpayments extends Okay
{	
   	public function checkout_form($order_id, $button_text = null)
	{
		if(empty($button_text))
			$button_text = 'Перейти к оплате';
			
		//параметры заказа	
		$order            = $this->orders->get_order((int)$order_id);
		$payment_method   = $this->payment->get_payment_method($order->payment_method_id);
		$payment_currency = $payment_method->currency_id;
		//получаем валюту
		if ($payment_currency==1) $currency='USD'; 
		if ($payment_currency==2) $currency='RUB'; 
		if ($payment_currency==4) $currency='UAH'; 
		
		//получаем id плательщика
		if ($order-> user_id!=0)
		$accountId = $order-> user_id;
		
		//получаем сумму заказа
		$amount = round($this->money->convert($order->total_price, $payment_method->currency_id, false), 2);	
        
        //получаем настройки метода оплаты
		$settings = $this->payment->get_payment_settings($payment_method->id);
		
		//получаем язык заказа переводим в язык виджета
		if ($settings['language']  == 'lans')
		{   $lang_id = $this->languages->lang_id();
		        if ($lang_id==1) $language='ru-RU';
		        if ($lang_id==2) $language='en-US';
        		if ($lang_id==3) $language='uk';}
		else 
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
            $purchases = $this->orders->get_purchases();

            foreach ($purchases as $purchase)
            {      
                if ($purchase->order_id == $order_id)
                    {   $price  = $this->money->convert($purchase->price, $currency_id, false);
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
            };        
            unset($p);
            $delivery_price = $order->delivery_price;
                if ($delivery_price>0)
                {   $delivery_method = $this->delivery->get_delivery($order->delivery_id);
                    $delivery_price = $this->money->convert($delivery_price, $currency_id, false);
                    $array =array(
                    'label'           =>trim($delivery_method->name),
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
                'Items'         =>$Items,
                'taxationSystem'=>intval($settings['taxationSystem']),
	            'email'         =>$order-> email,
	            'phone'         =>$order-> phone,
	            'amounts'       =>array (
	                'electronic'     => number_format($amount, 2, '.', ''),
		            'advancePayment' => 0,
		            'credit'         => 0,
		            'provision'      => 0,
	                )
	            ,);
            $data = array('cloudPayments' => array(
                        'customerReceipt'=>$receipt
                    )
                );

        };
        
        $result_url         = $this->config->root_url.'/order/'.$order->url;
		$server_url         = $this->config->root_url.'/payment/Cloudpayments/callback.php';

        //сохраняем полученные результаты
		$res['publicId']    = $settings['publicId'];
        $res['API']         = $settings['API'];
        $res['amount']      = $amount;
        $res['currency']    = $currency;
        $res['description'] = $description;
        $res['order_id']    = $order_id;
        $res['language']    = $language;
        $res['accountId']   = $accountId;
        $res['email']       = $email;
        $res['data']        = json_encode($data,JSON_UNESCAPED_UNICODE);
        $res['result_url']  = $result_url;
        $res['server_url']  = $server_url;
		return $res;
	}
}
