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
    $topic = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `id`='$id' AND `edit` != '1'")->fetchColumn();
    $topic_vote = $db->query("SELECT COUNT(*) FROM `cms_forum_vote` WHERE `type`='1' AND `topic`='$id'")->fetchColumn();
    require_once('../system/head.php');

    if ($topic_vote != 0 || $topic == 0) {
        echo $tools->displayError(_t('Wrong data'), '<a href="' . htmlspecialchars(getenv("HTTP_REFERER")) . '">' . _t('Back') . '</a>');
        require('../system/end.php');
        exit;
    }
    $resT = $db->query("SELECT `seo` FROM `forum` WHERE `type`='t' AND `id`='$id' ")->fetch();
    if (isset($_POST['submit'])) {
        $vote_name = mb_substr(trim($_POST['name_vote']), 0, 50);

        if (!empty($vote_name) && !empty($_POST[0]) && !empty($_POST[1]) && !empty($_POST['count_vote'])) {
            $db->exec("INSERT INTO `cms_forum_vote` SET
                `name`=" . $db->quote($vote_name) . ",
                `time`='" . time() . "',
                `type` = '1',
                `topic`='$id'
            ");
            $db->exec("UPDATE `forum` SET  `realid` = '1'  WHERE `id` = '$id'");
            $vote_count = abs(intval($_POST['count_vote']));

            if ($vote_count > 20) {
                $vote_count = 20;
            } else {
                if ($vote_count < 2) {
                    $vote_count = 2;
                }
            }

            for ($vote = 0; $vote < $vote_count; $vote++) {
                $text = mb_substr(trim($_POST[$vote]), 0, 30);

                if (empty($text)) {
                    continue;
                }

                $db->exec("INSERT INTO `cms_forum_vote` SET
                    `name`=" . $db->quote($text) . ",
                    `type` = '2',
                    `topic`='$id'
                ");
            }
            echo '<div class="gmenu text-center">' . _t('Poll added') . '<br /><a href="/forum/' . $id . '/' . $resT['seo'] . '.html">' . _t('Continue') . '</a></div>';
        } else {
            echo '<div class="rmenu text-center">' . _t('The required fields are not filled') . '<br /><a href="?act=addvote&amp;id=' . $id . '">' . _t('Repeat') . '</a></div>';
        }
    } else {
        echo '<div class="mrt-code card shadow--2dp"><div class="card__actions"><form action="index.php?act=addvote&amp;id=' . $id . '" method="post">' .
            '<div class="form-group">' .
            '<input type="text" size="20" maxlength="150" name="name_vote" value="' . htmlentities($_POST['name_vote'], ENT_QUOTES, 'UTF-8') . '" required="required" />' .
            '<label class="control-label" for="input">' . _t('Poll (max. 150)') . '</label><i class="bar"></i>' .
            '</div>';

        if (isset($_POST['plus'])) {
            ++$_POST['count_vote'];
        } elseif (isset($_POST['minus'])) {
            --$_POST['count_vote'];
        }

        if ($_POST['count_vote'] < 2 || empty($_POST['count_vote'])) {
            $_POST['count_vote'] = 2;
        } elseif ($_POST['count_vote'] > 20) {
            $_POST['count_vote'] = 20;
        }

        for ($vote = 0; $vote < $_POST['count_vote']; $vote++) {
            echo '<div class="form-group"><input type="text" name="' . $vote . '" value="' . htmlentities($_POST[$vote], ENT_QUOTES, 'UTF-8') . '" placeholder="Vote" />' .
                '<label class="control-label" for="input">' . _t('Answer') . ' ' . ($vote + 1) . ' (max. 50)</label><i class="bar"></i>' .
                '</div>';
        }

        echo '<input type="hidden" name="count_vote" value="' . abs(intval($_POST['count_vote'])) . '"/>' .
            '<div class="button-container">';
        echo ($_POST['count_vote'] < 20) ? '<button class="button" type="submit" name="plus"><span>' . _t('Add Answer') . '</span></button>' : '';
        echo $_POST['count_vote'] > 2 ? '<button class="button" type="submit" name="minus"><span>' . _t('Delete last') . '</span></button>' : '';
        echo '<button class="button" type="submit" name="submit"><span>' . _t('Save') . '</span></button></div></form></div>';
        echo '<div class="list1"><a href="/forum/' . $id . '/' . $resT['seo'] . '.html">' . _t('Back') . '</a></div></div>';
    }
} else {
    header('location: /?err');
}
