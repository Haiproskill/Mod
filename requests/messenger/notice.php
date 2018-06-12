<?php

$status_messenger = 0;
$status_notice = 0;
$status_online = 0;

$html_messenger = null;
$html_notice = null;
$html_online = null;

$count_messenger = 0;
$count_notice = 0;
$count_online = 0;

$sql = '';
$set_karma = $config['karma'];

if ($systemUser->isValid()) {
    // online
    if (!$systemUser->karma_off && $set_karma['on'] && $systemUser->karma_time <= (time() - 86400)) {
        $sql .= " `karma_time` = " . time() . ", ";
    }
    $movings = $systemUser->movings;
    if ($systemUser->lastdate < (time() - 60)) {
        $movings = 0;
        $sql .= " `sestime` = " . time() . ", ";
    }
    if ($systemUser->place != $headmod) {
        ++$movings;
        $sql .= " `place` = " . $db->quote($headmod) . ", ";
    }
    if ($systemUser->browser != $env->getUserAgent()) {
        $sql .= " `browser` = " . $db->quote($env->getUserAgent()) . ", ";
    }
    $totalonsite = $systemUser->total_on_site;
    if ($systemUser->lastdate > (time() - 60)) {
        $totalonsite = $totalonsite + time() - $systemUser->lastdate;
    }
    $db->query("UPDATE `users` SET $sql
        `movings` = '$movings',
        `total_on_site` = '$totalonsite',
        `lastdate` = '" . time() . "'
        WHERE `id` = " . $systemUser->id);

    // online list
    $ontime = time() - 60;
    $userOnline = $db->query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > '" . $ontime . "' ")->fetchColumn();
    if($userOnline) {
        $count_online = $userOnline;
        $status_online = '1';
        $requ = $db->query("SELECT `id` FROM `users` WHERE `lastdate` > '" . $ontime . "' ORDER BY `name`");
        $i = 1;
        while ($resu = $requ->fetch()){
            $next = null;
            if($i < $userOnline) $next = ', ';
            $infoUser = $db->query("SELECT * FROM `users` WHERE `id` = " . $resu['id'] . " ")->fetch();
            if(!empty($infoUser['ban']))
                $name = '<span class="">' . $infoUser['name'] . '</span>';
            else
                $name = $infoUser['name'];
            $html_online .= ($systemUser->id == $resu['id'] || !$systemUser->isValid() ? $name : '<a href="' . $config['homeurl'] . '/profile/?user=' . $resu['id'] . '">' . $name . '</a>') . $next;
            $i++;
        }
    }

    // thông báo
    $count_notice = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `read`='0' AND `sys`='1' AND `delete`!='" . $systemUser->id . "'")->fetchColumn();
    if ($count_notice) $status_notice = '1';

    // tin nhắn
    $total = $db->query('SELECT COUNT(DISTINCT `user_id`) FROM `cms_mail` WHERE `from_id` = ' . $systemUser->id . ' AND `delete` != ' . $systemUser->id . ' AND `read` = 0 AND `sys` != 1')->fetchColumn();
    if ($total) {
        $status_messenger = '1';
        $count_messenger = $total;
        $info = $db->query("SELECT DISTINCT `user_id` FROM `cms_mail` WHERE `from_id` = '" . $systemUser->id . "' AND `delete` != '" . $systemUser->id . "' AND `read` = '0' AND `sys` != '1' ORDER BY `time` DESC ")->fetch();
        $userpost = $db->query("SELECT `sex` FROM `users` WHERE `id`='$info[user_id]' LIMIT 1")->fetch();
        $avatar_name = $tools->avatar_name($info['user_id']);
        if (file_exists(('files/users/avatar/' . $avatar_name))) {
            $html_messenger = '/files/users/avatar/' . $avatar_name . '';
        } else {
            $html_messenger = '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
        }
        if ($total == 1) {
            $new_count_message = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `cms_mail`.`user_id`='{$info['user_id']}' AND `cms_mail`.`from_id`='" . $systemUser->id . "' AND `read`='0' AND `delete`!='" . $systemUser->id . "' AND `spam`='0' AND `sys`!='1' ")->fetchColumn();
            $count_messenger = $new_count_message;
        } else if ($total > 1) {
            $count_messenger = $total . '+';
        }
    }
    
    
    $data = array(
        'notice_ify' => $status_notice,
        'count_notice' => $count_notice,
        
        'notice_messenger' => $status_messenger,
        'count_messenger' => $count_messenger,
        'data_messenger' => $html_messenger,
        
        'notice_online' => $status_online,
        'count_online' => $count_online,
        'data_online' => $html_online
    );
    header("Content-type: application/json; charset=utf-8");
    echo json_encode($data);
    exit();
}