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

define('_IN_JOHNCMS', 1);

$textl = _t('Birthdays');
$headmod = 'birth';
require('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

// Выводим список именинников
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php"><b>' . _t('Community') . '</b></a>&#160;|&#160;' . _t('Birthdays') . '</div>';
$total = $db->query("SELECT COUNT(*) FROM `users` WHERE `dayb` = '" . date('j', time()) . "' AND `monthb` = '" . date('n', time()) . "' AND `preg` = '1'")->fetchColumn();

if ($total) {
    $req = $db->query("SELECT * FROM `users` WHERE `dayb` = '" . date('j', time()) . "' AND `monthb` = '" . date('n', time()) . "' AND `preg` = '1' LIMIT $start, $kmess");

    while ($res = $req->fetch()) {
        echo '<div class="list1">';
        echo $tools->displayUser($res) . '</div>';
        ++$i;
    }
    echo '</div>';
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div>';
    if ($total > $kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=birth&amp;', $start, $total, $kmess) . '</div>';
    }
    echo '</div>';
} else {
    echo '<div class="rmenu"><p>' . _t('The list is empty') . '</p></div></div>';
}
