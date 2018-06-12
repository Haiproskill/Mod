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

require('system/bootstrap.php');

/** @var Johncms\Api\ConfigInterface $config */
$config = App::getContainer()->get(Johncms\Api\ConfigInterface::class);

$referer = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : $config->homeurl;

if (isset($_GET['exit'])) {
    setcookie('cuid', '');
    setcookie('cups', '');
    session_destroy();
    header('Location: index.php');
} else {
    require('system/head.php');
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('Logout', 'system') . '</h4></div>';
    echo '<div class="rmenu">' .
        '<center>' . _t('Are you sure you want to leave the site?', 'system') . '</center>' .
        '</div>' .
        '<div class="list1">' .
        '<div class="button-container">' .
        '<a href="?exit"><button class="button"><span>' . _t('Logout', 'system') . '</span></button></a>&#160;' .
        '<a href="' . $referer . '"><button class="button"><span>' . _t('Cancel', 'system') . '</span></button></a>' .
        '</div>' .
        '</div></div>';
    require('system/end.php');
}
