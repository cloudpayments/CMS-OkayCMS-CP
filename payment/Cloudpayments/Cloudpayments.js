var payHandler = function () 
{
    //чек
    var data = $('#data').val();
    data = JSON.parse(data);
    //виджет
    var widget = new cp.CloudPayments
    (
        {   //локализация виджета
            language: $('#language').val()
        }
    );
        widget.charge
        (
            { // параметры виджета
                publicId:    $('#publicId').val(),//id из личного кабинета
                description: $('#description').val(),//назначение
                amount:      parseFloat($('#amount').val()), //сумма
                currency:    $('#currency').val(), //валюта
                invoiceId:   $('#order_id').val(), //номер заказа  (необязательно)
                accountId:   $('#accountId').val(), //идентификатор плательщика (необязательно)
                email:       $('#email').val(), //почта (необязательно)
                data:        data//чек
            },
           '{}',//действие при успешной оплате
           '{}'//действие при неуспешной оплате
        );
};