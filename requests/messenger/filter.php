<?php
$mass_read = [];
$html = '';
$st = 0;
$after = '';
$dem = '0';
$dd = [];
$avatar = null;
if ($systemUser->isValid()) {
    if (isset($_POST['uid'])) {
        $id = @addslashes($_POST['uid']);
    }
    if (isset($_POST['start_row']) && $_POST['start_row'] > 0) {
        $st = @addslashes($_POST['start_row']);
    }
    if (isset($_POST['after_id']) && $_POST['after_id'] > 0) {
        $after_id = @addslashes($_POST['after_id']);
    }

    $total = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE ((`user_id`='$id' AND `from_id`='" . $systemUser->id . "') OR (`user_id`='" . $systemUser->id . "' AND `from_id`='$id')) AND `sys`!='1' AND `delete`!='" . $systemUser->id . "' AND `spam`='0' ")->fetchColumn();
    if ($total > $kmess) {
        if (($total - $st) < $kmess) {
        } else {
            $dem = $total - $st - $kmess;
        }
    }
    $req = $db->query("SELECT `cms_mail`.*, `cms_mail`.`id` as `mid`, `cms_mail`.`time` as `mtime`, `users`.*
        FROM `cms_mail`
        LEFT JOIN `users` ON `cms_mail`.`user_id`=`users`.`id`
        WHERE `cms_mail`.`id` < $after_id AND ((`cms_mail`.`user_id`='$id' AND `cms_mail`.`from_id`='" . $systemUser->id . "') OR (`cms_mail`.`user_id`='" . $systemUser->id . "' AND `cms_mail`.`from_id`='$id'))
        AND `cms_mail`.`delete`!='" . $systemUser->id . "'
        AND `cms_mail`.`sys`!='1'
        AND `cms_mail`.`spam`='0'
        ORDER BY `cms_mail`.`time` ASC
        LIMIT " . $dem . "," . $kmess);
    $i = 0;
    while ($row = $req->fetch()) {
        if ($row['read'] == 0 && $row['from_id'] == $systemUser->id) {
            $mass_read[] = $row['mid'];
        }
        if ($row['from_id'] == $systemUser->id) {
            $who = 'them';
            $user_Them = $db->query("SELECT `sex` FROM `users` WHERE `id`='$row[user_id]' LIMIT 1")->fetch();
            $avatar_name = $tools->avatar_name($row['user_id']);
            if (file_exists(('files/users/avatar/' . $avatar_name))) {
                $avatar = '/files/users/avatar/' . $avatar_name;
            } else {
                $avatar = '/images/empty' . ($user_Them['sex'] ? ($user_Them['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
            }
            $icons = '<div class="circle-wrapper animated bounceIn" style="' . $avatar . 'background-size: 40px 40px;"></div>';
        } else {
            $who = 'me';
            $avatar = '0';
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


    if(($total - $st) <= $kmess) {
        $continue = 0;
    } else {
        $continue = 1;
    }


    $data = array(
        'status'   => 200,
        'continue' => $continue,
        'count'    => count($dd),
        'data'     => $dd
    );

    header("Content-type: application/json; charset=utf-8");
    echo json_encode($data);
    exit();
}