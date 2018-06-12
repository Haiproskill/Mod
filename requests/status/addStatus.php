<?php

$user = $tools->getUser(isset($_REQUEST['user']) ? abs(intval($_REQUEST['user'])) : 0);
$text = isset($_POST['text']) ? trim($_POST['text']) : '';
$data = array(
	'status' => 0,
	'error'  => 'Có lỗi xảy ra.!!'
);

if ($systemUser->isValid()) {
	if (!$user || empty($text)) {
		$data = array(
			'status' => 0,
	        'error'  => 'Chưa nhập nội dung.!!'
		);
	} else {
	    $rres = $db->query("SELECT * FROM `cms_users_guestbook`
	        WHERE `user_id` = " . $systemUser->id . "
            ORDER BY `time` DESC
	        LIMIT 1
	    ")->fetch();

	    if ($rres['text'] == $text) {
	        $data = array(
	        	'status' => 0,
	        	'error'  => 'Nội dung đã tồn tại.!!'
	    	);
	    } else {
			$db->prepare("INSERT INTO `cms_users_guestbook` SET
	            `from_id` = ?,
	            `user_id` = ?,
	            `time` = ?,
	            `text` = ?
	        ")->execute([
	        	$user['id'],
	            $systemUser->id,
	            time(),
	            $text,
	        ]);

		    $data = array(
			    'status' => 1
		    );
	    }
	}

	header("Content-type: application/json; charset=utf-8");
	echo json_encode($data);
	exit();
}
