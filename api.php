<?php

require_once('autoload.php');

$mode = htmlspecialchars($_GET['mode'], ENT_QUOTES);

switch ($mode) {
	case 'rates':
		$rate = new Rate();

		$date = '';
		$limit = DEFINE_LIMIT;
		$offset = DEFINE_OFFSET;

		if (!empty($_GET)){
			if (isset($_GET['date'])) $date = htmlspecialchars($_GET['date'], ENT_QUOTES);
		}

		if (!empty($_POST)){
			if (isset($_POST['limit'])) $limit = htmlspecialchars($_POST['limit'], ENT_QUOTES);
			if (isset($_POST['offset'])) $offset = htmlspecialchars($_POST['offset'], ENT_QUOTES);
		}

		if ($date != '')
			//вывод для конкретного числа
			$result = $rate->read($date, $limit, $offset);
		else
			//вывод всех, по датам
			$result = $rate->readAll($limit, $offset);

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($result);
		break;
	
	default:
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('message' => 'Invalid request. Read the documentation.',  'status' => 'error'));
		break;
}



?>