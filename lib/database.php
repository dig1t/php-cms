<?php
defined('ROOT') OR exit;

class Database {
  private $base;
	private $connection = false;
	private $query;
  
  public $_database;
  
	public function __construct($database = null) {
		$this->connect($database);
	}
  
	private function connect($database = null) {
		if ($this->connection) {
			$this->close();
		}
		
		try {
			if (!isset($database)) {
				$database = CONFIG['database']['default'];
			}
      
			$instance = new PDO(
				'mysql:host='.CONFIG['database']['host'].';dbname='.$database.';charset=utf8',
				CONFIG['database']['user'],
        CONFIG['database']['password'],
        array(
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        )
			);
      $instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      
      $this->base = $instance;
			$this->connection = true;
			$this->_database = $database;
		} catch (PDOException $e) {
			print 'Error: '.$e->getMessage();
			echo 'E300';
			die();
		}
	}
	
	public function close() {
		$this->base = null;
	}
	
	public function switchDB($database) {
		$this->connect($database);
	}
	
	public function prepare($sql) {
		if (!$this->connection) {
			$this->connect();
		}
		
		if ($this->base) {
			$sql = trim($sql);
			$q = $this->base->prepare($sql);
			//$q->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			return $q;
		}
	}
	
	public function execute($sql, $param = array()) {
		if (!$this->connection) {
			$this->connect();
		}
		
		$sql = trim($sql);
		
		try {
			$q = $this->prepare($sql);
			$q->execute($param);
			//print_r($q->errorInfo());
			return $q;
		} catch(PDOException $e) {
			echo 'E301';
		}
	}
	
	public function fetch($sql, $param = array(), $mode = PDO::FETCH_ASSOC) {
		$query = $this->execute($sql, $param);
		return $query->fetch($mode);
	}
	
	public function fetchAll($sql, $param = array(), $mode = PDO::FETCH_ASSOC) {
		$query = $this->execute($sql, $param);
		return $query->fetchAll($mode);
	}
	
	public function rowCount($sql, $param) {
		$query = $this->execute($sql, $param);
		return $query->rowCount();
	}
}
?>