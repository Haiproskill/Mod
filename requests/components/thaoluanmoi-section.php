<?php

$spage = 0;
$page = '';
if($config->mod_moderation) {
    $moderation = "AND `moderation` = '1' ";
} else {
    $moderation = "";
}
$baivietmoi = array();
$total = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' " . $moderation . "AND `close` != '1' ")->fetchColumn();
$req = $db->query("SELECT * FROM `forum` WHERE `type` = 't' " . $moderation . "AND `close` != '1' ORDER BY `time` DESC LIMIT $start, $kmess");
if ($req->rowCount()) {

    for ($i = 0; $res = $req->fetch(); ++$i) {
    	$userpost = $db->query("SELECT `sex` FROM `users` WHERE `id`='$res[user_id]' LIMIT 1")->fetch();
        $nikuser = $db->query("SELECT `from` FROM `forum` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "'ORDER BY `time` DESC");
        $colmes1 = $nikuser->rowCount();
        $cpg = ceil($colmes1 / $kmess);
        $nam = $nikuser->fetch();
        // Значки
        $icons = [
            ($res['vip'] ? $tools->image('pt.gif') : ''),
            ($res['realid'] ? $tools->image('rate.gif') : ''),
            ($res['edit'] ? $tools->image('tz.gif') : ''),
        ];
        if($res['thumb_extension'] == 'none') {
            $avatar_name = $tools->avatar_name($res['user_id']);
            if (file_exists(('files/users/avatar/' . $avatar_name))) {
                $baivietmoi[$i]['avatar'] = $config['homeurl'] . '/files/users/avatar/' . $avatar_name;
            } else {
                $baivietmoi[$i]['avatar'] = $config['homeurl'] . '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
            }
        } else {
            $thumb_file = $res['id'] . '.' . $res['thumb_extension'];
            if (file_exists(('files/forum/thumbnail/' . $thumb_file))) {
                $baivietmoi[$i]['avatar'] = $config['homeurl'] . '/files/forum/thumbnail/' . $thumb_file;
            } else {
                $baivietmoi[$i]['avatar'] = $config['homeurl'] . '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
            }
        }
        $baivietmoi[$i]['resFrom'] = $res['from'];
        $baivietmoi[$i]['link']    = $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html';
        $baivietmoi[$i]['title']   = ($res['bai'] ? 'Bài ' . $res['bai'] . ': ' : '') . (empty($res['text']) ? '-----' : $res['text']);
        $baivietmoi[$i]['colmes']  = $colmes1;

        if ($cpg > 1) {
            $baivietmoi[$i]['next'] = $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '_clip_p' . $cpg . '.html';
        }

        $baivietmoi[$i]['user'] = $res['from'];

        if (!empty($nam['from'])) {
            $baivietmoi[$i]['namFrom'] = $nam['from'];
        }
        $baivietmoi[$i]['time'] = $tools->thoigian($res['post-time']);
    }
}

if ($total > $kmess) {
    $spage = 1;
    $page = $tools->displayPagination('index.php?', $start, $total, $kmess);
}

$data = array(
    'status' => 1,
    'rate'   => 'forum',
    'html' => $baivietmoi,
    'page'   => array(
        'status' => $spage,
        'data'   => $page
    )
);

header("Content-type: application/json; charset=utf-8");
echo json_encode($data);
exit();

