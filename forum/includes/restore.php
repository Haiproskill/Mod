<?php
/*
 * JohnCMS NEXT Mobile Content Management System (http://johncms.com)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://johncms.com JohnCMS Project
 * @copyright   Copyright (C) JohnCMS Community
 * @license     GPL-3
 */

defined('_IN_JOHNCMS') or die('Error: restricted access');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

if (($systemUser->rights != 3 && $systemUser->rights < 6) || !$id) {
    header('Location: /?act=404');
    exit;
}

$req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND (`type` = 't' OR `type` = 'm')");

if ($req->rowCount()) {
    $res = $req->fetch();
    $resT = $db->query("SELECT * FROM `forum` WHERE `id` = '" . $res['refid'] . "' ")->fetch();
    $db->exec("UPDATE `forum` SET `close` = '0', `close_who` = '" . $systemUser->name . "' WHERE `id` = '$id'");

    if ($res['type'] == 't') {
        header('Location: /forum/' . $id . '/' . $res['seo'] . '.html');
    } else {
        $page = ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '" . $id . "'")->fetchColumn() / $kmess);
        header('Location: /forum/' . $res['refid'] . '/' . $resT['seo'] . ($page > 1 ? '_p' . $page : '') . '.html');
    }
} else {
    header('Location: /forum/index.html');
}
