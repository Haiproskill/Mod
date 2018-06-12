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

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';

require('../system/bootstrap.php');

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

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

// Закрываем от неавторизованных юзеров
if (!$systemUser->isValid()) {
    require('../system/head.php');
    echo $tools->displayError(_t('For registered users only'));
    require('../system/end.php');
    exit;
}

// Получаем данные пользователя
$user = $tools->getUser(isset($_REQUEST['user']) ? abs(intval($_REQUEST['user'])) : 0);

if (!$user) {
    require('../system/head.php');
    echo $tools->displayError(_t('This User does not exists'));
    require('../system/end.php');
    exit;
}

/**
 * Находится ли выбранный пользователь в контактах и игноре?
 *
 * @param int $id Идентификатор пользователя, которого проверяем
 * @return int Результат запроса:
 *                0 - не в контактах
 *                1 - в контактах
 *                2 - в игноре у меня
 */
function is_contact($id = 0)
{
    global $db, $systemUser;

    static $user_id = null;
    static $return = 0;

    if (!$systemUser->isValid() && !$id) {
        return 0;
    }

    if (is_null($user_id) || $id != $user_id) {
        $user_id = $id;
        $req = $db->query("SELECT * FROM `cms_contact` WHERE `user_id` = '" . $systemUser->id . "' AND `from_id` = '$id'");

        if ($req->rowCount()) {
            $res = $req->fetch();
            if ($res['ban'] == 1) {
                $return = 2;
            } else {
                $return = 1;
            }
        } else {
            $return = 0;
        }
    }

    return $return;
}

// Переключаем режимы работы
$array = [
    'activity'  => 'includes',
    'ban'       => 'includes',
    'edit'      => 'includes',
    'images'    => 'includes',
    'info'      => 'includes',
    'ip'        => 'includes',
    'status'    => 'includes',
    'karma'     => 'includes',
    'office'    => 'includes',
    'password'  => 'includes',
    'reset'     => 'includes',
    'settings'  => 'includes',
    'stat'      => 'includes',
];
$path = !empty($array[$act]) ? $array[$act] . '/' : '';

if (isset($array[$act]) && file_exists($path . $act . '.php')) {
    require_once($path . $act . '.php');
} else {
    // Анкета пользователя
    $headmod = 'profile,' . $user['id'];
    $textl = _t('Profile') . ': ' . htmlspecialchars($user['name']);
    require('../system/head.php');

    if (file_exists((ROOT_PATH . 'files/users/photo/' . $user['id'] . '.' . $user['cover_extension']))) {
        $img = $config['homeurl'] . '/files/users/photo/' . $user['id'] . '.' . $user['cover_extension'];
    } else {
        $img = $config['homeurl'] . '/images/default-cover-user.png';
    }

    if (file_exists((ROOT_PATH . 'files/users/photo/' . $user['id'] . '_cover.' . $user['cover_extension']))) {
        $cover_img_url = $config['homeurl'].'/files/users/photo/'.$user['id'] . '_cover.' . $user['cover_extension'];
    } else {
        $cover_img_url = $config['homeurl'].'/images/default-cover-user.png';
    }

    if (file_exists((ROOT_PATH . 'files/users/avatar/' . $user['id'] . '_100x100.' . $user['avatar_extension']))) {
        $avatari = $config['homeurl'] . '/files/users/avatar/'.$user['id'].'_100x100.' . $user['avatar_extension'];
    } else {
        $avatari = $config['homeurl'] . '/images/default-'.($user['sex'] == 'm' ? 'male' : 'female').'-avatar.png';
    }

    echo '<div class="timeline-header-wrapper">'.
        '<div class="cover-container">' .
        '<div class="cover-wrapper">' .
        '<img class="profile-cover" src="'.$cover_img_url.'?'.time().'" alt="'.$user['name'].'">';
    if ($user['id'] == $systemUser->id) {
        echo '<div class="cover-change-wrapper">' .
        '<i class="material-icons" title="Chọn ảnh bìa." onclick="javascript:$(\'.cover-image-input\').click();">&#xE412;</i>' .
        '<i class="material-icons" title="Đặt vị trí ảnh bìa." onclick="repositionCover();">&#xE5D5;</i>' .
        '</div>' .
        '<div class="cover-progress"></div>';
    }
    echo '</div>' .
        '<div class="cover-resize-wrapper">' .
        '<img class="profile-cover-resize" src="'.$img.'?'.time().'" alt="'.$user['name'].'">';
    if ($user['id'] == $systemUser->id) {
        echo '<div class="drag-div" align="center">' .
            'Kéo để đặt lại vị trí.!!' .
            '<div class="timeline-buttons cover-resize-buttons">' .
            '<a style="background: #FF5722;color: #ffffff;" onclick="saveReposition();">Lưu</a>' .
            '<a onclick="cancelReposition();">Hủy</a>' .
            '<form class="cover-position-form hidden" method="post">' .
            '<input class="cover-position" name="pos" value="0" type="hidden">' .
            '<input class="screen-width" name="width" value="920" type="hidden">' .
            '<input name="t" value="cover" type="hidden">' .
            '<input name="a" value="reposition" type="hidden">' .
            '<input name="timeline_id" value="'.$user['id'].'" type="hidden">' .
            '</form>' .
            '</div>';
    echo '</div>' .
        '<div class="cover-progress"></div>';
    }
    echo '</div>' .
        '<div class="avatar-wrapper">' .
        '<img class="profile-avatar avatar" src="'.$avatari.'?'.time().'" alt="'.$user['name'].'">';
    if ($user['id'] == $systemUser->id) {
        echo '<div class="avatar-change-wrapper">' .
            '<i class="material-icons" title="Chọn avatar" onclick="javascript:$(\'.change-avatar-input\').click();">&#xE412;</i>' .
            '</div>' .
            '<form class="change-avatar-form hidden" method="post" enctype="multipart/form-data" action="/request.php?t=avatar&a=post_upload">' .
            '<input class="change-avatar-input hidden" type="file" name="image" accept="image/jpeg,image/png" onchange="javascript:$(\'form.change-avatar-form\').submit();">' .
            '<input name="timeline_id" value="'.$user['id'].'" type="hidden">' .
            '<input name="t" value="avatar" type="hidden">' .
            '<input name="a" value="new" type="hidden">' .
            '</form>' .
            '<div class="avatar-progress-wrapper"></div>';
    }
    echo '</div>' .
        '<div class="timeline-name-wrapper"' . ($user['rights'] == 9 ? ' style="text-shadow: 0 0 6px rgb(255, 255, 255), 0 0 12px rgb(255, 255, 255);"' : '') . '>' .
        '<div id="ava_abc"' . ($user['rights'] == 9 ? ' style="color: #f00"' : '') . '>'.$user['name'].'</div>';
    if (!empty($user['status'])) {
        echo '<div class="status_p"' . ($user['rights'] == 9 ? ' style="color: #f00"' : '') . '>'.$user['status'].'</div>';
    }
    echo '</div>' .
        '</div>' .
        '<div class="timeline-statistics-wrapper">' .
        '<table border="0" width="100%" cellpadding="0" cellspacing="0">' .
        '<tr>' .
        '<td class="statistic" align="center" valign="middle">' .
        '<a href="?act=info&user=' . $user['id'] . '">Giới thiệu</a>' .
        '</td>' .
        '<td class="statistic" align="center" valign="middle">' .
        '<a href="album.php?act=list&amp;user=' . $user['id'] . '">Album ảnh</a>' .
        '</td>' .
        '<td class="statistic" align="center" valign="middle">' .
        '<a href="?act=activity&user=' . $user['id'] . '">Hoạt động</a>' .
        '</td>' .
        '</tr>' .
        '</table>' .
        '</div>' .
        '</div>';

    if ($user['id'] == $systemUser->id) {
        echo '<form class="cover-form hidden" method="post" enctype="multipart/form-data" action="/request.php?t=cover&a=post_upload">' .
        '<input class="cover-image-input hidden" type="file" name="image" accept="image/jpeg,image/png" onchange="javascript:$(\'form.cover-form\').submit();">' .
        '<input name="timeline_id" value="'.$user['id'].'" type="hidden">' .
        '<input name="t" value="cover" type="hidden">' .
        '<input name="a" value="new" type="hidden">' .
        '</form>';
    }
    if ($user['rights'] <= $systemUser->rights) {
        echo '<div class="mrt-code card shadow--2dp">' .
            '<div class="card__actions">Tài sản: ' . $tools->balans($user['balans']) . ' VNĐ</div>' .
            '</div>';
    }

    if ($user['id'] != $systemUser->id) {
        echo '<div class="mrt-code card shadow--2dp"><div class="card__actions">';
        if (!$tools->isIgnor($user['id'])
            && is_contact($user['id']) != 2
            && !isset($systemUser->ban['1'])
            && !isset($systemUser->ban['3'])
        ) {
            echo '<a href="/mail/index.php?act=write&amp;id=' . $user['id'] . '">' . _t('Write') . '</a>';
        }
        
        if (is_contact($user['id']) != 2) {
            if (!is_contact($user['id'])) {
                echo ' | <a href="../mail/index.php?id=' . $user['id'] . '">' . _t('Add to Contacts') . '</a>';
            } else {
                echo ' | <a href="../mail/index.php?act=deluser&amp;id=' . $user['id'] . '">' . _t('Remove from Contacts') . '</a>';
            }
        }

        $bancount = $db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $user['id'] . "'")->fetchColumn();

        if ($bancount) {
            echo ' | <a href="?act=ban&amp;user=' . $user['id'] . '">' . _t('Violations') . '</a>(' . $bancount . ')';
        }


        if (is_contact($user['id']) != 2) {
            echo ' | <a href="../mail/index.php?act=ignor&amp;id=' . $user['id'] . '&amp;add">' . _t('Block User') . '</a>';
        } else {
            echo ' | <a href="../mail/index.php?act=ignor&amp;id=' . $user['id'] . '&amp;del">' . _t('Unlock User') . '</a>';
        }

        echo '</div></div>';
    } else {
        // Блок почты
        echo '<div class="mrt-code card shadow--2dp">' .
            '<div class="card__actions">' . $tools->image('user-edit.png') . '<a href="?act=edit">' . _t('Edit Profile') . '</a></div>' .
            '<div class="card__actions card--border">' . $tools->image('user.png') . '<a href="../mail/">Messenger</a></div>';

        $count_systems = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `delete`!='" . $systemUser->id . "' AND `sys`='1'")->fetchColumn();
        //Системные сообщения
        $count_systems_new = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `delete`!='" . $systemUser->id . "' AND `sys`='1' AND `read`='0'")->fetchColumn();
        echo '<div class="card__actions card--border">' . $tools->image('mail-info.png') . '<a href="../mail/index.php?act=systems">' . _t('System') . '</a>&nbsp;(' . $count_systems . ($count_systems_new ? '/<span class="red">+' . $count_systems_new . '</span>' : '') . ')</div>';

        //Файлы
        $count_file = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE (`user_id`='" . $systemUser->id . "' OR `from_id`='" . $systemUser->id . "') AND `delete`!='" . $systemUser->id . "' AND `file_name`!='';")->fetchColumn();
        echo '<div class="card__actions card--border">' . $tools->image('file.gif') . '<a href="../mail/index.php?act=files">' . _t('Files') . '</a>&nbsp;(' . $count_file . ')</div>';

        // Блок контактов
        echo '</div>';
    }
    include 'stt.php';
}

require_once('../system/end.php');
