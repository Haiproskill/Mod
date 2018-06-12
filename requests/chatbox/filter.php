<?php
$st     = 0;
$rest   = 0;
$before = "";
$view   = '';
$chatbox_list = array();
$data = array(
    'status' => 0
);

if (isset($_POST['view']) && $_POST['view'] == 'new' && $systemUser->isValid()) {
    // $view = "`user_id` != '" . $systemUser->id . "' AND";
}
if (isset($_POST['admin']) && $_POST['admin'] == '1' && $systemUser->isValid() && $systemUser->rights >= 1) {
    $adm = '1';
} else {
    $adm = '0';
}
if (isset($_POST['start_row']) && $_POST['start_row'] > 0) {
    $st = @addslashes($_POST['start_row']);
}
if (isset($_POST['before_id']) && $_POST['before_id'] > 0) {
    $before_id = @addslashes($_POST['before_id']);
    $before = "`guest`.`id` > '$before_id' AND";
}

$req = $db->query("SELECT `guest`.*, `guest`.`id` AS `gid`, `users`.`rights`, `users`.`sex`, `users`.`id` FROM `guest` LEFT JOIN `users` ON `guest`.`user_id` = `users`.`id` WHERE " . $view . " ".$before." `guest`.`adm`=$adm ORDER BY `time` DESC LIMIT  $st,$kmess");
$newChatCount = $req->rowCount();
if ($newChatCount) {
    $countChat = $db->query("SELECT COUNT(*) FROM `guest` WHERE ".$before." `guest`.`adm`=$adm")->fetchColumn();
    if ($countChat > 10)
        $rest = $countChat - $st - 10;

    $i = 0;
    while ($gres = $req->fetch()) {
        $subtext = null;
        if ($gres['user_id']) {
            $post = $tools->checkout($gres['text'], 1, 1, 0, 1);
        } else {
            $gres['name'] = $tools->checkout($gres['name']);
            $post = $tools->checkout($gres['text'], 0, 2);
            $post = preg_replace('~\\[url=(https?://.+?)\\](.+?)\\[/url\\]|(https?://(www.)?[0-9a-z\.-]+\.[0-9a-z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', '###', $post);
            $replace = [
                '.ru'   => '***',
                '.com'  => '***',
                '.biz'  => '***',
                '.cn'   => '***',
                '.in'   => '***',
                '.net'  => '***',
                '.org'  => '***',
                '.info' => '***',
                '.mobi' => '***',
                '.wen'  => '***',
                '.kmx'  => '***',
                '.h2m'  => '***',
            ];
            $post = strtr($post, $replace);
        }
        $avatar_name = $tools->avatar_name($gres['user_id']);
        if (file_exists(('files/users/avatar/' . $avatar_name))) {
            $chatbox_list[$i]['avatar'] =  $config['homeurl'] . '/files/users/avatar/' . $avatar_name;
        } else {
            $chatbox_list[$i]['avatar'] =  $config['homeurl'] . '/images/empty' . ($gres['sex'] ? ($gres['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
        }
        $chatbox_list[$i]['id']         = $gres['gid'];
        $chatbox_list[$i]['name']       = $gres['name'];
        $chatbox_list[$i]['user_id']    = $gres['user_id'];
        $chatbox_list[$i]['rights']     = $gres['rights'];
        $chatbox_list[$i]['link']       = ($systemUser->isValid() && $gres['user_id'] != $systemUser->id ? 1 : 0);
        $chatbox_list[$i]['panel']      = ($systemUser->rights >= 6 ? 1 : 0);
        $chatbox_list[$i]['panel_more'] = ($systemUser->rights > $gres['rights'] || $systemUser->id == $gres['user_id'] ? 1 : 0);
        $chatbox_list[$i]['time']       = $tools->thoigian($gres['time']);
        $chatbox_list[$i]['timestamp']  = (round((time()-$gres['time'])/3600) < 1 ? $tools->timestamp($gres['time']) : 0);
        $chatbox_list[$i]['text']       = $post;
        if (!empty($gres['otvet'])) {
            $chatbox_list[$i]['reply']['time'] = $tools->displayDate($gres['otime']);
            $chatbox_list[$i]['reply']['name'] = $gres['admin'];
            $chatbox_list[$i]['reply']['text'] = $tools->checkout($gres['otvet'], 1, 1, 0, 1);
        } else
            $chatbox_list[$i]['reply']['time'] = 0;

        $i++;
    }

    $data = array(
        'status'       => 1,
        'rest'         => $rest,
        'chatbox_list' => $chatbox_list
    );
}
header("Content-type: application/json; charset=utf-8");
echo json_encode($data);
exit();