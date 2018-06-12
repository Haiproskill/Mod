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

    $typ = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `id` = '$id' ");
    $ms = $typ->fetch();
    if (!$typ->rowCount()) {
        require('../system/head.php');
        echo $tools->displayError(_t('Wrong data'));
        require('../system/end.php');
        exit;
    }

    if (isset($_GET['razd'])) {
        $razd = isset($_GET['razd']) ? abs(intval($_GET['razd'])) : false;

        if (!$razd) {
            require('../system/head.php');
            echo $tools->displayError(_t('Wrong data'));
            require('../system/end.php');
            exit;
        }

        $typ1 = $db->query("SELECT * FROM `forum` WHERE (`type` = 'r' OR `type` = 'c') AND `id` = '$razd'");

        if (!$typ1->rowCount()) {
            require('../system/head.php');
            echo $tools->displayError(_t('Wrong data'));
            require('../system/end.php');
            exit;
        }

        $db->exec("UPDATE `forum` SET
            `refid` = '$razd'
            WHERE `id` = '$id'
        ");
        header("Location: /forum/$id/" . $ms['seo'] . ".html");
    } else {
        // Перенос темы
        require('../system/head.php');

        if (empty($_GET['other'])) {
            $rz1 = $db->query("SELECT * FROM `forum` WHERE id='" . $ms['refid'] . "'")->fetch();
            $other = $rz1['refid'];
        } else {
            $other = intval($_GET['other']);
        }

        $fr1 = $db->query("SELECT * FROM `forum` WHERE id='" . $other . "'")->fetch();
        $rest = true;
        $allow = 0;
        $parent = $fr1['refid'];
        while ($parent != '0' && $rest != false) {
            $rest = $db->query("SELECT * FROM `forum` WHERE `id` = '$parent' LIMIT 1")->fetch();
            if ($rest['type'] == 'f' || $rest['type'] == 'r' || $rest['type'] == 'c') {
                $tree[] = '<a href="index.php?act=per&amp;id=' . $id . '&amp;other=' . $parent . '">' . $rest['text'] . '</a>';

                if (($rest['type'] == 'r'  || $rest['type'] == 'c') && !empty($rest['edit'])) {
                    $allow = intval($rest['edit']);
                }
            }
            $parent = $rest['refid'];
        }
        $tree[] = '<a href="index.php?act=per&amp;id=' . $id . '">Gốc</a>';
        @krsort($tree);
        $tree[] = $fr1['text'];
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4><a href="/forum/' . $id . '/' . $ms['seo'] . '.html"><b>' . _t('Forum') . '</b></a>&#160;|&#160;' . _t('Move Topic') . '</h4></div>' .
            '<div class="bmenu">' .
            '<b>' . _t('Category') . '</b>: ' . @implode(' > ', $tree) .
            '</div>';
        $raz = $db->query("SELECT * FROM `forum` WHERE `refid` = '$other' AND (`type` = 'f' OR `type` = 'r' OR `type` = 'c') ORDER BY `realid` ASC");

        while ($raz1 = $raz->fetch()) {
        	$coltem = $db->query("SELECT COUNT(*) FROM `forum` WHERE (`type` = 'r' OR `type` = 'c') AND `refid` = '" . $raz1['id'] . "'")->fetchColumn();
            echo '<div class="list1">';
            if ($coltem) {
                echo '<a href="index.php?act=per&amp;id=' . $id . '&amp;other=' . $raz1['id']  . '">' . $raz1['text'] . '</a>';
                echo " [$coltem]";
            }else{
                echo '' . $raz1['text'] . ' <a href="index.php?act=per&amp;id=' . $id . '&amp;razd=' . $raz1['id']  . '">(' . _t('Move') . ')</a>';
            }
            echo '</div>';
        }
        echo '</div>' .
            '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('Other categories') . '</h4></div>';
        $frm = $db->query("SELECT * FROM `forum` WHERE `type` = 'f' ORDER BY `realid` ASC");

        while ($frm1 = $frm->fetch()) {
            $coltem = $db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $frm1['id'] . "'")->fetchColumn();
            echo '<div class="list1">';
            if ($other == $frm1['id']){
                echo '<b>' . $frm1['text'] . '</b>';
            }else{
                echo '<a href="index.php?act=per&amp;id=' . $id . '&amp;other=' . $frm1['id'] . '">' . $frm1['text'] . '</a>';
            }
            if ($coltem) {
                echo " [$coltem]";
            }
            echo '</div>';
            ++$i;
        }

        echo '</div>';
    }
}
