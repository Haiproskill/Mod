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

$set_mail = unserialize($user['set_mail']);
$out = '';
$total = 0;
$ch = 0;
$mod = isset($_REQUEST['mod']) ? $_REQUEST['mod'] : '';

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

if (!$id) {
    $textl = _t('Mail');
    require_once('../system/head.php');
    echo $tools->displayError(_t('User does not exists'));
    require_once("../system/end.php");
    exit;
}
if ($id) {
    $req = $db->query("SELECT * FROM `users` WHERE `id` = '$id' LIMIT 1");

    if (!$req->rowCount()) {
        $textl = _t('Mail');
        require_once('../system/head.php');
        echo $tools->displayError(_t('User does not exists'));
        require_once("../system/end.php");
        exit;
    }

    $qs = $req->fetch();

    if ($mod == 'clear') {
        $textl = _t('Mail');
        require_once('../system/head.php');
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('Clear messages') . '</h4></div>';

        if (isset($_POST['clear'])) {
            $count_message = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE ((`user_id`='$id' AND `from_id`='" . $systemUser->id . "') OR (`user_id`='" . $systemUser->id . "' AND `from_id`='$id')) AND `delete`!='" . $systemUser->id . "'")->fetchColumn();

            if ($count_message) {
                $req = $db->query("SELECT `cms_mail`.* FROM `cms_mail` WHERE ((`cms_mail`.`user_id`='$id' AND `cms_mail`.`from_id`='" . $systemUser->id . "') OR (`cms_mail`.`user_id`='" . $systemUser->id . "' AND `cms_mail`.`from_id`='$id')) AND `cms_mail`.`delete`!='" . $systemUser->id . "' LIMIT " . $count_message);

                while ($row = $req->fetch()) {
                    if ($row['delete']) {
                        if ($row['file_name']) {
                            if (file_exists('../files/mail/' . $row['file_name']) !== false) {
                                @unlink('../files/mail/' . $row['file_name']);
                            }
                        }

                        $db->exec("DELETE FROM `cms_mail` WHERE `id`='{$row['id']}' LIMIT 1");
                    } else {
                        if ($row['read'] == 0 && $row['user_id'] == $systemUser->id) {
                            if ($row['file_name']) {
                                if (file_exists('../files/mail/' . $row['file_name']) !== false) {
                                    @unlink('../files/mail/' . $row['file_name']);
                                }
                            }

                            $db->exec("DELETE FROM `cms_mail` WHERE `id`='{$row['id']}' LIMIT 1");
                        } else {
                            $db->exec("UPDATE `cms_mail` SET `delete` = '" . $systemUser->id . "' WHERE `id` = '" . $row['id'] . "' LIMIT 1");
                        }
                    }
                }
            }

            echo '<div class="gmenu">' . _t('Messages are deleted') . '</div>';
        } else {
            echo '<div class="rmenu">
			<form action="index.php?act=write&amp;mod=clear&amp;id=' . $id . '" method="post">
			<center>' . _t('Confirm the deletion of messages') . '</center>
			<div class="button-container"><button class="button" type="submit" name="clear"><span>' . _t('Delete') . '</span></button></div>
			</form>
			</div>';
        }

        echo '<div class="list1"><a href="index.php?act=write&amp;id=' . $id . '">' . _t('Back') . '</a></div>';
        echo '<div class="list1"><a href="../profile/?act=office">' . _t('Personal') . '</a></div></div>';
        require_once('../system/end.php');
        exit;
    }
}

if (empty($_SESSION['error'])) {
    $_SESSION['error'] = '';
}
$out .= '<div class="mrt-code card shadow--2dp"><div class="phdr"><h5>' . ($id && isset($qs) ? _t('Personal correspondence with') . ' <a href="../profile/?user=' . $qs['id'] . '">' . $qs['name'] . '</a>' : _t('Send a message')) . '</h5></div></div>';
if ($id) {
    $total = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE ((`user_id`='$id' AND `from_id`='" . $systemUser->id . "') OR (`user_id`='" . $systemUser->id . "' AND `from_id`='$id')) AND `sys`!='1' AND `delete`!='" . $systemUser->id . "' AND `spam`='0'")->fetchColumn();
    $out .= '<div class="mrt-code card">';
    $out .= '<div class="phieubac-messenger"><div class="in-messenger" id="inner">';
    if ($total > 20) {
        $out .= '<center><button class="phieubac-message m-button" data-c="1" data-mid="' . $id . '" style="line-height:18px;" onclick="loadM()">Xem thêm....</button></center><br />';
    }
    $out .= '<div class="content-messenger" id="content" data-yid="' . $id . '">';
    if ($total) {
    	$dem = '0';
    	if ($total > 20) {
            $dem = $total - 20;
        }
        $req = $db->query("SELECT `cms_mail`.*, `cms_mail`.`id` as `mid`, `cms_mail`.`time` as `mtime`, `users`.`sex`, `users`.`id`
            FROM `cms_mail`
            LEFT JOIN `users` ON `cms_mail`.`user_id`=`users`.`id`
            WHERE ((`cms_mail`.`user_id`='$id' AND `cms_mail`.`from_id`='" . $systemUser->id . "') OR (`cms_mail`.`user_id`='" . $systemUser->id . "' AND `cms_mail`.`from_id`='$id'))
            AND `cms_mail`.`delete`!='" . $systemUser->id . "'
            AND `cms_mail`.`sys`!='1'
            AND `cms_mail`.`spam`='0'
            ORDER BY `cms_mail`.`time` ASC
            LIMIT " . $dem . ", 20");
            
        $mass_read = [];
        $test1 = false;
        $test2 = false;
        while ($row = $req->fetch()) {
            if ($row['from_id'] == $systemUser->id) {
                if ($test2 == true) {
                    $out .= '</div>';
                }
                if ($test1 == false) {
                    $out .= '<div class="message-group message-group-them">';
                }
                $test1 = true;
                $test2 = false;
                $who = 'them';

                $avatar_name = $tools->avatar_name($row['user_id']);
                if (file_exists(('../files/users/avatar/' . $avatar_name))) {
                    $avatar = 'background-image: url(/files/users/avatar/' . $avatar_name . '); ';
                } else {
                    $avatar = 'background-image: url(/images/empty' . ($row['sex'] ? ($row['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '); ';
                }
                $icons = '<div class="circle-wrapper animated bounceIn" style="' . $avatar . 'background-size: 40px 40px;"></div>';
            } else {
                if ($test1 == true) {
                    $out .= '</div>';
                }
                if ($test2 == false) {
                    $out .= '<div class="message-group message-group-me">';
                }
                $test1 = false;
                $test2 = true;
                $who = 'me';
                $icons = '';
            }
            if ($row['read'] == 0 && $row['from_id'] == $systemUser->id) {
                $mass_read[] = $row['mid'];
            }

            $post = $row['text'];
            $post = $tools->checkout($post, 1, 1, 0, 1);

            if ($row['file_name']) {
                $post .= '<div class="func">' . _t('File') . ': <a href="index.php?act=load&amp;id=' . $row['mid'] . '">' . $row['file_name'] . '</a> (' . formatsize($row['size']) . ')(' . $row['count'] . ')</div>';
            }

            $subtext = '<a href="index.php?act=delete&amp;id=' . $row['mid'] . '">' . _t('Delete') . '</a>';
            $arg = [
                'header'  => '(' . $tools->displayDate($row['mtime']) . ')',
                'body'    => $post,
                'sub'     => $subtext,
                'stshide' => 1,
            ];
            $out .= '<div class="message-wrapper ' . $who . '" mes-id="' . $row['mid'] . '">';
            $out .= $icons;
            $out .= '<div class="text-wrapper">' . $post . '</div>';
            $out .= '<div class="time-wrapper">' . (round((time()-$row['mtime'])/3600) < 1 ? '<span class="ajax-time" title="' . $tools->timestamp($row['mtime']) . '">' . $tools->thoigian($row['mtime']) . '</span>' : $tools->thoigian($row['mtime'])) . '</div>' .
                '</div>';
            $i++;
        }
        $out .= '</div>';
        //Ставим метку о прочтении
        if ($mass_read) {
            $result = implode(',', $mass_read);
            $db->exec("UPDATE `cms_mail` SET `read`='1' WHERE `from_id`='" . $systemUser->id . "' AND `id` IN (" . $result . ")");
        }
    }
    $out .= '</div></div></div>';
    $out .= '</div>';
}
if (!$tools->isIgnor($id) && empty($systemUser->ban['1']) && empty($systemUser->ban['3'])) {
    $out .= isset($_SESSION['error']) ? $_SESSION['error'] : '';
    $out .= '<div class="mrt-code card shadow--2dp">';
    if ($error){
        $out .= '<div class="jserroradd rmenu">' . implode('<br />', $error) . '</div>';
    }
    $out .= '<div class="jserror" style="display:none;"></div>';
    $out .= '<div class="card__actions">' .
        '<form id="ajaxMessenger" name="form" method="post">';
    $out .= $container->get(Johncms\Api\BbcodeInterface::class)->buttons('form', 'text');
    $out .= '<div class="form-group"><textarea id="mtextarea" rows="' . $systemUser->getConfig()->fieldHeight . '" name="text" required="required"></textarea><label class="control-label" for="mtextarea">' . _t('Message') . '</label><i class="bar"></i></div>';
    $out .= '<div class="button-container"><button class="button" type="submit" name="submit"><span class="flex-center">' . _t('Send') . '</span></button></div>' .
        '<input type="hidden" name="t" value="messenger" />' .
        '<input type="hidden" name="a" value="new" />' .
        '<input type="hidden" name="id" value="' . $id . '" />' .
        '</form></div></div>';
}
$textl = _t('Mail');
require_once('../system/head.php');
echo $out;
echo '<div class="mrt-code card shadow--2dp">';

if ($total) {
    echo '<div class="card__actions"><a href="index.php?act=write&amp;mod=clear&amp;id=' . $id . '">' . _t('Clear messages') . '</a></div>';
}

echo '<div class="' . ($total ? 'list1' : 'card__actions') . '"><a href="/profile' . ($id ? '/?user=' . $id : '') . '">' . _t('Personal') . '</a></div></div>';
unset($_SESSION['error']);
