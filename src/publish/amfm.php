<?php
return[

    'prefix' => 'amfm',
    'path' => 'files',
    'paging' => 24,
    'guard' => 'admin', //default web

    'file_types' => [
        'image' => [
            'image/jpeg',
            'image/png'
        ],
        'text' => []
    ]

];