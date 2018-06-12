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

if (!$systemUser->isValid() || !$id) {
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
}

$req = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 'm' " . ($systemUser->rights >= 7 ? "" : " AND `close` != '1'"));

if ($req->rowCount()) {
    // Предварительные проверки
    $res = $req->fetch();

    $topic = $db->query("SELECT `refid`, `curators`, `seo` FROM `forum` WHERE `id` = " . $res['refid'])->fetch();
    $curators = !empty($topic['curators']) ? unserialize($topic['curators']) : [];

    if (array_key_exists($systemUser->id, $curators)) {
        $systemUser->rights = 3;
    }

    $page = ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '$id'" . ($systemUser->rights < 7 ? " AND `close` != '1'" : ''))->fetchColumn() / $kmess);
    $posts = $db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `close` != '1'")->fetchColumn();
    $link = $config['homeurl'] . '/forum/' . $res['refid'] . '/' . $topic['seo'] . ($page > 1 ? '_p' . $page : '') . '.html#post-' . $res['id'];
    $error = false;

    if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
        // Проверка для Администрации
        if ($res['user_id'] != $systemUser->id) {
            $req_u = $db->query("SELECT * FROM `users` WHERE `id` = '" . $res['user_id'] . "'");

            if ($req_u->rowCount()) {
                $res_u = $req_u->fetch();

                if ($res_u['rights'] >= $systemUser->rights) {
                    $error = _t('You cannot edit posts of higher administration') . '<br /><a href="' . $link . '">' . _t('Back') . '</a>';
                }
            }
        }
    } else {
        // Проверка для обычных юзеров
        if ($res['user_id'] != $systemUser->id) {
            $error = _t('You are trying to change another\'s post') . '<br /><a href="' . $link . '">' . _t('Back') . '</a>';
        }

        if (!$error) {
            $section = $db->query("SELECT * FROM `forum` WHERE `id` = " . $topic['refid'])->fetch();
            $allow = !empty($section['edit']) ? intval($section['edit']) : 0;
            $check = true;

            if ($allow == 2) {
                $first = $db->query("SELECT * FROM `forum` WHERE `refid` = '" . $res['refid'] . "' ORDER BY `id` ASC LIMIT 1")->fetch();

                if ($first['user_id'] == $systemUser->id && $first['id'] == $id) {
                    $check = false;
                }
            }
        }
    }
} else {
    $error = _t('Message does not exists or has been deleted') . '<br /><a href="index.php">' . _t('Forum') . '</a>';
}

$fid = isset($_GET['fid']) && $_GET['fid'] > 0 ? abs(intval($_GET['fid'])) : false;
if($fid){
    $req_f = $db->query("SELECT * FROM `cms_forum_files` WHERE `id` = " . $fid);
    $res_f = $req_f->fetch();
    $req_ufile = $db->query("SELECT * FROM `users` WHERE `id` = '" . $res_f['user_id'] . "'");
    if ($req_ufile->rowCount()) {
        $res_ufile = $req_ufile->fetch();
        if($res_ufile['id'] != $systemUser->id && $res_ufile['rights'] >= $systemUser->rights){
            $error = _t('You cannot edit posts of higher administration') . '<br /><a href="' . $link . '">' . _t('Back') . '</a>';
        }
    }
}

if (!$error) {
    switch ($do) {
        case 'restore':
            // Восстановление удаленного поста
            $req_u = $db->query("SELECT `postforum` FROM `users` WHERE `id` = '" . $res['user_id'] . "'");

            if ($req_u->rowCount()) {
                // Добавляем один балл к счетчику постов юзера
                $res_u = $req_u->fetch();
                $db->exec("UPDATE `users` SET `postforum` = '" . ($res_u['postforum'] + 1) . "' WHERE `id` = '" . $res['user_id'] . "'");
            }

            $db->exec("UPDATE `forum` SET `close` = '0', `close_who` = " . $db->quote($systemUser->name) . " WHERE `id` = '$id'");
            $req_f = $db->query("SELECT * FROM `cms_forum_files` WHERE `post` = '$id'");

            if ($req_f->rowCount()) {
                $db->exec("UPDATE `cms_forum_files` SET `del` = '0' WHERE `post` = '$id'");
            }

            header('Location: ' . $link);
            break;

        case 'delfile':
            // Удаление поста, предварительное напоминание
            echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="' . $link . '"><b>' . _t('Forum') . '</b></a>&#160;|&#160' . _t('Delete file') . '</div>'
                . '<div class="rmenu"><center>'
                . _t('Do you really want to delete?') . '</center>'
                . '</div>'
                . '<div class="menu">'
                . '<form method="post" action="index.php?act=editpost&amp;do=deletefile&amp;fid=' . $fid . '&amp;id=' . $id . '">'
                . '<div class="button-container">'
                . '<button class="button" type="submit" name="delfile"><span>' . _t('Delete') . '</span></button>'
                . '</div></form>'
                . '</div>'
                . '<div class="card__actions card--border"><a href="' . $link . '">' . _t('Cancel') . '</a></div>'
                . '</div></div>';
            break;

        case 'deletefile':
            if (isset($_POST['delfile'])) {
                if ($req_f->rowCount()) {
                    $db->exec("DELETE FROM `cms_forum_files` WHERE `id` = " . $fid);
                    unlink('../files/forum/attach/' . $res_f['filelink']);
                    header('Location: ' . $link);
                } else {
                    echo $tools->displayError(_t('You cannot edit your posts after 5 minutes') . '<br /><a href="' . $link . '">' . _t('Back') . '</a>');
                    require('../system/end.php');
                    exit;
                }
            }
            break;

        case 'delete':
            // Удаление поста и прикрепленного файла
            if ($res['close'] != 1) {
                $req_u = $db->query("SELECT `postforum` FROM `users` WHERE `id` = '" . $res['user_id'] . "'");

                if ($req_u->rowCount()) {
                    // Вычитаем один балл из счетчика постов юзера
                    $res_u = $req_u->fetch();
                    $postforum = $res_u['postforum'] > 0 ? $res_u['postforum'] - 1 : 0;
                    $db->exec("UPDATE `users` SET `postforum` = '" . $postforum . "' WHERE `id` = '" . $res['user_id'] . "'");
                }
            }

            if ($systemUser->rights == 9 && !isset($_GET['hide'])) {
                // Удаление поста (для Супервизоров)
                $req_f = $db->query("SELECT * FROM `cms_forum_files` WHERE `post` = '$id'");

                if ($req_f->rowCount()) {
                    // Если есть прикрепленные файлы, удаляем их
                    while ($res_f = $req_f->fetch()) {
                        unlink('../files/forum/attach/' . $res_f['filelink']);
                    }
                }
                $db->exec("DELETE FROM `cms_forum_files` WHERE `post` = " . $id);

                // Формируем ссылку на нужную страницу темы
                $page = ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">" : "<") . " '$id'")->fetchColumn() / $kmess);
                $db->exec("DELETE FROM `forum` WHERE `id` = '$id'");

                if ($posts < 2) {
                    // Пересылка на удаление всей темы
                    header('Location: index.php?act=deltema&id=' . $res['refid']);
                } else {
                    header('Location: ' . $config['homeurl'] . '/forum/' . $res['refid'] . '/' . $topic['seo'] . ($page > 1 ? '_p' . $page : '') . '.html');
                }
            } else {
                // Скрытие поста
                $req_f = $db->query("SELECT * FROM `cms_forum_files` WHERE `post` = '$id'");

                if ($req_f->rowCount()) {
                    // Если есть прикрепленные файлы, скрываем их
                    $db->exec("UPDATE `cms_forum_files` SET `del` = '1' WHERE `post` = '$id'");
                }

                if ($posts == 1) {
                    // Если это был последний пост темы, то скрываем саму тему
                    $res_l = $db->query("SELECT `refid` FROM `forum` WHERE `id` = '" . $res['refid'] . "'")->fetch();
                    $res_t = $db->query("SELECT `seo` FROM `forum` WHERE `id` = '" . $res_l['refid'] . "'")->fetch();
                    $db->exec("UPDATE `forum` SET `close` = '1', `close_who` = '" . $systemUser->name . "' WHERE `id` = '" . $res['refid'] . "' AND `type` = 't'");
                    header('Location: ' . $config['homeurl'] . '/forum/' . $res_l['refid'] . '/' . $res_t['seo'] . '.html');
                } else {
                    $db->exec("UPDATE `forum` SET `close` = '1', `close_who` = '" . $systemUser->name . "' WHERE `id` = '$id'");
                    header('Location: ' . $config['homeurl'] . '/forum/' . $res['refid'] . '/' . $topic['seo'] . ($page > 1 ? '_p' . $page : '') . '.html');
                }
            }
            break;

        case 'del':
            // Удаление поста, предварительное напоминание
            echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="' . $link . '"><b>' . _t('Forum') . '</b></a>&#160;|&#160;' . _t('Delete Message') . '</div>' .
                '<div class="rmenu"><center>';

            if ($posts == 1) {
                echo _t('WARNING!<br>This is last post of topic. By deleting this post topic will be deleted (or hidden) too') . '<br>';
            }

            echo _t('Do you really want to delete?') . '</p>' .
                '</center><div class="button-container"><a href="' . $link . '"><button class="button"><span>' . _t('Cancel') . '</span></button></a>&#160;<a href="index.php?act=editpost&amp;do=delete&amp;id=' . $id . '"><button class="button"><span>' . _t('Delete') . '</span></button></a>';

            if ($systemUser->rights == 9) {
                echo '&#160;<a href="index.php?act=editpost&amp;do=delete&amp;hide&amp;id=' . $id . '"><button class="button"><span>' . _t('Hide') . '</span></button></a>';
            }

            echo '</div></div>';
            echo '<div class="list1"><small>' . _t('After deleting, one point will be subtracted from the counter of forum posts') . '</small></div></div>';
            break;

        default:
            // Редактирование поста
            $msg = isset($_POST['msg']) ? trim($_POST['msg']) : '';

            if (isset($_POST['submit'])) {
                if (empty($_POST['msg'])) {
                    echo $tools->displayError(_t('You have not entered the message'), '<a href="index.php?act=editpost&amp;id=' . $id . '">' . _t('Repeat') . '</a>');
                    require('../system/end.php');
                    exit;
                }

                $db->prepare('
                  UPDATE `forum` SET
                  `tedit` = ?,
                  `edit` = ?,
                  `kedit` = ?,
                  `text` = ?
                  WHERE `id` = ?
                ')->execute([
                    time(),
                    $systemUser->name,
                    ($res['kedit'] + 1),
                    $msg,
                    $id,
                ]);

                header('Location: ' . $config['homeurl'] . '/forum/' . $res['refid'] . '/' . $topic['seo'] . ($page > 1 ? '_p' . $page : '') . '.html');
            } else {
                $msg_pre = $tools->checkout($msg, 1, 1);
                $msg_pre = $tools->smilies($msg_pre, $systemUser->rights ? 1 : 0);
                $msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);
                echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="' . $link . '"><b>' . _t('Forum') . '</b></a>&#160;|&#160;' . _t('Edit Message') . '</div>';

                if ($msg && !isset($_POST['submit'])) {
                    $user = $db->query("SELECT * FROM `users` WHERE `id` = '" . $res['user_id'] . "' LIMIT 1")->fetch();
                    echo '<div class="list1">' . $tools->displayUser($user, ['iphide' => 1, 'header' => '<span class="gray">(' . $tools->displayDate($res['time']) . ')</span>', 'body' => $msg_pre]) . '</div>';
                }

                echo '<div class="card__actions card--border"><form name="form" action="?act=editpost&amp;id=' . $id . '&amp;start=' . $start . '" method="post">';
                echo App::getContainer()->get(Johncms\Api\BbcodeInterface::class)->buttons('form', 'msg');
                echo '<div class="form-group"><textarea rows="' . $systemUser->getConfig()->fieldHeight * 2 . '" name="msg" required="required">' . (empty($_POST['msg']) ? htmlentities($res['text'], ENT_QUOTES, 'UTF-8') : $tools->checkout($_POST['msg'])) . '</textarea>';
                echo  '<label class="control-label" for="textarea">' . _t('Edit Message') . '.....</label><i class="bar"></i>' .
                    '</div>';

                echo '<div class="button-container">' .
                    '<button class="button" type="submit" name="submit"><span>' . _t('Save') . '</span></button>' .
                    ($set_forum['preview'] ? '<button class="button" type="submit"><span>' . _t('Preview') . '</span></button>' : '') .
                    '</div></form></div>' .
                    '<div class="card__actions card--border"><a href="../help/?act=smileys">' . _t('Smilies') . '</a></div>' .
                    '<div class="card__actions card--border"><a href="' . $link . '">' . _t('Back') . '</a></div></div>';
            }
    }
} else {
    // Выводим сообщения об ошибках
    echo $tools->displayError($error);
}
