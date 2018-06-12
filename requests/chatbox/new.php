<?php

$html = null;
$statusOut = '200';
$error = [];
$name = isset($_POST['name']) ? mb_substr(trim($_POST['name']), 0, 20) : '';
$msg = isset($_POST['msg']) ? mb_substr(trim($_POST['msg']), 0, 5000) : '';
$code = isset($_POST['code']) ? trim($_POST['code']) : '';
$from = $systemUser->isValid() ? $systemUser->name : '';

if (isset($_POST['admin']) && $_POST['admin'] == '1' && $systemUser->isValid() && $systemUser->rights >= 1) {
    $adm = '1';
} else {
    $adm = '0';
}

if ($systemUser->isValid() || $config->mod_guest == 2) {
	if (empty($msg))
        $error[] = _t('You have not entered the message');

    if ($systemUser->ban['1'] || $systemUser->ban['13'])
        $error[] = _t('Access forbidden');

    if(!$config->mod_guest)
        $error[] = _t('Guestbook is closed');

    if (!$systemUser->isValid() && empty($name))
        $error[] = _t('Chưa nhập tên');

    if (!$systemUser->isValid() && (empty($code) || mb_strlen($code) < 4 || $code != $_SESSION['code']))
        $error[] = _t('The security code is not correct');

    if (empty($error)) {
        $req = $db->query("SELECT * FROM `guest` WHERE `user_id` = '" . $systemUser->id . "' ORDER BY `time` DESC");
        $res = $req->fetch();
        if ($res['text'] == $msg) {
            $error[] = _t('Message already exists');
        }
    }

    if (empty($error)) {
        $db->prepare("INSERT INTO `guest` SET
            `adm` = ?,
            `time` = ?,
            `user_id` = ?,
            `name` = ?,
            `text` = ?,
            `ip` = ?,
            `browser` = ?,
            `otvet` = ''
        ")->execute([
            $adm,
            time(),
            $systemUser->id,
            $from,
            $msg,
            $env->getIp(),
            $env->getUserAgent(),
        ]);
        // $id = $db->lastInsertId();
    } else {
        $statusOut = '300';
        $html = $tools->displayError($error);
    }
}
$data = array(
    'status' => $statusOut,
    'html' => $html
);

header("Content-type: application/json; charset=utf-8");
echo json_encode($data);
exit();

