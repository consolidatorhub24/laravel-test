<?php

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Agents\AgentFlights;
use Illuminate\Support\Facades\Validator;
use Amadeus\Client;
use Amadeus\Client\Params;
use Amadeus\Client\Result;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Amadeus\Client\RequestOptions\FareMasterPricerTbSearch;
use Amadeus\Client\RequestOptions\Fare\MPPassenger;
use Amadeus\Client\RequestOptions\Fare\MPItinerary;
use Amadeus\Client\RequestOptions\Fare\MPDate;
use Amadeus\Client\RequestOptions\Fare\MPLocation;
use Amadeus\Client\RequestOptions\FareInformativePricingWithoutPnrOptions;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\Passenger;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\Segment as infoSegment;
use Amadeus\Client\RequestOptions\AirSellFromRecommendationOptions;
use Amadeus\Client\RequestOptions\Air\SellFromRecommendation\Itinerary as sellItinerary;
use Amadeus\Client\RequestOptions\Air\SellFromRecommendation\Segment as sellSegment;

use Amadeus\Client\RequestOptions\PnrCreatePnrOptions;
use Amadeus\Client\RequestOptions\Pnr\Traveller;
use Amadeus\Client\RequestOptions\Pnr\Itinerary;
use Amadeus\Client\RequestOptions\Pnr\Segment;
use Amadeus\Client\RequestOptions\Pnr\Segment\Miscellaneous;
use Amadeus\Client\RequestOptions\Pnr\Element\Ticketing;
use Amadeus\Client\RequestOptions\Pnr\Element\Contact;
use Amadeus\Client\RequestOptions\PnrRetrieveOptions;
use Amadeus\Client\RequestOptions\PnrCancelOptions;
use Amadeus\Client\RequestOptions\PnrAddMultiElementsOptions;

use Amadeus\Client\RequestOptions\FopCreateFopOptions;
use Amadeus\Client\RequestOptions\Fop\Group;
use Amadeus\Client\RequestOptions\Fop\ElementRef;
use Amadeus\Client\RequestOptions\Fop\MopInfo;
use Amadeus\Client\RequestOptions\Fop\CreditCardInfo;
use Amadeus\Client\RequestOptions\FarePricePnrWithBookingClassOptions;

use Amadeus\Client\RequestOptions\TicketCreateTstFromPricingOptions;
use Amadeus\Client\RequestOptions\Ticket\Pricing;
use Amadeus\Client\RequestOptions\Ticket\PassengerReference;


use Amadeus\Client\RequestOptions\DocIssuanceIssueTicketOptions;
use Amadeus\Client\RequestOptions\Fare\InformativePricing\PricingOptions;
use Amadeus\Client\RequestOptions\Fop\PaxRef;
use Amadeus\Client\RequestOptions\MiniRule\Pricing as MiniRulePricing;
use Amadeus\Client\RequestOptions\MiniRuleGetFromRecOptions;
use Amadeus\Client\RequestOptions\Offer\AirRecommendation;
use Amadeus\Client\RequestOptions\Offer\PassengerDef;
use Amadeus\Client\RequestOptions\OfferConfirmAirOptions;
use Amadeus\Client\RequestOptions\OfferCreateOptions;
use Amadeus\Client\RequestOptions\OfferVerifyOptions;
use Amadeus\Client\RequestOptions\Pnr\Reference;
use Amadeus\Client\RequestOptions\Pnr\Element\ServiceRequest;
use Amadeus\Client\RequestOptions\Pnr\Element\FormOfPayment;
use Amadeus\Client\RequestOptions\PnrRetrieveAndDisplayOptions;
use Amadeus\Client\RequestOptions\Service\BookPriceService\Service;
use Amadeus\Client\RequestOptions\TicketCancelDocumentOptions;
use Amadeus\Client\RequestOptions\TicketDisplayTstOptions;
use Amadeus\Client\Struct\Fop\AttributeDetails;
use Amadeus\Client\Struct\Fop\GroupUsage;
use Amadeus\Client\Struct\Ticket\CancelDocument;
use Attribute;

class AgentFlightController extends Controller
{
    protected $client;

    public function __construct()
    {
        $msgLog = new Logger('RequestResponseLogs');
        $msgLog->pushHandler(new StreamHandler(base_path('/public/requestresponse.log'), Logger::INFO));
        $params = new Params([
            'authParams' => [
                'officeId' => 'NBO4128ZT', //The Amadeus Office Id you want to sign in to - must be open on your WSAP.
                'userId' => 'WS7HZCLI', //Also known as 'Originator' for Soap Header 1 & 2 WSDL's
                'passwordData' => base64_encode('oS744Xn58'), // *base 64 encoded* password
                'passwordLength' => 12,
                'dutyCode' => 'SU',
                'organizationId' => 'NMC-EASTAF',
            ],
            'sessionHandlerParams' => [
                'soapHeaderVersion' => Client::HEADER_V4,
                'wsdl' => base_path('/public/wsdl/1ASIWCLI7HZ_PDT_20220204_091336.wsdl'),
                'stateful' => false,
                'logger' => $msgLog,
            ],
            'requestCreatorParams' => [
                'receivedFrom' => 'Sumotrips' // The "Received From" string that will be visible in PNR History
            ]
        ]);
        $client = new Client($params);

        $this->client = $client;
    }

    public function index()
    {
        return view('agents.flights');
    }

    public function CheckIfUserIsAuthenticated()
    {
        //===========================TEST CASE 1=========================================================
        //========FLIGHT SEARCH===========//
        // $opt = new FareMasterPricerTbSearch([
        //     'nrOfRequestedResults' => 250,
        //     'nrOfRequestedPassengers' => 1,
        //     'passengers' => [
        //         new MPPassenger([
        //             'type' => MPPassenger::TYPE_ADULT,
        //             'count' => 1
        //         ]),
        //     ],
        //     'itinerary' => [
        //         new MPItinerary([
        //             'departureLocation' => new MPLocation(['city' => 'NBO']),
        //             'arrivalLocation' => new MPLocation(['city' => 'KGL']),
        //             'date' => new MPDate([
        //                 'dateTime' => new \DateTime('2022-05-24T00:00:00+0000', new \DateTimeZone('UTC'))
        //             ])
        //         ]),
        //     ],
        //     'flightOptions' => [
        //         FareMasterPricerTbSearch::FLIGHTOPT_PUBLISHED,
        //         FareMasterPricerTbSearch::FLIGHTOPT_UNIFARES,
        //         FareMasterPricerTbSearch::FLIGHTOPT_ELECTRONIC_TICKET,
        //         FareMasterPricerTbSearch::FLIGHTOPT_TICKET_AVAILABILITY_CHECK,
        //     ],
        // ]);
        // $recommendations = $this->client->fareMasterPricerTravelBoardSearch($opt);
        // if ($this->client) {
        //     $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $recommendations->responseXml);
        //     $response  = simplexml_load_string($response);
        //     $dataArray = json_decode(json_encode((array)$response), TRUE);
        //     dd(json_decode(json_encode($recommendations->responseXml), true));
        //     // dd($client->getLastRequest());
        //     // dd($client->getLastResponse());
        //     // dd(($dataArray['recommendation'][1]));
        // } else {
        //     echo "Error retrieving";
        // }
        // //==============================================FLIGHT PRICE fareInformativePricingWithoutPnr================================================
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
        //                 'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2022-05-19 08:00:00'),
        //                 'from' => 'NBO',
        //                 'to' => 'MBA',
        //                 'marketingCompany' => 'KQ',
        //                 'flightNumber' => '602',
        //                 'bookingClass' => 'V',
        //                 'segmentTattoo' => 1,
        //                 'groupNumber' => 1
        //             ]),
        //         ],
        //         'pricingOptions' => new PricingOptions([
        //             'overrideOptions' => [
        //                 PricingOptions::OVERRIDE_FARETYPE_PUB,
        //                 PricingOptions::OVERRIDE_FARETYPE_UNI,
        //                 PricingOptions::OVERRIDE_VALIDATING_CARRIER,
        //             ],
        //             'validatingCarrier' => 'KQ',
        //         ]),
        //     ])
        // );
        // if ($this->client) {
        //     $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $informativePricingResponse->responseXml);
        //     $response  = simplexml_load_string($response);
        //     $dataArray = json_decode(json_encode((array)$response), TRUE);
        //     // dd(json_decode(json_encode($informativePricingResponse->responseXml), true));
        //     // dd($this->client->getSessionData());
        //     // dd(($dataArray['recommendation'][1]));
        // } else {
        //     echo "Error retrieving";
        // }
        // // dd($client->securitySignOut());
        // // dd($client->getSessionData());
        // // dd($client->getLastRequest());
        // // dd($this->client->getLastResponse());
        // //===================================MINIRULE_GETFROMREC===============================
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
        // if ($this->client) {
        //     $response  = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $miniRulesResponse->responseXml);
        //     $response  = simplexml_load_string($response);
        //     $dataArray = json_decode(json_encode((array)$response), TRUE);
        //     //dd(json_decode(json_encode($miniRulesResponse->responseXml), true));
        //     //dd($client->getLastRequest());
        //     //dd($client->getLastResponse());
        //     //dd(($dataArray['recommendation'][1]));
        // } else {
        //     echo "Error retrieving";
        // }
        // ///////////////////////////////SECURITY SIGNOUT//////////////////////////
        // // $this->client->setStateful(false);
        // $this->client->securitySignOut();
        //dd($this->client->getLastRequest());
        /* dd($client->getLastResponse()); */
        //==============================================AIR_SELLRECOMMENDATION================================================
        // $this->client->setStateful(true);
        // $airSell = new AirSellFromRecommendationOptions([
        //     'itinerary' => [
        //         new sellItinerary([
        //             'from' => 'NBO',
        //             'to' => 'KGL',
        //             'segments' => [
        //                 new sellSegment([
        //                     'departureDate' => \DateTime::createFromFormat('Y-m-d H:i:s', '2022-04-24 11:10:00'),
        //                     'from' => 'NBO',
        //                     'to' => 'KGL',
        //                     'companyCode' => 'WB',
        //                     'flightNumber' => '453',
        //                     'bookingClass' => 'T',
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
        // // //==============================================PNR ADD MULTISEGMENTS================================================//

        // $createPnr = new PnrCreatePnrOptions([
        //     'travellers' => [
        //         new Traveller([
        //             'number' => 1,
        //             'firstName' => 'Jane',
        //             'lastName' => 'Mwakenya',
        //             'travellerType' => Traveller::TRAV_TYPE_ADULT,
        //             'dateOfBirth' => \DateTime::createFromFormat('Y-m-d', '1989-01-01'),
        //         ]),
        //     ],
        //     'actionCode' => PnrCreatePnrOptions::ACTION_NO_PROCESSING,
        //     'itinerary' => [
        //         new Itinerary([
        //             'segments' => [
        //                 new Miscellaneous([
        //                     'status ' => Segment::STATUS_CONFIRMED,
        //                     'company' => 'KQ',
        //                     'date' => \DateTime::createFromFormat('Ymd', '20220424', new \DateTimeZone('UTC')),
        //                     'cityCode' => 'NBO',
        //                     'freeText' => 'WARNING - CLASS AVAILABILITY MAY NOT BE SUFFICIENT ON CERTAIN FLIGHTS'
        //                 ]),
        //             ]
        //         ]),
        //     ],
        //     'elements' => [
        //         new Ticketing([
        //             'ticketMode' => Ticketing::TICKETMODE_OK
        //         ]),
        //         new Contact([
        //             'type' => Contact::TYPE_PHONE_MOBILE,
        //             'value' => '+254725784567'
        //         ]),
        //         new Contact([
        //             'type' => Contact::TYPE_EMAIL,
        //             'value' => 'devops.2@clifford.co.ke'
        //         ]),
        //         new ServiceRequest([
        //             'type' => 'DOCS',
        //             'status' => ServiceRequest::STATUS_HOLD_CONFIRMED,
        //             'company' => 'WB',
        //             'quantity' => 1,
        //             'freeText' => [
        //                 'P-KE-123456-KE-01JAN88-M-09DEC25-MWAKENYA-JANE'
        //             ],
        //             'references' => [
        //                 new Reference([
        //                     'type' => Reference::TYPE_PASSENGER_REQUEST,
        //                     'id' => 1,
        //                 ])
        //             ]
        //         ]),
        //     ]
        // ]);
        // $createdPnr = $this->client->pnrCreatePnr($createPnr);
        // // //==================================BookingClass===============================================================
        // $pricingResponse = $this->client->farePricePnrWithBookingClass(
        //     new FarePricePnrWithBookingClassOptions([
        //         'overrideOptions' => [
        //             PricingOptions::OVERRIDE_FARETYPE_PUB,
        //             PricingOptions::OVERRIDE_FARETYPE_UNI,
        //             PricingOptions::OVERRIDE_VALIDATING_CARRIER,
        //         ],
        //         'validatingCarrier' => 'WB',
        //     ])
        // );
        // //=================================================OFFERCREATE====================================//
        // $offerCreateResponse = $this->client->offerCreate(
        //     new OfferCreateOptions([
        //         'airRecommendations' => [
        //             new AirRecommendation([
        //                 'type' => AirRecommendation::TYPE_FARE_RECOMMENDATION_NR,
        //                 'id' => 1,
        //                 'paxReferences' => [
        //                     new PassengerDef([
        //                         'passengerTattoo' => 2,
        //                         'passengerType' => PassengerDef::ADULT_PASSENGER,
        //                     ])
        //                 ]
        //             ])
        //         ]
        //     ])
        // );

        // //==============================================PNR ADD MULTISEGMENTS================================================
        // $pnrReply1 = $this->client->pnrAddMultiElements(
        //     new PnrAddMultiElementsOptions([
        //         'actionCode' => PnrAddMultiElementsOptions::ACTION_WARNING_AT_EOT,
        //         'actionCode' => PnrAddMultiElementsOptions::ACTION_STOP_EOT_ON_SELL_ERROR,
        //         'actionCode' => PnrAddMultiElementsOptions::ACTION_END_TRANSACT_RETRIEVE, //ET: END AND RETRIEVE
        //     ]),
        // );

        // $this->client->securitySignOut();
        // dd($this->client->getLastRequest());

        //////////////////OFFER_VERIFYOFFER/////////////////////////////////
        // $this->client->setStateful(true);

        // $pnrcontent = $this->client->pnrRetrieveAndDisplay(
        //     new PnrRetrieveAndDisplayOptions([
        //         'recordLocator' => 'VCR4MR',
        //         'retrieveOption' => PnrRetrieveAndDisplayOptions::RETRIEVEOPTION_ALL
        //     ])
        // );

        // // $this->client->securitySignOut();
        // // dd($this->client->getLastRequest());

        ////////////////////VERIFY_OFFER/////////////////////////////////////
        // $this->client->setStateful(true);
        // $pnrRetrieveDisplay = $this->client->pnrRetrieveAndDisplay(
        //     new PnrRetrieveAndDisplayOptions([
        //         'recordLocator' => 'VCR4MR',
        //         'retrieveOption' => PnrRetrieveAndDisplayOptions::RETRIEVEOPTION_ALL
        //     ])
        // );
        // $offerVerifyResponse = $this->client->offerVerify(
        //     new OfferVerifyOptions([
        //         'offerReference' => 1,
        //         'segmentName' => 'AIR'
        //     ])
        // );
        // //==============================================PNR ADD MULTISEGMENTS================================================
        // $pnrReply2 = $this->client->pnrAddMultiElements(
        //     new PnrAddMultiElementsOptions([
        //         'actionCode' => PnrAddMultiElementsOptions::ACTION_WARNING_AT_EOT,
        //         'actionCode' => PnrAddMultiElementsOptions::ACTION_STOP_EOT_ON_SELL_ERROR,
        //         'actionCode' => PnrAddMultiElementsOptions::ACTION_END_TRANSACT_RETRIEVE,
        //     ]),
        // );
        // $this->client->securitySignOut();
        // dd($this->client->getLastRequest());


        //////////////////////////////CONFIRM_OFFER//////////////////////////////
        $this->client->setStateful(true);

        $pnrRetrieveDisplay1 = $this->client->pnrRetrieveAndDisplay(
            new PnrRetrieveAndDisplayOptions([
                'recordLocator' => 'VCR4MR',
                'retrieveOption' => PnrRetrieveAndDisplayOptions::RETRIEVEOPTION_ALL
            ])
        );

        $offerConfirmResponse = $this->client->offerConfirmAir(
            new OfferConfirmAirOptions([
                'tattooNumber' => 1
            ])
        );

        /////////////////////////////FOP_CREATEFORMOFPAYMENT///////////////////////////
        $option = new FopCreateFopOptions([
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
                                'expiryDate' => '0922',
                                'securityId' => '123'
                            ])
                        ]),
                    ]
                ])
            ]
        ]);
        $fopResponse = $this->client->fopCreateFormOfPayment($option);

        //==============================================PNR ADD MULTISEGMENTS================================================
        $pnrReply3 = $this->client->pnrAddMultiElements(
            new PnrAddMultiElementsOptions([
                'actionCode' => PnrAddMultiElementsOptions::ACTION_WARNING_AT_EOT,
                'actionCode' => PnrAddMultiElementsOptions::ACTION_STOP_EOT_ON_SELL_ERROR,
                'actionCode' => PnrAddMultiElementsOptions::ACTION_END_TRANSACT,
            ]),
        );
        $this->client->securitySignOut();

        //=============================================TICKET==============================//
        $this->client->setStateful(true);
        $pnrcontent2 = $this->client->pnrRetrieve(
            new PnrRetrieveOptions(['recordLocator' => 'VCR4MR'])
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
        dd($this->client->getLastRequest());
    }
}