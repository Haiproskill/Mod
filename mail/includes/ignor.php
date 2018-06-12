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

$headmod = 'mail';
$textl = _t('Mail');
require_once('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('Blocklist') . '</h4></div>';

if (isset($_GET['del'])) {
    if ($id) {
        //Проверяем существование пользователя
        $req = $db->query('SELECT * FROM `users` WHERE `id` = ' . $id);

        if (!$req->rowCount()) {
            echo $tools->displayError(_t('User does not exists'), '', 'text-center');
            echo '</div>';
            require_once("../system/end.php");
            exit;
        }

        //Удаляем из заблокированных
        if (isset($_POST['submit'])) {
            $q = $db->query("SELECT * FROM `cms_contact` WHERE `user_id`='" . $systemUser->id . "' AND `from_id`='" . $id . "' AND `ban`='1'");

            if (!$q->rowCount()) {
                echo '<div class="rmenu">' . _t('User not blocked') . '</div>';
            } else {
                $db->exec("UPDATE `cms_contact` SET `ban`='0' WHERE `user_id`='" . $systemUser->id . "' AND `from_id`='$id' AND `ban`='1'");
                echo '<div class="rmenu">' . _t('User is unblocked') . '</div>';
            }
        } else {
            echo '<div class="list1 text-center"><form action="index.php?act=ignor&amp;id=' . $id . '&amp;del" method="post">
			' . _t('You really want to unblock contact?') . '
			<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Unblock') . '</span></button></div>
			</form></div>';
        }
    } else {
        echo $tools->displayError(_t('Contact isn\'t chosen'), '', 'text-center');
    }
} elseif (isset($_GET['add'])) {
    if ($id) {
        $req = $db->query('SELECT * FROM `users` WHERE `id` = ' . $id);
        if (!$req->rowCount()) {
            echo $tools->displayError(_t('User does not exists'), '', 'text-center');
            echo '</div>';
            require_once("../system/end.php");
            exit;
        }

        $res = $req->fetch();
        //Добавляем в заблокированные
        if (isset($_POST['submit'])) {
            if ($res['rights'] > $systemUser->rights) {
                echo '<div class="rmenu text-center">' . _t('This user can not be blocked') . '</div>';
            } else {
                $q = $db->query("SELECT * FROM `cms_contact`
				WHERE `user_id`='" . $systemUser->id . "' AND `from_id`='" . $id . "';");

                if (!$q->rowCount()) {
                    $db->query("INSERT INTO `cms_contact` SET
					`user_id` = '" . $systemUser->id . "',
					`from_id` = '" . $id . "',
					`time` = '" . time() . "',
					`ban`='1'");
                } else {
                    $db->exec("UPDATE `cms_contact` SET `ban`='1', `friends`='0', `type`='1' WHERE `user_id`='" . $systemUser->id . "' AND `from_id`='$id'");
                    $db->exec("UPDATE `cms_contact` SET `friends`='0', `type`='1' WHERE `user_id`='$id' AND `from_id`='" . $systemUser->id . "'");
                }

                echo '<div class="rmenu text-center">' . _t('User is blocked') . '</div>';
            }
        } else {
            echo '<div class="list1 text-center"><form action="index.php?act=ignor&amp;id=' . $id . '&amp;add" method="post">
			<p>' . _t('You really want to block contact?') . '</p>
			<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Block') . '</span></button></div>
			</form></div>';
            echo '<div class="list1"><a href="' . (isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php') . '">' . _t('Back') . '</a></div>';
        }
    } else {
        echo $tools->displayError(_t('Contact isn\'t chosen'), '', 'text-center');
    }
    echo '</div>';
} else {
    echo '<div class="topmenu"><a href="index.php">' . _t('My Contacts') . '</a> | <b>' . _t('Blocklist') . '</b></div>';

    //Отображаем список заблокированных контактов
    $total = $db->query("SELECT COUNT(*) FROM `cms_contact` WHERE `user_id` = '" . $systemUser->id . "' AND `ban`='1'")->fetchColumn();

    if ($total) {
        if ($total > $kmess) {
            echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=ignor&amp;', $start, $total, $kmess) . '</div>';
        }

        $req = $db->query("SELECT `users`.* FROM `cms_contact`
		    LEFT JOIN `users` ON `cms_contact`.`from_id`=`users`.`id`
		    WHERE `cms_contact`.`user_id`='" . $systemUser->id . "'
		    AND `ban`='1'
		    ORDER BY `cms_contact`.`time` DESC
		    LIMIT $start, $kmess"
        );

        for ($i = 0; ($row = $req->fetch()) !== false; ++$i) {
            echo '<div class="list1">';
            $subtext = '<a href="index.php?act=write&amp;id=' . $row['id'] . '">' . _t('Correspondence') . '</a> | <a href="index.php?act=deluser&amp;id=' . $row['id'] . '">' . _t('Delete') . '</a> | <a href="index.php?act=ignor&amp;id=' . $row['id'] . '&amp;del">' . _t('Unblock') . '</a>';
            $count_message = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE ((`user_id`='{$row['id']}' AND `from_id`='" . $systemUser->id . "') OR (`user_id`='" . $systemUser->id . "' AND `from_id`='{$row['id']}')) AND `delete`!='" . $systemUser->id . "' AND `sys`!='1' AND `spam`!='1';")->fetchColumn();
            $new_count_message = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `cms_mail`.`user_id`='" . $systemUser->id . "' AND `cms_mail`.`from_id`='{$row['id']}' AND `read`='0' AND `delete`!='" . $systemUser->id . "' AND `sys`!='1' AND `spam`!='1'")->fetchColumn();
            $arg = [
                'header' => '(' . $count_message . ($new_count_message ? '/<span class="red">+' . $new_count_message . '</span>' : '') . ')',
                'sub'    => $subtext,
            ];
            echo $tools->displayUser($row, $arg);
            echo '</div>';
        }
    } else {
        echo '<div class="list1 text-center">' . _t('The list is empty') . '</div>';
    }
    echo '</div>';
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    if ($total > $kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('index.php?act=ignor&amp;', $start, $total, $kmess) . '</div>';
    }
    echo '</div>';
}
