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

$textl = _t('List of users');
$headmod = 'userlist';
require('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

// Выводим список пользователей
$total = $db->query("SELECT COUNT(*) FROM `users` WHERE `preg` = 1")->fetchColumn();
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php"><strong>' . _t('Community') . '</strong></a>&#160;|&#160;' . _t('List of users') . '</div>';

if ($total > $kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=userlist&amp;', $start, $total, $kmess) . '</div>';
}
echo '</div><div class="mrt-code card shadow--2dp">';
$req = $db->query("SELECT `id`, `name`, `sex`, `lastdate`, `datereg`, `status`, `rights`, `ip`, `browser`, `rights`, `ip_via_proxy` FROM `users` WHERE `preg` = 1 ORDER BY `datereg` DESC LIMIT $start, $kmess");

for ($i = 0; ($res = $req->fetch()) !== false; $i++) {
    echo '<div class="card__actions fauthor' . ($i == '0' ? '' : ' card--border' ) . '">';
    echo $tools->displayUser($res) . '</div>';
}
echo '</div><div class="mrt-code card shadow--2dp">';
echo '<div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

if ($total > $kmess) {
    echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=userlist&amp;', $start, $total, $kmess) . '</div>';
}
echo '</div>';
echo '<div class="mrt-code card shadow--2dp"><div class="card__actions"><a href="search.php">' . _t('User Search') . '</a></div>' .
    '<div class="list1"><a href="index.php">' . _t('Back') . '</a></div></div>';
