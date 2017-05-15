<?php
	session_start();

	if (isset($_SESSION['key']) === false) {
		header('Location: map');
		die();
	}

	if (isset($_GET['id']) === false) {
		header('Location: map');
		die();
	}

	$id = $_GET['id'];
	$json = file_get_contents('data.json');
	$data = json_decode($json, true);

	if (isset($data[$id]) === false) {
		header('Location: map');
		die();
	}

	date_default_timezone_set('Asia/Taipei');
	$time = date('YmdHis');
	file_put_contents("backup/$time.delete.$id.json", $json);

	unset($data[$id]);
	file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT));

	header('Location: ' . (isset($_SERVER['HTTP_REFERER']) === true ? $_SERVER['HTTP_REFERER'] : 'map'));
?>
