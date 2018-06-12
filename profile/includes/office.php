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

$headmod = 'office';
$textl = _t('My Account');
require('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

// Проверяем права доступа
if ($user['id'] != $systemUser->id) {
    echo $tools->displayError(_t('Access forbidden'));
    require('../system/end.php');
    exit;
}

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

// Блок настроек
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('Settings') . '</h4></div>' .
    '<div class="list1">' . $tools->image('user-edit.png') . '<a href="?act=edit">' . _t('Edit Profile') . '</a></div>' .
    '<div class="list1">' . $tools->image('lock.png') . '<a href="?act=password">' . _t('Change Password') . '</a></div>' .
    '<div class="list1">' . $tools->image('settings.png') . '<a href="?act=settings">' . _t('System Settings') . '</a></div>';
if ($systemUser->rights >= 1) {
    echo '<div class="list1">' . $tools->image('forbidden.png') . '<span class="red"><a href="../admin/"><b>' . _t('Admin Panel') . '</b></a></span></div>';
}
echo '</div>';

// Выход с сайта
echo '<div class="gmenu"><p><a href="' . $config['homeurl'] . '/profile/index.php">' . _t('Personal', 'system') . '</a></p></div>';
