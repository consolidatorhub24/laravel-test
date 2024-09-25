<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Amadeus\Client;
use Amadeus\Client\Params;
use Amadeus\Client\RequestOptions\Air\RetrieveSeatMap\FlightInfo;
use Amadeus\Client\RequestOptions\AirFlightInfoOptions;
use Amadeus\Client\RequestOptions\AirSellFromRecommendationOptions;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\Passenger;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\PricingOptions;
// use Amadeus\Client\RequestOptions\Fare\InformativePricing\Segment;
use Amadeus\Client\RequestOptions\Pnr\Segment;
use Amadeus\Client\Result;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Amadeus\Client\RequestOptions\FareMasterPricerCalendarOptions;
use Amadeus\Client\RequestOptions\FareMasterPricerTbSearch;
use Amadeus\Client\RequestOptions\Fare\MPPassenger;
use Amadeus\Client\RequestOptions\Fare\MPItinerary;
use Amadeus\Client\RequestOptions\Fare\MPDate;
use Amadeus\Client\RequestOptions\Fare\MPLocation;
use Amadeus\Client\RequestOptions\FareInformativePricingWithoutPnrOptions;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\Segment as infoSegment;
use Amadeus\Client\RequestOptions\MiniRuleGetFromRecOptions;
use Amadeus\Client\RequestOptions\MiniRule\Pricing as MiniRulePricing;
use Amadeus\Client\RequestOptions\Air\SellFromRecommendation\Itinerary as sellItinerary;
use Amadeus\Client\RequestOptions\Air\SellFromRecommendation\Segment as sellSegment;
use Amadeus\Client\RequestOptions\AirRetrieveSeatMapOptions;
use Amadeus\Client\RequestOptions\CommandCrypticOptions;
use Amadeus\Client\RequestOptions\DocIssuanceIssueTicketOptions;
use Amadeus\Client\RequestOptions\FareCheckRulesOptions;
use Amadeus\Client\RequestOptions\FarePricePnrWithBookingClassOptions;
use Amadeus\Client\RequestOptions\Fop\CreditCardInfo;
use Amadeus\Client\RequestOptions\Fop\Group;
use Amadeus\Client\RequestOptions\Fop\MopInfo;
use Amadeus\Client\RequestOptions\FopCreateFopOptions;
use Amadeus\Client\RequestOptions\PnrAddMultiElementsOptions;
use Amadeus\Client\RequestOptions\PnrCreatePnrOptions;
use Amadeus\Client\RequestOptions\Pnr\Traveller;
use Amadeus\Client\RequestOptions\Pnr\Itinerary;
use Amadeus\Client\RequestOptions\Pnr\Segment\Miscellaneous;
use Amadeus\Client\RequestOptions\Pnr\Element\Ticketing;
use Amadeus\Client\RequestOptions\Pnr\Element\Contact;
use Amadeus\Client\RequestOptions\Pnr\Reference;
use Amadeus\Client\RequestOptions\PnrRetrieveOptions;
use Amadeus\Client\RequestOptions\PnrCancelOptions;
use Amadeus\Client\RequestOptions\Ticket\Pricing;
use Amadeus\Client\Struct\Ticket\CancelDocument;
use Amadeus\Client\RequestOptions\Pnr\Element\ServiceRequest;
use Amadeus\Client\RequestOptions\Pnr\Element\FormOfPayment;
use Amadeus\Client\RequestOptions\PnrRetrieveAndDisplayOptions;
use Amadeus\Client\RequestOptions\Queue;
use Amadeus\Client\RequestOptions\QueueListOptions;
use Amadeus\Client\RequestOptions\QueuePlacePnrOptions;
use Amadeus\Client\RequestOptions\Service\BookPriceService\Service;
use Amadeus\Client\RequestOptions\TicketCreateTstFromPricingOptions;
use Amadeus\Client\RequestOptions\TicketDisplayTstOptions;

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
        // ==============================================FLIGHT SEARCH================================================
        // $opt = new FareMasterPricerTbSearch([
        //     'nrOfRequestedResults' => 250,
        //     'nrOfRequestedPassengers' => 1,
        //     'passengers' => [
        //         new MPPassenger([
        //             'type' => MPPassenger::TYPE_ADULT,
        //             'count' => 1
        //         ]),
        //         // new MPPassenger([
        //         //     'type' => MPPassenger::TYPE_CHILD,
        //         //     'count' => 1
        //         // ]),
        //         // new MPPassenger([
        //         //     'type' => MPPassenger::TYPE_INFANT,
        //         //     'count' => 1
        //         // ]),
        //     ],
        //     'itinerary' => [
        //         new MPItinerary([
        //             'departureLocation' => new MPLocation(['city' => 'NBO']),
        //             'arrivalLocation' => new MPLocation(['city' => 'MBA']),
        //             'date' => new MPDate([
        //                 'dateTime' => new \DateTime('2024-10-10T00:00:00+0000', new \DateTimeZone('UTC'))
        //             ])
        //         ]),
        //         new MPItinerary([
        //             'departureLocation' => new MPLocation(['city' => 'MBA']),
        //             'arrivalLocation' => new MPLocation(['city' => 'NBO']),
        //             'date' => new MPDate([
        //                 'dateTime' => new \DateTime('2024-10-13T00:00:00+0000', new \DateTimeZone('UTC'))
        //             ])
        //         ])

        //     ],
        //     'flightOptions' => [
        //         // FareMasterPricerTbSearch::CORPORATE_QUALIFIER_UNIFARE,
        //         FareMasterPricerTbSearch::FLIGHTOPT_PUBLISHED,
        //         FareMasterPricerTbSearch::FLIGHTOPT_UNIFARES,
        //         FareMasterPricerTbSearch::FLIGHTOPT_ELECTRONIC_TICKET,
        //         FareMasterPricerTbSearch::FLIGHTOPT_TICKET_AVAILABILITY_CHECK,
        //     ],
        //     // 'airlineOptions' => [
        //     //     FareMasterPricerTbSearch::AIRLINEOPT_MANDATORY => ['TK']
        //     // ],
        //     // 'corporateCodesUnifares' => [''],
        //     // 'corporateQualifier' => FareMasterPricerTbSearch::CORPORATE_QUALIFIER_UNIFARE,
        //     'cabinClass' => FareMasterPricerTbSearch::CABIN_ECONOMY,
        // ]);
        // $recommendations =  $this->client->fareMasterPricerTravelBoardSearch($opt);
        // dd($recommendations->response);
        // if ($recommendations) {
        //     $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $recommendations->responseXml);
        //     $response  = simplexml_load_string($response);
        //     $dataArray = json_decode(json_encode((array)$response), TRUE);
        //     dd(($dataArray['recommendation'][0]));
        //     /* dd(json_decode(json_encode($recommendations->responseXml), true)); */
        //     // dd($client->getLastRequest());
        // } else {
        //     echo "Error retrieving";
        // }

        // $this->client->setStateful(true);

        // $informativePricingResponse = $this->client->fareInformativePricingWithoutPnr(
        //     new FareInformativePricingWithoutPnrOptions([
        //         'passengers' => [
        //             new Passenger([
        //                 'tattoos' => [1],
        //                 'type' => Passenger::TYPE_ADULT
        //             ]),
        //         ],
        //         'segments' => [
        //             new infoSegment([
        //                 'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-10-10 06:30:00'),
        //                 'from' => 'NBO',
        //                 'to' => 'MBA',
        //                 'marketingCompany' => 'KQ',
        //                 'flightNumber' => '600',
        //                 'bookingClass' => 'V',
        //                 'segmentTattoo' => 1,
        //                 'groupNumber' => 1
        //             ]),
        //             new infoSegment([
        //                 'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-10-13 09:50:00'),
        //                 'from' => 'MBA',
        //                 'to' => 'NBO',
        //                 'marketingCompany' => 'KQ',
        //                 'flightNumber' => '603',
        //                 'bookingClass' => 'V',
        //                 'segmentTattoo' => 2,
        //                 'groupNumber' => 2
        //             ]),
        //         ],
        //         'pricingOptions' => new PricingOptions([
        //             'overrideOptions' => [
        //                 PricingOptions::OVERRIDE_FARETYPE_PUB,
        //                 PricingOptions::OVERRIDE_FARETYPE_UNI,
        //             ],
        //             'validatingCarrier' => 'KQ',
        //         ]),
        //     ])
        // );

        // if ($informativePricingResponse) {
        //     // $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $informativePricingResponse->responseXml);
        //     // $response  = simplexml_load_string($response);
        //     // $dataArray = json_decode(json_encode((array)$response), TRUE);
        //     // dd(json_decode(json_encode($informativePricingResponse->responseXml), true));
        //     // dd(($dataArray));
        //     // dd($client->getLastRequest());
        // } else {
        //     echo "Error retrieving";
        // }

        // // //===================================MINIRULE_GETFROMREC===============================
        // $miniRulesResponse = $this->client->miniRuleGetFromRec(
        //     new MiniRuleGetFromRecOptions([
        //         'pricings' => [
        //             new MiniRulePricing([
        //                 'id' => MiniRulePricing::ALL_PRICINGS,
        //                 'type' => MiniRulePricing::TYPE_FARE_RECOMMENDATION_NUMBER
        //             ])
        //         ]
        //     ]),
        // );
        // // $rulesResponse = $this->client->fareCheckRules(
        // //     new FareCheckRulesOptions([
        // //         'recommendations' => [1], //Pricing nr 1
        // //         'fareComponents' => [1],
        // //         'categoryList' => true
        // //     ])
        // // );
        // if ($this->client) {
        //     $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $miniRulesResponse->responseXml);
        //     $response  = simplexml_load_string($response);
        //     $dataArray = json_decode(json_encode((array)$response), TRUE);
        //     // dd(json_decode(json_encode($miniRulesResponse->responseXml), true));
        //     //dd($client->getLastRequest());
        //     //dd($client->getLastResponse());
        //     //dd(($dataArray['recommendation'][1]));
        // } else {
        //     echo "Error retrieving";
        // }

        // $this->client->securitySignOut();

        // dd('One');
        // //==============================================AIR_SELLRECOMMENDATION================================================
        // $this->client->setStateful(true);
        // $airSell = new AirSellFromRecommendationOptions([
        //     'itinerary' => [
        //         new sellItinerary([
        //             'from' => 'NBO',
        //             'to' => 'MBA',
        //             'segments' => [
        //                 new sellSegment([
        //                     'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-10-10 06:30:00'),
        //                     'from' => 'NBO',
        //                     'to' => 'MBA',
        //                     'companyCode' => 'KQ',
        //                     'flightNumber' => '600',
        //                     'bookingClass' => 'V',
        //                     'nrOfPassengers' => 1,
        //                     'statusCode' => sellSegment::STATUS_SELL_SEGMENT
        //                 ]),
        //             ]
        //         ]),
        //         new sellItinerary([
        //             'from' => 'MBA',
        //             'to' => 'NBO',
        //             'segments' => [
        //                 new sellSegment([
        //                     'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-10-13 09:50:00'),
        //                     'from' => 'MBA',
        //                     'to' => 'NBO',
        //                     'companyCode' => 'KQ',
        //                     'flightNumber' => '603',
        //                     'bookingClass' => 'V',
        //                     'nrOfPassengers' => 1,
        //                     'statusCode' => sellSegment::STATUS_SELL_SEGMENT
        //                 ]),
        //             ]
        //         ]),
        //     ]
        // ]);

        // $sellResult = $this->client->airSellFromRecommendation($airSell);
        // if ($this->client) {
        //     $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3",  $sellResult->responseXml);
        //     $response  = simplexml_load_string($response);
        //     $dataArray = json_decode(json_encode((array)$response), TRUE);
        //     // dd(json_decode(json_encode($sellResult->responseXml), true));
        //     // dd($client->getLastRequest());
        //     // dd(($dataArray['recommendation'][1]));
        // } else {
        //     echo "Error retrieving";
        // }
        // //==============================================PNR ADD MULTISEGMENTS================================================
        // $createdPnr = $this->client->pnrCreatePnr(
        //     new PnrCreatePnrOptions([
        //         'travellers' => [
        //             new Traveller([
        //                 'number' => 1,
        //                 'firstName' => 'Mike',
        //                 'lastName' => 'Kenya',
        //                 'travellerType' => Traveller::TRAV_TYPE_ADULT,
        //                 // 'withInfant' => true,
        //                 // 'infant' => new Traveller([
        //                 //     'firstName' => 'Rex',
        //                 //     'lastName' => 'Kimani',
        //                 //     'dateOfBirth' => \DateTime::createFromFormat('Y-m-d', '2001-01-01', new \DateTimeZone('UTC'))
        //                 // ])
        //             ]),
        //             // new Traveller([
        //             //     'number' => 2,
        //             //     'firstName' => 'Larry',
        //             //     'lastName' => 'Onyango',
        //             //     'travellerType' => Traveller::TRAV_TYPE_ADULT,
        //             // ]),
        //             // new Traveller([
        //             //     'number' => 3,
        //             //     'firstName' => 'Tim',
        //             //     'lastName' => 'Onyango',
        //             //     'travellerType' => Traveller::TRAV_TYPE_CHILD,
        //             // ]),
        //         ],
        //         'actionCode' => PnrCreatePnrOptions::ACTION_NO_PROCESSING,
        //         'itinerary' => [
        //             new Itinerary([
        //                 'segments' => [
        //                     new Miscellaneous([
        //                         'status ' => Segment::STATUS_CONFIRMED,
        //                         'company' => 'KQ',
        //                         'date' => \DateTime::createFromFormat('Ymd', '20241010', new \DateTimeZone('UTC')),
        //                         'cityCode' => 'NBO',
        //                         'quantity' => 1,
        //                         'freeText' => 'WARNING - CLASS AVAILABILITY MAY NOT BE SUFFICIENT ON CERTAIN FLIGHTS',
        //                         'references' => [
        //                             new Reference([
        //                                 'type' => Reference::TYPE_PASSENGER_TATTOO,
        //                                 'id' => 1
        //                             ]),
        //                         ]
        //                     ]),
        //                 ]
        //             ]),
        //             new Itinerary([
        //                 'segments' => [
        //                     new Miscellaneous([
        //                         'status ' => Segment::STATUS_CONFIRMED,
        //                         'company' => 'KQ',
        //                         'date' => \DateTime::createFromFormat('Ymd', '20240813', new \DateTimeZone('UTC')),
        //                         'cityCode' => 'MBA',
        //                         'quantity' => 1,
        //                         'freeText' => 'WARNING - CLASS AVAILABILITY MAY NOT BE SUFFICIENT ON CERTAIN FLIGHTS',
        //                         'references' => [
        //                             new Reference([
        //                                 'type' => Reference::TYPE_PASSENGER_TATTOO,
        //                                 'id' => 1
        //                             ]),
        //                         ]
        //                     ]),
        //                 ]
        //             ]),
        //         ],
        //         'elements' => [
        //             new Ticketing([
        //                 'ticketMode' => Ticketing::TICKETMODE_OK
        //             ]),
        //             new Contact([
        //                 'type' => Contact::TYPE_PHONE_MOBILE,
        //                 'value' => '+254725784567'
        //             ]),
        //             new Contact([
        //                 'type' => Contact::TYPE_EMAIL,
        //                 'value' => 'devops.2@clifford.co.ke'
        //             ]),
        //             new ServiceRequest([
        //                 'type' => 'CTCE',
        //                 'status' => ServiceRequest::STATUS_HOLD_CONFIRMED,
        //                 'company' => 'KQ',
        //                 'quantity' => 1,
        //                 'freeText' => [
        //                     'devops.2//clifford.co.ke'
        //                 ],
        //             ]),

        //             new ServiceRequest([
        //                 'type' => 'DOCS',
        //                 'status' => ServiceRequest::STATUS_HOLD_CONFIRMED,
        //                 'company' => 'KQ',
        //                 'quantity' => 1,
        //                 'freeText' => [
        //                     'P-KE-123456-KE-01JAN89-M-09DEC25-KENYA-MIKE'
        //                 ],
        //             ]),
        //             // new ServiceRequest([
        //             //     'type' => 'DOCS',
        //             //     'status' => ServiceRequest::STATUS_HOLD_CONFIRMED,
        //             //     'company' => 'WB',
        //             //     'quantity' => 1,
        //             //     'freeText' => [
        //             //         'P-KE-123456-KE-01JAN18-M-09DEC25-ONYANGO-TIM'
        //             //     ],
        //             //     'references' => [
        //             //         new Reference([
        //             //             'type' => Reference::TYPE_PASSENGER_TATTOO,
        //             //             'id' => 3
        //             //         ])
        //             //     ]
        //             // ]),
        //         ]
        //     ])
        // );

        // $options = new FopCreateFopOptions([
        //     'transactionCode' => FopCreateFopOptions::TRANS_CREATE_FORM_OF_PAYMENT,
        //     'fopGroup' => [
        //         new Group([
        //             'mopInfo' => [
        //                 new MopInfo([
        //                     'sequenceNr' => 1,
        //                     'fopCode' => 'CCVI',
        //                     'fopType' => MopInfo::FOPTYPE_FP_ELEMENT,
        //                     'mopPaymentType' => MopInfo::MOP_PAY_TYPE_CREDIT_CARD,
        //                     'creditCardInfo' => new CreditCardInfo([
        //                         'vendorCode' => 'VI',
        //                         'cardNumber' => '4541000000000016',
        //                         'expiryDate' => '0925',
        //                         'securityId' => '123'
        //                     ])
        //                 ]),
        //             ]
        //         ])
        //     ]
        // ]);

        // $fopResponse = $this->client->fopCreateFormOfPayment($options);
        // // //==================================BookingClass===============================================================
        // $pricingResponse = $this->client->farePricePnrWithBookingClass(
        //     new FarePricePnrWithBookingClassOptions([
        //         'overrideOptions' => [
        //             PricingOptions::OVERRIDE_FARETYPE_PUB,
        //             PricingOptions::OVERRIDE_FARETYPE_UNI,
        //         ],
        //         'validatingCarrier' => 'KQ',
        //     ])
        // );

        // //==================================TICKET CREATE TST FROM PRICING===============================================================
        // $createTstResponse = $this->client->ticketCreateTSTFromPricing(
        //     new TicketCreateTstFromPricingOptions([
        //         'pricings' => [
        //             new Pricing([
        //                 'tstNumber' => 1
        //             ]),
        //             // new Pricing([
        //             //     'tstNumber' => 2
        //             // ]),
        //             // new Pricing([
        //             //     'tstNumber' => 3
        //             // ]),
        //         ]
        //     ])
        // );

        // //==============================================PNR ADD MULTISEGMENTS================================================
        // $pnrReply1 = $this->client->pnrAddMultiElements(
        //     new PnrAddMultiElementsOptions([
        //         // 'actionCode' => PnrAddMultiElementsOptions::ACTION_END_TRANSACT_RETRIEVE,
        //         // 'actionCode' => PnrAddMultiElementsOptions::ACTION_END_TRANSACT,
        //         'actionCode' => PnrAddMultiElementsOptions::ACTION_WARNING_AT_EOT,
        //         // 'actionCode' => PnrAddMultiElementsOptions::ACTION_STOP_EOT_ON_SELL_ERROR, //ET: END AND RETRIEVE
        //     ]),
        // );

        // $pnrReply2 = $this->client->pnrAddMultiElements(
        //     new PnrAddMultiElementsOptions([
        //         'actionCode' => PnrAddMultiElementsOptions::ACTION_END_TRANSACT_RETRIEVE,
        //         // 'actionCode' => PnrAddMultiElementsOptions::ACTION_END_TRANSACT,
        //         // 'actionCode' => PnrAddMultiElementsOptions::ACTION_WARNING_AT_EOT,
        //         // 'actionCode' => PnrAddMultiElementsOptions::ACTION_STOP_EOT_ON_SELL_ERROR, //ET: END AND RETRIEVE
        //     ]),
        // );


        // $pnrcontent1 = $this->client->pnrRetrieve(new PnrRetrieveOptions());


        // // $pnrcontent1 = $this->client->pnrRetrieve(new PnrRetrieveOptions());


        // // $issueTicketResponse = $this->client->docIssuanceIssueTicket(
        // //     new DocIssuanceIssueTicketOptions([
        // //         'options' => [
        // //             DocIssuanceIssueTicketOptions::OPTION_ETICKET,
        // //             DocIssuanceIssueTicketOptions::OPTION_RETRIEVE_PNR
        // //         ],
        // //     ])
        // // );


        // // $pnrcontent2 = $this->client->pnrRetrieve(new PnrRetrieveOptions());

        // $this->client->securitySignOut();

        // dd('Finished');

        //////QUEUE_PLACEPNR
        $placeResult = $this->client->queuePlacePnr(
            new QueuePlacePnrOptions([
                'targetQueue' => new Queue([
                    'queue' => 1,
                    'category' => 1
                ]),
                'recordLocator' => 'JNJNEF'
            ])
        );
        dd($placeResult);

        $this->client->setStateful(true);
        $pnrcontent2 = $this->client->pnrRetrieve(
            new PnrRetrieveOptions(['recordLocator' => 'RYOZRZ'])
        );

        $issueTicketResponse = $this->client->docIssuanceIssueTicket(
            new DocIssuanceIssueTicketOptions([
                'options' => [
                    DocIssuanceIssueTicketOptions::OPTION_ETICKET,
                    DocIssuanceIssueTicketOptions::OPTION_RETRIEVE_PNR
                ],
            ])
        );
        $pnrcontent3 = $this->client->pnrRetrieve(new PnrRetrieveOptions());

        $this->client->securitySignOut();

        dd('Finished');
    }
}