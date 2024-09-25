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
use Amadeus\Client\RequestOptions\DocIssuance\CompoundOption;
use Amadeus\Client\RequestOptions\DocIssuance\Option;
use Amadeus\Client\RequestOptions\DocIssuanceIssueMiscDocOptions;
use Amadeus\Client\RequestOptions\DocIssuanceIssueTicketOptions;
use Amadeus\Client\RequestOptions\Fare\MPFeeId;
use Amadeus\Client\RequestOptions\Fare\PricePnr\FareFamily;
use Amadeus\Client\RequestOptions\Fare\PricePnr\PaxSegRef;
use Amadeus\Client\RequestOptions\FareCheckRulesOptions;
use Amadeus\Client\RequestOptions\FareGetFareFamilyDescriptionOptions;
use Amadeus\Client\RequestOptions\FarePricePnrWithBookingClassOptions;
use Amadeus\Client\RequestOptions\FarePriceUpsellWithoutPnrOptions;
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
use Amadeus\Client\RequestOptions\Reference as reqReference;
use Amadeus\Client\RequestOptions\ReferenceGroup;
use Amadeus\Client\RequestOptions\SalesReportsDisplayQueryReportOptions;
use Amadeus\Client\RequestOptions\Service\BookPriceService\Service;
use Amadeus\Client\RequestOptions\TicketCreateTstFromPricingOptions;
use Amadeus\Client\RequestOptions\TicketDisplayTstOptions;
use Amadeus\Client\RequestOptions\TicketProcessEDocOptions;

class FlightController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = amadeus_client();
    }


    public function tryAmadeus()
    {

        // $seatmapInfo = $this->client->airRetrieveSeatMap(
        //     new AirRetrieveSeatMapOptions([
        //         'flight' => new FlightInfo([
        //             'departureDate' => \DateTime::createFromFormat('Ymd', '20240910'),
        //             'departure' => 'NBO',
        //             'arrival' => 'DXB',
        //             'airline' => 'EK',
        //             'flightNumber' => '722'
        //         ])
        //     ])
        // );

        // dd($seatmapInfo);


        $response = $this->client->ticketProcessEDoc(
            new TicketProcessEDocOptions([
                'action' => TicketProcessEDocOptions::ACTION_ETICKET_DISPLAY,
                'ticketNumber' => '7066070512697'
            ])
        );

        dd($response);

        $opt = new SalesReportsDisplayQueryReportOptions([
            'salesIndicator' => SalesReportsDisplayQueryReportOptions::SALESIND_INTERNATIONAL,
            'currencyType' => SalesReportsDisplayQueryReportOptions::CURRENCY_TARGET,
            'currency' => 'KES',
            'fopType' => SalesReportsDisplayQueryReportOptions::FOP_CREDIT_CARD,
            'startDate' => \DateTime::createFromFormat('Ymd', '20240701', new \DateTimeZone('UTC')),
            'endDate' => \DateTime::createFromFormat('Ymd', '20240911', new \DateTimeZone('UTC')),
            'specificDate' => \DateTime::createFromFormat('YmdHis', '20240910000000', new \DateTimeZone('UTC')),
            'specificDateType' => SalesReportsDisplayQueryReportOptions::DATE_TYPE_SPECIFIC
        ]);

        $salesReportResult = $this->client->salesReportsDisplayQueryReport($opt);

        dd($salesReportResult);

        $this->client->setStateful(true);
        $pnrcontent2 = $this->client->pnrRetrieve(
            new PnrRetrieveOptions(['recordLocator' => 'KOKC6M'])
        );


        $pnrcontent3 = $this->client->pnrRetrieve(new PnrRetrieveOptions());

        $this->client->securitySignOut();

        // ==============================================FLIGHT SEARCH================================================
        // $opt = new FareMasterPricerTbSearch([
        //     'nrOfRequestedResults' => 250,
        //     'nrOfRequestedPassengers' => 2,
        //     'passengers' => [
        //         new MPPassenger([
        //             'type' => MPPassenger::TYPE_ADULT,
        //             'count' => 1
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
        //             'arrivalLocation' => new MPLocation(['city' => 'FRA']),
        //             'date' => new MPDate([
        //                 'dateTime' => new \DateTime('2024-12-10T00:00:00+0000', new \DateTimeZone('UTC'))
        //             ])
        //         ]),
        //         // new MPItinerary([
        //         //     'departureLocation' => new MPLocation(['city' => 'TYO']),
        //         //     'arrivalLocation' => new MPLocation(['city' => 'NBO']),
        //         //     'date' => new MPDate([
        //         //         'dateTime' => new \DateTime('2024-12-12T00:00:00+0000', new \DateTimeZone('UTC'))
        //         //     ])
        //         // ])
        //     ],
        //     // 'requestedFlightTypes' => [
        //     //     FareMasterPricerTbSearch::FLIGHTTYPE_NONSTOP,
        //     //     // FareMasterPricerTbSearch::FLIGHTTYPE_DIRECT
        //     // ],
        //     'flightOptions' => [
        //         FareMasterPricerTbSearch::FLIGHTOPT_PUBLISHED,
        //         FareMasterPricerTbSearch::FLIGHTOPT_UNIFARES,
        //         FareMasterPricerTbSearch::FLIGHTOPT_ELECTRONIC_TICKET,
        //         FareMasterPricerTbSearch::FLIGHTOPT_TICKET_AVAILABILITY_CHECK,
        //     ],
        //     'feeIds' => [
        //         new MPFeeId(['type' => MPFeeId::FEETYPE_FARE_FAMILY_INFORMATION, 'number' => 3]),
        //     ],
        //     // 'cabinClass' => FareMasterPricerTbSearch::CABIN_ECONOMY,
        //     'doTicketabilityPreCheck' => true,
        //     'currencyOverride' => 'KES',
        // ]);
        // $recommendations =  $this->client->fareMasterPricerTravelBoardSearch($opt);
        // dd($recommendations->response);

        // // //==============================================FLIGHT UPSELL================================================
        // $this->client->setStateful(true);

        // $upsellResponse = $this->client->farePriceUpsellWithoutPnr(
        //     new FarePriceUpsellWithoutPnrOptions([
        //         'passengers' => [
        //             new Passenger([
        //                 'tattoos' => [1],
        //                 'type' => Passenger::TYPE_ADULT
        //             ]),
        //             new Passenger([
        //                 'tattoos' => [2],
        //                 'type' => Passenger::TYPE_CHILD
        //             ]),
        //             new Passenger([
        //                 'tattoos' => [1],
        //                 'type' => Passenger::TYPE_INFANT
        //             ]),
        //         ],
        //         'segments' => [
        //             new infoSegment([
        //                 'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-12-10 16:45:00'),
        //                 'from' => 'NBO',
        //                 'to' => 'DXB',
        //                 'marketingCompany' => 'EK',
        //                 'flightNumber' => '720',
        //                 'bookingClass' => 'L',
        //                 'segmentTattoo' => 1,
        //                 'groupNumber' => 1
        //             ]),
        //             new infoSegment([
        //                 'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-12-11 01:50:00'),
        //                 'from' => 'DXB',
        //                 'to' => 'FRA',
        //                 'marketingCompany' => 'LH',
        //                 'flightNumber' => '631',
        //                 'bookingClass' => 'V',
        //                 'segmentTattoo' => 2,
        //                 'groupNumber' => 1
        //             ]),
        //         ],
        //         'pricingOptions' => new PricingOptions([
        //             'overrideOptions' => [
        //                 PricingOptions::OVERRIDE_FARETYPE_PUB,
        //                 'FFH',
        //                 PricingOptions::OVERRIDE_FARETYPE_UNI,
        //                 PricingOptions::OVERRIDE_RETURN_LOWEST,
        //             ],
        //             'validatingCarrier' => 'LH',
        //             'currencyOverride' => 'KES',
        //         ]),
        //     ])
        // );

        // $fareFamiliesResponse = $this->client->fareGetFareFamilyDescription(
        //     new FareGetFareFamilyDescriptionOptions([
        //         'referenceGroups' => [
        //             new ReferenceGroup([
        //                 new reqReference('REC', 1),
        //             ]),
        //             new ReferenceGroup([
        //                 new reqReference('REC', 2),
        //             ]),
        //             new ReferenceGroup([
        //                 new reqReference('REC', 3),
        //             ]),
        //             new ReferenceGroup([
        //                 new reqReference('REC', 4),
        //             ]),
        //         ]
        //     ])
        // );

        // $this->client->securitySignOut();

        // dd($upsellResponse->response, $fareFamiliesResponse->response);

        // //==============================================AIR_SELLRECOMMENDATION================================================
        $this->client->setStateful(true);
        $airSell = new AirSellFromRecommendationOptions([
            'itinerary' => [
                new sellItinerary([
                    'from' => 'NBO',
                    'to' => 'FRA',
                    'segments' => [
                        new sellSegment([
                            'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-12-10 16:45:00'),
                            'from' => 'NBO',
                            'to' => 'DXB',
                            'companyCode' => 'EK',
                            'flightNumber' => '720',
                            'bookingClass' => 'L',
                            'nrOfPassengers' => 2,
                            'statusCode' => sellSegment::STATUS_CONFIRMED
                        ]),
                        new sellSegment([
                            'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2024-12-11 01:50:00'),
                            'from' => 'DXB',
                            'to' => 'FRA',
                            'companyCode' => 'LH',
                            'flightNumber' => '631',
                            'bookingClass' => 'V',
                            'nrOfPassengers' => 2,
                            'statusCode' => sellSegment::STATUS_CONFIRMED
                        ]),
                    ]
                ]),

            ]
        ]);

        $sellResult = $this->client->airSellFromRecommendation($airSell);

        // //==============================================PNR ADD MULTISEGMENTS================================================
        $createdPnr = $this->client->pnrCreatePnr(
            new PnrCreatePnrOptions([
                'travellers' => [
                    new Traveller([
                        'number' => 1,
                        'firstName' => 'Jack',
                        'lastName' => 'Black',
                        'travellerType' => Traveller::TRAV_TYPE_ADULT,
                        'withInfant' => true,
                        'infant' => new Traveller([
                            'firstName' => 'Tina',
                            'lastName' => 'Kimani',
                            'dateOfBirth' => \DateTime::createFromFormat('Y-m-d', '2023-01-01', new \DateTimeZone('UTC'))
                        ])
                    ]),
                    // new Traveller([
                    //     'number' => 2,
                    //     'firstName' => 'Larry',
                    //     'lastName' => 'Onyango',
                    //     'travellerType' => Traveller::TRAV_TYPE_ADULT,
                    // ]),
                    new Traveller([
                        'number' => 2,
                        'firstName' => 'Paul',
                        'lastName' => 'Onyango',
                        'travellerType' => Traveller::TRAV_TYPE_CHILD,
                    ]),
                ],
                'actionCode' => PnrCreatePnrOptions::ACTION_NO_PROCESSING,
                'itinerary' => [
                    new Itinerary([
                        'segments' => [
                            new Miscellaneous([
                                'status ' => Segment::STATUS_CONFIRMED,
                                'company' => 'LH',
                                'date' => \DateTime::createFromFormat('Ymd', '20241210', new \DateTimeZone('UTC')),
                                'cityCode' => 'NBO',
                                'quantity' => 3,
                                'freeText' => 'WARNING - CLASS AVAILABILITY MAY NOT BE SUFFICIENT ON CERTAIN FLIGHTS',
                                'references' => [
                                    new Reference([
                                        'type' => Reference::TYPE_PASSENGER_TATTOO,
                                        'id' => 1
                                    ]),
                                    new Reference([
                                        'type' => Reference::TYPE_PASSENGER_TATTOO,
                                        'id' => 2
                                    ]),
                                ]
                            ]),
                        ]
                    ]),
                ],
                'elements' => [
                    new Ticketing([
                        'ticketMode' => Ticketing::TICKETMODE_OK
                    ]),
                    new Contact([
                        'type' => Contact::TYPE_PHONE_MOBILE,
                        'value' => '+254725784567'
                    ]),
                    new Contact([
                        'type' => Contact::TYPE_EMAIL,
                        'value' => 'devops.2@clifford.co.ke'
                    ]),
                    new ServiceRequest([
                        'type' => 'CTCE',
                        'status' => ServiceRequest::STATUS_HOLD_CONFIRMED,
                        'company' => 'LH',
                        'quantity' => 1,
                        'freeText' => [
                            'devops.2//clifford.co.ke'
                        ],
                        'references' => [
                            new Reference([
                                'type' => Reference::TYPE_PASSENGER_TATTOO,
                                'id' => 1
                            ])
                        ]
                    ]),

                    // new ServiceRequest([
                    //     'type' => 'DOCS',
                    //     'status' => ServiceRequest::STATUS_HOLD_CONFIRMED,
                    //     'company' => 'EK',
                    //     'quantity' => 1,
                    //     'freeText' => [
                    //         'P-KE-123456-KE-01JAN89-M-09DEC25-KENYA-PETER'
                    //     ],
                    //     'references' => [
                    //         new Reference([
                    //             'type' => Reference::TYPE_PASSENGER_TATTOO,
                    //             'id' => 1
                    //         ])
                    //     ]
                    // ]),
                    // new ServiceRequest([
                    //     'type' => 'DOCS',
                    //     'status' => ServiceRequest::STATUS_HOLD_CONFIRMED,
                    //     'company' => 'WB',
                    //     'quantity' => 1,
                    //     'freeText' => [
                    //         'P-KE-123456-KE-01JAN18-M-09DEC25-ONYANGO-TIM'
                    //     ],
                    //     'references' => [
                    //         new Reference([
                    //             'type' => Reference::TYPE_PASSENGER_TATTOO,
                    //             'id' => 3
                    //         ])
                    //     ]
                    // ]),
                ]
            ])
        );

        $options = new FopCreateFopOptions([
            'transactionCode' => FopCreateFopOptions::TRANS_CREATE_FORM_OF_PAYMENT,
            'fopGroup' => [
                new Group([
                    'mopInfo' => [
                        new MopInfo([
                            'sequenceNr' => 1,
                            'fopCode' => 'CCVI',
                            'fopType' => MopInfo::FOPTYPE_FP_ELEMENT,
                            'mopPaymentType' => MopInfo::MOP_PAY_TYPE_CREDIT_CARD,
                            'creditCardInfo' => new CreditCardInfo([
                                'vendorCode' => 'VI',
                                'cardNumber' => '4541000000000016',
                                'expiryDate' => '0925',
                                'securityId' => '123'
                            ])
                        ]),
                    ]
                ])
            ]
        ]);

        $fopResponse = $this->client->fopCreateFormOfPayment($options);
        // //==================================BookingClass===============================================================
        $pricingResponse = $this->client->farePricePnrWithBookingClass(
            new FarePricePnrWithBookingClassOptions([
                'overrideOptions' => [
                    PricingOptions::OVERRIDE_FARETYPE_PUB,
                    PricingOptions::OVERRIDE_FARETYPE_UNI,
                ],
                'fareFamily' => [
                    new FareFamily([
                        'fareFamily' => 'ECOBASEA',
                        'paxSegRefs' => [
                            new PaxSegRef([
                                'type' => PaxSegRef::TYPE_SEGMENT,
                                'reference' => 1
                            ]),
                            new PaxSegRef([
                                'type' => PaxSegRef::TYPE_SEGMENT,
                                'reference' => 2
                            ]),
                        ]
                    ]),
                ],
                'validatingCarrier' => 'LH',
            ])
        );

        //==================================TICKET CREATE TST FROM PRICING===============================================================
        $createTstResponse = $this->client->ticketCreateTSTFromPricing(
            new TicketCreateTstFromPricingOptions([
                'pricings' => [
                    new Pricing([
                        'tstNumber' => 1
                    ]),
                    new Pricing([
                        'tstNumber' => 2
                    ]),
                    new Pricing([
                        'tstNumber' => 3
                    ]),
                ]
            ])
        );

        //==============================================PNR ADD MULTISEGMENTS================================================
        $pnrReply1 = $this->client->pnrAddMultiElements(
            new PnrAddMultiElementsOptions([
                'actionCode' => PnrAddMultiElementsOptions::ACTION_WARNING_AT_EOT,
            ]),
        );

        if ($pnrReply1->status !== Result::STATUS_OK) {
            return $pnrReply1;
        }

        $pnrReply2 = $this->client->pnrAddMultiElements(
            new PnrAddMultiElementsOptions([
                'actionCode' => PnrAddMultiElementsOptions::ACTION_END_TRANSACT_RETRIEVE,
            ]),
        );


        $pnrcontent1 = $this->client->pnrRetrieve(new PnrRetrieveOptions());

        $this->client->securitySignOut();

        dd($pnrcontent1);
    }
}
