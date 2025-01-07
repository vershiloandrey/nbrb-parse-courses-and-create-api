<?php

require_once('classes/DB.php');

$db = new DB();

/* 
Для запроса с датой - достаточно отправить дату гет переменной.
Можно использовать limit и offset.
По умолчанию limit=100, offset=0
*/
$curl = curl_init('http://localhost/api/rates?date=2025-01-07'); 	

/*  Для запроса всех дат использовать этот запрос (без даты).
Можно использовать limit и offset.
По умолчанию limit=100, offset=0
*/
//$curl = curl_init('https://localhost/api/rates'); 				
$array = array(
	// 'limit'    => '10',
	// 'offset' => '0'
);	

curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($array, '', '&'));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($curl);
curl_close($curl);

header('Content-Type: application/json; charset=utf-8');
echo $response;

?>