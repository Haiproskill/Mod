<?php

$post_id = isset($_REQUEST['post_id']) ? abs(intval($_REQUEST['post_id'])) : 0;
$text    = isset($_POST['text']) 	   ? trim($_POST['text'])              : '';
$type    = 'reply';

if ($systemUser->isValid()) {
    if (!$post_id || empty($text)) {
    	$data = array(
    		'status' => 0,
            'error'  => 'Chưa nhập nội dung.!!'
    	);
    } else {
        $rres = $db->query("SELECT * FROM `cms_users_guestbook_comments`
            WHERE `post_id` = " . $db->quote($post_id) . "
            AND `user_id` = " . $systemUser->id . "
            ORDER BY `id` DESC
            LIMIT 1
            ")->fetch();

        if ($rres['text'] == $text) {
            $data = array(
            'status' => 0,
            'error'  => 'Nội dung đã tồn tại.!!'
        );
        } else {

            $db->prepare("INSERT INTO `cms_users_guestbook_comments` SET
                `post_id` = ?,
                `user_id` = ?,
                `time` = ?,
                `text` = ?,
                `type` = ?
            ")->execute([
                $post_id,
                $systemUser->id,
                time(),
                $text,
                $type,
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
