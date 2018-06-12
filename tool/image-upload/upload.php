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
require('../../system/bootstrap.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

$db = $container->get(PDO::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

// $root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

require('../../system/head.php');

if (!$systemUser->isValid()) {
    echo $tools->displayError(_t('For registered users only'));
    require('../../system/end.php');
    exit;
}

echo '<div class="imgur mrt-code card shadow--2dp"><div class="phdr"><h4>Chọn một bức hình</h4></div>';
echo '<div class="jserror" style="display:none;"></div>';

echo '<form enctype="multipart/form-data" method="post">'
    . '<div class="list1">'
    . '<div class="file_input_div"onclick="javascript:$(\'#file_input_file\').click();">'
    . '<div class="file_input">'
    . '<label class="image_input_button m-button m-button--fab m-button--mini-fab m-button--colored">'
    . '<i class="material-icons">&#xE2C6;</i>'
    . '</label>'
    . '</div>'
    . '<div id="file_input_text_div" class="m-textfield">'
    . '<input class="file_input_text m-textfield__input" type="text" disabled readonly id="file_input_text" value=""/>'
    . '<label class="m-textfield__label" for="file_input_text"></label>'
    . '</div>'
    . '</div>'
    . '<input id="file_input_file" class="none" type="file" name="imagefile" required="required" />'
    . '<input type="hidden" name="t" value="app" />'
    . '<input type="hidden" name="a" value="imgur" />'
    . '<div class="button-container t10"><button class="button" type="submit" name="submit"><span>' . _t('Upload') . '</span></button></div>'
    . '</div></form>'
    . '<div class="list1">Tải lên tối đa: ' . $config['flsz'] . 'kb.</div>'
    . '</div>'
    . '<div class="mrt-code card shadow--2dp">'
    . '<div class="card__actions"><a href="'.$config['homeurl'].'/upload-hinhanh">Danh sách chung.</a></div>'
    . '<div class="list1"><a href="'.$config['homeurl'].'/upload-hinhanh/album.html">Ảnh của tôi.</a></div>';
echo '</div>';
require_once('../../system/end.php');
?>