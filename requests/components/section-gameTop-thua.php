<?php

$spage = 0;
$page = '';
$top = array();
$total = $db->query("SELECT COUNT(*) FROM `users` WHERE `preg` = 1 ")->fetchColumn();
$req = $db->query("SELECT `id`, `name`, `sex`, `status`, `rights`, `balansDown` FROM `users` WHERE `preg` = 1 ORDER BY `balansDown` DESC LIMIT $start, $kmess");
if ($req->rowCount()) {
	$i = 0;
	while ($res = $req->fetch()) {
        $avatar_name = $tools->avatar_name($res['id']);
        if (file_exists(('files/users/avatar/' . $avatar_name))) {
            $top[$i]['avatar'] = $config['homeurl'] . '/files/users/avatar/' . $avatar_name;
        } else {
            $top[$i]['avatar'] = $config['homeurl'] . '/images/empty' . ($res['sex'] ? ($res['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
        }

        if($res['sex'] == 'm'){
            $top[$i]['sex'] = 'm';
        }else{
            $top[$i]['sex'] = 'w';
        }

        $top[$i]['id']     = $res['id'];
        $top[$i]['name']   = $res['name'];
        $top[$i]['rights'] = $res['rights'];
        $top[$i]['balans'] = $tools->balans($res['balansDown']);

        ++$i;
	}
}
if ($total > $kmess) {
	$spage = 1;
    $page = $tools->displayPagination('game-top.php?', $start, $total, $kmess);
}
$data = array (
    'status' => 1,
    'rate'   => 'gametop',
    'html'   => $top,
    'page'   => array(
    	'status' => $spage,
    	'data'   => $page
    )
);

header("Content-type: application/json; charset=utf-8");
echo json_encode($data);
exit();
