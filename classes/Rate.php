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

	// Функция импорта с сайта НБРБ
	function import()
	{

		$curl = curl_init('https://api.nbrb.by/exrates/rates?periodicity=0');
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);
		curl_close($curl);

		$rates  = json_decode($response);

		$index_change = 0; // индикатор изменений

		if ($rates) {
			if (is_array($rates)) {
				
				$string_inserts = "";
				$index = 0;

				foreach ($rates as $rate)
				{
					$index++;
					if (!$rate->Cur_Scale)
						$rate->Cur_Scale = 1;
					$scale = htmlspecialchars($rate->Cur_Scale, ENT_QUOTES);
					$rate_id = htmlspecialchars($rate->Cur_ID, ENT_QUOTES);
					$abbreviation = htmlspecialchars($rate->Cur_Abbreviation, ENT_QUOTES);
					$date = htmlspecialchars($rate->Date, ENT_QUOTES);
					$name = htmlspecialchars($rate->Cur_Name, ENT_QUOTES);
					$officialRate = htmlspecialchars(str_replace(',', '.', $rate->Cur_OfficialRate), ENT_QUOTES);

					if ($string_inserts != "") $string_inserts .= ",";
					$string_inserts .= "('".$rate_id."','".$date."','".$abbreviation."','".$scale."','".$name."','".$officialRate."')";

					if ($index == MAX_COUNT_INSERTS){
						if ($string_inserts != ''){
							$sql = "INSERT INTO `rates`( `rate_id`,`date`, `abbreviation`, `scale`, `name`, `officialRate`) VALUES " . $string_inserts . " ON DUPLICATE KEY UPDATE `scale`=VALUES(`scale`),`officialRate`=VALUES(`officialRate`)";
							$result = $this->db->exec($sql);
							$index_change += $result;
						}
						$index = 0;
						$string_inserts = '';
					}
				}

				if ($string_inserts != ''){
					$sql = "INSERT INTO `rates`(`rate_id`,`date`, `abbreviation`, `scale`, `name`, `officialRate`) VALUES " . $string_inserts . " ON DUPLICATE KEY UPDATE `scale`=VALUES(`scale`),`officialRate`=VALUES(`officialRate`)";
					$result = $this->db->exec($sql);
					$index_change += $result;

				}
			}
		}

		if ($index_change == 0)
			return "No change.";
		else
			return "Updated.";
	}
}


?>