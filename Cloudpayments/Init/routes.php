<?php

namespace Okay\Modules\OkayCMS\Cloudpayments;

return [
    'OkayCMS_Cloudpayments_callback' => [
        'slug' => 'payment/OkayCMS/Cloudpayments/callback',
        'params' => [
            'controller' => __NAMESPACE__ . '\Controllers\CallbackController',
            'method' => 'payOrder',
        ],
    ],
];