<?php

return [

    'characters' => [
        '2', '3', '4', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'm', 'n', 'p', 'q', 'r', 't', 'u', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'X', 'Y', 'Z'
    ],

    'default'   => [
        'length'    => 6,
        'width'     => 160,
        'height'    => 52,
        'quality'   => 100,
        'math'      => false,
        'bgColor'   => '#ecf2f4',
        'fontColors' => env('CAPTCHA_COLOR_ONE') == 'true' ? ['#000000'] : ['#2c3e50', '#c0392b', '#16a085', '#8e44ad', '#303f9f', '#f57c00', '#795548', '#003399', '#990000', '#003300', '#808000', '#800080', '#330000', '#4000ff', '#0000ff', '#ff4000'],
    ],

    'math' => [
        'length' => 9,
        'width' => 120,
        'height' => 36,
        'quality' => 90,
        'math' => true,
        'bgColor'   => '#ecf2f4',
        'fontColors' => env('CAPTCHA_COLOR_ONE') == 'true' ? ['#000000'] : [
            '#2c3e50', '#c0392b', '#16a085', '#8e44ad', '#303f9f', '#f57c00', '#795548', '#003399',
            '#990000', '#003300', '#808000', '#800080', '#330000', '#4000ff', '#0000ff', '#ff4000'
        ],
    ],

    'flat'   => [
        'length'    => 6,
        'width'     => 160,
        'height'    => 46,
        'quality'   => 100,
        'lines'     => 1,
        'bgImage'   => true,
        'bgColor'   => '#ecf2f4',
        'fontColors' => env('CAPTCHA_COLOR_ONE') == 'true' ? ['#000000'] : [
            '#2c3e50', '#c0392b', '#16a085', '#8e44ad', '#303f9f', '#f57c00', '#795548', '#003399',
            '#990000', '#003300', '#808000', '#800080', '#330000', '#4000ff', '#0000ff', '#ff4000'
        ],
        'contrast'  => -6,
    ],

    'mini'   => [
        'length'    => 6,
        'width'     => 60,
        'height'    => 32,
        'bgColor'   => '#ecf2f4',
        'fontColors' => env('CAPTCHA_COLOR_ONE') == 'true' ? ['#000000'] : [
            '#2c3e50', '#c0392b', '#16a085', '#8e44ad', '#303f9f', '#f57c00', '#795548', '#003399',
            '#990000', '#003300', '#808000', '#800080', '#330000', '#4000ff', '#0000ff', '#ff4000'
        ],
    ],

    'inverse'   => [
        'length'    => 6,
        'width'     => 160,
        'height'    => 52,
        'quality'   => 90,
        'sensitive' => true,
        'angle'     => 12,
        'sharpen'   => 10,
        'blur'      => 2,
        'invert'    => false,
        'contrast'  => -6,
    ]

];
