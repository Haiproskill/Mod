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

if ($systemUser->rights >= 7) {
    $req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 't'");

    if (!$req->rowCount() || $systemUser->rights < 7) {
        echo $tools->displayError(_t('Topic has been deleted or does not exists'));
        require('../system/end.php');
        exit;
    }

    $topic = $req->fetch();
    $req = $db->query("SELECT `forum`.*, `users`.`id`
        FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
        WHERE `forum`.`refid`='$id' AND `users`.`rights` < 6 AND `users`.`rights` != 3 GROUP BY `forum`.`from` ORDER BY `forum`.`from`");
    $total = $req->rowCount();
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4><a href="/forum/' . $id . '/' . $topic['seo'] . '_start' . $start . '.html"><b>' . _t('Forum') . '</b></a> | ' . _t('Curators') . '</h4></div>' .
        '<div class="bmenu">' . $topic['text'] . '</div>';
    $curators = [];
    $users = !empty($topic['curators']) ? unserialize($topic['curators']) : [];

    if (isset($_POST['submit'])) {
        $users = isset($_POST['users']) ? $_POST['users'] : [];
        if (!is_array($users)) {
            $users = [];
        }
    }

    if ($total > 0) {
        echo '<form action="index.php?act=curators&amp;id=' . $id . '&amp;start=' . $start . '" method="post">';
        $i = 0;
        echo '<div class="list1">';
        while ($res = $req->fetch()) {
            $checked = array_key_exists($res['user_id'], $users) ? true : false;

            if ($checked) {
                $curators[$res['user_id']] = $res['from'];
            }

            echo '<br /><div class="checkbox">' .
                '<label>' .
                '<input type="checkbox" name="users[' . $res['user_id'] . ']" value="' . $res['from'] . '"' . ($checked ? ' checked="checked"' : '') . '/><i class="helper"></i>&#160;' .
                '<a href="/profile/?user=' . $res['user_id'] . '">' . $res['from'] . '</a>' .
                '</label></div><br />';
        }

        echo '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Assign') . '</span></button></div></div></form>';

        if (isset($_POST['submit'])) {
            $db->exec("UPDATE `forum` SET `curators`=" . $db->quote(serialize($curators)) . " WHERE `id` = '$id'");
        }

    } else {
        echo $tools->displayError(_t('The list is empty'));
    }
    echo '</div><div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div></div>' .
        '<div class="mrt-code card shadow--2dp"><div class="card__actions"><a href="/forum/' . $id . '/' . $topic['seo'] . '_start' . $start . '.html">' . _t('Back') . '</a></div></div>';
}
