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

defined('_IN_JOHNADM') or die('Error: restricted access');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

$config = $container->get('config')['johncms'];

// Проверяем права доступа
if ($systemUser->rights < 7) {
    header('Location: /?err');
    exit;
}

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4><a href="index.php"><b>' . _t('Admin Panel') . '</b></a>&#160;|&#160;' . _t('Permissions') . '</h4></div>';

if (isset($_POST['submit'])) {
    $config['mod_reg'] = isset($_POST['reg']) ? intval($_POST['reg']) : 0;
    $config['mod_forum'] = isset($_POST['forum']) ? intval($_POST['forum']) : 0;
    $config['mod_moderation'] = isset($_POST['moderation']) ? intval($_POST['moderation']) : 1;
    $config['mod_guest'] = isset($_POST['guest']) ? intval($_POST['guest']) : 0;
    $config['mod_lib'] = isset($_POST['lib']) ? intval($_POST['lib']) : 0;
    $config['mod_lib_comm'] = isset($_POST['libcomm']);
    $config['mod_down'] = isset($_POST['down']) ? intval($_POST['down']) : 0;
    $config['mod_down_comm'] = isset($_POST['downcomm']);
    $config['active'] = isset($_POST['active']) ? intval($_POST['active']) : 0;
    $config['site_access'] = isset($_POST['access']) ? intval($_POST['access']) : 0;

    $configFile = "<?php\n\n" . 'return ' . var_export(['johncms' => $config], true) . ";\n";

    if (!file_put_contents(ROOT_PATH . 'system/config/system.local.php', $configFile)) {
        echo 'ERROR: Can not write system.local.php</div></div></body></html>';
        exit;
    }

    echo '<div class="rmenu text-center">' . _t('Settings are saved successfully') . '</div>';

    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
}

$color = ['red', 'yelow', 'green', 'gray'];
echo '<form method="post" action="index.php?act=access">';

// Управление доступом к Форуму
echo '<div class="list1">' .
    '<h3>' . _t('Forum') . '</h3>' .
    '<div class="form-radio">' .
    '<div class="radio"><label><input type="radio" value="2" name="forum" ' . ($config['mod_forum'] == 2 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access is allowed') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="1" name="forum" ' . ($config['mod_forum'] == 1 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Only for authorized') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="3" name="forum" ' . ($config['mod_forum'] == 3 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Read only') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="0" name="forum" ' . (!$config['mod_forum'] ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access denied') .'</label></div>' .
    '</div>';

// Mod kiểm duyệt
echo '<br /><br /><h3>Kiểm duyệt bài viết</h3>' .
    '<div class="form-radio">' .
    '<div class="radio"><label><input type="radio" value="1" name="moderation" ' . ($config['mod_moderation'] == 1 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;Có</label></div>' .
    '<div class="radio"><label><input type="radio" value="0" name="moderation" ' . (!$config['mod_moderation'] ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;Không</label></div>' .
    '</div>';

// Управление доступом к Гостевой
echo '<br /><br /><h3>' . _t('Guestbook') . '</h3>' .
    '<div class="form-radio">' .
    '<div class="radio"><label><input type="radio" value="2" name="guest" ' . ($config['mod_guest'] == 2 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access is allowed') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="1" name="guest" ' . ($config['mod_guest'] == 1 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Only for authorized') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="0" name="guest" ' . (!$config['mod_guest'] ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access denied') . '</label></div>' .
    '</div>';

// Управление доступом к Библиотеке
echo '<br /><br /><h3>' . _t('Library') . '</h3>' .
    '<div class="form-radio">' .
    '<div class="radio"><label><input type="radio" value="2" name="lib" ' . ($config['mod_lib'] == 2 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access is allowed') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="1" name="lib" ' . ($config['mod_lib'] == 1 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Only for authorized') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="0" name="lib" ' . (!$config['mod_lib'] ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access denied') . '</label></div>' .
    '</div>' .
    '<div class="checkbox"><label>' .
    '<input name="libcomm" type="checkbox" value="1" ' . ($config['mod_lib_comm'] ? 'checked="checked"' : '') . ' /><i class="helper"></i>&#160;' . _t('Comments') .
    '</label></div>';

// Управление доступом к Загрузкам
echo '<br /><br /><br /><br /><h3>' . _t('Downloads') . '</h3>' .
    '<div class="form-radio">' .
    '<div class="radio"><label><input type="radio" value="2" name="down" ' . ($config['mod_down'] == 2 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access is allowed') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="1" name="down" ' . ($config['mod_down'] == 1 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Only for authorized') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="0" name="down" ' . (!$config['mod_down'] ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access denied') . '</label></div>' .
    '</div>' .
    '<div class="checkbox"><label>' .
    '<input name="downcomm" type="checkbox" value="1" ' . ($config['mod_down_comm'] ? 'checked="checked"' : '') . ' /><i class="helper"></i>&#160;' . _t('Comments') .
    '</label></div>';

// Управление доступом к Активу сайта (списки юзеров и т.д.)
echo '<br /><br /><br /><br /><h3>' . _t('Community') . '</h3>' .
    '<div class="form-radio">' .
    '<div class="radio"><label><input type="radio" value="1" name="active" ' . ($config['active'] ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access is allowed') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="0" name="active" ' . (!$config['active'] ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Only for authorized') . '</label></div>' .
    '</div>';

// Управление доступом к Регистрации
echo '<br /><br /><h3>' . _t('Registration') . '</h3>' .
    '<div class="form-radio">' .
    '<div class="radio"><label><input type="radio" value="2" name="reg" ' . ($config['mod_reg'] == 2 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access is allowed') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="1" name="reg" ' . ($config['mod_reg'] == 1 ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('With moderation') . '</label></div>' .
    '<div class="radio"><label><input type="radio" value="0" name="reg" ' . (!$config['mod_reg'] ? 'checked="checked"' : '') . '/><i class="helper"></i>&#160;' . _t('Access denied') . '</label></div>' .
    '</div>';

echo '<div class="button-container"><button class="button" type="submit" name="submit" id="button"><span>' . _t('Save') . '</span></button></div></div>';
echo '<div class="list1"><small>' . _t('Administrators always have access to all closed modules and comments') . '</small></div>' .
    '</div>';
    
