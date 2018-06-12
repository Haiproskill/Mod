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

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

if (!$id || !$systemUser->isValid()) {
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
}

// Проверяем, тот ли юзер заливает файл и в нужное ли место
$res = $db->query("SELECT * FROM `forum` WHERE `id` = '$id'")->fetch();
$user = $db->query("SELECT `rights` FROM `users` WHERE `id`='" . $res['user_id'] . "' LIMIT 1")->fetch();
if ($res['type'] != 'm' || ($res['user_id'] != $systemUser->id && $user['rights'] >= $systemUser->rights)) {
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
}

$topid = $res['refid'];
$resmrt = $db->query("SELECT * FROM `forum` WHERE `id` = '$topid'")->fetch();
$pa2 = $db->query("SELECT `id` FROM `forum` WHERE `type` = 'm' AND `refid` = '" . $res['refid'] . "'")->rowCount();
$page = ceil($pa2 / $kmess);

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><b>' . _t('Add File') . '</b></div>' .
    '<div class="jserror" style="display:none;"></div>' .
    '<div class="list1 formjs">' .
    '<form action="index.php?act=addfile&amp;id=' . $id . '" method="post" enctype="multipart/form-data">' .
    '<div class="file_input_div" onclick="javascript:$(\'#file_input_file\').click();">' .
    '<div class="file_input">' .
    '<label class="image_input_button m-button m-button--fab m-button--mini-fab m-button--colored">' .
    '<i class="material-icons">&#xE2C6;</i>' .
    '</label>' .
    '</div>' .
    '<div id="file_input_text_div" class="m-textfield">' .
    '<input class="file_input_text m-textfield__input" type="text" disabled readonly id="file_input_text" />' .
    '<label class="m-textfield__label" for="file_input_text"></label>' .
    '</div>' .
    '</div>' .
    '<input id="file_input_file" class="none" type="file" name="fail" required="required"/>' .
    '<input name="id" value="' . $id . '" type="hidden">' .
    '<input name="t" value="forum" type="hidden">' .
    '<input name="a" value="addfile" type="hidden">' .
    '<div class="form-group"><input type="number" min="0" value="" name="balans" placeholder="0"><label class="control-label" for="input">Giá:</label><i class="bar"></i></div>' .
    '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Upload') . '</span></button></div></form></div>' .
    '<div class="list1">' . _t('Max. Size') . ': ' . $config['flsz'] . 'kb.</div>' .
    '<div class="list1"><a href="/forum/' . $res['refid'] . '/' . $resmrt['seo'] . ($page > 1 ? '_p' . $page : '') . '.html#post-' . $res['id'] . '">' . _t('Back to topic') . '</a></div>' .
    '</div>';
