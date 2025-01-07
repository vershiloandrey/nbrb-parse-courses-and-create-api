<?php

class Rate
{
	private $db;
	private $rates = array();
	
	function __construct()
	{
		$this->db = new DB();
	}

	// Функция отображения курсов за указанную дату, с пагинацией 
	function read($date = '', $limit = 100, $offset = 0)
	{
		$WHERE = '';

		if ($date!=''){				
			$WHERE = "
				WHERE date >= '".$date."T00:00:00' AND date <= '".$date."T23:59:59'
			";
		}

		$PAGINATION = "
			LIMIT ".$limit."
			OFFSET ".$offset;

		// подсчет общего количества строк
		$query = "SELECT count(*) as cnt FROM rates $WHERE";
		$row = $this->db->fetchAll($query);
		$total = $row['cnt'];


		$query = "SELECT * FROM rates $WHERE $PAGINATION";
		foreach($this->db->query($query) as $row){
			$this->rates[] = $row;
		}

		if (!empty($this->rates) && $total != 0){
			return array('data' => $this->rates, 'limit' => $limit,  'offset'=> $offset, 'total' => $total, 'status' => 'ok');
		} elseif ($total == 0) {
			return array('message' => 'Courses are not available',  'status' => 'error');
		} elseif (empty($this->rates) && $total != 0) {
			return array('message' => 'Incorrect limit and offset values',  'status' => 'error');
		}

	}

	// Функция отображения всех курсов, с группировкой по дате, с пагинацией 
	function readAll($limit = 100, $offset = 0)
	{

		$WHERE = ' ORDER BY `rates`.`date` ASC';

		$PAGINATION = "
			LIMIT ".$limit."
			OFFSET ".$offset;

		// подсчет общего количества строк
		$query = "SELECT count(*) as cnt FROM rates $WHERE";
		$row = $this->db->fetchAll($query);
		$total = $row['cnt'];


		$query = "SELECT * FROM rates $WHERE $PAGINATION";
		foreach($this->db->query($query) as $row){
			$this->rates[$row['date']][] = $row;
		}

		if (!empty($this->rates) && $total != 0){
			return array('data' => $this->rates, 'limit' => $limit,  'offset'=> $offset, 'total' => $total, 'status' => 'ok');
		} elseif ($total == 0) {
			return array('message' => 'Courses are not available',  'status' => 'error');
		} elseif (empty($this->rates) && $total != 0) {
			return array('message' => 'Incorrect limit and offset values',  'status' => 'error');
		}
	}
}


?>