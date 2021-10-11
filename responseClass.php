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
				return $this->insert(false);
				break;
			case 'bulk-insert' :
				return $this->insert(true);
				break;
			case 'delete' :
				return $this->delete(false);
				break;
			case 'bulk-delete' :
				return $this->delete(true);
				break;
			case 'update':
				return $this->update(false);
				break;
			case 'bulk-update':
				return $this->update(true);
				break;
			case 'first':
				return $this->first();
				break;
			case 'find':
			default :
				return $this->find();
				break;
		}
	}
	
	public function insert($multiple = false)
	{
		$mongo = new MongoClass();
		if ( $multiple ){
			$insert = $mongo->insertMany($this->inputs[ 'data' ], $this->collection);
			
			return $this->responseSuccess(null, $insert->getInsertedCount());
		}
		$insert = $mongo->insertOne($this->inputs[ 'data' ], $this->collection);
		
		return $this->responseSuccess(null, $insert->getInsertedCount());
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
	
	public function delete($multiple = true)
	{
		$mongo = new MongoClass();
		if ( ! isset($this->inputs[ 'where' ]) ){
			return $this->responseError(
				[ 'error' => [ 'code' => 'whereRequired', 'message' => 'where is required' ] ]
			);
		}
		if ( $multiple ){
			$update = $mongo->deleteOne($this->inputs[ 'where' ], $this->collection);
		}
		else {
			$update = $mongo->deleteMany($this->inputs[ 'where' ], $this->collection);
		}
		$result = $this->responseError(null);
		if ( $update->isAcknowledged() ){
			$result = $this->responseSuccess(null, $update->getDeletedCount());
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
	
	public function update($multiple = true)
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
		$mongo = new MongoClass();
		if ( $multiple ){
			$update = $mongo->updateMany($data, $where, $this->collection);
			if ( $update->isAcknowledged() ){
				return $this->responseSuccess(null, $update->getModifiedCount());
			}
			
			return $this->responseError($update);
		}
		$update = $mongo->updateOne($data, $where, $this->collection);
		if ( $update->isAcknowledged() ){
			return $this->responseSuccess([], $update->getModifiedCount());
		}
		
		return $this->responseError($update);
	}
	
	public function find()
	{
		$mongo    = new MongoClass();
		$response = $mongo->read($this->inputs, $this->collection);
		if ( $response ){
			return $this->responseSuccess($response, count($response), 200);
		}
		
		return $this->responseError($response, 0, 404);
	}
	
	public function first()
	{
		$mongo    = new MongoClass();
		$response = $mongo->read($this->inputs, $this->collection, true);
		if ( $response ){
			return $this->responseSuccess($response, 1, 200);
		}
		
		return $this->responseError($response, 0, 404);
	}
	
	public function processOptions()
	{
		return null;
	}
	
}
