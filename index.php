<?php
    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');
    require_once __DIR__."/vendor/autoload.php";

    use Monolog\Handler\StreamHandler;
    use Monolog\Logger;
    $base_url = "/api/v1/chat";
    $pun_webhook = "/api/v1/pun/room";

    $loggingMethod = function($requestObject, $responseObject) {
        $logger = new Logger('logger');
        $logger->pushHandler(new StreamHandler('./logs/'.date('Ymd').'.log'));
        $logger->info("[".$requestObject->url."]");
        $logger->info("request-query=========================================================================");
        $logger->info(json_encode($requestObject->query));
        $logger->info("request-body==========================================================================");
        $logger->info(json_encode($requestObject->data));
        $logger->info("response==============================================================================");
        $logger->info(json_encode($responseObject));
        $logger->info("======================================================================================");
    };
    
    $schemeMethod = function($requestObject) {
        $fileName = str_replace('/', '.', $requestObject->url);
        $schemeFileExsist = file_exists("./scheme/$fileName.json");
        
        if (!$schemeFileExsist) {
            $output = fopen("./scheme/$fileName.json", "a");
            fwrite($output, json_encode($requestObject->data));
            fclose($output);
        }
    };

    $chatWebhookCallBack = function($loggingMethod, $schemeMethod) {
        $request = Flight::request();
        $base_response = array("ResultCode"=>0, "DebugMessage"=>"OK");
        Flight::json($base_response);

        $loggingMethod($request, $base_response);
        $schemeMethod($request);
    };

    $punWebhookCallBack = function($loggingMethod, $schemeMethod) {
        $request = Flight::request();      
        $base_response = array("ResultCode"=>0, "ErrorCode"=>0);        
        Flight::json($base_response);
        
        $loggingMethod($request, $base_response);
        $schemeMethod($request);
    };
    Flight::route("POST /api/v1/chat/*",        $chatWebhookCallBack($loggingMethod, $schemeMethod));
    Flight::route("POST /api/v1/pun/room/*",    $punWebhookCallBack($loggingMethod, $schemeMethod));

    Flight::route('*', function(){
        Flight::json(array("bye"=>"guys"));
    });

    Flight::start();