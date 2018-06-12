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

$headmod = 'news';
require('../system/bootstrap.php');

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';
$do = isset($_REQUEST['do']) ? trim($_REQUEST['do']) : false;

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

$textl = _t('News');
require('../system/head.php');

switch ($do) {
    case 'add':
        // Добавление новости
        if ($systemUser->rights >= 6) {
            echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php"><h4><b>' . _t('News') . '</b></a>&#160;|&#160;' . _t('Add') . '</h4></div>';
            $old = 20;

            if (isset($_POST['submit'])) {
                $error = [];
                $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : false;
                $seo = $tools->seourl($name);
                $text = isset($_POST['text']) ? trim($_POST['text']) : false;

                if (!$name) {
                    $error[] = _t('You have not entered news title');
                }

                if (!$text) {
                    $error[] = _t('You have not entered news text');
                }

                $flood = $tools->antiflood();

                if ($flood) {
                    $error[] = sprintf(_t('You cannot add the message so often. Please, wait %d seconds.'), $flood);
                }

                if (!$error) {
                    $rid = 0;

                    if (!empty($_POST['pf']) && ($_POST['pf'] != '0')) {
                        $pf = intval($_POST['pf']);
                        $rz = $_POST['rz'];
                        $pr = $db->query("SELECT * FROM `forum` WHERE `refid` = '$pf' AND `type` = 'r'");

                        while ($pr1 = $pr->fetch()) {
                            $arr[] = $pr1['id'];
                        }

                        foreach ($rz as $v) {
                            if (in_array($v, $arr)) {
                                $db->prepare('
                                  INSERT INTO `forum` SET
                                  `refid` = ?,
                                  `type` = \'t\',
                                  `time` = ?,
                                  `user_id` = ?,
                                  `from` = ?,
                                  `text` = ?,
                                  `seo` = ?,
                                  `soft` = \'\',
                                  `edit` = \'\',
                                  `curators` = \'\'
                                ')->execute([
                                    $v,
                                    time(),
                                    $systemUser->id,
                                    $systemUser->name,
                                    $name,
                                    $seo,
                                ]);

                                /** @var Johncms\Api\EnvironmentInterface $env */
                                $env = $container->get(Johncms\Api\EnvironmentInterface::class);
                                $rid = $db->lastInsertId();

                                $db->prepare('
                                  INSERT INTO `forum` SET
                                  `refid` = ?,
                                  `type` = \'m\',
                                  `time` = ?,
                                  `user_id` = ?,
                                  `from` = ?,
                                  `ip` = ?,
                                  `soft` = ?,
                                  `text` = ?,
                                  `edit` = \'\',
                                  `curators` = \'\'
                                ')->execute([
                                    $rid,
                                    time(),
                                    $systemUser->id,
                                    $systemUser->name,
                                    $env->getIp(),
                                    $env->getUserAgent(),
                                    $text,
                                ]);
                            }
                        }
                    }

                    $db->prepare('
                      INSERT INTO `news` SET
                      `time` = ?,
                      `avt` = ?,
                      `name` = ?,
                      `text` = ?,
                      `kom` = ?
                    ')->execute([
                        time(),
                        $systemUser->name,
                        $name,
                        $text,
                        $rid,
                    ]);

                    $db->exec('UPDATE `users` SET `lastpost` = ' . time() . ' WHERE `id` = ' . $systemUser->id);
                    echo '<div class="gmenu text-center">' . _t('News added') . '<br /><a href="index.php">' . _t('Back to news') . '</a></div>';
                } else {
                    echo $tools->displayError($error, '<a href="index.php">' . _t('Back to news') . '</a>');
                }
                echo '</div>';
            } else {
                echo '<form action="index.php?do=add" method="post"><div class="list1">' .
                    '<div class="form-group">' .
                    '<input type="text" name="name" value="" required="required" />' .
                    '<label class="control-label" for="input">' . _t('Title') . '</label><i class="bar"></i>' .
                    '</div>' .
                    '<div class="form-group">' .
                    '<textarea rows="' . $systemUser->getConfig()->fieldHeight . '" name="text" required="required"></textarea>' .
                    '<label class="control-label" for="textarea">' . _t('Text') . '</label><i class="bar"></i>' .
                    '</div>' .
                    '<p><h3>' . _t('Discussion') . '</h3></p>' .
                    '<div class="form-radio">';
                $fr = $db->query("SELECT * FROM `forum` WHERE `type` = 'f'");

                while ($fr1 = $fr->fetch()) {
                    echo '<div class="radio"><label><input type="radio" name="pf" value="' . $fr1['id'] . '"/><i class="helper"></i>' .
                        '<div class="form-group valign-sub" style="display:inline;"><select name="rz[]">';
                    $pr = $db->query("SELECT * FROM `forum` WHERE `type` = 'r' AND `refid` = '" . $fr1['id'] . "'");

                    while ($pr1 = $pr->fetch()) {
                        echo '<option value="' . $pr1['id'] . '">' . $pr1['text'] . '</option>';
                    }
                    echo '</select><label class="control-label" for="select">' . $fr1['text'] . '</label><i class="bar"></i>' .
                        '</div></label></div>';
                }
                echo '<div class="radio"><label><input type="radio" name="pf" value="0" checked="checked" /><i class="helper"></i>' . _t('Do not discuss') . '</label></div>';
                echo '</div><div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Save') . '</span></button></div>' .
                    '</div></form>' .
                    '<div class="list1"><a href="index.php">' . _t('Back to news') . '</a></div>';
                echo '</div>';
            }
        } else {
            header("location: index.php");
        }
        break;

    case 'edit':
        // Редактирование новости
        if ($systemUser->rights >= 6) {
            echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php"><h4><b>' . _t('News') . '</b></a>&#160;|&#160;' . _t('Edit') . '</h4></div>';

            if (!$id) {
                echo $tools->displayError(_t('Wrong data')) . '</div>';
                require('../system/end.php');
                exit;
            }

            if (isset($_POST['submit'])) {
                $error = [];

                if (empty($_POST['name'])) {
                    $error[] = _t('You have not entered news title');
                }

                if (empty($_POST['text'])) {
                    $error[] = _t('You have not entered news text');
                }

                $name = htmlspecialchars(trim($_POST['name']));
                $text = trim($_POST['text']);

                if (!$error) {
                    $db->prepare('
                      UPDATE `news` SET
                      `name` = ?,
                      `text` = ?
                      WHERE `id` = ?
                    ')->execute([
                        $name,
                        $text,
                        $id,
                    ]);
                    echo '<div class="gmenu text-center">' . _t('Article changed') . '</div>';
                } else {
                    echo $tools->displayError($error);
                }
            }
            $res = $db->query("SELECT * FROM `news` WHERE `id` = '$id'")->fetch();
            echo '<div class="list1"><form action="index.php?do=edit&amp;id=' . $id . '" method="post">' .
                '<div class="form-group">' .
                '<input type="text" name="name" value="' . $res['name'] . '" required="required" />' .
                '<label class="control-label" for="input">' . _t('Title') . '</label><i class="bar"></i>' .
                '</div>' .
                '<div class="form-group">' .
                '<textarea rows="' . $systemUser->getConfig()->fieldHeight . '" name="text" required="required">' . htmlentities($res['text'], ENT_QUOTES, 'UTF-8') . '</textarea>' .
                '<label class="control-label" for="textarea">' . _t('Text') . '</label><i class="bar"></i>' .
                '</div>' .
                '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Save') . '</span></button></div>' .
                '</form></div>' .
                '<div class="list1"><a href="index.php">' . _t('Back to news') . '</a></div>' .
                '</div>';
        } else {
            header('location: index.php');
        }
        break;

    case 'clean':
        // Чистка новостей
        if ($systemUser->rights >= 7) {
            echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php"><h4><b>' . _t('News') . '</b></a>&#160;|&#160;' . _t('Clear') . '</h4></div>';

            if (isset($_POST['submit'])) {
                $cl = isset($_POST['cl']) ? intval($_POST['cl']) : '';

                switch ($cl) {
                    case '1':
                        // Чистим новости, старше 1 недели
                        $db->query("DELETE FROM `news` WHERE `time` <= " . (time() - 604800));
                        $db->query("OPTIMIZE TABLE `news`");

                        echo '<div class="gmenu text-center">' . _t('Delete all news older than 1 week') . '</div>';
                        break;

                    case '2':
                        // Проводим полную очистку
                        $db->query("TRUNCATE TABLE `news`");

                        echo '<div class="gmenu text-center">' . _t('Delete all news') . '</div>';
                        break;
                    default :
                        // Чистим сообщения, старше 1 месяца
                        $db->query("DELETE FROM `news` WHERE `time` <= " . (time() - 2592000));
                        $db->query("OPTIMIZE TABLE `news`;");

                        echo '<div class="gmenu text-center">' . _t('Delete all news older than 1 month') . '</div>';
                }
            }
            echo '<div class="list1"><form id="clean" method="post" action="index.php?do=clean">' .
                '<h3>' . _t('Clearing parameters') . '</h3>' .
                '<div class="form-radio">' .
                '<div class="radio"><label><input type="radio" name="cl" value="0" checked="checked" /><i class="helper"></i>' . _t('Older than 1 month') . '</label></div>' .
                '<div class="radio"><label><input type="radio" name="cl" value="1" /><i class="helper"></i>' . _t('Older than 1 week') . '</label></div>' .
                '<div class="radio"><label><input type="radio" name="cl" value="2" /><i class="helper"></i>' . _t('Clear all') . '</label></div>' .
                '</div>' .
                '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Clear') . '</span></button></div>' .
                '</form></div>' .
                '<div class="list1"><a href="index.php">' . _t('Cancel') . '</a></div>' .
                '</div>';
        } else {
            header("location: index.php");
        }
        break;

    case 'del':
        // Удаление новости
        if ($systemUser->rights >= 6) {
            echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php"><h4><b>' . _t('News') . '</b></a>&#160;|&#160;' . _t('Delete') . '</h4></div>';

            if (isset($_GET['yes'])) {
                $db->query("DELETE FROM `news` WHERE `id` = '$id'");

                echo '<div class="gmenu text-center">' . _t('Article deleted') . '<br /><a href="index.php">' . _t('Back to news') . '</a></div>';
            } else {
                echo '<div class="rmenu text-center">' . _t('Do you really want to delete?') .
                    '<br /><a href="index.php?do=del&amp;id=' . $id . '&amp;yes">' . _t('Delete') . '</a> | <a href="index.php">' . _t('Cancel') . '</a></div>';
            }
            echo '</div>';
        } else {
            header("location: index.php");
        }
        break;

    default:
        // Вывод списка новостей
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('News') . '</h4></div>';

        if ($systemUser->rights >= 6) {
            echo '<div class="topmenu"><a href="index.php?do=add">' . _t('Add') . '</a> | <a href="index.php?do=clean">' . _t('Clear') . '</a></div>';
        }
        echo '</div>';
        $total = $db->query("SELECT COUNT(*) FROM `news`")->fetchColumn();
        if ($total){
            echo '<div class="mrt-code card shadow--2dp">';
        }
        $req = $db->query("SELECT * FROM `news` ORDER BY `time` DESC LIMIT $start, $kmess");
        $i = 0;

        while ($res = $req->fetch()) {
            echo '<div class="' . ($i == 0 ? 'card__actions' : 'list1') . '">';
            $text = $tools->checkout($res['text'], 1, 1);
            $text = $tools->smilies($text, 1);
            echo '<h2>' . $res['name'] . '</h2>' .
                '<span class="gray"><small>' . _t('Author') . ': ' . $res['avt'] . ' (' . $tools->displayDate($res['time']) . ')</small></span>' .
                '<br />' . $text . '<div class="sub">';

            if ($res['kom'] != 0 && $res['kom'] != "") {
                $komm = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 'm' AND `refid` = '" . $res['kom'] . "'")->fetchColumn();
                $mess = $db->query("SELECT `seo` FROM `forum` WHERE `id` = '" . $res['kom'] . "'")->fetch();
                if ($komm >= 0) {
                    echo '<a href="/forum/' . $res['kom'] . '/' . $mess['seo'] . '.html">' . _t('Discuss in Forum') . ' (' . $komm . ')</a><br>';
                }
            }

            if ($systemUser->rights >= 6) {
                echo '<a href="index.php?do=edit&amp;id=' . $res['id'] . '">' . _t('Edit') . '</a> | ' .
                    '<a href="index.php?do=del&amp;id=' . $res['id'] . '">' . _t('Delete') . '</a>';
            }

            echo '</div></div>';
            ++$i;
        }
        if($total){
            echo '</div>';
        }
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ':&#160;' . $total . '</div>';

        if ($total > $kmess) {
            echo '<div class="topmenu">' . $tools->displayPagination('index.php?', $start, $total, $kmess) . '</div>';
        }
        echo '</div>';
}

require('../system/end.php');
