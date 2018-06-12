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

require('../system/bootstrap.php');

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
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

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

/** @var Johncms\Counters $counters */
$counters = App::getContainer()->get('counters');

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

if (isset($_SESSION['ref'])) {
    unset($_SESSION['ref']);
}

// Настройки форума
$set_forum = $systemUser->isValid() ? unserialize($systemUser->set_forum) : [
    'farea'    => 0,
    'upfp'     => 0,
    'preview'  => 1,
    'postclip' => 1,
    'postcut'  => 2,
];

// Список расширений файлов, разрешенных к выгрузке

// Файлы архивов
$ext_arch = [
    'zip',
    'rar',
    '7z',
    'tar',
    'gz',
    'apk',
];
// Звуковые файлы
$ext_audio = [
    'mp3',
    'amr',
];
// Файлы документов и тексты
$ext_doc = [
    'txt',
    'pdf',
    'doc',
    'docx',
    'rtf',
    'djvu',
    'xls',
    'xlsx',
];
// Файлы Java
$ext_java = [
    'sis',
    'sisx',
    'apk',
];
// Файлы картинок
$ext_pic = [
    'jpg',
    'jpeg',
    'gif',
    'png',
    'bmp',
];
// Файлы SIS
$ext_sis = [
    'sis',
    'sisx',
];
// Файлы видео
$ext_video = [
    '3gp',
    'avi',
    'flv',
    'mpeg',
    'mp4',
];
// Файлы Windows
$ext_win = [
    'exe',
    'msi',
];
// Другие типы файлов (что не перечислены выше)
$ext_other = ['wmf'];

// Ограничиваем доступ к Форуму
$error = '';

if (!$config->mod_forum && $systemUser->rights < 7) {
    $error = _t('Forum is closed');
} elseif ($config->mod_forum == 1 && !$systemUser->isValid()) {
    $error = _t('For registered users only');
}

if ($error) {
    require('../system/head.php');
    echo '<div class="rmenu text-center">' . $error . '</div>';
    require('../system/end.php');
    exit;
}

$headmod = $id ? 'forum,' . $id : 'forum';

// Заголовки страниц форума
if (empty($id)) {
    $textl = _t('Forum');
} else {
    $res = $db->query("SELECT `text`, `bai`, `tags`, `type` FROM `forum` WHERE `id`= " . $id)->fetch();
    $resM = $db->query("SELECT `text` FROM `forum` WHERE `type` = 'm' AND `refid`='$id' LIMIT 1")->fetch();
    $hdr = preg_replace('#\[c\](.*?)\[/c\]#si', '', $res['text']);
    $hdr = strtr($hdr, [
        '&laquo;' => '',
        '&raquo;' => '',
        '&quot;'  => '',
        '&amp;'   => '',
        '&lt;'    => '',
        '&gt;'    => '',
        '&#039;'  => '',
    ]);
    $hdr = $tools->checkout($hdr, 2, 2);
    $hdr = $tools->cutText($hdr, 0, 57);
    $textl = empty($hdr) ? _t('Forum') : ($res['bai'] ? 'Bài ' . $res['bai'] . ': ' : '') . $hdr;
    $canonical = $config['homeurl'] . $_SERVER['REQUEST_URI'];
    if($res['type'] == 't') {
        $keywords = (!empty($res['tags']) ? $tools->checkout($res['tags'], 2, 2) . ', ' : '') . $hdr;
        $desc = $tools->checkout($resM['text'], 2, 2, 1);
        $desc = $tools->cutText($desc, 0, 195);
        $descriptions = $desc;
    }
}

// Переключаем режимы работы
$mods = [
    'addfile',
    'addvote',
    'close',
    'deltema',
    'delvote',
    'editpost',
    'editvote',
    'file',
    'files',
    'filter',
    'loadtem',
    'massdel',
    'moderation',
    'nt',
    'per',
    'post',
    'ren',
    'restore',
    'say',
    'tema',
    'thumb',
    'users',
    'vip',
    'vote',
    'who',
    'curators',
];

if ($act && ($key = array_search($act, $mods)) !== false && file_exists('includes/' . $mods[$key] . '.php')) {
    require('includes/' . $mods[$key] . '.php');
} else {
    require('../system/head.php');
    
    $seo = $_GET['seo'];

    // Если форум закрыт, то для Админов выводим напоминание
    if (!$config->mod_forum) {
        echo '<div class="alarm text-center">' . _t('Forum is closed') . '</div>';
    } elseif ($config->mod_forum == 3) {
        echo '<div class="rmenu text-center">' . _t('Read only') . '</div>';
    }

    if($config->mod_moderation) {
        $moderation = "AND `moderation` = '1' ";
    } else {
        $moderation = "";
    }

    if ($id) {
        // Определяем тип запроса (каталог, или тема)
        $type = $db->query("SELECT * FROM `forum` WHERE `id`= '$id' AND `seo`=" . $db->quote($seo) . " ");

        if (!$type->rowCount()) {
            // Если темы не существует, показываем ошибку
            echo $tools->displayError(_t('Topic has been deleted or does not exists'), '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php">' . _t('Forum') . '</a>');
            require('../system/end.php');
            exit;
        }

        $type1 = $type->fetch();

        if (($config->mod_moderation && !$systemUser->isValid() && !$type1['moderation']) || ($config->mod_moderation && !$type1['moderation'] && $systemUser->id != $type1['user_id'] && $systemUser->rights < 6)) {
            // Если темы не существует, показываем ошибку
            echo $tools->displayError(_t('Topic has been deleted or does not exists'), '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php">' . _t('Forum') . '</a>');
            require('../system/end.php');
            exit;
        }

        // Фиксация факта прочтения Топика
        if ($systemUser->isValid() && $type1['type'] == 't') {
            $req_r = $db->query("SELECT * FROM `cms_forum_rdm` WHERE `topic_id` = '$id' AND `user_id` = '" . $systemUser->id . "' LIMIT 1");

            if ($req_r->rowCount()) {
                $res_r = $req_r->fetch();

                if ($type1['time'] > $res_r['time']) {
                    $db->exec("UPDATE `cms_forum_rdm` SET `time` = '" . time() . "' WHERE `topic_id` = '$id' AND `user_id` = '" . $systemUser->id . "' LIMIT 1");
                }
            } else {
                $db->exec("INSERT INTO `cms_forum_rdm` SET `topic_id` = '$id', `user_id` = '" . $systemUser->id . "', `time` = '" . time() . "'");
            }
        }

        // Получаем структуру форума
        $res = true;
        $allow = 0;
        $parent = $type1['refid'];

        while ($parent != '0' && $res != false) {
            $res = $db->query("SELECT * FROM `forum` WHERE `id` = '$parent' LIMIT 1")->fetch();

            if ($res['type'] == 'f' || $res['type'] == 'r' || $res['type'] == 'c') {
                $tree[] = '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $parent . '/' . $res['seo'] . '.html">' . $res['text'] . '</a>';

                if (($res['type'] == 'r'  || $res['type'] == 'c') && !empty($res['edit'])) {
                    $allow = intval($res['edit']);
                }
            }
            $parent = $res['refid'];
        }

        $tree[] = '<a class="tload" href="' . $config['homeurl'] . '/forum/index.html">' . _t('Forum') . '</a>';
        krsort($tree);

        if ($type1['type'] != 'f' && $type1['type'] != 'r' && $type1['type'] != 'c' && $type1['type'] != 't' && $type1['type'] != 'm') {
            $tree[] = '<b>' . $type1['text'] . '</b>';
        }

        // Счетчик файлов и ссылка на них
        $sql = ($systemUser->rights == 9) ? "" : " AND `del` != '1'";

        if ($type1['type'] == 'f') {
            $count = $db->query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `cat` = '$id'" . $sql)->fetchColumn();

            if ($count > 0) {
                $filelink = '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=files&amp;c=' . $id . '">' . _t('Category Files') . '</a>';
            }
        } elseif ($type1['type'] == 'r') {
            $count = $db->query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `subcat` = '$id'" . $sql)->fetchColumn();

            if ($count > 0) {
                $filelink = '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=files&amp;s=' . $id . '">' . _t('Section Files') . '</a>';
            }
        } elseif ($type1['type'] == 'c') {
            $count = $db->query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `subcat` = '$id'" . $sql)->fetchColumn();

            if ($count > 0) {
                $filelink = '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=files&amp;s=' . $id . '">' . _t('Section Files') . '</a>';
            }
        } elseif ($type1['type'] == 't') {
            $count = $db->query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `topic` = '$id'" . $sql)->fetchColumn();

            if ($count > 0) {
                $filelink = '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=files&amp;t=' . $id . '">' . _t('Topic Files') . '</a>';
            }
        }

        $filelink = isset($filelink) ? $filelink . '&#160;<span class="red">(' . $count . ')</span>' : false;

        // Счетчик "Кто в теме?"
        $wholink = false;

        if ($systemUser->isValid() && $type1['type'] == 't') {
            $online_u = $db->query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 60) . " AND `place` = 'forum,$id'")->fetchColumn();
            $online_g = $db->query("SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > " . (time() - 60) . " AND `place` = 'forum,$id'")->fetchColumn();
            $wholink = '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=who&amp;id=' . $id . '">' . _t('Who is here') . '?</a>&#160;<span class="red">(' . $online_u . '&#160;/&#160;' . $online_g . ')</span><br />';
        }

        echo '<div class="mrt-code card shadow--4dp"><div class="phdr forum-title"><span>' . implode(' > ', $tree) . '</span></div></div>';

        switch ($type1['type']) {
            case 'f':
                ////////////////////////////////////////////////////////////
                // Список разделов форума                                 //
                ////////////////////////////////////////////////////////////
                $req = $db->query("SELECT `id`, `text`, `soft`, `seo`, `edit` FROM `forum` WHERE `type`='r' AND `refid`='$id' ORDER BY `realid`");
                $total = $req->rowCount();
                echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . (empty($type1['text']) ? '-----' : $type1['text']) . '</h4></div>';

                if ($total) {
                    $i = 0;

                    while ($res = $req->fetch()) {
                        echo '<div class="list1">';
                        $coltem = $db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['id'] . "' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1'") . "")->fetchColumn();
                        echo '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html">' . $res['text'] . '</a>';

                        if ($coltem) {
                            echo " [$coltem]";
                        }

                        if (!empty($res['soft'])) {
                            echo '<div class="sub"><span class="gray">' . $res['soft'] . '</span></div>';
                        }

                        echo '</div>';
                        ++$i;
                    }

                    unset($_SESSION['fsort_id']);
                    unset($_SESSION['fsort_users']);
                } else {
                    echo '<div class="list2">' . _t('There are no sections in this category') . '</div>';
                }
                echo '</div>';

                echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div></div>';
                break;

            case 'r':
                ////////////////////////////////////////////////////////////
                // Список топиков                                         //
                ////////////////////////////////////////////////////////////
                $totalc = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='c' AND `refid`='$id'")->fetchColumn();

                $total = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `refid`='$id' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1'"))->fetchColumn();
                echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . (empty($type1['text']) ? '-----' : $type1['text']) . '</h4></div>';

                if (($systemUser->isValid() && !isset($systemUser->ban['1']) && !isset($systemUser->ban['11']) && $config->mod_forum != 4) || $systemUser->rights) {
                    // Кнопка создания новой темы
                    if (!$totalc)
                        echo '<a style="position:absolute; top:10px;right:10px;" href="' . $config['homeurl'] . '/forum/index.php?act=nt&amp;id=' . $id . '"><div class="m-button m-button--fab m-button--colored"><i class="material-icons">add</i></div></a>';
                }

                if ($totalc) {
                	$reqc = $db->query("SELECT `id`, `text`, `soft`, `seo`, `edit` FROM `forum` WHERE `type`='c' AND `refid`='$id' ORDER BY `realid`");
                    $i = 0;
                    while ($res = $reqc->fetch()) {
                        echo '<div class="list1">';
                        $coltem = $db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['id'] . "' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1'") . "")->fetchColumn();
                        echo '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html">' . $res['text'] . '</a>';
                        if ($coltem) {
                            echo " [$coltem]";
                        }
                        if (!empty($res['soft'])) {
                            echo '<div class="sub"><span class="gray">' . $res['soft'] . '</span></div>';
                        }
                        echo '</div>';
                        ++$i;
                    }
                    echo '</div>';

                    echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $totalc . '</div>';

                    if ($totalc > $kmess) {
                        echo '<div class="topmenu">' . $tools->displayPaginationSeo($config['homeurl'] . '/forum/' . $id . '/' . $seo, $start, $totalc, $kmess) . '</div>';
                    }

                } else if ($total && !$totalc) {
                    $req = $db->query("SELECT * FROM `forum` WHERE `type`='t' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1' ") . "AND `refid`='$id' ORDER BY `vip` DESC, `time` DESC LIMIT $start, $kmess");
                    $i = 0;

                    while ($res = $req->fetch()) {
                        echo '<div class="list1' . ($res['close'] ? ' card__remote' : '') . '">';

                        $userpost = $db->query("SELECT `sex` FROM `users` WHERE `id`='$res[user_id]' LIMIT 1")->fetch();
                        $nam = $db->query("SELECT `from` FROM `forum` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "' ORDER BY `time` DESC LIMIT 1")->fetch();
                        $colmes = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='m' AND `refid`='" . $res['id'] . "'" . ($systemUser->rights >= 7 ? '' : " AND `close` != '1'"))->fetchColumn();
                        $cpg = ceil($colmes / $kmess);
                        $np = $db->query("SELECT COUNT(*) FROM `cms_forum_rdm` WHERE `time` >= '" . $res['time'] . "' AND `topic_id` = '" . $res['id'] . "' AND `user_id` = " . $systemUser->id)->fetchColumn();
                        // Значки
                        $icons = [
                            ($np ? (!$res['vip'] ? $tools->image('op.gif') : '') : $tools->image('np.gif')),
                            ($res['vip'] ? $tools->image('pt.gif') : ''),
                            ($res['realid'] ? $tools->image('rate.gif') : ''),
                            ($res['edit'] ? $tools->image('tz.gif') : ''),
                        ];
                        echo '<table cellpadding="0" cellspacing="0"><tr><td style="padding-right: 5px;">';
                        if($res['thumb_extension'] == 'none') {
                            $avatar_name = $tools->avatar_name($res['user_id']);
                            if (file_exists(('../files/users/avatar/' . $avatar_name))) {
                                echo '<img src="/files/users/avatar/' . $avatar_name . '" class="thumb" alt="' . $res['from'] . '" />';
                            } else {
                                echo '<img src="' . $config['homeurl'] . '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="thumb" alt="' . $res['from'] . '" />';
                            }
                        } else {
                            $thumb_file = $res['id'] . '.' . $res['thumb_extension'];
                            if (file_exists(('../files/forum/thumbnail/' . $thumb_file))) {
                                echo '<img src="/files/forum/thumbnail/' . $thumb_file . '" class="thumb" alt="thumbnail" />';
                            } else {
                                echo '<img src="' . $config['homeurl'] . '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="thumb" alt="thumbnail" />';
                            }
                        }
                        echo '</td><td>';
                        echo implode('', array_filter($icons));
                        echo '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html">' . ($res['bai'] ? 'Bài ' . $res['bai'] . ': ' : '') . (empty($res['text']) ? '-----' : $res['text']) . '</a> [' . $colmes . ']';

                        if ($cpg > 1) {
                            echo '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '_p' . $cpg . '.html">&#160;&gt;&gt;</a>';
                        }

                        echo '<div class="sub">';
                        echo $res['from'];

                        if (!empty($nam['from'])) {
                            echo '&#160;/&#160;' . $nam['from'];
                        }

                        echo ' <span class="gray">(' . $tools->thoigian($res['time']) . ')</span></div>';
                        echo '</td></tr></table>';
                        echo '</div>';
                        ++$i;
                    }
                    echo '</div>';

                    echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

                    if ($total > $kmess) {
                        echo '<div class="topmenu">' . $tools->displayPaginationSeo($config['homeurl'] . '/forum/' . $id . '/' . $seo, $start, $total, $kmess) . '</div>';
                    }
                } else {
                    echo '<div class="list2">' . _t('No topics in this section') . '</div>';
                }
                echo '</div>';

                unset($_SESSION['fsort_id']);
                unset($_SESSION['fsort_users']);
                break;

                case 'c':
                ////////////////////////////////////////////////////////////
                // Список топиков                                         //
                ////////////////////////////////////////////////////////////
                $totalc = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='c' AND `refid`='$id'")->fetchColumn();

                $total = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `refid`='$id' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1'"))->fetchColumn();
                echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . (empty($type1['text']) ? '-----' : $type1['text']) . '</h4></div>';

                if (($systemUser->isValid() && !isset($systemUser->ban['1']) && !isset($systemUser->ban['11']) && $config->mod_forum != 4) || $systemUser->rights) {
                    // Кнопка создания новой темы
                    if (!$totalc)
                        echo '<a style="position:absolute; top:10px;right:10px;" href="' . $config['homeurl'] . '/forum/index.php?act=nt&amp;id=' . $id . '"><div class="m-button m-button--fab m-button--colored"><i class="material-icons">add</i></div></a>';
                }

                if ($totalc) {
                	$reqc = $db->query("SELECT `id`, `text`, `soft`, `seo`, `edit` FROM `forum` WHERE `type`='c' AND `refid`='$id' ORDER BY `realid`");
                    $i = 0;
                    while ($res = $reqc->fetch()) {
                        echo '<div class="list1">';
                        $coltem = $db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['id'] . "' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1'") . "")->fetchColumn();
                        echo '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html">' . $res['text'] . '</a>';
                        if ($coltem) {
                            echo " [$coltem]";
                        }
                        if (!empty($res['soft'])) {
                            echo '<div class="sub"><span class="gray">' . $res['soft'] . '</span></div>';
                        }
                        echo '</div>';
                        ++$i;
                    }
                    echo '</div>';

                    echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $totalc . '</div>';

                    if ($totalc > $kmess) {
                        echo '<div class="topmenu">' . $tools->displayPaginationSeo($config['homeurl'] . '/forum/' . $id . '/' . $seo, $start, $totalc, $kmess) . '</div>';
                    }
                } else if ($total && !$totalc) {
                    $req = $db->query("SELECT * FROM `forum` WHERE `type`='t' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1' ") . "AND `refid`='$id' ORDER BY `vip` DESC, `time` DESC LIMIT $start, $kmess");
                    $i = 0;

                    while ($res = $req->fetch()) {
                        echo '<div class="list1' . ($res['close'] ? ' card__remote' : '') . '">';

                        $userpost = $db->query("SELECT `sex` FROM `users` WHERE `id`='$res[user_id]' LIMIT 1")->fetch();
                        $nam = $db->query("SELECT `from` FROM `forum` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "' ORDER BY `time` DESC LIMIT 1")->fetch();
                        $colmes = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='m' AND `refid`='" . $res['id'] . "'" . ($systemUser->rights >= 7 ? '' : " AND `close` != '1'"))->fetchColumn();
                        $cpg = ceil($colmes / $kmess);
                        $np = $db->query("SELECT COUNT(*) FROM `cms_forum_rdm` WHERE `time` >= '" . $res['time'] . "' AND `topic_id` = '" . $res['id'] . "' AND `user_id` = " . $systemUser->id)->fetchColumn();
                        // Значки
                        $icons = [
                            ($np ? (!$res['vip'] ? $tools->image('op.gif') : '') : $tools->image('np.gif')),
                            ($res['vip'] ? $tools->image('pt.gif') : ''),
                            ($res['realid'] ? $tools->image('rate.gif') : ''),
                            ($res['edit'] ? $tools->image('tz.gif') : ''),
                        ];
                        echo '<table cellpadding="0" cellspacing="0"><tr><td style="padding-right: 5px;">';
                        if($res['thumb_extension'] == 'none') {
                        $avatar_name = $tools->avatar_name($res['user_id']);
                            if (file_exists(('../files/users/avatar/' . $avatar_name))) {
                                echo '<img src="/files/users/avatar/' . $avatar_name . '" class="thumb" alt="' . $res['from'] . '" />';
                            } else {
                                echo '<img src="' . $config['homeurl'] . '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="thumb" alt="' . $res['from'] . '" />';
                            }
                        } else {
                            $thumb_file = $res['id'] . '.' . $res['thumb_extension'];
                            if (file_exists(('../files/forum/thumbnail/' . $thumb_file))) {
                                echo '<img src="/files/forum/thumbnail/' . $thumb_file . '" class="thumb" alt="thumbnail" />';
                            } else {
                                echo '<img src="' . $config['homeurl'] . '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="thumb" alt="thumbnail" />';
                            }
                        }
                        echo '</td><td>';
                        echo implode('', array_filter($icons));
                        echo '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html">' . ($res['bai'] ? 'Bài ' . $res['bai'] . ': ' : '') . (empty($res['text']) ? '-----' : $res['text']) . '</a> [' . $colmes . ']';

                        if ($cpg > 1) {
                            echo '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '_p' . $cpg . '.html">&#160;&gt;&gt;</a>';
                        }

                        echo '<div class="sub">';
                        echo $res['from'];

                        if (!empty($nam['from'])) {
                            echo '&#160;/&#160;' . $nam['from'];
                        }

                        echo ' <span class="gray">(' . $tools->thoigian($res['time']) . ')</span></div>';
                        echo '</td></tr></table>';
                        echo '</div>';
                        ++$i;
                    }
                    echo '</div>';

                    echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

                    if ($total > $kmess) {
                        echo '<div class="topmenu">' . $tools->displayPaginationSeo($config['homeurl'] . '/forum/' . $id . '/' . $seo, $start, $total, $kmess) . '</div>';
                    }
                } else {
                    echo '<div class="list2">' . _t('No topics in this section') . '</div>';
                }
                echo '</div>';
                unset($_SESSION['fsort_id']);
                unset($_SESSION['fsort_users']);
                break;

            case 't':
                ////////////////////////////////////////////////////////////
                // Показываем тему с постами                              //
                ////////////////////////////////////////////////////////////
                $filter = isset($_SESSION['fsort_id']) && $_SESSION['fsort_id'] == $id ? 1 : 0;
                $sql = '';

                if ($filter && !empty($_SESSION['fsort_users'])) {
                    // Подготавливаем запрос на фильтрацию юзеров
                    $sw = 0;
                    $sql = ' AND (';
                    $fsort_users = unserialize($_SESSION['fsort_users']);

                    foreach ($fsort_users as $val) {
                        if ($sw) {
                            $sql .= ' OR ';
                        }

                        $sortid = intval($val);
                        $sql .= "`forum`.`user_id` = '$sortid'";
                        $sw = 1;
                    }
                    $sql .= ')';
                }

                // Если тема помечена для удаления, разрешаем доступ только администрации
                if ($systemUser->rights < 6 && $type1['close'] == 1) {
                    $nameTop = $db->query("SELECT `text`, `seo` FROM `forum` WHERE `id`='$type1[refid]' ")->fetch();
                    echo '<div class="rmenu text-center">' . _t('Topic deleted') . '<br><a class="tload" href="' . $config['homeurl'] . '/forum/' . $type1['refid'] . '/' . $nameTop['seo'] . '.html">' . _t('Go to Section') . '</a></div>';
                    require('../system/end.php');
                    exit;
                }

                // Счетчик постов темы
                $colmes = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='m'$sql AND `refid`='$id'" . ($systemUser->rights >= 7 ? '' : " AND `close` != '1'"))->fetchColumn();

                if ($start >= $colmes) {
                    // Исправляем запрос на несуществующую страницу
                    $start = max(0, $colmes - (($colmes % $kmess) == 0 ? $kmess : ($colmes % $kmess)));
                }
                echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="phdr"><h1>' . ($type1['bai'] ? 'Bài ' . $type1['bai'] . ': ' : '') . (empty($type1['text']) ? '-----' : $type1['text']) . '</h1></div>';

                if ($colmes > $kmess) {
                    echo "\n" . '<div class="topmenu">' . $tools->displayPaginationSeo($config['homeurl'] . '/forum/' . $id . '/' . $seo, $start, $colmes, $kmess) . '</div>';
                }
                if ($config->mod_moderation && !$type1['moderation']) {
                    echo "\n" . '<div class="rmenu text-center">Chủ đề đang chờ được kiểm duyệt.!' . ($systemUser->rights >= 6 ? '</div><div class="card__actions text-center"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=moderation&amp;id=' . $id . '">Kiểm duyệt</a>' : '') . '</div>';
                }
                // Метка удаления темы
                if ($type1['close']) {
                    echo "\n" . '<div class="rmenu text-center">' . _t('Topic deleted by') . ': <b>' . $type1['close_who'] . '</b></div>';
                } elseif (!empty($type1['close_who']) && $systemUser->rights >= 7) {
                    echo "\n" . '<div class="gmenu text-center"><small>' . _t('Undelete topic') . ': <b>' . $type1['close_who'] . '</b></small></div>';
                }

                // Метка закрытия темы
                if ($type1['edit']) {
                    echo "\n" . '<div class="rmenu text-center">' . _t('Topic closed') . '</div>';
                }
                echo "\n" . '</div>';

                // Блок голосований
                if ($type1['realid']) {
                    $clip_forum = isset($_GET['clip']) ? '_clip' : '';
                    $vote_user = $db->query("SELECT COUNT(*) FROM `cms_forum_vote_users` WHERE `user`='" . $systemUser->id . "' AND `topic`='$id'")->fetchColumn();
                    $topic_vote = $db->query("SELECT `name`, `time`, `count` FROM `cms_forum_vote` WHERE `type`='1' AND `topic`='$id' LIMIT 1")->fetch();
                    echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="gmenu"><b>' . $tools->checkout($topic_vote['name']) . '</b><br />';
                    $vote_result = $db->query("SELECT `id`, `name`, `count` FROM `cms_forum_vote` WHERE `type`='2' AND `topic`='" . $id . "' ORDER BY `id` ASC");

                    if (!$type1['edit'] && !isset($_GET['vote_result']) && $systemUser->isValid() && $vote_user == 0) {
                        // Выводим форму с опросами
                        echo '<form action="' . $config['homeurl'] . '/forum/index.php?act=vote&amp;id=' . $id . '" method="post">' .
                            '<div class="form-radio">';
                        while ($vote = $vote_result->fetch()) {
                            echo '<div class="radio">' .
                                '<label>' .
                                '<input type="radio" value="' . $vote['id'] . '" name="vote"/><i class="helper"></i>' . $tools->checkout($vote['name'], 0, 1) .
                                '</label>' .
                                '</div>';
                        }
                        echo '</div>' .
                            '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Vote') . '</span></button></div>' .
                            '</div><div class="list1"><a class="tload" href="' . $config['homeurl'] . '/forum/' . $id . '/' . $type1['seo'] . '_start' . $start . '_vote' . $clip_forum . '.html">' . _t('Results') . '</a></div></form>';
                    } else {
                        // Выводим результаты голосования
                        echo '<small>';

                        while ($vote = $vote_result->fetch()) {
                            $count_vote = $topic_vote['count'] ? round(100 / $topic_vote['count'] * $vote['count']) : 0;
                            echo $tools->checkout($vote['name'], 0, 1) . ' [' . $vote['count'] . ']<br />';
                            if($count_vote == 0){
                                $color_vote = ' noVote';
                            } else if($count_vote > 0 && $count_vote <= 10) {
                                $color_vote = ' color--red';
                            } else if ($count_vote > 10 && $count_vote <= 20) {
                                $color_vote = ' color--amber-900';
                            } else if ($count_vote > 20 && $count_vote <= 30) {
                                $color_vote = ' color--yellow-800';
                            } else if ($count_vote > 30 && $count_vote <= 60) {
                                $color_vote = ' color--green-600';
                            } else {
                                $color_vote = ' color--cyan-700';
                            }
                            echo '<div class="LineVote"><div class="VoteBar' . $color_vote . '" style="width:' . $count_vote . '%;">&#160;' . $count_vote . '%</div></div>';
                        }

                        echo '</small></div><div class="bmenu">' . _t('Total votes') . ': ';

                        if ($systemUser->rights > 6) {
                            echo '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=users&amp;id=' . $id . '">' . $topic_vote['count'] . '</a>';
                        } else {
                            echo $topic_vote['count'];
                        }

                        echo '</div>';

                        if ($systemUser->isValid() && $vote_user == 0) {
                            echo '<div class="bmenu"><a class="tload" href="' . $config['homeurl'] . '/forum/' . $id . '/' . $type1['seo'] . '_start' . $start . $clip_forum . '.html">' . _t('Vote') . '</a></div>';
                        }
                    }
                    echo '</div>';
                }

                // Получаем данные о кураторах темы
                $curators = !empty($type1['curators']) ? unserialize($type1['curators']) : [];
                $curator = false;

                if ($systemUser->rights < 6 && $systemUser->rights != 3 && $systemUser->isValid()) {
                    if (array_key_exists($systemUser->id, $curators)) {
                        $curator = true;
                    }
                }

                // Фиксация первого поста в теме
                if (($set_forum['postclip'] == 2 && ($set_forum['upfp'] ? $start < (ceil($colmes - $kmess)) : $start > 0)) || isset($_GET['clip'])) {
                    $postres = $db->query("SELECT `forum`.*, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`
                    FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
                    WHERE `forum`.`type` = 'm' AND `forum`.`refid` = '$id'" . ($systemUser->rights >= 7 ? "" : " AND `forum`.`close` != '1'") . "
                    ORDER BY `forum`.`id` LIMIT 1")->fetch();
                    echo '<div class="mrt-code card">' .
                        '<div class="card__actions"><p>';

                    if ($systemUser->isValid() && $systemUser->id != $postres['user_id']) {
                        echo '<a class="tload" href="' . $config['homeurl'] . '/profile/?user=' . $postres['user_id'] . '&amp;fid=' . $postres['id'] . '"><strong>' . $postres['from'] . '</strong></a> ';
                    } else {
                        echo '<strong>' . $postres['from'] . '</strong> ';
                    }

                    $user_rights = [
                        3 => '(FMod)',
                        6 => '(Smd)',
                        7 => '(Adm)',
                        9 => '(SV!)',
                    ];
                    echo @$user_rights[$postres['rights']];
                    echo(time() > $postres['lastdate'] + 60 ? '<span class="red"> [Off]</span>' : '<span class="wgreen"> [ON]</span>');
                    echo ' <span class="gray fsize--12" style="float:right;">(' . $tools->thoigian($postres['time']) . ')</span><br />';

                    if ($postres['close']) {
                        echo '<span class="red">' . _t('Post deleted') . '</span><br>';
                    }

                    echo $tools->checkout(mb_substr($postres['text'], 0, 500), 0, 2);

                    if (mb_strlen($postres['text']) > 500) {
                        echo '...<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=post&amp;id=' . $postres['id'] . '">' . _t('Read more') . '</a>';
                    }

                    echo '</p></div></div>';
                }

                // Памятка, что включен фильтр
                if ($filter) {
                    echo "\n" . '<div class="rmenu text-center">' . _t('Filter by author is activated') . '</div><br />';
                }

                // Задаем правила сортировки (новые внизу / вверху)
                if ($systemUser->isValid()) {
                    $order = $set_forum['upfp'] ? 'DESC' : 'ASC';
                } else {
                    $order = 'ASC';
                }
                if($start == 0){
                    $db->exec("UPDATE `forum` SET `view`=`view` + 1 WHERE `id`=" . $id . "");
                }
                ////////////////////////////////////////////////////////////
                // Основной запрос в базу, получаем список постов темы    //
                ////////////////////////////////////////////////////////////
                $req = $db->query("
                  SELECT `forum`.*, `users`.`name`, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`
                  FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
                  WHERE `forum`.`type` = 'm' AND `forum`.`refid` = '$id'"
                    . ($systemUser->rights >= 7 ? "" : " AND `forum`.`close` != '1'") . "$sql
                  ORDER BY `forum`.`id` $order LIMIT $start, $kmess
                ");

                // Верхнее поле "Написать"
                if (($config->mod_moderation && $type1['moderation']) || !$config->mod_moderation) {
                    if (($systemUser->isValid() && !$type1['edit'] && $set_forum['upfp'] && $config->mod_forum != 3 && $allow != 4) || ($systemUser->rights >= 7 && $set_forum['upfp'])) {
                        echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="card__actions"><form name="form1" action="' . $config['homeurl'] . '/forum/index.php?act=say&amp;id=' . $id . '" method="post">';
                        $token = mt_rand(1000, 100000);
                        $_SESSION['token'] = $token;
                        echo $container->get(Johncms\Api\BbcodeInterface::class)->buttons('form1', 'msg');
                        echo '<div class="form-group"><textarea rows="' . $systemUser->getConfig()->fieldHeight . '" name="msg" required="required"></textarea>' .
                            '<label class="control-label" for="textarea">Viết gì đó.....</label><i class="bar"></i>' .
                            '</div>' .
                            '<div class="checkbox"><label>' .
                            '<input type="checkbox" name="addfiles" value="1" /><i class="helper"></i>' . _t('Add File') .
                            '</label></div>' .
                            '<div class="button-container">' .
                            '<button class="button" type="submit" name="submit"><span>' . _t('Write') . '</span></button>' .
                            (isset($set_forum['preview']) && $set_forum['preview'] ? '<button class="button" type="submit"><span>' . _t('Preview') . '</span></button>' : '') .
                            '</div><input type="hidden" name="token" value="' . $token . '"/>' .
                            '</p></form></div>';
                        echo '</div>';
                    }
                }

                // Для администрации включаем форму массового удаления постов
                if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
                    echo '<form action="' . $config['homeurl'] . '/forum/index.php?act=massdel" method="post">';
                }
                $i = 1;

                ////////////////////////////////////////////////////////////
                // Основной список постов                                 //
                ////////////////////////////////////////////////////////////
                while ($res = $req->fetch()) {
                    // Фон поста
                    echo "\n" . '<div class="mrt-code card forum_card shadow--2dp ' . ($res['close'] ? 'card__remote' : '') . '" id="post-' . $res['id'] . '">';
                    echo "\n" . '<div class="card__actions fauthor">';

                    // Пользовательский аватар
                    $avatar_name = $tools->avatar_name($res['user_id']);
                    if (file_exists(('../files/users/avatar/' . $avatar_name))) {
                        echo "\n" . '<img src="/files/users/avatar/' . $avatar_name . '" class="avatar" alt="' . $res['from'] . '" />';
                    } else {
                        echo "\n" . '<img src="' . $config['homeurl'] . '/images/empty' . ($res['sex'] ? ($res['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="avatar" alt="' . $res['from'] . '" />';
                    }
                    echo "\n" . '<ul class="finfo">' .
                              "\n" . '<li>';

                    // Метка пола
                    $sexColor = null;
                    if ($res['sex']) {
                        if($res['sex'] == 'm'){
                            $sexColor = 'mColor';
                        }else{
                            $sexColor = 'wColor';
                        }
                        echo ($res['datereg'] > time() - 86400 ? '<i class="material-icons list__item-icon ' . $sexColor . '">&#xE7FE;</i>' : '<i class="material-icons list__item-icon ' . $sexColor . '">&#xE7FD;</i>');
                    } else {
                        echo $tools->image('del.png');
                    }

                    $name = !$res['name'] ? $res['from'] : $res['name'];

                    // Ник юзера и ссылка на его анкету
                    if ($systemUser->isValid() && $systemUser->id != $res['user_id']) {
                        echo '<a href="' . $config['homeurl'] . '/profile/?user=' . $res['user_id'] . '" class="tload">' . $tools->rightsColor($res['rights'], $name) . '</a>&#160;';
                    } else {
                        echo $tools->rightsColor($res['rights'], $name) . '&#160;';
                    }

                    // Метка должности
                    $user_rights = [
                        3 => '(FMod)',
                        6 => '(Smd)',
                        7 => '(Adm)',
                        9 => '(SV!)',
                    ];
                    echo (isset($user_rights[$res['rights']]) ? $user_rights[$res['rights']] . '&#160;' : '');

                    // Метка онлайн/офлайн
                    echo (time() > $res['lastdate'] + 60 ? '<span class="red"> [Off]</span> ' : '<span class="wgreen"> [ON]</span> ');

                    // Статус пользователя
                    if (!empty($res['status'])) {
                        echo "\n" . '</li><li>';
                        echo "\n" . '<i class="material-icons list__item-icon">&#xE838;</i><small>' . $res['status'] . '</small>';
                    }

                    // Закрываем таблицу с аватаром
                    echo "\n" . '</li></ul>';
                    $count = $start+$i;
                    echo "\n" . '</div>' .
                        "\n" . '<div class="card__actions card--border mrt2">' .
                        "\n" . '<div class="fleft"><span class="mrt-td"><i class="material-icons">&#xE8B5;</i>&#160;' . $tools->thoigian($res['time']) . '</span></div><div class="fright"><a class="tload" href="/forum/post-' . $res['id'] . '.html" title="Link to post">['.($count == 1 ? 'TOP' : '#'.$count).']</a></div></table>' .
                        "\n" . '</div>' .
                        "\n" . '<div class="card__actions">' .
                        "\n" . ($i == 1 ? '<h2 class="text-center">' . ($type1['bai'] ? 'Bài ' . $type1['bai'] . ': ' : '') . (empty($type1['text']) ? '-----' : $type1['text']) . '</h2><br />' : '');

                    ////////////////////////////////////////////////////////////
                    // Вывод текста поста                                     //
                    ////////////////////////////////////////////////////////////
                    $text = $res['text'];
                    $text = $tools->checkout($text, 1, 1, 0, 1, 1);
                    echo "\n" . $text;

                    // Если пост редактировался, показываем кем и когда
                    if ($res['kedit']) {
                        echo "\n" . '<div class="edit gray text-right">' . _t('Edited') . '</div>';
                    }
                    echo '</div>';

                    // Задаем права на редактирование постов
                    if (
                        (($systemUser->rights == 3 || $systemUser->rights >= 6 || $curator) && $systemUser->rights > $res['rights'])
                        || ($res['user_id'] == $systemUser->id && !$set_forum['upfp'] && ($start + $i) == $colmes && $res['time'] > time() - 300)
                        || ($res['user_id'] == $systemUser->id && $set_forum['upfp'] && $start == 0 && $i == 1 && $res['time'] > time() - 300)
                        || ($i == 1 && $allow == 2 && $res['user_id'] == $systemUser->id)
                        || ($res['user_id'] == $systemUser->id)
                    ) {
                        $allowEdit = true;
                    } else {
                        $allowEdit = false;
                    }

                    // Если есть прикрепленные файлы, выводим их
                    $freq = $db->query("SELECT * FROM `cms_forum_files` WHERE `post` = '" . $res['id'] . "'");

                    if ($freq->rowCount()) {
                        echo "\n" . '<ul class="atack-file"><li class="atack-file--title">' . _t('Attachment') . ':</li>';
                        if (!$systemUser->isValid()) {
                            echo '<li class="atack-file--info">Nội dung chỉ dành cho thành viên đăng nhập.!!</li>';
                        } else {
                            while ($fres = $freq->fetch()) {
                                $fls = round(@filesize('../files/forum/attach/' . $fres['filelink']) / 1024, 2);
                                echo "\n" . '<li class="atack-file--info">';
                                // Предпросмотр изображений
                                $att_ext = strtolower(pathinfo('./files/forum/attach/' . $fres['filelink'], PATHINFO_EXTENSION));
                                $pic_ext = [
                                    'gif',
                                    'jpg',
                                    'jpeg',
                                    'png',
                                ];

                                if (in_array($att_ext, $pic_ext)) {
                                    $image = '/forum/thumbinal.php?file=' . $fres['id'];
                                    $alt   = $fres['filename'];
                                    $isImage = true;
                                } else {
                                    $image = '/assets/images/files/' . $att_ext . '.png';
                                    $linkfile = '../assets/images/files/' . $att_ext . '.png';
                                    if (!file_exists($linkfile))
                                        $image = '/assets/images/files/file.png';
                                    $alt   = $fres['filename'];
                                    $isImage = false;
                                }
                                echo "\n" . '<div class="fthumb">' .
                                    "\n" . '<img src="' . $image . '" alt="' . $alt . '" ' . (!$isImage ? 'class="ficon"' : '') . '/></div>' .
                                    '<div class="info">
                                    Tập tin: <a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=file&amp;id=' . $fres['id'] . '">' . $fres['filename'] . '</a><br />
                                    ' . ($fres['balans'] > 0 ? '<span style="color: #F00">Giá: ' . number_format($fres['balans'], 0, ",", ".") . ' VNĐ</span><br />' : '') . '
                                    Kích thước: ' . $fls . ' kb</div>';

                                if ($allowEdit) {
                                    echo "\n" . '<a style="position: absolute; bottom: 3px; right: 5px;" href="' . $config['homeurl'] . '/forum/index.php?act=editpost&amp;do=delfile&amp;fid=' . $fres['id'] . '&amp;id=' . $res['id'] . '">' . _t('Delete') . '</a>';
                                }

                                echo "\n" . '</li>';
                            }
                        }
                        echo "\n" . '</ul>';
                    }

                    if($systemUser->isValid()){
                        $like = $tools->Like_Check($res['id'], "Like", 'forum');
                        $like_statusicon = 'icon-like-blf--18';
                        $lostyle = 'display:none;';
                        $i_status = 'Thích';
                        if($like) {
                            $like_status = 'UnLike';
                            $i_status = 'Like';
                            $like_statusicon = 'icon-like-new--18';
                            $lostyle = 'display:inline-block;';
                        } else {
                            $like_status = 'Like';
                        }

                        // Reaction status check for "Love"
                        $love = $tools->Like_Check($res['id'], "Love", 'forum');
                        if($love){
                            $love_status = 'UnLove';
                            $i_status = 'Love';
                            $like_statusicon = 'icon-love-new--18'; 
                            $lostyle = 'display:inline-block;';
                        } else {
                            $love_status = 'Love';
                        }

                        // Reaction status check for "Haha"
                        $haha = $tools->Like_Check($res['id'], "Haha", 'forum');
                        if($haha){
                            $haha_status = 'UnHaha';
                            $i_status = 'Haha';
                            $like_statusicon = 'icon-haha-new--18'; 
                            $lostyle = 'display:inline-block;';
                        } else {
                            $haha_status = 'Haha';
                        }

                        // Reaction status check for "Hihi"
                        $hihi = $tools->Like_Check($res['id'], "Hihi", 'forum');
                        if($hihi){
                            $hihi_status = 'UnHihi';
                            $i_status = 'Hihi';
                            $like_statusicon = 'icon-hihi-new--18'; 
                            $lostyle = 'display:inline-block;';
                        } else {
                            $hihi_status = 'Hihi';
                        }

                        // Reaction status check for "Woww"
                        $woww = $tools->Like_Check($res['id'], "Woww", 'forum');
                        if($woww){
                            $woww_status = 'UnWoww';
                            $i_status = 'Woww';
                            $like_statusicon = 'icon-woww-new--18'; 
                            $lostyle = 'display:inline-block;';
                        } else {
                            $woww_status = 'Woww';
                        }

                        // Reaction status check for "Cry"
                        $Cry = $tools->Like_Check($res['id'], "Cry", 'forum');
                        if($Cry){
                            $cry_status = 'UnCry';
                            $i_status = 'Cry';
                            $like_statusicon = 'icon-cry-new--18'; 
                            $lostyle = 'display:inline-block;';
                        } else {
                            $cry_status = 'Cry'; 
                        }

                        // Reaction status check for "Angry"
                        $angry = $tools->Like_Check($res['id'], "Angry", 'forum');
                        if($angry){
                            $angry_status = 'UnAngry';
                            $i_status = 'Angry';
                            $like_statusicon = 'icon-angry-new--18'; 
                            $lostyle = 'display:inline-block;';
                        } else {
                            $angry_status = 'Angry';
                        }

                        // Reaction status check for "Angry"
                        $wtf = $tools->Like_Check($res['id'], "WTF", 'forum');
                        if($wtf){
                            $wtf_status = 'UnWTF';
                            $i_status = 'WTF';
                            $like_statusicon = 'icon-like-blf--18'; 
                            $lostyle = 'display:inline-block;';
                        } else {
                            $wtf_status = 'WTF';
                        }

                        echo "\n" . '<div class="forum-more">' .
                            "\n" . '<div class="like-it">' .
                            "\n" . '<div class="new_like" map="forum" id="' . $res['id'] . '">' .
                            "\n" . '<div class="like-pit first_click post-like-unlike-comment">' .
                           "\n" . '<div class="icon-lpn ' . $like_statusicon . ' reaction_grap-style reactionTrans" id="ulk' . $res['id'] . '" style="' . $lostyle . '"></div><div class="reatext" id="reatext' . $res['id'] . '">' . $i_status . '</div>' .
                            "\n" . '</div>' .
                            "\n" . '<ul id="ForumReactions' . $res['id'] . '" class="ForumReactions new_like_items">' .
                            "\n" . '<li class="like_hover op-lw like_button reactionTrans" id="like'  . $res['id'] . '" rel="' . $like_status  . '" map="forum"><div class="reactionTrans icon-newL icon-like-new"></div></li>'  .
                            "\n" . '<li class="love_hover op-lw like_button reactionTrans" id="love'  . $res['id'] . '" rel="' . $love_status  . '" map="forum"><div class="reactionTrans icon-newL icon-love-new"></div></li>'  .
                            "\n" . '<li class="haha_hover op-lw like_button reactionTrans" id="haha'  . $res['id'] . '" rel="' . $haha_status  . '" map="forum"><div class="reactionTrans icon-newL icon-haha-new"></div></li>'  .
                            "\n" . '<li class="hihi_hover op-lw like_button reactionTrans" id="hihi'  . $res['id'] . '" rel="' . $hihi_status  . '" map="forum"><div class="reactionTrans icon-newL icon-hihi-new"></div></li>'  .
                            "\n" . '<li class="woww_hover op-lw like_button reactionTrans" id="woww'  . $res['id'] . '" rel="' . $woww_status  . '" map="forum"><div class="reactionTrans icon-newL icon-woww-new"></div></li>'  .
                            "\n" . '<li class="cry_hover op-lw like_button reactionTrans" id="cry'   . $res['id'] . '" rel="' . $cry_status   . '" map="forum"><div class="reactionTrans icon-newL icon-cry-new"></div></li>'   .
                            "\n" . '<li class="angry_hover op-lw like_button reactionTrans" id="angry' . $res['id'] . '" rel="' . $angry_status . '" map="forum"><div class="reactionTrans icon-newL icon-angry-new"></div></li>' .
                            "\n" . '<li class="wtf_hover op-lw like_button reactionTrans" id="wtf'   . $res['id'] . '" rel="' . $wtf_status   . '" map="forum"><div class="reactionTrans icon-newL icon-like-blf"></div></li>'  .
                            "\n" . '</ul>' .
                            "\n" . '</div>' .
                            "\n" . '</div>';

                            if($res['user_id'] != $systemUser->id) {
                                echo "\n" . '<div class="like-it">' .
                                "\n" . '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=say&amp;id=' . $res['id'] . '&amp;start=' . $start . '&amp;cyt">' . _t('Quote') . '</a>' .
                                "\n" . '</div>';
                            }
                            echo "\n" . '</div>';
                    }

                    $sep = null;
                    $lstyle = null;
                    $allCheck = $tools->Like_CountTotal($res['id'], 'forum');
                    if(!$allCheck){
                        $lstyle = "display:none;";
                    }
                    echo "\n" . '<ul class="who-likes-this-post" id="reactions'.$res['id'].'" style="'.$lstyle.'">';
                    //Like Started
                    if($tools->Like_CountT($res['id'], 'Like', 'forum')>0) { 
                        echo '<li class="likes reaction_wrap-style icon-newL icon-like-new--18 lpos" id="elike'.$res['id'].'" style="'.$lstyle.'"><span class="reaction_use">' . $tools->Reactions_URel($res['id'], 'Like', 'forum') . '</span></li>'; 
                    } else {
                        echo '<li class="likes reaction_wrap-style icon-newL icon-like-new--18 lpos" id="elike'.$res['id'].'" style="display:none"></li>';
                    }
                    //Love Started
                    if($tools->Like_CountT($res['id'], 'Love', 'forum')){
                        echo '<li class="loves reaction_wrap-style icon-newL icon-love-new--18 lpos" id="elove'.$res['id'].'" style="'.$lstyle.'"><span class="reaction_use">' . $tools->Reactions_URel($res['id'], 'Love', 'forum') . '</span></li>'; 
                    } else {
                        echo '<li class="loves reaction_wrap-style icon-newL icon-love-new--18 lpos" id="elove'.$res['id'].'" style="display:none"></li>';
                    }
                    //Haha Started
                    if($tools->Like_CountT($res['id'], 'Haha', 'forum')){
                        echo '<li class="hahas reaction_wrap-style icon-newL icon-haha-new--18 lpos" id="ehaha'.$res['id'].'" style="'.$lstyle.'"><span class="reaction_use">' . $tools->Reactions_URel($res['id'], 'Haha', 'forum') . '</span></li>'; 
                    } else {
                        echo '<li class="hahas reaction_wrap-style icon-newL icon-haha-new--18 lpos" id="ehaha'.$res['id'].'" style="display:none"></li>';
                    }
                    //Hihi Started
                    if($tools->Like_CountT($res['id'], 'Hihi', 'forum')){
                        echo '<li class="hihis reaction_wrap-style icon-newL icon-hihi-new--18 lpos" id="ehihi'.$res['id'].'" style="'.$lstyle.'"><span class="reaction_use">' . $tools->Reactions_URel($res['id'], 'Hihi', 'forum') . '</span></li>'; 
                    } else {
                        echo '<li class="hihis reaction_wrap-style icon-newL icon-hihi-new--18 lpos" id="ehihi'.$res['id'].'" style="display:none"></li>';
                    }
                    //Woww Started
                    if($tools->Like_CountT($res['id'], 'Woww', 'forum')){
                        echo '<li class="wowws reaction_wrap-style icon-newL icon-woww-new--18 lpos" id="ewoww'.$res['id'].'" style="'.$lstyle.'"><span class="reaction_use">' . $tools->Reactions_URel($res['id'], 'Woww', 'forum') . '</span></li>'; 
                    } else {
                        echo '<li class="wowws reaction_wrap-style icon-newL icon-woww-new--18 lpos" id="ewoww'.$res['id'].'" style="display:none"></li>';
                    }
                    //Cry Started
                    if($tools->Like_CountT($res['id'], 'Cry', 'forum')){
                        echo '<li class="crys reaction_wrap-style icon-newL icon-cry-new--18 lpos" id="ecry'.$res['id'].'" style="'.$lstyle.'"><span class="reaction_use">' . $tools->Reactions_URel($res['id'], 'Cry', 'forum') . '</span></li>'; 
                    } else {
                        echo '<li class="crys reaction_wrap-style icon-newL icon-cry-new--18 lpos" id="ecry'.$res['id'].'" style="display:none"></li>';
                    }
                    //Angry Started
                    if($tools->Like_CountT($res['id'], 'Angry', 'forum')){
                        echo '<li class="angrys reaction_wrap-style icon-newL icon-angry-new--18 lpos" id="eangry'.$res['id'].'" style="'.$lstyle.'"><span class="reaction_use">' . $tools->Reactions_URel($res['id'], 'Angry', 'forum') . '</span></li>'; 
                    } else {
                        echo '<li class="angrys reaction_wrap-style icon-newL icon-angry-new--18 lpos" id="eangry'.$res['id'].'" style="display:none"></li>';
                    }
                    //WTF Started
                    if($tools->Like_CountT($res['id'], 'WTF', 'forum')){
                        echo '<li class="wtfs reaction_wrap-style icon-newL icon-like-blf--18 lpos" id="ewtf'.$res['id'].'" style="'.$lstyle.'"><span class="reaction_use">' . $tools->Reactions_URel($res['id'], 'WTF', 'forum') . '</span></li>'; 
                    } else {
                        echo '<li class="wtfs reaction_wrap-style icon-newL icon-like-blf--18 lpos" id="ewtf'.$res['id'].'" style="display:none"></li>';
                    }
                    echo '<li class="totals reaction_total-style" id="etotals'.$res['id'].'"><div id="total_count'.$res['id'].'" class="numcount bbc"><span class="totalco" id="totalco'.$res['id'].'">'.$tools->Like_CountTotal($res['id'], 'forum').'</span></div></li>'; 
                    echo "\n" . '</ul>';

                    // Ссылки на редактирование / удаление постов
                    if ($allowEdit) {
                        echo '<div class="card__actions card--border mrt">';

                        // Чекбокс массового удаления постов
                        if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
                            echo '<div class="checkbox"><label><input type="checkbox" name="delch[]" value="' . $res['id'] . '" /><i class="helper"></i></label></div>&#160;';
                        }

                        // Служебное меню поста
                        $menu = [
                            '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=editpost&amp;id=' . $res['id'] . '">' . _t('Edit') . '</a>',
                            '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?id=' . $res['id'] . '&act=addfile">Thêm đính kèm</a>',
                            ($systemUser->rights >= 7 && $res['close'] == 1 ? '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=editpost&amp;do=restore&amp;id=' . $res['id'] . '">' . _t('Restore') . '</a>' : ''),
                            ($res['close'] == 1 ? '' : '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=editpost&amp;do=del&amp;id=' . $res['id'] . '">' . _t('Delete') . '</a>'),
                        ];
                        echo implode(' • ', array_filter($menu));

                        // Показываем, кто удалил пост
                        if ($res['close']) {
                            echo '<div class="red">' . _t('Post deleted') . ': <b>' . $res['close_who'] . '</b></div>';
                        } elseif (!empty($res['close_who'])) {
                            echo '<div class="wgreen">' . _t('Post restored by') . ': <b>' . $res['close_who'] . '</b></div>';
                        }

                        // Показываем IP и Useragent
                        if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
                            if ($res['ip_via_proxy']) {
                                echo '<div class="gray"><b class="red"><a class="tload" href="' . $config->homeurl . '/admin/index.php?act=search_ip&amp;ip=' . long2ip($res['ip']) . '">' . long2ip($res['ip']) . '</a></b> - ' .
                                    '<a class="tload" href="' . $config->homeurl . '/admin/index.php?act=search_ip&amp;ip=' . long2ip($res['ip_via_proxy']) . '">' . long2ip($res['ip_via_proxy']) . '</a>' .
                                    ' - ' . $res['soft'] . '</div>';
                            } else {
                                echo '<div class="gray"><a class="tload" href="' . $config->homeurl . '/admin/index.php?act=search_ip&amp;ip=' . long2ip($res['ip']) . '">' . long2ip($res['ip']) . '</a> - ' . $res['soft'] . '</div>';
                            }
                        }
                        echo "\n" . '</div>';
                    }

                    echo "\n" . '</div>';
                    ++$i;
                }

                // Кнопка массового удаления постов
                if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
                    echo '<button class="button" type="submit" style="line-height: 1;border-radius:0;margin: 0;"><span>' . _t('Delete') . '</span></button><br /><br />';
                    echo '</form>';
                }

                // Нижнее поле "Написать"
                if (($config->mod_moderation && $type1['moderation']) || !$config->mod_moderation) {
                    if (($systemUser->isValid() && !$type1['edit'] && !$set_forum['upfp'] && $config->mod_forum != 3 && $allow != 4) || ($systemUser->rights >= 7 && !$set_forum['upfp'])) {
                        echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="card__actions"><form name="form2" action="' . $config['homeurl'] . '/forum/index.php?act=say&amp;id=' . $id . '" method="post">';
                        $token = mt_rand(1000, 100000);
                        $_SESSION['token'] = $token;
                        echo $container->get(Johncms\Api\BbcodeInterface::class)->buttons('form2', 'msg');
                        echo '<div class="form-group">' .
                            '<textarea rows="' . $systemUser->getConfig()->fieldHeight . '" name="msg" required="required"></textarea>' .
                            '<label class="control-label" for="textarea">Viết gì đó.....</label><i class="bar"></i>' .
                            '</div>' .
                            '<div class="checkbox"><label>' .
                            '<input type="checkbox" name="addfiles" value="1" /><i class="helper"></i>' . _t('Add File') .
                            '</label></div>';
                        echo '<div class="button-container">' .
                            '<button class="button" type="submit" name="submit"><span>' . _t('Write') . '</span></button>' .
                            (isset($set_forum['preview']) && $set_forum['preview'] ? '&#160;<button class="button" type="submit"><span>' . _t('Preview') . '</span></button>' : '') .
                            '</div><input type="hidden" name="token" value="' . $token . '"/>' .
                            '</form></div></div>';
                    }
                }

                echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="phdr">' . $type1['view'] . ' lượt xem và ' . ($colmes -1) . ' bình luận.</div>';

                // Постраничная навигация
                if ($colmes > $kmess) {
                    echo "\n" . '<div class="topmenu">' . $tools->displayPaginationSeo($config['homeurl'] . '/forum/' . $id . '/' . $seo, $start, $colmes, $kmess) . '</div>';
                }
                echo '</div>';
                echo "\n" . '<ul class="ul-class">';
                if(!empty($type1['tags'])){
                    $demtags = @explode(',', $type1['tags']);
                    foreach ($demtags AS $key => $value) {
                        $datav1 = $tools->checkout(trim($value), 2, 2);
                        $datav2 = rawurlencode($datav1);
                        echo "\n" . '<li class="ul-list"><a href="' . $config['homeurl'] . '/forum/search.php?search='.$datav2.'" class="tload"><span class="m-chip__text">' . $datav1 . '</span></a></li>';
                    }
                } else echo $tools->createTags($type1['text'], 1);

                // Список кураторов
                echo '</ul><br />';
                if ($curators) {
                    $array = [];

                    foreach ($curators as $key => $value) {
                        $avatar_name = $tools->avatar_name($key);
                        $user_sex = $db->query("SELECT `name`, `sex` FROM `users` WHERE `id`='$key' LIMIT 1")->fetch();
                        if (file_exists(('../files/users/avatar/' . $avatar_name))) {
                            $avt = $config['homeurl'] . '/files/users/avatar/' . $avatar_name;
                        } else {
                            $avt = $config['homeurl'] . '/images/empty' . ($user_sex['sex'] ? ($user_sex['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
                        }
                        $array[] = ($systemUser->isValid() ? '<a href="' . $config['homeurl'] . '/profile/?user=' . $key . '" rel="nofollow" class="tload m-chip m-chip--contact">' : '<span class="m-chip m-chip--contact">') . '<img class="m-chip__contact" src="' . $avt . '" alt="' . $user_sex['name'] . '" /><span class="m-chip__text">' . $value . '</span>' . ($systemUser->isValid() ? '</a>' : '</span>');
                    }

                    echo "\n" . '<div class="func">' . implode($array) . '</div><br />';
                }

                $reqCM = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `close` != '1' AND `id` != '$id' AND `refid`='" . $type1['refid'] . "' ORDER BY RAND() LIMIT 7");
                if ($reqCM->rowCount()) {
	                echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="phdr"><strong>Cùng Chuyên Mục</strong></div>';
                    for ($i = 0; $resCM = $reqCM->fetch(); ++$i) {
                        echo "\n" . '<div class="list1">';
                        echo "\n" . '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $resCM['id'] . '/' . $resCM['seo'] . '.html">' . ($resCM['bai'] ? 'Bài ' . $resCM['bai'] . ': ' : '') . (empty($resCM['text']) ? '-----' : $resCM['text']) . '</a>';
                        echo "\n" . '</div>';
                    }
                    echo "\n" . '</div>';
                }
                
                echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="phdr"><strong>Công cụ:</strong></div>';
                // Admin
                if ($systemUser->rights == 3 || $systemUser->rights >= 6) {
                    if ($systemUser->rights >= 7) {
                        echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=curators&amp;id=' . $id . '&amp;start=' . $start . '">' . _t('Curators of the Topic') . '</a></div>';
                    }
                    echo isset($topic_vote) && $topic_vote > 0
                        ? '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=editvote&amp;id=' . $id . '">' . _t('Edit Poll') . '</a><br><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=delvote&amp;id=' . $id . '">' . _t('Delete Poll') . '</a></div>'
                        : '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=addvote&amp;id=' . $id . '">' . _t('Add Poll') . '</a></div>';
                    echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=ren&amp;id=' . $id . '">' . _t('Rename Topic') . '</a></div>';

                    // Закрыть - открыть тему
                    if ($type1['edit'] == 1) {
                        echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=close&amp;id=' . $id . '">' . _t('Open Topic') . '</a></div>';
                    } else {
                        echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=close&amp;id=' . $id . '&amp;closed">' . _t('Close Topic') . '</a></div>';
                    }

                    // Удалить - восстановить тему
                    if ($type1['close'] == 1) {
                        echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=restore&amp;id=' . $id . '">' . _t('Restore Topic') . '</a></div>';
                    }

                    echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=deltema&amp;id=' . $id . '">' . _t('Delete Topic') . '</a></div>';

                    if ($type1['vip'] == 1) {
                        echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=vip&amp;id=' . $id . '">' . _t('Unfix Topic') . '</a></div>';
                    } else {
                        echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=vip&amp;id=' . $id . '&amp;vip">' . _t('Pin Topic') . '</a></div>';
                    }

                    echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=per&amp;id=' . $id . '">' . _t('Move Topic') . '</a></div>';
                }
                $user = $db->query("SELECT `rights` FROM `users` WHERE `id`='" . $type1['user_id'] . "' LIMIT 1")->fetch();
                if ($type1['user_id'] == $systemUser->id || $systemUser->rights > $user['rights']) {
                    echo '<div class="card__actions card--border"><a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=thumb&amp;id=' . $id . '">Edit Thumbnail</a></div>';
                }
                echo '<div class="card__actions card--border">' . ($filelink ? "\n" . $filelink . "\n" . '</div>' . "\n" . '<div class="card__actions card--border">' : '');
                if ($filter) {
                    echo '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=filter&amp;id=' . $id . '&amp;do=unset">' . _t('Cancel Filter') . '</a>';
                } else {
                    echo '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=filter&amp;id=' . $id . '&amp;start=' . $start . '">' . _t('Filter by author') . '</a>';
                }
                echo "\n" . '</div><div class="card__actions card--border">';
                echo "\n" . '<a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=tema&amp;id=' . $id . '">' . _t('Download Topic') . '</a>';
                echo "\n" . '</div></div>';
                break;

            default:
                // Если неверные данные, показываем ошибку
                echo $tools->displayError(_t('Wrong data'));
                break;
        }
    } else {
        ////////////////////////////////////////////////////////////
        // Список Категорий форума                                //
        ////////////////////////////////////////////////////////////
        $count = $db->query("SELECT COUNT(*) FROM `cms_forum_files`" . ($systemUser->rights >= 7 ? '' : " WHERE `del` != '1'"))->fetchColumn();
        echo '<div class="mrt-code card shadow--4dp"><div class="phdr"><h4>' . _t('Forum') . '</h4></div>' .
            '<div class="list2"><a class="tload" href="/forum/search.php">' . _t('Search') . '</a> |<a class="tload" href="/forum/index.php?act=files">' . _t('Files') . '</a> <span class="red">(' . $count . ')</span></div></div>';
        $req = $db->query("SELECT `id`, `text`, `soft`, `seo` FROM `forum` WHERE `type`='f' ORDER BY `realid`");
        $i = 0;

        while ($res = $req->fetch()) {
            $count = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='r' AND `refid`='" . $res['id'] . "'")->fetchColumn();
            echo '<div class="mrt-code card shadow--2dp">' .
                '<div class="phdr"><h4>' . $res['text'] . '</h4></div>';
            $reqc = $db->query("SELECT `id`, `seo`, `text`, `soft`, `edit` FROM `forum` WHERE `type`='r' AND `refid`='$res[id]' ORDER BY `realid`");
            $totalc = $reqc->rowCount();
            if ($totalc) {
                $ii = 0;
                while ($resc = $reqc->fetch()) {
                    echo '<div class="card__actions card--border">';
                    $coltemc = $db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $resc['id'] . "' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1'") . "")->fetchColumn();
                    echo '<img class="icon" src="/assets/images/mt.gif"><a class="tload" href="' . $config['homeurl'] . '/forum/' . $resc['id'] . '/' . $resc['seo'] . '.html">' . $resc['text'] . '</a>';

                    if ($coltemc) {
                        echo " [$coltemc]";
                    }
                    if (!empty($resc['soft'])) {
                        echo '<div class="sub"><span class="gray">' . $resc['soft'] . '</span></div>';
                    }
                    echo '</div>';
                    ++$ii;
                }
            } else {
                echo '<div class="card__actions card--border">' . _t('There are no sections in this category') . '</div>';
            }
            echo '</div>';
            ++$i;
        }
        $online_u = $db->query("SELECT COUNT(*) FROM `users` WHERE `lastdate` > " . (time() - 60) . " AND `place` LIKE 'forum%'")->fetchColumn();
        $online_g = $db->query("SELECT COUNT(*) FROM `cms_sessions` WHERE `lastdate` > " . (time() - 60) . " AND `place` LIKE 'forum%'")->fetchColumn();
        unset($_SESSION['fsort_id']);
        unset($_SESSION['fsort_users']);
    }
}

require_once('../system/end.php');
