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

require('../system/head.php');

if ($systemUser->isValid() && $config->mod_moderation && $systemUser->rights < 6 && $systemUser->rights != 3){
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Chờ kiểm duyệt.</h4></div>';
    $req = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `moderation` = '0' AND `user_id` = '" . $systemUser->id . "' ORDER BY `time` DESC LIMIT $start, $kmess ");
    while ($res = $req->fetch()) {
        echo '<div class="list1"><a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html">' . ($res['bai'] ? 'Bài ' . $res['bai'] . ': ' : '') . (empty($res['text']) ? '-----' : $res['text']) . '</a></div>';
    }
    echo '</div>';
    echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="phdr">Tổng: ' . $count_post . '</div>';
    if ($count_post > $kmess) {
        echo "\n" . '<div class="topmenu">' . $tools->displayPagination($config['homeurl'] . '/forum/index.php?act=moderation&', $start, $count_post, $kmess) . '</div>';
    }
    echo '</div>';
    
    require('../system/end.php');
    exit;
}
if (!$systemUser->isValid()
    || isset($systemUser->ban['1'])
    || isset($systemUser->ban['11'])
    || ($systemUser->rights < 6 && $systemUser->rights != 3)
) {
    echo $tools->displayError(_t('Access forbidden'));
    require('../system/end.php');
    exit;
}

if($id){
    $res = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 't' ")->fetch();
    if (!$res || ($res['user_id'] == $systemUser->id && $systemUser->rights != 3 && $systemUser->rights < 6)) {
        echo $tools->displayError(_t('Wrong data'));
        require('../system/end.php');
        exit;
    }
    if(isset($_POST['submit'])) {
        $db->exec("UPDATE `forum` SET `moderation` = '1', `moderation_who` = '" . $systemUser->id . "' WHERE id='" . $id . "'");
        header("Location: " . $config['homeurl'] . "/forum/index.php?act=moderation");
    } else if(isset($_POST['submitdel'])) {
        header("Location: " . $config['homeurl'] . "/forum/index.php?act=deltema&id=$id");
    } else {
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Kiểm duyệt.</h4></div>' .
            '<div class="bmenu">Chủ đề: ' . $res['text'] . '</div>';
        echo '<div class="list1">• Bạn phải hiểu rõ <a href="' . $config['homeurl'] . '/help/?act=forum"><strong>nội quy diễn đàn</strong></a>.!<br />' .
            '• Bạn có chắc <strong>nội dung</strong> trong chủ đề <a href="' . $config['homeurl'] . '/forum/' . $id . '/' . $res['seo'] . '.html"><strong>' . $res['text'] . '</strong></a> này phù hợp với <a href="' . $config['homeurl'] . '/help/?act=forum"><strong>nội quy diễn đàn</strong></a> ?<br />' .
            '• Nếu nội dung bài viết bị phản ánh, người kiểm duyệt sẽ phải chịu trách nhiệm cho điều này.! ' .
            '<form name="form" method="post">' .
            '<div class="button-container"><button class="button" type="submit" name="submit"><span>Kiểm duyệt</span></button>' .
            '<button class="button" type="submit" name="submitdel"><span>Loại bỏ</span></button>' .
            '</div>' .
            '</form>' .
            '</div>';
        echo '</div>';
    }
} else {
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Kiểm duyệt.</h4></div>';
    $req = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `moderation` = '0' ORDER BY `time` DESC LIMIT $start, $kmess ");
    while ($res = $req->fetch()) {
        echo '<div class="list1"><a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html">' . ($res['bai'] ? 'Bài ' . $res['bai'] . ': ' : '') . (empty($res['text']) ? '-----' : $res['text']) . '</a></div>';
    }
    echo '</div>';
    echo "\n" . '<div class="mrt-code card shadow--2dp"><div class="phdr">Tổng: ' . $count_post . '</div>';
    if ($count_post > $kmess) {
        echo "\n" . '<div class="topmenu">' . $tools->displayPagination($config['homeurl'] . '/forum/index.php?act=moderation&', $start, $count_post, $kmess) . '</div>';
    }
    echo '</div>';
}

