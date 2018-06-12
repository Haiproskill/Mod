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

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

$textl = _t('Registration');
$headmod = 'registration';
require('../system/head.php');

// Если регистрация закрыта, выводим предупреждение
if (!$config->mod_reg || $systemUser->isValid()) {
    echo '<p>' . _t('Registration is temporarily closed') . '</p>';
    require('../system/end.php');
    exit;
}

$captcha = isset($_POST['captcha']) ? trim($_POST['captcha']) : null;
$reg_nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
$lat_nick = $tools->rusLat($reg_nick);
$reg_pass = isset($_POST['password']) ? trim($_POST['password']) : '';
$config_pass = isset($_POST['password_config']) ? trim($_POST['password_config']) : '';
$reg_name = isset($_POST['imname']) ? trim($_POST['imname']) : '';
$reg_about = isset($_POST['about']) ? trim($_POST['about']) : '';
$reg_sex = isset($_POST['sex']) ? trim($_POST['sex']) : false;

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('Registration') . '</h4></div>';

if (isset($_POST['submit'])) {
    /** @var PDO $db */
    $db = $container->get(PDO::class);

    // Принимаем переменные
    $error = [];

    // Проверка Логина
    if (empty($reg_nick)) {
        $error[] = _t('You have not entered Nickname');
    } elseif (mb_strlen($reg_nick) < 2 || mb_strlen($reg_nick) > 20) {
        $error[] = _t('Nickname wrong length');
    }

    if (preg_match('/[^\da-z\-\~\_\.]+/', $lat_nick)) {
        $error[] = _t('Invalid characters');
    }

    // Проверка пароля
    if (empty($reg_pass)) {
        $error[] = _t('You have not entered password');
    } elseif (mb_strlen($reg_pass) < 3) {
        $error[] = _t('Invalid length');
    }
	if ($reg_pass != $config_pass) {
        $error[] = _t('Mật khẩu không khớp.');
    }

    // Проверка пола
    if ($reg_sex != 'm' && $reg_sex != 'zh') {
        $error[] = _t('You have not selected genger');
    }

    // Проверка кода CAPTCHA
    if (!$captcha
        || !isset($_SESSION['code'])
        || mb_strlen($captcha) < 4
        || $captcha != $_SESSION['code']
    ) {
        $error[] = _t('The security code is not correct');
    }

    unset($_SESSION['code']);

    // Проверка переменных
    if (empty($error)) {
        $pass = md5(md5($reg_pass));
        $reg_name = htmlspecialchars(mb_substr($reg_name, 0, 50));
        $reg_about = htmlspecialchars(mb_substr($reg_about, 0, 1000));
        // Проверка, занят ли ник
        $stmt = $db->prepare('SELECT * FROM `users` WHERE `name_lat` = ?');
        $stmt->execute([$lat_nick]);

        if ($stmt->rowCount()) {
            $error[] = _t('Selected Nickname is already in use');
        }
    }

    if (empty($error)) {
        /** @var Johncms\Api\EnvironmentInterface $env */
        $env = $container->get(Johncms\Api\EnvironmentInterface::class);

        $preg = $config->mod_reg > 1 ? 1 : 0;
        $db->prepare('
          INSERT INTO `users` SET
          `name` = ?,
          `name_lat` = ?,
          `password` = ?,
          `imname` = ?,
          `about` = ?,
          `status` = \'Soi Cầu Lô Đề\',
          `sex` = ?,
          `rights` = 0,
          `ip` = ?,
          `ip_via_proxy` = ?,
          `browser` = ?,
          `datereg` = ?,
          `lastdate` = ?,
          `sestime` = ?,
          `preg` = ?,
          `set_user` = \'\',
          `set_forum` = \'\',
          `set_mail` = \'\',
          `smileys` = \'\'
        ')->execute([
            $reg_nick,
            $lat_nick,
            $pass,
            $reg_name,
            $reg_about,
            $reg_sex,
            $env->getIp(),
            $env->getIpViaProxy(),
            $env->getUserAgent(),
            time(),
            time(),
            time(),
            $preg,
        ]);

        $usid = $db->lastInsertId();

        echo '<div class="gmenu text-center"><h3>Đăng ký thành công.</h3></div>' .
          '<div class="list1 text-center">' .
            'Xin chào <b>' . $reg_nick . '</b>, giờ đây bạn đã là thành viên của StyleVietNam...<br />';

        if ($config->mod_reg == 1) {
            echo '<p><span class="red"><b>' . _t('Please, wait until a moderator approves your registration') . '</b></span></p>';
        } else {
            $_SESSION['uid'] = $usid;
            $_SESSION['ups'] = md5(md5($reg_pass));
            echo '<p><a href="' . $config->homeurl . '">' . _t('Enter') . '</a></p>';
        }

        echo '</div></div>';
        require('../system/end.php');
        exit;
    }
}

// Форма регистрации
if ($config->mod_reg == 1) {
    echo '<div class="rmenu"><p>' . _t('You can get authorized on the site after confirmation of your registration.') . '</p></div>';
}
if ($error) {
    echo $tools->displayError($error);
}
echo '<form action="index.php" method="post"><div class="list1">' .
    '<div class="form-group">
      <input type="text" name="nick" maxlength="15" value="' . htmlspecialchars($reg_nick) . '" required="required"/>
      <label class="control-label" for="input">' . _t('Choose Nickname') . '</label><i class="bar"></i>
    </div>' .
    '<div class="form-group">
      <input type="password" name="password" maxlength="20" value="' . htmlspecialchars($reg_pass) . '" required="required"/>
      <label class="control-label" for="input">' . _t('Assign a password') . '</label><i class="bar"></i>
    </div>' .
	'<div class="form-group">
      <input type="password" name="password_config" maxlength="20" value="' . htmlspecialchars($config_pass) . '" required="required"/>
      <label class="control-label" for="input">' . _t('Config a password') . '</label><i class="bar"></i>
    </div>' .
    '<div class="form-group">
      <select name="sex">' .
    '<option value="0"' . (!$reg_sex ? ' selected="selected"' : '') . '>' . _t('Select Gender') . '</option>' .
    '<option value="m"' . ($reg_sex == 'm' ? ' selected="selected"' : '') . '>' . _t('Man') . '</option>' .
    '<option value="zh"' . ($reg_sex == 'zh' ? ' selected="selected"' : '') . '>' . _t('Woman') . '</option>' .
      '</select></div>' .
    '<table cellpadding="0" cellspacing="0"><tr><td style="padding-right: 5px;"><img src="../captcha.php?r=' . rand(1000, 9999) . '" alt="' . _t('Verification code') . '" border="1" /></td><td>' .
    '<div class="form-group"><input type="text" maxlength="5"  name="captcha" required="required" /><label class="control-label" for="input">' . _t('Verification code') . '</label><i class="bar"></i>
    </div>' .
    '</td></tr></table>' .
    '<div class="button-container">
    <button  type="submit" name="submit" class="button"><span>' . _t('Registration') . '</span></button>
  </div></div></form>' .
    '<div class="list1"><small>' . _t('Please, do not register names like 111, shhhh, uuuu, etc. They will be deleted. <br /> Also all the profiles registered via proxy servers will be deleted') . '</small></div></div>';

require('../system/end.php');
