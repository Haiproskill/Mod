<?php
if ($systemUser->isValid()) {
	$_POST['pos'] = $tools->stringEscape($_POST['pos']);

	$position = abs($_POST['pos']);
	$width = 920;

	if (isset($_POST['width']))
	{
	    $width = $_POST['width'];
	}

	$timelineId = $_POST['timeline_id'];
	$cover_id = $systemUser->id;

	$cover_url = $tools->createCover($cover_id, ($position / $width));
	if ($cover_url)
	{
	    $db->exec("UPDATE `users` SET `cover_position`='" . $position . "' WHERE `id` = " . $systemUser->id);
	    $data = array(
	        'status' => 1,
	        'url' => $home . '/' . $cover_url
	    );
	}
	header("Content-type: application/json; charset=utf-8");
	//echo json_encode($_POST['pos'] . ', ' .$position . ', ' . $width . ', ' . ($position / $width));
	echo json_encode($data);
	exit();
}
