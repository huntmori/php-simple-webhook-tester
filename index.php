<?php 
require_once __DIR__."/vendor/autoload.php";

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
$base_url = "/api/v1/chat/channel";

$baseFunction = function() {
    $request = Flight::request();
    $logger = new Logger('logger');
    $logger->pushHandler(new StreamHandler('./logs/'.date('Ymd').'.log'));
    $base_url = "/api/v1/chat/channel";
    $logger->info("[".$request->url."]");
    $logger->info("request-query=========================================================================");
    $logger->info(json_encode($request->query));
    $logger->info("request-body==========================================================================");
    $logger->info(json_encode($request->data));
    
    $base_response = array("ResultCode"=>0, "DebugMessage"=>"OK", "Request"=>(array)($request));
    $logger->info("response==============================================================================");
    $logger->info(json_encode($base_response));
    $logger->info("======================================================================================");
    Flight::json($base_response);
};

Flight::route("POST $base_url/create", $baseFunction);

Flight::route("POST $base_url/destroy", $baseFunction);

Flight::route("POST $base_url/subscribe", $baseFunction);

Flight::route("POST $base_url/unsubscribe", $baseFunction);

Flight::route("POST $base_url/publish-message", $baseFunction);


Flight::start();
