<script src="https://widget.cloudpayments.ru/bundles/cloudpayments?cms=OkayCms"></script>
<script type="text/javascript">
    cloudpaymentsPay = function () {
        var widget = new cp.CloudPayments({
            language: '{$language|escape}'
        });
        widget.{$payment_scheme|escape}({ // options
            publicId: '{$public_id|escape}',  //id из личного кабинета
            description: '{$description|escape}', //назначение
            amount: {$amount|escape}, //сумма
            currency: '{$currency|escape}', //валюта
            invoiceId: '{$invoiceId|escape}', //номер заказа  (необязательно)
            accountId: '{$accountId|escape}', //идентификатор плательщика (необязательно)
            skin: '{$skin|escape}', //дизайн виджета
            data: {$data}
        },
        '{$result_url}',//действие при успешной оплате
        '{$result_url}'//действие при неуспешной оплате
        );
    };    
</script>
<input type="button" class="button" value="Оплатить" onclick="cloudpaymentsPay()">