<?php

class mongoClass
{
	//Database configuration
	private $db_host;
	private $db_port;
	private $connection;
	private $db_name;
	
	public function __construct()
	{
		$this->setDbHost('localhost');
		$this->setDbPort('27017');
		$this->setDbName('moortak_db');
		$this->setConnection();
	}
	
	/**
	 * @return string
	 */
	public function getDbHost()
	{
		return $this->db_host;
	}
	
	/**
	 * @param string $db_host
	 */
	public function setDbHost($db_host)
	{
		$this->db_host = $db_host;
	}
	
	/**
	 * @param array  $data
	 * @param string $collection
	 *
	 * @return int
	 */
	public function insert($data = [], $collection = '')
	{
		$bulk = new MongoDB\Driver\BulkWrite();
		$bulk->insert($data);
		try {
			return $this->getConnection()->executeBulkWrite(
				"{$this->getDbName()}.{$collection}", $bulk
			)->isAcknowledged();
		} catch ( \MongoDB\Driver\Exception\Exception $exception ) {
			return $exception->getMessage();
		}
	}
	
	/**
	 * @return \MongoDB\Driver\Manager
	 */
	public function getConnection()
	{
		return $this->connection;
	}
	
	/**
	 * @return string
	 */
	public function setConnection()
	{
		try {
			//			return (new MongoDB\Client())->selectDatabase($this->getDbName());
			$host = $this->getDbHost() . ':' . $this->getDbPort();
			
			return $this->connection = new MongoDB\Driver\Manager('mongodb://' . $host);
		} catch ( MongoDB\Driver\Exception\Exception $e ) {
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
	
	/**
	 * @param array  $new_data
	 * @param array  $condition
	 * @param string $collection
	 * @param bool   $multiple
	 * @param bool   $upsert
	 *
	 * @return string
	 */
	public function update($new_data = [], $condition = [], $collection = '', $multiple = true, $upsert = false)
	{
		$bulk = new MongoDB\Driver\BulkWrite();
		$bulk->update(
			$condition,
			[ [ '$set' => $new_data, ] ],
			[ 'multiple' => $multiple, 'upsert' => $upsert ]
		);
		try {
			return $this->getConnection()->executeBulkWrite(
				"{$this->getDbName()}.{$collection}", $bulk
			);
		} catch ( \MongoDB\Driver\Exception\Exception $exception ) {
			return $exception->getMessage();
		}
	}
	
	/**
	 * @param array  $condition
	 * @param string $collection
	 * @param int    $limit
	 *
	 * @return string
	 */
	public function delete($condition = [], $collection = '', $limit = 1)
	{
		$bulk = new MongoDB\Driver\BulkWrite();
		$bulk->delete($condition, [ 'limit' => (int) $limit ]);
		try {
			return $this->getConnection()->executeBulkWrite(
				"{$this->getDbName()}.{$collection}", $bulk
			);
		} catch ( \MongoDB\Driver\Exception\Exception $exception ) {
			return $exception->getMessage();
		}
	}
	
	/**
	 * @param array  $inputs
	 * @param string $collection
	 *
	 * @return array
	 * @throws \MongoDB\Driver\Exception\Exception
	 */
	public function read($inputs = [], $collection = '')
	{
		$filters           = isset($inputs[ 'filters' ]) ? $inputs[ 'filters' ] : [];
		$sort              = isset($inputs[ 'sort' ]) ? $inputs[ 'sort' ] : [];
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
		//		return $options;
		//		return $collection_filter;
		$read    = new MongoDB\Driver\Query($collection_filter, $options);
		$execute = "{$this->getDbName()}.{$collection}";
		$cursor  = $this->getConnection()->executeQuery($execute, $read);
		$result  = [];
		foreach ( $cursor as $document ) {
			$result[] = $document;
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
	
}
