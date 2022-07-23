<?php
    // error_reporting(E_ALL);
    // ini_set('display_errors', '1');
    require_once __DIR__."/vendor/autoload.php";

    use Monolog\Handler\StreamHandler;
    use Monolog\Logger;

    class Controller {
		function loggingMethod($requestObject, $responseObject) {
			if (is_dir('./logs') === false ) {
				mkdir('./logs', 0755, false);
			}

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
		}

		 function schemeMethod($requestObject) {
			if (is_dir('./scheme') === false ) {
				mkdir('./scheme', 0755, false);
			}

			$fileName = str_replace('/', '.', $requestObject->url);
			$schemeFileExsist = file_exists("./scheme/$fileName.json");

			if (!$schemeFileExsist) {
				$output = fopen("./scheme/$fileName.json", "a");
				fwrite($output, json_encode($requestObject->data));
				fclose($output);
			}
		}

		function chatWebhookCallBack() {
			$request = Flight::request();
			$base_response = array("ResultCode"=>0, "DebugMessage"=>"OK");
			Flight::json($base_response);

			$this->loggingMethod($request, $base_response);
			$this->schemeMethod($request);
		}

		function punWebhookCallBack() {
			$request = Flight::request();      
			$base_response = array("ResultCode"=>0, "ErrorCode"=>0);        
			Flight::json($base_response);

			$this->loggingMethod($request, $base_response);
			$this->schemeMethod($request);
		}
	}

    $controller = new Controller();
    Flight::route("POST /api/v1/chat/*",        array($controller, 'chatWebhookCallBack'));
    Flight::route("POST /api/v1/pun/room/*",    array($controller, 'punWebhookCallBack'));

    Flight::route('*', function(){
        Flight::json(array("bye"=>"guys"));
    });

    Flight::start();