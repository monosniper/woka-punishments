<?php

return [
    "database" => [
        "host" => "193.164.17.17",
        "db_name" => "wokasmilebans",
        "username" => "asusbans",
        "password" => "oS5sE1tV2frF1tA2fI8dzB3lB3aM9e",

        "bans_table" => "litebans_bans",
        "mutes_table" => "litebans_mutes",
        "kicks_table" => "litebans_kicks",
        "history_table" => "litebans_history",
    ],
    'server_ip' => 'MC.WOKA.FUN', // IP сервера
    'logo_url' => 'https://storage.easyx.ru/images/easydonate/logos/Gzn6m05rE20HGE3Qnumydv2Z02vcZvFR.png', // Ссылка на лого
    'max_items_per_page' => 7, // Макс. число строк на странице
    'theme' => [
        'fallback_color' => '#414345', // Цвет в браузерах, не поддерживающих градиент
        'left_color' => '#232526', // Цвет градиента слева
        'right_color' => '#414345', // Цвет градиента справа
        'tag_color' => [
            'background' => '#ffffff29', // Цвет фона тега
            'color' => '#fff', // Цвет обводки и текста тега
        ],
        'modal' => [
            'background' => '#232526',
            'border' => [
                'color' => '#fff',
                'width' => '1px',
            ]
        ]
    ]
];