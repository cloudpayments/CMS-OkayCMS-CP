# Платежный модуль CloudPayments для OKAY CMS
Модуль позволит добавить на ваш сайт оплату банковскими картами через платежный сервис CloudPayments. 
Для корректной работы модуля необходима регистрация в сервисе.

Порядок регистрации описан в [документации CloudPayments](https://cloudpayments.ru/Docs/Connect)

### Совместимость:
* OKAY v.2.2.2 и выше;


### Возможности:  
• Одностадийная схема оплаты;  
• Выбор локализации платежного виджета;  
• Выбор валюты;  
• Поддержка онлайн-касс (ФЗ-54);  
• Отправка чеков по email;  
• Отправка чеков по SMS;  
• Выбор системы налогообложения;  
• Выбор НДС для товаров и НДС для доставки отдельно;  
 
### Установка

## Техническая настройка
### Личный кабинет CloudPayments
В личном кабинете CloudPayments в настройках сайта необходимо включить следующие уведомления:

* **Запрос на проверку платежа** (Сheck):\
http(s)://domain.ru/payment/CloudPayments/callback.php?action=check
* **Уведомление о принятом платеже** (Pay):\
http(s)://domain.ru/payment/CloudPayments/callback.php?action=pay
* **Уведомление о возврате платежа** (Refund):\
http(s)://domain.ru/payment/CloudPayments/callback.php?action=refund

где domain.ru — доменное имя вашего сайта. Во всех случаях требуется выбирать вариант по умолчанию: кодировка — UTF-8, HTTP-метод — POST, формат — CloudPayments

### Установка модуля
Для установки модуля необходимо поместить содержимое каталогов design и payment из архива в соответствующие каталоги на сервере
  
### Панель администратора OKAY CMS
В настройках платежей (Настройки сайта -> Способы оплаты) необходимо добавить новый способ оплаты выбрав тип CloudPayments и указать следующие настройки:
* **Идентификатор сайта** — Public id сайта из личного кабинета CloudPayments
* **Секретный ключ** — API Secret из личного кабинета CloudPayments
* **Локализация виджета** — Язык виджета. Если не указан, то значение определяется на основе языка сайта
В настройках валюты (Настройки сайта -> Валюты) у всех используемых валют необходимо включить функцию отображение копеек

#### При использовании интеграции с онлайн-кассой
* **Онлайн-касса** — Включение/отключение формирования онлайн-чека при оплате
* **Система налогообложения** — Тип системы налогообложения. Возможные значения перечислены в документации CloudPayments https://cloudpayments.ru/Docs/Directory#taxation-system
* **Ставка НДС** — Указание ставки НДС. Все возможные значения указаны в документации https://cloudpayments.ru/Docs/Kassa#data-format
* **Ставка НДС для доставки** — Указание ставки НДС для доставки. Аналогично ставке НДС.

После указания всех данных сохранить настройки.

![Настройки CloudPayments в OKAY CMS](doc/img/OKAY_CMS.png)
