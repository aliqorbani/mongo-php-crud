<?php

use MongoDB\Client;

class mongoClass
{
	//Database configuration
	private $db_host;
	private $db_port;
	private $connection;
	private $db_name;
	
	public function __construct($host = 'localhost', $port = '27017', $db_name = 'moortak_db')
	{
		$this->setDbHost($host);
		$this->setDbPort($port);
		$this->setDbName($db_name);
		$this->setConnection();
	}
	
	public function insertOne($data = [], $collection = '')
	{
		try {
			$client = $this->getConnection()->selectDatabase($this->db_name)->selectCollection(
				$collection
			);
			
			return $client->insertOne($data);
		} catch ( Error $e ) {
			return $e->getMessage();
		}
	}
	
	/**
	 * @return \MongoDB\Client
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * @return \MongoDB\Client
	 */
	public function setConnection()
	{
		$host             = $this->getDbHost() . ':' . $this->getDbPort();
		$this->connection = (new Client("mongodb://{$host}"));
	}
	
	/**
	 * @param array  $data
	 * @param string $collection
	 *
	 * @return \MongoDB\InsertManyResult|string
	 */
	public function insertMany($data = [], $collection = '')
	{
		try {
			$client = $this->getConnection()->selectDatabase($this->getDbName())->selectCollection(
				$collection
			);
			
			return $client->insertMany($data);
		} catch ( Error $e ) {
			return $e->getMessage();
		}
	}
	
	/**
	 * @return string
	 */
	public function getDbName()
	{
		return $this->db_name;
	}
	
	/**
	 * @param string $db_name
	 */
	public function setDbName($db_name)
	{
		$this->db_name = $db_name;
	}
	
	public function updateOne($data = [], $condition = [], $collection = '')
	{
		try {
			$client = $this->getConnection()->selectDatabase($this->getDbName())->selectCollection(
				$collection
			);
			
			return $client->updateOne($condition, [ '$set' => $data ]);
		} catch ( Error $e ) {
			return $e->getMessage();
		}
	}
	
	public function updateMany($data = [], $condition = [], $collection = '')
	{
		try {
			$client = $this->getConnection()->selectDatabase($this->getDbName())->selectCollection(
				$collection
			);
			
			return $client->updateMany($condition, [ '$set' => $data ]);
		} catch ( Error $e ) {
			return $e->getMessage();
		}
	}
	
	public function updateOrCreate($data = [], $condition = [], $collection = '')
	{
		try {
			$client = $this->getConnection()->selectDatabase($this->getDbName())->selectCollection(
				$collection
			);
			
			return $client->updateOne($condition, [ '$set' => $data ], [ 'upsert' => true ]);
		} catch ( Error $e ) {
			return $e->getMessage();
		}
	}
	
	public function replaceOne($data = [], $condition = [], $collection = '')
	{
		try {
			$client = $this->getConnection()->selectDatabase($this->getDbName())->selectCollection(
				$collection
			);
			
			return $client->replaceOne($condition, [ '$set' => $data ]);
		} catch ( Error $e ) {
			return $e->getMessage();
		}
	}
	
	public function deleteOne($condition = [], $collection = '')
	{
		try {
			$client = $this->getConnection()->selectDatabase($this->getDbName())->selectCollection(
				$collection
			);
			
			return $client->deleteOne($condition);
		} catch ( Error $e ) {
			return $e->getMessage();
		}
	}
	
	public function deleteMany($condition = [], $collection = '')
	{
		try {
			$client = $this->getConnection()->selectDatabase($this->getDbName())->selectCollection(
				$collection
			);
			
			return $client->deleteMany($condition);
		} catch ( Error $e ) {
			return $e->getMessage();
		}
	}
	
	public function read($inputs = [], $collection = '', $single = false)
	{
		$filters           = $inputs[ 'filters' ] ?? [];
		$sort              = $inputs[ 'sort' ] ?? [];
		$limit             = $inputs[ 'limit' ] ?? 100;
		$collection_filter = [];
		if ( isset($filters) ){
			foreach ( $filters as $filter ) {
				$key   = $filter[ 'name' ];
				$value = $filter[ 'value' ];
				switch ( $filter[ 'operation' ] ) {
					case 'equalsTo' :
					case '=' :
						$operation = '$eq';
						break;
					case 'notEqualsTo' :
					case '!=' :
						$operation = '$ne';
						break;
					case 'graterThan' :
					case '>' :
						$operation = '$gt';
						break;
					case 'lessThan' :
					case '<' :
						$operation = '$lt';
						break;
					case 'greaterThanEquals' :
					case '>=' :
						$operation = '$gte';
						break;
					case 'lessThanEquals' :
					case '<=' :
						$operation = '$lte';
						break;
					default :
						$operation = '$eq';
				}
				if ( is_numeric($value) ){
					$value = (int) $value;
				}
				$collection_filter[ $key ] = [ $operation => $value ];
			}
		}
		foreach ( $sort as $option ) {
			$value = '-1';
			if ( $option[ 'operation' ] === 'asc' ){
				$value = '1';
			}
			if ( $option[ 'operation' ] === 'desc' ){
				$value = '-1';
			}
			$options[ 'sort' ][ $option[ 'field' ] ] = (int) $value;
		}
		$options[ 'projection' ] = [ '_id' => 0 ];
		$options[ 'limit' ]      = $limit;
		//		return $options;
		//		return $collection_filter;
		$client = $this->getConnection()->selectDatabase($this->getDbName())->selectCollection(
			$collection
		);
		$result = $client->find($collection_filter, $options)->toArray();
		if ( $single ){
			$result = $client->findOne($collection_filter, $options);
		}
		
		return $result;
	}
	
	/**
	 * @return string
	 */
	public function getDbPort()
	{
		return $this->db_port;
	}
	
	/**
	 * @param string $db_port
	 */
	public function setDbPort($db_port)
	{
		$this->db_port = $db_port;
	}
	
	/**
	 * @return string
	 */
	protected function getDbHost()
	{
		return $this->db_host;
	}
	
	/**
	 * @param string $db_host
	 */
	protected function setDbHost($db_host)
	{
		$this->db_host = $db_host;
	}
	
}
