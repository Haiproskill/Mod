<?php

$html = '';
$st = 0;
$before = "";
$statusOut = '300';
$dd = [];
$avatar = null;
if ($systemUser->isValid()) {
    if (isset($_POST['uid'])) {
        $id = @addslashes($_POST['uid']);
    }

    if (isset($_POST['before_id']) && $_POST['before_id'] > 0) {
        $before_id = @addslashes($_POST['before_id']);
        $before = "`cms_mail`.`id` > '$before_id' AND";
    }

    $total = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE ((`user_id`='$id' AND `from_id`='" . $systemUser->id . "') OR (`user_id`='" . $systemUser->id . "' AND `from_id`='$id')) AND `sys`!='1' AND `delete`!='" . $systemUser->id . "' AND `spam`='0'")->fetchColumn();
    $req = $db->query("SELECT `cms_mail`.*, `cms_mail`.`id` as `mid`, `cms_mail`.`time` as `mtime`, `users`.*
        FROM `cms_mail`
        LEFT JOIN `users` ON `cms_mail`.`user_id`=`users`.`id`
        WHERE ".$before."
         ((`cms_mail`.`user_id`='$id' AND `cms_mail`.`from_id`='" . $systemUser->id . "') OR (`cms_mail`.`user_id`='" . $systemUser->id . "' AND `cms_mail`.`from_id`='$id'))
        AND `cms_mail`.`delete`!='" . $systemUser->id . "'
        AND `cms_mail`.`sys`!='1'
        AND `cms_mail`.`spam`='0'
        ORDER BY `cms_mail`.`time` ASC
        ");
    $mass_read = [];
    $i = 0;
    while ($row = $req->fetch()) {
        if ($row['from_id'] == $systemUser->id) {
            $who = 'them';
            $user_Them = $db->query("SELECT `sex` FROM `users` WHERE `id`='$row[user_id]' LIMIT 1")->fetch();
            $avatar_name = $tools->avatar_name($row['user_id']);
            if (file_exists(('files/users/avatar/' . $avatar_name))) {
                $avatar = '/files/users/avatar/' . $avatar_name . '); ';
            } else {
                $avatar = '/images/empty' . ($user_Them['sex'] ? ($user_Them['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
            }
        } else {
            $who = 'me';
            $avatar = '0';
        }
        if ($row['read'] == 0 && $row['from_id'] == $systemUser->id) {
            $mass_read[] = $row['mid'];
        }
        $post = $row['text'];
        $post = $tools->checkout($post, 1, 1, 0, 1);

        if ($row['file_name']) {
            $post .= '<div class="func">' . _t('File') . ': <a href="index.php?act=load&amp;id=' . $row['mid'] . '">' . $row['file_name'] . '</a> (' . formatsize($row['size']) . ')(' . $row['count'] . ')</div>';
        }


        $dd[$i] = array(
            'id'         => $row['mid'],
            'avatar'     => $avatar,
            'text'       => $post,
            'time'       => $tools->thoigian($row['mtime']),
            'timestamp'  => (round((time()-$row['mtime'])/3600) < 1 ? $tools->timestamp($row['mtime']) : 0)
        );

        $i++;
    }

    if ($mass_read) {
        $result = implode(',', $mass_read);
        $db->exec("UPDATE `cms_mail` SET `read`='1' WHERE `from_id`='" . $systemUser->id . "' AND `id` IN (" . $result . ")");
    }
    if (count($dd) > 0){
        $statusOut = '200';
    }

    $data = array(
        'status'   => $statusOut,
        'count'    => count($dd),
        'data'     => $dd
    );

    header("Content-type: application/json; charset=utf-8");
    echo json_encode($data);
    exit();
}