<?php
	session_start();

	if (isset($_SESSION['key']) === false) {
		header('Location: map');
		die();
	}

	if (isset($_POST['name']) === false ||
		isset($_FILES['photo']) === false ||
		isset($_POST['remark']) === false ||
		isset($_POST['latitude']) === false ||
		isset($_POST['longitude']) === false) {
		header('Location: map');
		die();
	} 

	date_default_timezone_set('Asia/Taipei');

	$time = date('YmdHis');
	$json = file_get_contents('data.json');
	$id = uniqid();
	file_put_contents("backup/$time.add.$id.json", $json);
	$data = json_decode($json, true);
	$ref =& $data[$id];

	$ref = array(
		'name' => trim($_POST['name']),
		'latitude' => floatval($_POST['latitude']),
		'longitude' => floatval($_POST['longitude'])
	);

	$remark = trim($_POST['remark']);

	if ($remark !== '') {
		$ref['remark'] = $remark;
	}

	$photo = $_FILES['photo'];

	if ($photo['error'] === 0) {
		switch($photo['type']) {
			case 'image/jpeg':
				$suffix = '.jpg';
				break;
			case 'image/png':
				$suffix = '.png';
				break;
			default:
				$suffix = false;
		}

		if ($suffix !== false) {
			$name = uniqid() . $suffix;
			$ref['photo'] = $name;
			move_uploaded_file($photo['tmp_name'], 'photos/' . $name);
		}
	}

	file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT));

	header('Location: map?id=' . $id);
?>
