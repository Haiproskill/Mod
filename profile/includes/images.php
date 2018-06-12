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

$textl = _t('Edit Profile');
require('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

if (($systemUser->id != $user['id'] && $systemUser->rights < 7)
    || $user['rights'] > $systemUser->rights
) {
    // Если не хватает прав, выводим ошибку
    echo $tools->displayError(_t('You cannot edit profile of higher administration'));
    require('../system/end.php');
    exit;
}

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

switch ($mod) {
    case 'avatar':
        // Выгружаем аватар
        echo '<div class="up_avatar mrt-code card shadow--2dp"><div class="phdr"><h4><a href="?user=' . $user['id'] . '"><b>' . _t('Profile') . '</b></a>&#160;|&#160;' . _t('Upload Avatar') . '</h4></div>';
        echo '<div class="jserror" style="display:none;"></div>';

        echo '<form id="ajaxAvatar" enctype="multipart/form-data" method="post">'
            . '<div class="list1">'
            . '<div class="file_input_div">'
            . '<div class="file_input">'
            . '<label class="image_input_button m-button m-button--fab m-button--mini-fab m-button--colored">'
            . '<i class="material-icons">&#xE2C6;</i>'
            . '<input id="file_input_file" class="none" type="file" name="imagefile" required="required" />'
            . '</label>'
            . '</div>'
            . '<div id="file_input_text_div" class="m-textfield">'
            . '<input class="file_input_text m-textfield__input" type="text" disabled readonly id="file_input_text" value="' . _t('Select Image') . ':"/>'
            . '<label class="m-textfield__label" for="file_input_text"></label>'
            . '</div>'
            . '</div>'
            . '<input type="hidden" name="t" value="avatar" />'
            . '<input type="hidden" name="a" value="default" />'
            . '<input type="hidden" name="uid" value="' . $user['id'] . '" /></p>'
            . '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Upload') . '</span></button></div>'
            . '</div></form>'
            . '<div class="list1"><small>'
            . sprintf(_t('Allowed image formats: JPG, PNG, GIF. File size should not exceed %d kb.<br>The new image will replace old (if was).'), $config['flsz'])
            . '</small></div>';
        echo '</div>';
    break;

    default:
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4><a href="?user=' . $user['id'] . '"><b>' . _t('Profile') . '</b></a>&#160;|&#160;Cài đặt Avatar</h4></div>';
        if(isset($_GET['delavatar'])){
            if($_GET['delavatar'] == 'yes'){
                if($user['avatar_extension'] != 'none'){
                    $photo_dir = '../files/users/avatar';
                    $ext_base = $user['avatar_extension'];
                    if (file_exists(($photo_dir . '/' . $user['id'] . '.' . $ext_base))) {
                        @unlink($photo_dir . '/' . $user['id'] . '.' . $ext_base);
                    }
                    if (file_exists(($photo_dir . '/' . $user['id'] . '_100x100.' . $ext_base))) {
                        @unlink($photo_dir . '/' . $user['id'] . '_100x100.' . $ext_base);
                    }
                    if (file_exists(($photo_dir . '/' . $user['id'] . '_100x75.' . $ext_base))) {
                        @unlink($photo_dir . '/' . $user['id'] . '_100x75.' . $ext_base);
                    }
                    if (file_exists(($photo_dir . '/' . $user['id'] . '_thumb.' . $ext_base))) {
                        @unlink($photo_dir . '/' . $user['id'] . '_thumb.' . $ext_base);
                    }
                    $db->exec("UPDATE `users` SET
                        `avatar_extension` = 'none'
                        WHERE `id` = '" . $user['id'] . "'
                    ");
                    echo '<div class="gmenu text-center">Xoá avatar thành công.!</div>';
                }
            } else {
                echo '<div class="rmenu text-center">Bạn thực sự muốn xoá avatar.?<br /><a href="?act=images&amp;user=' . $user['id'] . '">No</a> | <a href="?act=images&amp;delavatar=yes&amp;user=' . $user['id'] . '">Yes</a></div>';
            }
        }
        echo '<div class="list1 text-center">Avatar hiện tại của bạn:<br /><br />';
        $link = '';

        $avatar_name = $tools->avatar_name($user['id']);
        if (file_exists(('../files/users/avatar/' . $avatar_name))) {
            echo '<img src="/files/users/avatar/' . $avatar_name . '" alt="' . $user['name'] . '" class="avatar" />';
            $link = ' | <a href="?act=images&amp;delavatar&amp;user=' . $user['id'] . '">' . _t('Delete') . '</a>';
        } else {
            echo '<img src="' . $config['homeurl'] . '/images/empty' . ($user['sex'] ? ($user['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="avatar" alt="' . $user['name'] . '" />';
        }

        echo '<br /><br /><a href="?act=images&amp;mod=avatar&amp;user=' . $user['id'] . '">' . _t('Upload') . '</a>';
        echo $link;

        echo '</div>' .
            '</div>';
    break;

    case 'thumb':
    echo '<div class="up_avatar mrt-code card shadow--2dp"><div class="phdr"><h4><a href="?user=' . $user['id'] . '"><b>' . _t('Profile') . '</b></a>&#160;|&#160;' . _t('Upload Avatar') . '</h4></div>';
        echo '<div class="jserror" style="display:none;"></div>';

        echo '<form enctype="multipart/form-data" method="post">'
            . '<div class="list1">'
            . '<div class="file_input_div">'
            . '<div class="file_input">'
            . '<label class="image_input_button m-button m-button--fab m-button--mini-fab m-button--colored">'
            . '<i class="material-icons">&#xE2C6;</i>'
            . '<input id="file_input_file" class="none" type="file" name="imagefile" required="required" />'
            . '</label>'
            . '</div>'
            . '<div id="file_input_text_div" class="m-textfield">'
            . '<input class="file_input_text m-textfield__input" type="text" disabled readonly id="file_input_text" value="' . _t('Select Image') . ':"/>'
            . '<label class="m-textfield__label" for="file_input_text"></label>'
            . '</div>'
            . '</div>'
            . '<input type="hidden" name="t" value="avatar" />'
            . '<input type="hidden" name="a" value="thumb" />'
            . '<input type="hidden" name="uid" value="' . $user['id'] . '" /></p>'
            . '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Upload') . '</span></button></div>'
            . '</div></form>'
            . '<div class="list1"><small>'
            . sprintf(_t('Allowed image formats: JPG, PNG, GIF. File size should not exceed %d kb.<br>The new image will replace old (if was).'), $config['flsz'])
            . '</small></div>';
        echo '</div>';
    break;
}
