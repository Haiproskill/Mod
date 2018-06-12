<?php

$html = '';
$ch = 0;
$statusOut = '200';
$error = [];
if ($systemUser->isValid()) {
    if (isset($_POST['id']) && isset($_POST['text']) && empty($systemUser->ban['1']) && empty($systemUser->ban['3']) && !$tools->isIgnor($id)) {
        $text = trim($_POST['text']);
        $id = trim($_POST['id']);
        $req = $db->query("SELECT * FROM `users` WHERE `id` = " . $db->quote($id) . " LIMIT 1");

        if (!$req->rowCount())
            $error[] = _t('User does not exists');
        if(empty($text))
            $error[] = _t('Message cannot be empty');
        if ($id && $id == $systemUser->id)
            $error[] = _t('You cannot send messages to yourself');

        if (empty($error)) {
            $ignor = $db->query("SELECT COUNT(*) FROM `cms_contact`
    		WHERE `user_id`='" . $systemUser->id . "'
    		AND `from_id`=" . $db->quote($id) . "
    		AND `ban`='1';")->fetchColumn();

            if ($ignor) {
                $error[] = _t('The user at your ignore list. Sending the message is impossible.');
            }

            if (empty($error)) {
                $ignor_m = $db->query("SELECT COUNT(*) FROM `cms_contact`
    			WHERE `user_id`=" . $db->quote($id) . "
    			AND `from_id`='" . $systemUser->id . "'
    			AND `ban`='1';")->fetchColumn();

                if ($ignor_m) {
                    $error[] = _t('The user added you in the ignore list. Sending the message isn\'t possible.');
                }
            }
        }

        if (empty($error)) {
            $q = $db->query("SELECT * FROM `cms_contact`
    		WHERE `user_id`='" . $systemUser->id . "' AND `from_id`=" . $db->quote($id) . " ");

            if (!$q->rowCount()) {
                $db->exec("INSERT INTO `cms_contact` SET
    			`user_id` = '" . $systemUser->id . "',
    			`from_id` = " . $db->quote($id) . ",
    			`time` = '" . time() . "'");
                $ch = 1;
            }

            $q1 = $db->query("SELECT * FROM `cms_contact`
    		WHERE `user_id`=" . $db->quote($id) . " AND `from_id`='" . $systemUser->id . "'");

            if (!$q1->rowCount()) {
                $db->exec("INSERT INTO `cms_contact` SET
    			`user_id` = " . $db->quote($id) . ",
    			`from_id` = '" . $systemUser->id . "',
    			`time` = '" . time() . "'");
                $ch = 1;
            }
        }

        if (empty($error)) {
            $rres = $db->query("SELECT * FROM `cms_mail`
            WHERE `user_id` = " . $systemUser->id . "
            AND `from_id` = " . $db->quote($id) . "
            ORDER BY `id` DESC
            LIMIT 1
            ")->fetch();

            if ($rres['text'] == $text) {
                $error[] = _t('Message already exists');
            }
        }


        if (empty($error)) {
            $db->query("INSERT INTO `cms_mail` SET
    		`user_id` = '" . $systemUser->id . "',
    		`from_id` = " . $db->quote($id) . ",
    		`text` = " . $db->quote($text) . ",
    		`time` = '" . time() . "',
    		`file_name` = '',
    		`size` = '0' ");

            $db->exec("UPDATE `users` SET `lastpost` = '" . time() . "' WHERE `id` = '" . $systemUser->id . "'");

            if ($ch == 0) {
                $db->exec("UPDATE `cms_contact` SET `time` = '" . time() . "' WHERE `user_id` = '" . $systemUser->id . "' AND `from_id` = " . $db->quote($id) . " ");
                $db->exec("UPDATE `cms_contact` SET `time` = '" . time() . "' WHERE `user_id` = " . $db->quote($id) . " AND `from_id` = '" . $systemUser->id . "'");
            }
        }
    }
    if (!empty($error)) {
        $statusOut = 300;
        $html = $tools->displayError($error);
    }
    $data = array(
        'status' => $statusOut,
        'html' => $html
    );

    header("Content-type: application/json; charset=utf-8");
    echo json_encode($data);
    exit();
}