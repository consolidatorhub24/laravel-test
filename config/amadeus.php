<?php

// return [
//     'auth' => [
//         'officeId' => env('AMADEUS_OFFICE_ID', 'NBO4128ZT'),
//         'userId' => env('AMADEUS_USER_ID', 'WS7HZCLI'),
//         'password' => env('AMADEUS_PASSWORD', 'oS744Xn58'),
//         'dutyCode' => env('AMADEUS_DUTY_CODE', 'SU'),
//         'organizationId' => env('AMADEUS_ORGANIZATION_ID', 'NMC-EASTAF'),
//     ],
//     'session' => [
//         'soapHeaderVersion' => env('AMADEUS_SOAP_HEADER_VERSION', \Amadeus\Client::HEADER_V4),
//         'wsdl' => env('AMADEUS_WSDL', 'wsdl/1ASIWCLI7HZ_PDT_20220204_091336.wsdl'),
//         'stateful' => env('AMADEUS_STATEFUL', false),
//     ],
//     'request' => [
//         'receivedFrom' => env('AMADEUS_RECEIVED_FROM', 'my test project'),
//     ],
//     'log' => [
//         'path' => env('AMADEUS_LOG_PATH', storage_path('logs/amadeus.log')),
//     ],
// ];

return [
    'auth' => [
        'officeId' => env('AMADEUS_OFFICE_ID', 'NBO4128ZQ'),
        'userId' => env('AMADEUS_USER_ID', 'WSAFKAFR'),
        'password' => env('AMADEUS_PASSWORD', '5FUaI5u%%5in'),
        'dutyCode' => env('AMADEUS_DUTY_CODE', 'SU'),
        'organizationId' => env('AMADEUS_ORGANIZATION_ID', 'NMC-EASTAF'),
    ],
    'session' => [
        'soapHeaderVersion' => env('AMADEUS_SOAP_HEADER_VERSION', \Amadeus\Client::HEADER_V4),
        'wsdl' => env('AMADEUS_WSDL', 'new_wsdl/1ASIWAFRAFK_PDT_20240823_093832.wsdl'),
        'stateful' => env('AMADEUS_STATEFUL', false),
    ],
    'request' => [
        'receivedFrom' => env('AMADEUS_RECEIVED_FROM', 'AFROATLAS'),
    ],
    'log' => [
        'path' => env('AMADEUS_LOG_PATH', storage_path('logs/amadeus.log')),
    ],
];
