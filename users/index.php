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

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';

$headmod = 'users';
require('../system/bootstrap.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

// Закрываем от неавторизованных юзеров
if (!$systemUser->isValid() && !$config->active) {
    require('../system/head.php');
    echo $tools->displayError(_t('For registered users only'));
    require('../system/end.php');
    exit;
}

// Переключаем режимы работы
$array = [
    'admlist'  => 'includes',
    'birth'    => 'includes',
    'online'   => 'includes',
    'top'      => 'includes',
    'userlist' => 'includes',
];
$path = !empty($array[$act]) ? $array[$act] . '/' : '';

if (array_key_exists($act, $array) && file_exists($path . $act . '.php')) {
    require_once($path . $act . '.php');
} else {
    /** @var PDO $db */
    $db = $container->get(PDO::class);

    /** @var Johncms\Counters $counters */
    $counters = $container->get('counters');

    // Актив сайта
    $textl = _t('Community');
    require('../system/head.php');

    $brth = $db->query("SELECT COUNT(*) FROM `users` WHERE `dayb` = '" . date('j', time()) . "' AND `monthb` = '" . date('n', time()) . "' AND `preg` = '1'")->fetchColumn();
    $count_adm = $db->query("SELECT COUNT(*) FROM `users` WHERE `rights` > 0")->fetchColumn();

    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h5>' . _t('Community') . '</h5></div>' .
        '<div class="list1"><form action="search.php" method="post">' .
        '<div class="form-group"><input type="text" name="search" required="required" />' .
        '<label class="control-label" for="input">' . _t('Look for the User') . '</label><i class="bar"></i>' .
        '</div>' .
        '<div class="button-container"><button  type="submit" name="submit" class="button"><span>' . _t('Search') . '</span></button></div>' .
        '</form></div>' .
        '<div class="list1"><a href="index.php?act=userlist">' . _t('Users') . '</a> (' . $container->get('counters')->users() . ')</div>' .
        '<div class="list1"><a href="index.php?act=admlist">' . _t('Administration') . '</a> (' . $count_adm . ')</div>' .
        ($brth ? '<div class="list1"><a href="index.php?act=birth">' . _t('Birthdays') . '</a> (' . $brth . ')</div>' : '') .
        '<div class="list1"><a href="index.php?act=top">' . _t('Top Activity') . '</a></div>' .
        '</div>';
}

require_once('../system/end.php');
