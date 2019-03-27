<?php
return[

    'prefix' => 'amfm',
    'path' => 'public',
    'disk' => 'public',
    'paging' => 24,
    'middleware' => ['web'],
    'guard' => 'admin', //default web

    'file_types' => [
        'image' => [
            'image/jpeg',
            'image/png'
        ],
        'text' => []
    ]

];