<?php
//require_once __DIR__ . "/vendor/autoload.php";
require_once 'MongoClass.php';
require_once 'responseClass.php';
$mongo          = new mongoClass();
$response_class = new responseClass();
$response       = $response_class->processRequest();
$count          = isset($response[ 'count' ]) ? $response[ 'count' ] : count($response);
$result         = isset($response[ 'result' ]) ? $response[ 'result' ] : $response;
$success        = isset($response[ 'success' ]) ? $response[ 'success' ] : true;
$status_code    = isset($response[ 'status_code' ]) ? $response[ 'status_code' ] : 200;
unset($result[ 'count' ], $result[ 'success' ], $result[ 'status_code' ], $result[ 'result' ]);
echo $response_class->toJson(
	[ 'success' => $success, 'count' => $count, 'result' => $result ], $status_code
);
