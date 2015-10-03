<?php
	$request = $_SERVER['REQUEST_URI'];

	$data = json_decode(file_get_contents('data.json'), true);

	foreach ($data as &$item) {
		if (isset($item['photo'])) {
			$item['photo'] = (isset($_SERVER['HTTPS']) === true && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . substr($request, 0, strrpos($request, '/') + 1) . 'photos/' .$item['photo'];
		}
	}

	header('Content-Type: application/json');
	echo json_encode($data);
?>
