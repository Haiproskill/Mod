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

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
    if (!$id) {
        require('../system/head.php');
        echo $tools->displayError(_t('Wrong data'));
        require('../system/end.php');
        exit;
    }

    // Проверяем, существует ли тема
    $req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 't'");

    if (!$req->rowCount()) {
        require('../system/head.php');
        echo $tools->displayError(_t('Topic has been deleted or does not exists'));
        require('../system/end.php');
        exit;
    }

    $res = $req->fetch();
    $reqr = $db->query("SELECT * FROM `forum` WHERE `id` = '" . $res['refid'] . "' ")->fetch();
    if (isset($_POST['submit'])) {
        $del = isset($_POST['del']) ? intval($_POST['del']) : null;

        if ($del == 2 && $systemUser->rights == 9) {
            // Удаляем топик
            $req1 = $db->query("SELECT * FROM `cms_forum_files` WHERE `topic` = '$id'");

            if ($req1->rowCount()) {
                while ($res1 = $req1->fetch()) {
                    unlink('../files/forum/attach/' . $res1['filelink']);
                }

                $db->exec("DELETE FROM `cms_forum_files` WHERE `topic` = '$id'");
                $db->query("OPTIMIZE TABLE `cms_forum_files`");
            }

            $db->exec("DELETE FROM `forum` WHERE `refid` = '$id'");
            $db->exec("DELETE FROM `forum` WHERE `id`='$id'");
        } elseif ($del = 1) {
            // Скрываем топик
            $db->exec("UPDATE `forum` SET `close` = '1', `close_who` = '" . $systemUser->name . "' WHERE `id` = '$id'");
            $db->exec("UPDATE `cms_forum_files` SET `del` = '1' WHERE `topic` = '$id'");
        }
        header('Location: /forum/' . $res['refid'] . '/' . $reqr['seo'] . '.html');
    } else {
        // Меню выбора режима удаления темы
        require('../system/head.php');
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4><a href="/forum/' . $id . '/' . $res['seo'] . '.html"><b>' . _t('Forum') . '</b></a>&#160;|&#160;' . _t('Delete Topic') . '</h4></div>' .
            '<div class="list1 text-center"><form method="post" action="index.php?act=deltema&amp;id=' . $id . '">' .
            _t('Do you really want to delete?') .
            '<div class="form-radio">' .
            '<div class="radio"><label><input type="radio" value="1" name="del" checked="checked"/><i class="helper"></i>&#160;' . _t('Hide') . '</label></div>' .
            ($systemUser->rights == 9 ? '<div class="radio"><label><input type="radio" value="2" name="del" /><i class="helper"></i>&#160;' . _t('Delete') . '&#160;&#160;</label></div>' : '') .
            '</div>' .
            '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Perform') . '</span></button></div>' .
            '</form></div>' .
            '<div class="list1"><a href="/forum/' . $id . '/' . $res['seo'] . '.html">' . _t('Cancel') . '</a></div></div>';
    }
}
