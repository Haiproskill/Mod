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

$textl = htmlspecialchars($user['name']) . ': ' . _t('Edit Profile');
require('../system/head.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

// Проверяем права доступа для редактирования Профиля
if ($user['id'] != $systemUser->id && ($systemUser->rights < 7 || $user['rights'] >= $systemUser->rights)) {
    echo $tools->displayError(_t('You cannot edit profile of higher administration'));
    require('../system/end.php');
    exit;
}

if(!empty($systemUser->ban)){
    require('../system/end.php');
    exit;
}

// Сброс настроек
if ($systemUser->rights >= 7 && $systemUser->rights > $user['rights'] && $act == 'reset') {
    $db->exec("UPDATE `users` SET `set_user` = '', `set_forum` = '' WHERE `id` = " . $user['id']);
    echo '<div class="gmenu">' . _t('Default settings are set') . '<br /><a href="?user=' . $user['id'] . '">' . _t('Back') . '</a></div>';
    require('../system/end.php');
    exit;
}

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h5><a href="?user=' . $user['id'] . '">' . ($user['id'] != $systemUser->id ? _t('Profile') : _t('My Profile')) . '</a>&#160;|&#160;' . _t('Edit') . '</h5></div></div>';

if (isset($_POST['submit'])) {
    // Принимаем данные из формы, проверяем и записываем в базу
    $error = [];
    $user['imname'] = isset($_POST['imname']) ? htmlspecialchars(mb_substr(trim($_POST['imname']), 0, 25)) : '';
    $user['live'] = isset($_POST['live']) ? htmlspecialchars(mb_substr(trim($_POST['live']), 0, 50)) : '';
    $user['dayb'] = isset($_POST['dayb']) ? intval($_POST['dayb']) : 0;
    $user['monthb'] = isset($_POST['monthb']) ? intval($_POST['monthb']) : 0;
    $user['yearofbirth'] = isset($_POST['yearofbirth']) ? intval($_POST['yearofbirth']) : 0;
    $user['about'] = isset($_POST['about']) ? htmlspecialchars(mb_substr(trim($_POST['about']), 0, 500)) : '';
    $user['mibile'] = isset($_POST['mibile']) ? htmlspecialchars(mb_substr(trim($_POST['mibile']), 0, 40)) : '';
    $user['mail'] = isset($_POST['mail']) ? htmlspecialchars(mb_substr(trim($_POST['mail']), 0, 40)) : '';
    $user['mailvis'] = isset($_POST['mailvis']) ? 1 : 0;
    $user['icq'] = isset($_POST['icq']) ? intval($_POST['icq']) : 0;
    $user['skype'] = isset($_POST['skype']) ? htmlspecialchars(mb_substr(trim($_POST['skype']), 0, 40)) : '';
    $user['jabber'] = isset($_POST['jabber']) ? htmlspecialchars(mb_substr(trim($_POST['jabber']), 0, 40)) : '';
    $user['www'] = isset($_POST['www']) ? htmlspecialchars(mb_substr(trim($_POST['www']), 0, 40)) : '';
    // Данные юзера (для Администраторов)
    $user['name'] = isset($_POST['name']) ? htmlspecialchars(mb_substr(trim($_POST['name']), 0, 20)) : $user['name'];
    $user['status'] = isset($_POST['status']) ? htmlspecialchars(mb_substr(trim($_POST['status']), 0, 50)) : '';
    $user['karma_off'] = isset($_POST['karma_off']) ? 1 : 0;
    $user['sex'] = isset($_POST['sex']) && $_POST['sex'] == 'm' ? 'm' : 'zh';
    $user['rights'] = isset($_POST['rights']) ? abs(intval($_POST['rights'])) : $user['rights'];

    // Проводим необходимые проверки
    if ($user['rights'] > $systemUser->rights || $user['rights'] > 9 || $user['rights'] < 0) {
        $user['rights'] = 0;
    }

    if ($systemUser->rights >= 7) {
        if (mb_strlen($user['name']) < 2 || mb_strlen($user['name']) > 40) {
            $error[] = _t('Min. nick length 2, max. 20 characters');
        }

        $lat_nick = $tools->rusLat($user['name']);

        if (preg_match("/[^\da-z\-\~\_\.]+/", $lat_nick)) {
            $error[] = _t('Nick contains invalid characters');
        }
    }
    if ($user['dayb'] || $user['monthb'] || $user['yearofbirth']) {
        if ($user['dayb'] < 1 || $user['dayb'] > 31 || $user['monthb'] < 1 || $user['monthb'] > 12) {
            $error[] = _t('Invalid format date of birth');
        }
    }

    if ($user['icq'] && ($user['icq'] < 10000 || $user['icq'] > 999999999)) {
        $error[] = _t('ICQ number must be at least 5 digits and max. 10');
    }

    if (!$error) {
        $stmt = $db->prepare('UPDATE `users` SET
          `imname` = ?,
          `status` = ?,
          `live` = ?,
          `dayb` = ?,
          `monthb` = ?,
          `yearofbirth` = ?,
          `about` = ?,
          `mibile` = ?,
          `mail` = ?,
          `mailvis` = ?,
          `icq` = ?,
          `skype` = ?,
          `jabber` = ?,
          `www` = ?
          WHERE `id` = ?
        ');

        $stmt->execute([
            $user['imname'],
            $user['status'],
            $user['live'],
            $user['dayb'],
            $user['monthb'],
            $user['yearofbirth'],
            $user['about'],
            $user['mibile'],
            $user['mail'],
            $user['mailvis'],
            $user['icq'],
            $user['skype'],
            $user['jabber'],
            $user['www'],
            $user['id'],
        ]);

        if ($systemUser->rights >= 7) {
            $stmt = $db->prepare('UPDATE `users` SET
              `name` = ?,
              `karma_off` = ?,
              `sex` = ?,
              `rights` = ?
              WHERE `id` = ?
            ');

            $stmt->execute([
                $user['name'],
                $user['karma_off'],
                $user['sex'],
                $user['rights'],
                $user['id'],
            ]);
        }

        echo '<div class="gmenu">' . _t('Data saved') . '</div>';
    } else {
        echo $tools->displayError($error);
    }
}

echo '<div class="mrt-code card shadow--2dp"><div class="card__actions">';
// Форма редактирования анкеты пользователя
echo '<form action="?act=edit&amp;user=' . $user['id'] . '" method="post">' .
    _t('Username') . ': <b>' . $user['name_lat'] . '</b><br>';

if ($systemUser->rights >= 7) {
    echo '<div class="form-group"><input type="text" value="' . $user['name'] . '" name="name" required="required" />' .
    '<label class="control-label" for="input">' . _t('Nickname') . '</label><i class="bar"></i>' .
    '</div>';
} else {
    echo '<span class="gray">' . _t('Nickname') . ':</span> <b>' . $user['name'] . '</b>';
}
echo '<div class="form-group"><input type="text" value="' . $user['status'] . '" name="status" placeholder="Status" />' .
    '<label class="control-label" for="input">' . _t('Status') . '</label><i class="bar"></i>' .
    '</div>';

echo '</div></div>' .
    '<div class="mrt-code card shadow--2dp">' .
    '<div class="card__actions">' .
    '<div class="form-group"><input type="text" value="' . $user['imname'] . '" name="imname" placeholder="Full name" /><label class="control-label" for="input">' . _t('Your name') . '</label><i class="bar"></i></div>' .
    '' . _t('Date of birth (d.m.y)') . '<br />' .
    '<div class="form-group" style="width:38px; display:inline-block; margin-top:5px;"><input type="text" value="' . $user['dayb'] . '" size="2" maxlength="2" name="dayb" /><label class="control-label" for="input"></label><i class="bar"></i></div>/' .
    '<div class="form-group" style="width:38px; display:inline-block; margin-top:5px;"><input type="text" value="' . $user['monthb'] . '" size="2" maxlength="2" name="monthb" /><label class="control-label" for="input"></label><i class="bar"></i></div>/' .
    '<div class="form-group" style="width:58px; display:inline-block; margin-top:5px;"><input type="text" value="' . $user['yearofbirth'] . '" size="4" maxlength="4" name="yearofbirth" /><label class="control-label" for="input"></label><i class="bar"></i></div>' .
    '<div class="form-group"><input type="text" value="' . $user['live'] . '" name="live" placeholder="City" /><label class="control-label" for="input">' . _t('City, Country') . '</label><i class="bar"></i></div>' .
    '<div class="form-group"><textarea rows="' . $systemUser->getConfig()->fieldHeight . '" name="about" placeholder="About">' . strip_tags($user['about']) . '</textarea><label class="control-label" for="textarea">' . _t('About myself') . '</label><i class="bar"></i></div>' .
    '<div class="form-group"><input type="text" value="' . $user['mibile'] . '" name="mibile" placeholder="Phone" /><label class="control-label" for="input">' . _t('Phone number') . '</label><i class="bar"></i></div>' .
    '<div class="form-group" style="margin-bottom:10px;"><input type="text" value="' . $user['mail'] . '" name="mail" placeholder="Email" /><label class="control-label" for="input">E-mail</label><i class="bar"></i></div>' .
    '<div class="checkbox" style="margin-top:5px;">' .
    '<label><input name="mailvis" type="checkbox" value="1" ' . ($user['mailvis'] ? 'checked="checked"' : '') . ' /><i class="helper"></i> ' . _t('Show in Profile') .
    '</label>' .
    '</div>' .
    '<br /><small class="gray">' . _t('Warning! Write your e-mail correctly. Your password will be sent to the email address on record for this account.') . '</small>';
    /**
    echo '<div class="form-group"><input type="text" value="' . $user['icq'] . '" name="icq" size="10" maxlength="10" /><label class="control-label" for="input">ICQ</label><i class="bar"></i></div>' .
    '<div class="form-group"><input type="text" value="' . $user['skype'] . '" name="skype" /><label class="control-label" for="input">Skype</label><i class="bar"></i></div>' .
    '<div class="form-group"><input type="text" value="' . $user['jabber'] . '" name="jabber" /><label class="control-label" for="input">Jabber</label><i class="bar"></i></div>';
    */
    echo '<div class="form-group"><input type="text" value="' . $user['www'] . '" name="www" placeholder="Website" /><label class="control-label" for="input">' . _t('Site') . '</label><i class="bar"></i></div>';

// Административные функции
if ($systemUser->rights >= 7) {
    echo '<h3>' . _t('Administrative Functions') . '</h3><ul>';
    echo '<li><input name="karma_off" type="checkbox" value="1" ' . ($user['karma_off'] ? 'checked="checked"' : '') . ' />&#160;' . _t('Prohibit Karma') . '</li>';
    echo '<li><a href="?act=password&amp;user=' . $user['id'] . '">' . _t('Change Password') . '</a></li>';

    if ($systemUser->rights > $user['rights']) {
        echo '<li><a href="?act=reset&amp;user=' . $user['id'] . '">' . _t('Reset User options to default') . '</a></li>';
    }

    echo '<li>' . _t('Select gender') . ':<br>' .
        '<input type="radio" value="m" name="sex" ' . ($user['sex'] == 'm' ? 'checked="checked"' : '') . '/>&#160;' . _t('Man') . '<br>' .
        '<input type="radio" value="zh" name="sex" ' . ($user['sex'] == 'zh' ? 'checked="checked"' : '') . '/>&#160;' . _t('Woman') . '</li>' .
        '</ul>';

    if ($user['id'] != $systemUser->id) {
        echo '<h3>' . _t('Position on the Site') . '</h3><ul>' .
            '<div class="form-radio radio"><label><input name="rights" type="radio" value="0" ' . (!$user['rights'] ? 'checked="checked"' : '') . '/><i class="helper"></i> &#160;' . _t('User') . '</label></div>

            <div class="form-radio radio"><label><input name="rights" type="radio" value="3" ' . ($user['rights'] == 3 ? 'checked="checked"' : '') . '/><i class="helper"></i> &#160;' . _t('Forum Moderator') . '</label></div>

            <div class="form-radio radio"><label><input name="rights" type="radio" value="4" ' . ($user['rights'] == 4 ? 'checked="checked"' : '') . '/><i class="helper"></i> &#160;' . _t('Download Moderator') . '</label></div>

            <div class="form-radio radio"><label><input name="rights" type="radio" value="5" ' . ($user['rights'] == 5 ? 'checked="checked"' : '') . '/><i class="helper"></i> &#160;' . _t('Library Moderator') . '</label></div>

            <div class="form-radio radio"><label><input name="rights" type="radio" value="6" ' . ($user['rights'] == 6 ? 'checked="checked"' : '') . '/><i class="helper"></i> &#160;' . _t('Super Modererator') . '</label></div>';

        if ($systemUser->rights == 9) {
            echo '<div class="form-radio radio"><label><input name="rights" type="radio" value="7" ' . ($user['rights'] == 7 ? 'checked="checked"' : '') . '/><i class="helper"></i> &#160;' . _t('Administrator') . '</label></div>
            <div class="form-radio radio"><label><input name="rights" type="radio" value="9" ' . ($user['rights'] == 9 ? 'checked="checked"' : '') . '/><i class="helper"></i> &#160;' . _t('Supervisor') . '</label></div>';
        }
        echo '</ul>';
    }
}

echo '<div class="button-container"><button class="button" type="submit" name="submit"><span>' . _t('Save') . '</span></button></div></form>' .
    '</div></div>';

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('Settings') . '</h4></div>' .
    '<div class="list1">' . $tools->image('lock.png') . '<a href="?act=password">' . _t('Change Password') . '</a></div>' .
    '<div class="list1">' . $tools->image('settings.png') . '<a href="?act=settings">' . _t('System Settings') . '</a></div>';
if ($systemUser->rights >= 1) {
    echo '<div class="list1">' . $tools->image('forbidden.png') . '<span class="red"><a href="../admin/"><b>' . _t('Admin Panel') . '</b></a></span></div>';
}
echo '</div>';
