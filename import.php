<?php

require_once('autoload.php');

$db = new DB();

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
					$result = $db->exec($sql);
					$index_change += $result;
				}
				$index = 0;
				$string_inserts = '';
			}
		}

		if ($string_inserts != ''){
			$sql = "INSERT INTO `rates`(`rate_id`,`date`, `abbreviation`, `scale`, `name`, `officialRate`) VALUES " . $string_inserts . " ON DUPLICATE KEY UPDATE `scale`=VALUES(`scale`),`officialRate`=VALUES(`officialRate`)";
			$result = $db->exec($sql);
			$index_change += $result;

		}
	}
}

if ($index_change == 0)
	echo "Без изменений";
else
	echo "Ок";

?>