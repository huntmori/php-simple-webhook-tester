<?php
    require_once __DIR__."/vendor/autoload.php";

    use Monolog\Handler\StreamHandler;
    use Monolog\Logger;
	use flight\net\Request;
	use flight\net\Response;

    class Controller {
		function makeDirectoryIfNotExist(string $path):void {
			if (is_dir($path) === false ) {
				mkdir($path, 0755, false);
			}
		}

		function loggingMethod(Request $requestObject, array $responseObject): void {
			
			$this->makeDirectoryIfNotExist('./logs');

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

		function schemeMethod(Request $requestObject): void {
			$this->makeDirectoryIfNotExist('./scheme');

			$fileName = str_replace('/', '.', $requestObject->url);
			$schemeFileExsist = file_exists("./scheme/$fileName.json");

			if (!$schemeFileExsist) {
				$output = fopen("./scheme/$fileName.json", "w+");
				fwrite($output, json_encode($requestObject->data));
				fclose($output);
			}
		}

		function chatWebhookCallBack(): void {
			$request = Flight::request();
			$baseResponse = array("ResultCode"=>0, "DebugMessage"=>"OK");
			if ($request->url == "/api/v1/chat/publish-message") {
				$baseResponse["data"] = $request->data['Message'];
			}
			Flight::json($baseResponse);

			$this->loggingMethod($request, $baseResponse);
			$this->schemeMethod($request);
		}

		function punWebhookCallBack(): void {
			$request = Flight::request();      
			$baseResponse = array("ResultCode"=>0, "ErrorCode"=>0);        
			Flight::json($baseResponse);

			$this->loggingMethod($request, $baseResponse);
			$this->schemeMethod($request);
		}

		function authTest(): void {
			$request = Flight::request();

			$response = array("ResultCode"=>1, "UserId"=>1, "Request"=>$request);
			Flight::json($response);
			$this->loggingMethod($request, $response);
			$this->schemeMethod($request);
		}
	}

    $controller = new Controller();
	Flight::route("POST /api/v1/chat/user",     array($controller, 'authTest'));
    Flight::route("POST /api/v1/chat/*",        array($controller, 'chatWebhookCallBack'));
    Flight::route("POST /api/v1/pun/room/*",    array($controller, 'punWebhookCallBack'));
	Flight::route("POST /api/v1/user", function() {
		
	});

    Flight::route('*', function(){
        Flight::json(array("bye"=>"guys"));
    });

    Flight::start();