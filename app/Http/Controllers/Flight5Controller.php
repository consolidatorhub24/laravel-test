<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Amadeus\Client;
use Amadeus\Client\Params;
use Amadeus\Client\RequestOptions\AirFlightInfoOptions;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\Passenger;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\PricingOptions;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\Segment;
use Amadeus\Client\Result;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Amadeus\Client\RequestOptions\PnrRetrieveOptions;
use Amadeus\Client\RequestOptions\FareMasterPricerCalendarOptions;
use Amadeus\Client\RequestOptions\FareMasterPricerTbSearch;
use Amadeus\Client\RequestOptions\Fare\MPPassenger;
use Amadeus\Client\RequestOptions\Fare\MPItinerary;
use Amadeus\Client\RequestOptions\Fare\MPDate;
use Amadeus\Client\RequestOptions\Fare\MPLocation;
use Amadeus\Client\RequestOptions\FareInformativePricingWithoutPnrOptions;
use Amadeus\Client\RequestOptions\MiniRuleGetFromRecOptions;
use Amadeus\Client\RequestOptions\MiniRule\Pricing as MiniRulePricing;
use Amadeus\Client\Struct\Ticket\CancelDocument;

class FlightController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = amadeus_client();
    }


    public function tryAmadeus()
    {

        // Test Case One 
        //     //==============================================FLIGHT SEARCH================================================
        // $opt = new FareMasterPricerTbSearch([
        //     'nrOfRequestedResults' => 250,
        //     'nrOfRequestedPassengers' => 3,
        //     'passengers' => [
        //         new MPPassenger([
        //             'type' => MPPassenger::TYPE_ADULT,
        //             'count' => 2
        //         ]),
        //         new MPPassenger([
        //             'type' => MPPassenger::TYPE_CHILD,
        //             'count' => 1
        //         ]),
        //         new MPPassenger([
        //             'type' => MPPassenger::TYPE_INFANT,
        //             'count' => 1
        //         ]),
        //     ],
        //     'itinerary' => [
        //         new MPItinerary([
        //             'departureLocation' => new MPLocation(['city' => 'NBO']),
        //             'arrivalLocation' => new MPLocation(['city' => 'MBA']),
        //             'date' => new MPDate([
        //                 'dateTime' => new \DateTime('2024-07-29T00:00:00+0000', new \DateTimeZone('UTC'))
        //             ])
        //         ]),
        //         new MPItinerary([
        //             'departureLocation' => new MPLocation(['city' => 'MBA']),
        //             'arrivalLocation' => new MPLocation(['city' => 'NBO']),
        //             'date' => new MPDate([
        //                 'dateTime' => new \DateTime('2024-07-31T00:00:00+0000', new \DateTimeZone('UTC'))
        //             ])
        //         ])

        //     ],
        //     'flightOptions' => [
        //         FareMasterPricerTbSearch::FLIGHTOPT_PUBLISHED,
        //         FareMasterPricerTbSearch::FLIGHTOPT_UNIFARES,
        //         FareMasterPricerTbSearch::FLIGHTOPT_ELECTRONIC_TICKET,
        //         FareMasterPricerTbSearch::FLIGHTOPT_TICKET_AVAILABILITY_CHECK,
        //     ],
        //     'cabinClass' => FareMasterPricerTbSearch::CABIN_ECONOMY,
        // ]);
        // $recommendations =  $this->client->fareMasterPricerTravelBoardSearch($opt);
        // if ($recommendations) {
        //     $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $recommendations->responseXml);
        //     $response  = simplexml_load_string($response);
        //     $dataArray = json_decode(json_encode((array)$response), TRUE);
        //     dd(($dataArray['recommendation'][1]));
        //     /* dd(json_decode(json_encode($recommendations->responseXml), true)); */
        //     // dd($client->getLastRequest());
        // } else {
        //     echo "Error retrieving";
        // }

        $this->client->setStateful(true);
        $informativePricingResponse = $this->client->fareInformativePricingWithoutPnr(
            new FareInformativePricingWithoutPnrOptions([
                'passengers' => [
                    new Passenger([
                        'tattoos' => [1],
                        'type' => Passenger::TYPE_ADULT
                    ]),
                    new Passenger([
                        'tattoos' => [3],
                        'type' => Passenger::TYPE_CHILD
                    ]),
                    new Passenger([
                        'tattoos' => [1],
                        'type' => Passenger::TYPE_INFANT
                    ]),
                ],

                'segments' => [
                    new Segment([
                        'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-07-29 10:00:00'),
                        'from' => 'WIL',
                        'to' => 'MBA',
                        'marketingCompany' => 'H1',
                        'flightNumber' => '6006',
                        'bookingClass' => 'O',
                        'segmentTattoo' => 1,
                        'groupNumber' => 1
                    ]),
                    new Segment([
                        'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-07-31 13:30:00'),
                        'from' => 'MBA',
                        'to' => 'WIL',
                        'marketingCompany' => 'H1',
                        'flightNumber' => '6007',
                        'bookingClass' => 'O',
                        'segmentTattoo' => 2,
                        'groupNumber' => 2
                    ])
                ],
                'pricingOptions' => new PricingOptions([
                    'overrideOptions' => [
                        PricingOptions::OVERRIDE_FARETYPE_PUB,
                        PricingOptions::OVERRIDE_FARETYPE_UNI,
                    ],
                    'validatingCarrier' => 'H1',
                ]),
            ])
        );


        if ($informativePricingResponse) {
            // $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $informativePricingResponse->responseXml);
            // $response  = simplexml_load_string($response);
            // $dataArray = json_decode(json_encode((array)$response), TRUE);
            // dd(json_decode(json_encode($informativePricingResponse->responseXml), true));
            // dd(($dataArray));
            // dd($client->getLastRequest());
        } else {
            echo "Error retrieving";
        }

        // //===================================MINIRULE_GETFROMREC===============================
        $miniRulesResponse = $this->client->miniRuleGetFromRec(
            new MiniRuleGetFromRecOptions([
                'pricings' => [
                    new MiniRulePricing([
                        'id' => MiniRulePricing::ALL_PRICINGS,
                        'type' => MiniRulePricing::TYPE_FARE_RECOMMENDATION_NUMBER
                    ])
                ]
            ]),
        );
        if ($this->client) {
            $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $miniRulesResponse->responseXml);
            $response  = simplexml_load_string($response);
            $dataArray = json_decode(json_encode((array)$response), TRUE);
            // dd(json_decode(json_encode($miniRulesResponse->responseXml), true));
            //dd($client->getLastRequest());
            //dd($client->getLastResponse());
            //dd(($dataArray['recommendation'][1]));
        } else {
            echo "Error retrieving";
        }


        $this->client->securitySignOut();
    }
}