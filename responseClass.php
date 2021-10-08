<?php

class responseClass
{
	public $inputs;
	public $collection;
	public $method;
	public $requests;
	
	/**
	 * @var array
	 */
	public function __construct()
	{
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");
		header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
		header("Access-Control-Max-Age: 3600");
		header(
			"Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With"
		);
		$this->inputs     = (array) $this->toArray(file_get_contents('php://input'));
		$this->collection = isset($this->inputs[ 'collection' ]) ? $this->inputs[ 'collection' ] :
			'sample';
		$this->method     = $this->inputs[ '_method' ];
		//		$this->method = $_SERVER[ "REQUEST_METHOD" ];
	}
	
	/**
	 * @param string $json
	 *
	 * @return mixed
	 */
	public function toArray($json = '')
	{
		return json_decode($json, true);
	}
	
	/**
	 * @param array $data
	 *
	 * @return false|string
	 */
	public function toJson($data = [], $status_code = 200)
	{
		http_response_code($status_code);
		
		return json_encode($data);
	}
	
	public function processRequest()
	{
		switch ( $this->method ) {
			case 'insert' :
				return $this->processPost();
				break;
			case 'delete' :
				return $this->processDelete();
				break;
			case 'update':
				return $this->processPut();
				break;
			case 'find':
			default :
				return $this->processGet();
				break;
		}
	}
	
	public function processPost()
	{
		$mongo                     = new MongoClass();
		$insert                    = $mongo->insert($this->inputs[ 'data' ], $this->collection);
		$response[ 'success' ]     = $insert;
		$response[ 'result' ]      = null;
		$response[ 'count' ]       = 1;
		$response[ 'status_code' ] = 500;
		if ( $insert ){
			$response[ 'result' ]      = null;
			$response[ 'count' ]       = 1;
			$response[ 'status_code' ] = 201;
		}
		
		return $response;
	}
	
	public function processDelete()
	{
		$mongo = new MongoClass();
		
		return $mongo->delete($this->requests, $this->collection);
	}
	
	public function processPut()
	{
		if ( ! isset($this->inputs[ 'where' ]) ){
			return $this->responseError(
				[ 'error' => [ 'code' => 'whereRequired', 'message' => 'where is required' ] ]
			);
		}
		if ( ! isset($this->inputs[ 'data' ]) ){
			return $this->responseError(
				[ 'error' => [ 'code' => 'dataRequired', 'message' => 'data is required' ] ]
			);
		}
		$where = $this->inputs[ 'where' ];
		$data  = $this->inputs[ 'data' ];
		//		return [$where,$data];
		$mongo  = new MongoClass();
		$update = $mongo->update($data, $where, $this->collection, true, false);
		$result = $this->responseError(null);
		if ( $count = $update->getModifiedCount() ){
			$result = $this->responseSuccess(null, $count);
		}
		
		return $result;
	}
	
	final public function responseError($data = null, $count = 0, $status_code = 400)
	{
		return [
			'success'     => false,
			'result'      => $data,
			'count'       => $count,
			'status_code' => $status_code,
		];
	}
	
	final public function responseSuccess($data = null, $count = 0, $status_code = 200)
	{
		return [
			'success'     => true,
			'result'      => $data,
			'count'       => $count,
			'status_code' => $status_code,
		];
	}
	
	public function processGet()
	{
		$mongo    = new MongoClass();
		$response = $mongo->read($this->inputs, $this->collection);
		$result   = $this->responseError($response, 0, 404);
		if ( $response ){
			$result = $this->responseSuccess($response, count($response), 200);
		}
		
		return $result;
	}
	
	public function processOptions()
	{
		return null;
	}
	
}
