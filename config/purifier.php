<?php

return [
    'allowed_script_sources' => [
        'cdnjs.cloudflare.com',
        'www.googletagmanager.com',
        'ajax.googleapis.com',
    ],
    'allowed_css_sources' => [
        'cdnjs.cloudflare.com',
        'fonts.googleapis.com',
        'stackpath.bootstrapcdn.com'
    ],
    'allowed' => 'script[src], div, span, b, strong, i, em, p, br, ul, ol, li, a[href], img[src|alt], h1, h2, h3, h4, h5, h6, pre, code'
];
