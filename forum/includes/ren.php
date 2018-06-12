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

    $ms = $db->query("SELECT * FROM `forum` WHERE `id` = '$id'")->fetch();

    if ($ms['type'] != "t") {
        require('../system/head.php');
        echo $tools->displayError(_t('Wrong data'));
        require('../system/end.php');
        exit;
    }

    if (isset($_POST['submit'])) {
        $nn = isset($_POST['nn']) ? trim($_POST['nn']) : '';
        $tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
        $bai = isset($_POST['bai']) ? trim($_POST['bai']) : '';
        $tiento = isset($_POST['tiento']) ? trim($_POST['tiento']) : '';
        $seo = $tools->seourl($nn);

        if (!$nn) {
            require('../system/head.php');
            echo $tools->displayError(_t('You have not entered topic name'), '<a href="index.php?act=ren&amp;id=' . $id . '">' . _t('Repeat') . '</a>');
            require('../system/end.php');
            exit;
        }

        // Проверяем, есть ли тема с таким же названием?
        $pt = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `refid` = '" . $ms['refid'] . "' and text=" . $db->quote($nn) . " LIMIT 1");

        if ($pt->rowCount()) {
            require('../system/head.php');
            echo $tools->displayError(_t('Topic with same name already exists in this section'), '<a href="index.php?act=ren&amp;id=' . $id . '">' . _t('Repeat') . '</a>');
            require('../system/end.php');
            exit;
        }

        $db->exec("UPDATE `forum` SET  `text`=" . $db->quote($nn) . ", `seo`=" . $db->quote($seo) . ", `tags`=" . $db->quote($tags) . ", `bai`=" . $db->quote($bai) . " WHERE id='" . $id . "'");
        header("Location: " . $config['homeurl'] . "/forum/$id/$seo.html");
    } else {
        // Переименовываем тему
        require('../system/head.php');
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="' . $config['homeurl'] . '/forum/' . $id . '/' . $ms['seo'] . '.html"><b>' . _t('Forum') . '</b></a>&#160;|&#160;' . _t('Rename Topic') . '</div>' .
            '<div class="list1"><form action="index.php?act=ren&amp;id=' . $id . '" method="post">' .
            '<div class="form-group">' .
            '<input type="text" name="nn" value="' . $ms['text'] . '" required="required" />' .
            '<label class="control-label" for="input">' . _t('Topic name') . '</label><i class="bar"></i>' .
            '</div>' .
            '<div class="form-group">' .
            '<input type="text" name="tags" value="' . $ms['tags'] . '" placeholder="Tags" />' .
            '<label class="control-label" for="input">Tags:</label><i class="bar"></i>' .
            '</div>' .
            '<div class="form-group">' .
            '<input type="number" name="bai" value="' . $ms['bai'] . '" placeholder="0" />' .
            '<label class="control-label" for="input">Bài (chỉ khi bạn đang viết dự án, VD: bài 1, bài 2, ...):</label><i class="bar"></i>' .
            '</div>' .
            '<div class="button-container">' .
            '<button class="button" type="submit" name="submit"><span>' . _t('Save') . '</span></button>' .
            '</div>' .
            '</form></div></div>' .
            '<div class="mrt-code card shadow--2dp"><div class="card__actions"><a href="' . $config['homeurl'] . '/forum/' . $id . '/' . $ms['seo'] . '.html">' . _t('Back') . '</a></div></div>';
    }
}
