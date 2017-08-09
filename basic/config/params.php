<?php

return [
    'adminEmail' => 'admin@example.com',
    //设置默认每页显示行数
    'pageSize' => [
        'manage' => 4,
        'user'   => 1,
        'product' => 10,//商品分页数
        'frontproduct' => 9,
        'order' => 10,
    ],
    //默认头像
    'defaultValue' => [
        'avatar' => 'assets/admin/img/contact-img.png',
    ],
    //快递方式
    'express' => [
        1 => '中通快递',
        2 => '顺丰快递',
    ],
    //快递价格
    'expressPrice' => [
        1 => 15,
        2 => 20,
    ],
];
