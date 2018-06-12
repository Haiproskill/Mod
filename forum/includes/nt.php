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

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

// Закрываем доступ для определенных ситуаций
if (!$id
    || !$systemUser->isValid()
    || isset($systemUser->ban['1'])
    || isset($systemUser->ban['11'])
    || (!$systemUser->rights && $config['mod_forum'] == 3)
) {
    require('../system/head.php');
    echo $tools->displayError(_t('Access forbidden'));
    require('../system/end.php');
    exit;
}

// Вспомогательная Функция обработки ссылок форума
function forum_link($m)
{
    global $config, $db;

    if (!isset($m[3])) {
        return '[url=' . $m[1] . ']' . $m[2] . '[/url]';
    } else {
        $p = parse_url($m[3]);

        if ('http://' . $p['host'] . (isset($p['path']) ? $p['path'] : '') . '?id=' == $config['homeurl'] . '/forum/index.php?id=') {
            $thid = abs(intval(preg_replace('/(.*?)id=/si', '', $m[3])));
            $req = $db->query("SELECT `text` FROM `forum` WHERE `id`= '$thid' AND `type` = 't' AND `close` != '1'");

            if ($req->rowCount()) {
                $res = $req->fetch();
                $name = strtr($res['text'], [
                    '&quot;' => '',
                    '&amp;'  => '',
                    '&lt;'   => '',
                    '&gt;'   => '',
                    '&#039;' => '',
                    '['      => '',
                    ']'      => '',
                ]);

                if (mb_strlen($name) > 40) {
                    $name = mb_substr($name, 0, 40) . '...';
                }

                return '[url=' . $m[3] . ']' . $name . '[/url]';
            } else {
                return $m[3];
            }
        } else {
            return $m[3];
        }
    }
}
$totalc = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='c' AND `refid`='$id'")->fetchColumn();
$req_r = $db->query("SELECT * FROM `forum` WHERE (`type` = 'r' OR `type` = 'c' ) AND `id` = '$id' LIMIT 1");
$res_r = $req_r->fetch();
// Проверка на флуд
$flood = $tools->antiflood();

if ($flood) {
    require('../system/head.php');
    echo $tools->displayError(sprintf(_t('You cannot add the message so often<br>Please, wait %d sec.'), $flood) . ', <a href="' . $config['homeurl'] . '/forum/' . $id . '/' . $res_r['seo'] . '_start' . $start . '.html">' . _t('Back') . '</a>');
    require('../system/end.php');
    exit;
}

if (!$req_r->rowCount()) {
    require('../system/head.php');
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
}
if ($totalc) {
    require('../system/head.php');
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
}
require('../system/head.php');
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4><a href="' . $config['homeurl'] . '/forum/' . $id . '/' . $res_r['seo'] . '.html"><b>' . _t('Forum') . '</b></a>&#160;|&#160;' . _t('New Topic') . '</h4></div>';
$th = filter_has_var(INPUT_POST, 'th')
    ? mb_substr(filter_var($_POST['th'], FILTER_SANITIZE_SPECIAL_CHARS, ['flag' => FILTER_FLAG_ENCODE_HIGH]), 0, 255)
    : '';
$seo  = $tools->seourl($th);
$tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
$bai  = isset($_POST['bai']) ? trim($_POST['bai']) : '0';
$msg  = isset($_POST['msg']) ? trim($_POST['msg']) : '';
$msg  = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);

if (isset($_POST['submit'])
    && isset($_POST['token'])
    && isset($_SESSION['token'])
    && $_POST['token'] == $_SESSION['token']
) {
    $error = [];

    if (empty($th)) {
        $error[] = _t('You have not entered topic name');
    }

    if (mb_strlen($th) < 2 || mb_strlen($th) > 255) {
        $error[] = _t('Topic name too short');
    }
    if (!empty($bai) && ($bai < 1 || $bai > 100000)) {
        $error[] = _t('Bạn đang không tuân thủ nguyên tắc bài viết.');
    }
    if (!empty($tags) && (mb_strlen($tags) < 3 || mb_strlen($tags) > 150)) {
        $error[] = _t('Tags yêu cầu 4 đến 150 ký tự.');
    }

    if (empty($msg)) {
        $error[] = _t('You have not entered the message');
    }

    if (mb_strlen($msg) < 4) {
        $error[] = _t('Text is too short');
    }

    if (!$error) {
        $msg = preg_replace_callback('~\\[url=(http://.+?)\\](.+?)\\[/url\\]|(http://(www.)?[0-9a-zA-Z\.-]+\.[0-9a-zA-Z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', 'forum_link', $msg);

        // Прверяем, есть ли уже такая тема в текущем разделе?
        if ($db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' AND `refid` = '$id' AND `text` = '$th'")->fetchColumn() > 0) {
            $error[] = _t('Topic with same name already exists in this section');
        }

        // Проверяем, не повторяется ли сообщение?
        $req = $db->query("SELECT * FROM `forum` WHERE `user_id` = '" . $systemUser->id . "' AND `type` = 'm' ORDER BY `time` DESC");

        if ($req->rowCount()) {
            $res = $req->fetch();

            if ($msg == $res['text']) {
                $error[] = _t('Message already exists');
            }
        }
    }

    if (!$error) {
        unset($_SESSION['token']);

        if($config->mod_moderation && $systemUser->rights < 6) {
            $moderation = '0';
        } else {
            $moderation = '1';
        }

        /** @var Johncms\Api\EnvironmentInterface $env */
        $env = App::getContainer()->get(Johncms\Api\EnvironmentInterface::class);

        // Если задано в настройках, то назначаем топикстартера куратором
        $curator = $res_r['edit'] == 1 ? serialize([$systemUser->id => $systemUser->name]) : '';

        // Добавляем тему
        $db->prepare('
          INSERT INTO `forum` SET
           `refid` = ?,
           `type` = \'t\',
           `time` = ?,
           `post-time` = ?,
           `user_id` = ?,
           `from` = ?,
           `text` = ?,
           `bai` = ?,
           `seo` = ?,
           `tags` = ?,
           `ip` = ?,
           `ip_via_proxy` = ?,
           `soft` = ?,
           `edit` = \'\',
           `curators` = ?,
           `moderation` = ?
        ')->execute([
            $id,
            time(),
            time(),
            $systemUser->id,
            $systemUser->name,
            $th,
            $bai,
            $seo,
            $tags,
            $env->getIp(),
            $env->getIpViaProxy(),
            $env->getUserAgent(),
            $curator,
            $moderation,
        ]);

        $rid = $db->lastInsertId();

        // Добавляем текст поста
        $db->prepare('
          INSERT INTO `forum` SET
          `refid` = ?,
          `type` = \'m\',
          `time` = ?,
          `user_id` = ?,
          `from` = ?,
          `ip` = ?,
          `ip_via_proxy` = ?,
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
            $env->getIpViaProxy(),
            $env->getUserAgent(),
            $msg,
        ]);

        $postid = $db->lastInsertId();

        /** Begin user tag */
        $exists = array();

        if(preg_match('#@([a-zA-Z0-9\-\~\_\.]+)#si', $msg)){
            preg_match_all('#@([a-zA-Z0-9\-\~\_\.]+)#si', $msg, $tagThanhVien);
            foreach($tagThanhVien[1] AS $tag){
                $data = $db->prepare('SELECT * FROM `users` WHERE `name` = ?');
                $data->execute([$tag]);
                $user = $data->fetch();
                if(isset($exists[intval($user['id'])]) == false && $data->rowCount() && $user['id'] != $systemUser->id) {
                    $exists[intval($user['id'])] = true;
                    $db->prepare('
                        INSERT INTO `cms_mail` SET
                          `user_id` = ?,
                          `from_id` = ?,
                          `sys` = \'1\',
                          `time` = ?,
                          `reid` = ?,
                          `type` = \'forum.tag.nt\'
                    ')->execute([
                        $systemUser->id,
                        $user['id'],
                        time(),
                        $postid,
                    ]);
                }
            }
        }

        if(preg_match('#\[\@(.+?)\]#s', $msg)){
            preg_match_all('#\[\@(.+?)\]#s', $msg, $tagThanhVien);
            foreach($tagThanhVien[1] AS $tag){
                $tag = trim($tag);
                $data = $db->prepare('SELECT * FROM `users` WHERE `name` = ?');
                $data->execute([$tag]);
                $user = $data->fetch();
                if(isset($exists[intval($user['id'])]) == false && $data->rowCount() && $user['id'] != $systemUser->id){
                    $exists[intval($user['id'])] = true;
                    $db->prepare('
                        INSERT INTO `cms_mail` SET
                          `user_id` = ?,
                          `from_id` = ?,
                          `sys` = \'1\',
                          `time` = ?,
                          `reid` = ?,
                          `type` = \'forum.tag.nt\'
                    ')->execute([
                        $systemUser->id,
                        $user['id'],
                        time(),
                        $postid,
                    ]);
                }
            }
        }
        /** End user tag */

        // Записываем счетчик постов юзера
        $fpst = $systemUser->postforum + 1;
        $db->exec("UPDATE `users` SET
            `postforum` = '$fpst',
            `lastpost` = '" . time() . "'
            WHERE `id` = '" . $systemUser->id . "'
        ");

        // Ставим метку о прочтении
        $db->exec("INSERT INTO `cms_forum_rdm` SET
            `topic_id`='$rid',
            `user_id`='" . $systemUser->id . "',
            `time`='" . time() . "'
        ");

        if ($_POST['thumb'] == 1) {
            $path = ROOT_PATH . 'files/forum/thumbnail/';
            $dataCrop = array(
                'type' => 'crop',
                'width' => 100,
                'height' => 100,
                'name' => ''
                );

            if(preg_match('#\[img\](https?://.+?)\[\/img\]#i', $msg, $maches)) {
                $img = $maches[1];
                if(@getimagesize($img)){
                    $nameFile = basename($img);
                    $ext = strtolower(substr($nameFile, strrpos($nameFile, '.') + 1, strlen($nameFile) - strrpos($nameFile, '.')));
                    $path = $path . $rid . '.' . $ext;
                    $data = $tools->grab($img);
                    if (file_put_contents($path, $data)) {
                        $tools->processMedia($dataCrop['type'], $path, $path, $dataCrop['width'], $dataCrop['height']);
                        $db->exec("UPDATE `forum` SET
                            `thumb_extension` = '" . $ext . "'
                            WHERE `id` = '" . $rid  . "'
                          ");
                        header('Location: ' . $config['homeurl'] . '/forum/' . $rid . '/' . $seo . '.html#post-' . $postid);
                    } else {
                        @unlink($path);
                    }
                }
            } else if(preg_match('#\[img=(https?://.+?)\]#i', $msg, $maches)) {
                $img = $maches[1];
                if(@getimagesize($img)){
                    $nameFile = basename($img);
                    $ext = strtolower(substr($nameFile, strrpos($nameFile, '.') + 1, strlen($nameFile) - strrpos($nameFile, '.')));
                    $path = $path . $rid . '.' . $ext;
                    $data = $tools->grab($img);
                    if (file_put_contents($path, $data)) {
                        $tools->processMedia($dataCrop['type'], $path, $path, $dataCrop['width'], $dataCrop['height']);
                        $db->exec("UPDATE `forum` SET
                            `thumb_extension` = '" . $ext . "'
                            WHERE `id` = '" . $rid  . "'
                          ");
                        header('Location: ' . $config['homeurl'] . '/forum/' . $rid . '/' . $seo . '.html#post-' . $postid);
                    } else {
                        @unlink($path);
                    }
                }
            } else {
                header("Location: index.php?id=$rid&act=thumb");
            }
        } else {
            header("Location: index.php?id=$rid&act=thumb");
        }
    } else {
        // Выводим сообщение об ошибке
        echo $tools->displayError($error);
    }
}

$res_c = $db->query("SELECT * FROM `forum` WHERE `id` = '" . $res_r['refid'] . "'")->fetch();
$msg_pre = $tools->checkout($msg, 1, 1, 0, 1);
$msg_pre = preg_replace('#\[c\](.*?)\[/c\]#si', '<div class="quote">\1</div>', $msg_pre);

if ($msg && $th && !isset($_POST['submit'])) {
    echo '<div class="list1">' . $tools->image('op.gif') . '<span style="font-weight: bold">' . $th . '</span></div>' .
        '<div class="list2">' . $tools->displayUser($systemUser, ['iphide' => 1, 'header' => '<span class="gray">(' . $tools->displayDate(time()) . ')</span>', 'body' => $msg_pre]) . '</div>';
}
// Получаем структуру форума
$rest = true;
$allow = 0;
$parent = $res_c['refid'];
while ($parent != '0' && $rest != false) {
    $rest = $db->query("SELECT * FROM `forum` WHERE `id` = '$parent' LIMIT 1")->fetch();
    if ($rest['type'] == 'f' || $rest['type'] == 'r' || $rest['type'] == 'c') {
        $tree[] = '<a href="' . $config['homeurl'] . '/forum/' . $parent . '/' . $rest['seo'] . '.html">' . $rest['text'] . '</a>';

        if (($rest['type'] == 'r'  || $rest['type'] == 'c') && !empty($rest['edit'])) {
            $allow = intval($rest['edit']);
        }
    }
    $parent = $rest['refid'];
}
@krsort($tree);
$tree[] = '<a href="' . $config['homeurl'] . '/forum/' . $res_c['id'] . '/' . $res_c['seo'] . '.html">' . $res_c['text'] . '</a>';
$tree[] = '<a href="' . $config['homeurl'] . '/forum/' . $res_r['id'] . '/' . $res_r['seo'] . '.html">' . $res_r['text'] . '</a>';
echo '<div class="list1">' .
    _t('Section') . ': ' . implode(' > ', $tree) .
    '<form name="form" action="index.php?act=nt&amp;id=' . $id . '" method="post">' .
    '<div class="form-group">' .
    '<input type="text" size="20" maxlength="100" name="th" value="' . $th . '" required="required" />' .
    '<label class="control-label" for="input">' . _t('Title(max. 100)') . '</label><i class="bar"></i>' .
    '</div>' .
    '<div class="form-group">' .
    '<input type="number" name="bai" min="0" value="' . $bai . '" placeholder="0"/>' .
    '<label class="control-label" for="input">Bài (chỉ khi bạn đang viết dự án, VD: bài 1, bài 2, ...):</label><i class="bar"></i>' .
    '</div>' .
    '<div class="form-group">' .
    '<input type="text" name="tags" value="' . $tags . '" placeholder="Tags" />' .
    '<label class="control-label" for="input">Tags:</label><i class="bar"></i>' .
    '</div>';
echo $container->get(Johncms\Api\BbcodeInterface::class)->buttons('form', 'msg');
echo '<div class="form-group">' .
    '<textarea rows="' . $systemUser->getConfig()->fieldHeight * 2 . '" name="msg" required="required">' . (isset($_POST['msg']) ? $tools->checkout($_POST['msg']) : '') . '</textarea>' .
    '<label class="control-label" for="textarea">' . _t('Message') . '</label><i class="bar"></i>' .
    '</div>' .
    '<div class="checkbox">' .
    '<label>' .
    '<input type="checkbox" name="thumb" value="1" checked="checked" /><i class="helper"></i> Auto thumbnail' .
    '</label>' .
    '</div>';

$token = mt_rand(1000, 100000);
$_SESSION['token'] = $token;
echo '<div class="button-container">' .
    '<button class="button" type="submit" name="submit"><span>' . _t('Save') . '</span></button>' .
    ($set_forum['preview'] ? '&#160;<button class="button" type="submit"><span>' . _t('Preview') . '</span></button>' : '') .
    '<input type="hidden" name="token" value="' . $token . '"/>' .
    '</div></form></div>';
echo '</div>';
echo '<div class="mrt-code card shadow--2dp"><div class="card__actions"><a href="../help/?act=smileys">' . _t('Smilies') . '</a></div>' .
    '<div class="card__actions card--border"><a href="' . $config['homeurl'] . '/forum/' . $id . '/' . $res_r['seo'] . '.html">' . _t('Back') . '</a></div></div>';
