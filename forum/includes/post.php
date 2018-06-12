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

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

if (empty($_GET['id'])) {
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
}

// Запрос сообщения
$res = $db->query("SELECT `forum`.*, `users`.`name`, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`
FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
WHERE `forum`.`type` = 'm' AND `forum`.`id` = '$id'" . ($systemUser->rights >= 7 ? "" : " AND `forum`.`close` != '1'") . " LIMIT 1")->fetch();
if (!$res['id']) {
    echo $tools->displayError(_t('Wrong data'));
    require('../system/end.php');
    exit;
}
// Запрос темы
$them = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `id` = '" . $res['refid'] . "'")->fetch();
if ($config->mod_moderation && !$them['moderation']) {
    echo $tools->displayError('Bài viết chưa được kiểm duyệt.!');
    require('../system/end.php');
    exit;
}
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><b>' . _t('Topic') . ':</b>&#160;' . $them['text'] . '</div></div><div class="mrt-code card shadow--2dp"><div class="card__actions fauthor">';

// Данные пользователя
$avatar_name = $tools->avatar_name($res['user_id']);
if (file_exists(('../files/users/avatar/' . $avatar_name))) {
    echo '<img src="' . $config['homeurl'] . '/files/users/avatar/' . $avatar_name . '" class="avatar" alt="' . $res['from'] . '" />';
} else {
    echo '<img src="' . $config['homeurl'] . '/images/empty' . ($res['sex'] ? ($res['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="avatar" alt="' . $res['from'] . '" />';
}
echo '<ul class="finfo">'.
    '<li>';

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
    echo '<a href="/profile/?user=' . $res['user_id'] . '">' . $tools->rightsColor($res['rights'], $name) . '</a> ';
} else {
    echo $tools->rightsColor($res['rights'], $name) . ' ';
}

// Метка должности
$user_rights = [
    3 => '(FMod)',
    6 => '(Smd)',
    7 => '(Adm)',
    9 => '(SV!)',
];
echo @$user_rights[$res['rights']];

// Метка Онлайн / Офлайн
echo(time() > $res['lastdate'] + 60 ? '<span class="red"> [Off]</span> ' : '<span class="wgreen"> [ON]</span> ');

// Статус юзера
if (!empty($res['status'])) {
    echo '</li><li>';
    echo '<i class="material-icons list__item-icon">star</i><small>' . $res['status'] . '</small>';
}

echo '</li></ul>';
echo '</div>' .
          '<div class="card__actions card--border mrt2">' .
          '<div class="fleft"><span class="mrt-td"><i class="material-icons" style="font-size: 14px">schedule</i>&#160;' . $tools->thoigian($res['time']) . '</span></div>

          <div class="fright"><a href="/forum/post-' . $res['id'] . '.html" title="Link to post">['.($count == 1 ? 'TOP' : '#'.$count).']</a></div>' .
          '</div>' .
          '<div class="card__actions">';
// Вывод текста поста
$text = $tools->checkout($res['text'], 1, 1, 0, 1);

echo $text;

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

// Если есть прикрепленный файл, выводим его описание
$freq = $db->query("SELECT * FROM `cms_forum_files` WHERE `post` = '" . $res['id'] . "'");

if ($freq->rowCount()) {
    echo '<ul class="atack-file"><li class="atack-file--title">' . _t('Attachment') . ':</li>';
    if (!$systemUser->isValid()) {
        echo '<li class="atack-file--info">Nội dung chỉ dành cho thành viên đăng nhập.!!</li>';
    } else {
        while ($fres = $freq->fetch()){
            $fls = round(@filesize('../files/forum/attach/' . $fres['filename']) / 1024, 2);
            echo '<li class="atack-file--info">';
            // Предпросмотр изображений
            $att_ext = strtolower(pathinfo('./files/forum/attach/' . $fres['filename'], PATHINFO_EXTENSION));
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
                "\n" . '<div class="info">' .
                "\n" . 'Tập tin: <a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=file&amp;id=' . $fres['id'] . '">' . $fres['filename'] . '</a><br />' .
                ($fres['balans'] > 0 ? '<span style="color: #F00">Giá: ' . number_format($fres['balans'], 0, ",", ".") . ' VNĐ</span><br />' : '') .
                "\n" . 'Kích thước: ' . $fls . ' kb</div>';

            if ($allowEdit) {
                echo "\n" . '<a style="position: absolute; bottom: 3px; right: 5px;" href="' . $config['homeurl'] . '/forum/index.php?act=editpost&amp;do=delfile&amp;fid=' . $fres['id'] . '&amp;id=' . $res['id'] . '">' . _t('Delete') . '</a>';
            }

            echo '</li>';
        }
    }
    echo '</div>';
}

echo '</div></div>';

// Вычисляем, на какой странице сообщение?
$page = ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $res['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '$id'")->fetchColumn() / $kmess);
echo '<div class="mrt-code card shadow--2dp"><div class="card__actions"><a href="' . $config['homeurl'] . '/forum/' . $res['refid'] . '/' . $them['seo'] . ($page > 1 ? '_p' . $page : '') . '.html#post-' . $id . '">' . _t('Back to topic') . '</a></div>';
echo '<div class="card__actions card--border"><a href="' . $config['homeurl'] . '/forum/index.html">' . _t('Forum') . '</a></div></div>';
