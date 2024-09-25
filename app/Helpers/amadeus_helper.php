<?php

use Amadeus\Client;
use Amadeus\Client\Params;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

if (!function_exists('amadeus_client')) {
    function amadeus_client()
    {
        $msgLog = new Logger('RequestResponseLogs');
        $msgLog->pushHandler(new StreamHandler(config('amadeus.log.path'), Logger::INFO));

        $params = new Params([
            'authParams' => [
                'officeId' => config('amadeus.auth.officeId'),
                'userId' => config('amadeus.auth.userId'),
                'passwordData' => base64_encode(config('amadeus.auth.password')),
                'passwordLength' => strlen(base64_encode(config('amadeus.auth.password'))),
                'dutyCode' => config('amadeus.auth.dutyCode'),
            ],
            'sessionHandlerParams' => [
                'soapHeaderVersion' => config('amadeus.session.soapHeaderVersion'),
                'wsdl' => config('amadeus.session.wsdl'),
                'stateful' => config('amadeus.session.stateful'),
                'logger' => $msgLog,
            ],
            'requestCreatorParams' => [
                'receivedFrom' => config('amadeus.request.receivedFrom')
            ]
        ]);

        return new Client($params);
    }
}