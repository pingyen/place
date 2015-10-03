<?php
	if (isset($_POST['id']) === false ||
		isset($_POST['address']) === false ||
		isset($_FILES['photo']) === false ||
		isset($_POST['remark']) === false ||
		isset($_POST['latitude']) === false ||
		isset($_POST['longitude']) === false) {
		header('Location: map');
		die();
	}

	$id = $_POST['id'];
	$json = file_get_contents('data.json');
	$data = json_decode($json, true);

	if (isset($data[$id]) === false) {
		header('Location: map');
		die();
	}

	date_default_timezone_set('Asia/Taipei');
	$time = date('YmdHis');
	file_put_contents("backup/$time.modify.$id.json", $json);
	$ref =& $data[$id];

	$ref['address'] = trim($_POST['address']);
	$ref['latitude'] = floatval($_POST['latitude']);
	$ref['longitude'] = floatval($_POST['longitude']);

	$remark = trim($_POST['remark']);

	if ($remark === '') {
		unset($ref['remark']);
	}
	else {
		$ref['remark'] = $remark;
	}

	if (isset($_POST['delete']) === true) {
		unset($ref['photo']);
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

	header('Location: map');
?>
