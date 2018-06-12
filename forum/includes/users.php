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

require('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

$req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 't' AND `close` != '1'");

if (!$req->rowCount()) {
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
}
$res = $req->fetch();

$topic_vote = $db->query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type` = '1' AND `topic` = '$id'")->fetchColumn();

if ($topic_vote == 0 || $systemUser->rights < 7) {
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
} else {
    $topic_vote = $db->query("SELECT `name`, `time`, `count` FROM `cms_forum_vote` WHERE `type` = '1' AND `topic` = '$id' LIMIT 1")->fetch();
    echo '<div class="mrt-code card shadow--2dp"><div  class="phdr">' . _t('Who voted in the poll') . ' tại:&#160;<a href="/forum/' . $id . '/' . $res['seo'] . '.html"><b>' . htmlentities($topic_vote['name'], ENT_QUOTES, 'UTF-8') . '</b></a></div>';
    $total = $db->query("SELECT COUNT(*) FROM `cms_forum_vote_users` WHERE `topic`='$id'")->fetchColumn();
    $req = $db->query("SELECT `cms_forum_vote_users`.*, `users`.`rights`, `users`.`lastdate`, `users`.`name`, `users`.`sex`, `users`.`status`, `users`.`datereg`, `users`.`id`
    FROM `cms_forum_vote_users` LEFT JOIN `users` ON `cms_forum_vote_users`.`user` = `users`.`id`
    WHERE `cms_forum_vote_users`.`topic`='$id' LIMIT $start,$kmess");
    $i = 0;

    while ($res = $req->fetch()) {
        echo '<div class="list1">';
        echo $tools->displayUser($res, ['iphide' => 1]);
        echo '</div>';
        ++$i;
    }

    if ($total == 0) {
        echo '<div class="rmenu">' . _t('No one has voted in this poll yet') . '</div>';
    }
    echo '</div>';
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    if ($total > $kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=users&amp;id=' . $id . '&amp;', $start, $total, $kmess) . '</div>';
    }
    echo '</div>';
}

require('../system/end.php');
