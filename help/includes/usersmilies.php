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

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

// Каталог пользовательских Смайлов
$dir = glob(ROOT_PATH . 'images/smileys/user/*', GLOB_ONLYDIR);

foreach ($dir as $val) {
    $val = explode('/', $val);
    $cat_list[] = array_pop($val);
}
$kmess2 = 100;

$facebook = [
    'bestfb',
    'bigli-migli',
    'bo_cau',
    'koko',
    'ninja',
    'minions',
    'mugsy',
];

$google = [
    'googlesmile',
    'other',
];

$start2 = isset($_REQUEST['page']) ? $page * $kmess2 - $kmess2 : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);

$cat = isset($_GET['cat']) && in_array(trim($_GET['cat']), $cat_list) ? trim($_GET['cat']) : $cat_list[0];
$smileys = glob(ROOT_PATH . 'images/smileys/user/' . $cat . '/*.{gif,jpg,png,webp}', GLOB_BRACE);
$total = count($smileys);
$end = $start2 + $kmess2;

if ($end > $total) {
    $end = $total;
}

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4><a href="?act=smilies"><b>' . _t('Smilies') . '</b></a> | ' .
    (array_key_exists($cat, smiliesCat()) ? smiliesCat()[$cat] : ucfirst(htmlspecialchars($cat))) .
    '</h4></div>';

if ($total) {
    if ($systemUser->isValid()) {
        $user_sm = isset($systemUser->smileys) ? unserialize($systemUser->smileys) : '';

        if (!is_array($user_sm)) {
            $user_sm = [];
        }

        echo '<div class="gmenu">' .
            '<a href="?act=my_smilies">' . _t('My smilies') . '</a>  (' . count($user_sm) . ' / ' . $user_smileys . ')</div>' .
            '<form action="?act=set_my_sm&amp;cat=' . $cat . '&amp;start=' . $start2 . '" method="post">';
    }

    if ($total > $kmess2) {
        echo '<div class="topmenu">' . $tools->displayPagination('?act=usersmilies&amp;cat=' . urlencode($cat) . '&amp;', $start2, $total, $kmess2) . '</div>';
    }

    for ($i = $start2; $i < $end; $i++) {
        $smiled1 = null;
        $path = 'images/smileys/user/' . $cat . '/' . basename($smileys[$i]);
        $check = $db->query("SELECT COUNT(*) FROM `smileys` WHERE `file` = '$path' ")->fetchColumn();
        echo '<div class="list1">';
        if ($check){
            $res = $db->query("SELECT * FROM `smileys` WHERE `file` = '$path' ")->fetch();
            $text = trim($res['key']);
            $setKey = explode(",", $res['key']);
            $smile = $setKey[0];
            if ($systemUser->isValid()) {
                $smiled1 = (in_array($smile, $user_sm) ? '&#160;&#160;&#160;&#160;&#160; ' : '<div class="checkbox"><label><input type="checkbox" name="add_sm[]" value="' . $smile . '" /><i class="helper"></i>');
            }
        } else
            $text = 'smilies chưa được cập nhật.';
        $smiled2 = '<img class="smilies' . (array_search($cat, $google) !== false ? ' smilies20' : '') . (array_search($cat, $facebook) !== false ? ' smilies80' : '') . '" src="/images/smileys/user/' . $cat . '/' . basename($smileys[$i]) . '" alt="" />&#160;' . $text . '';
        echo $smiled1 . '&#160;' . $smiled2 . ($check &&  $systemUser->isValid() ? (in_array($smile, $user_sm) ? '' : '</label></div>') : '') . '</div>';
    }

    if ($systemUser->isValid()) {
        echo '<div class="gmenu"><input type="submit" name="add" value=" ' . _t('Add') . ' "/></div></form>';
    }
} else {
    echo '<div class="menu"><p>' . _t('The list is empty') . '</p></div>';
}

echo '</div><div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

if ($total > $kmess2) {
    echo '<div class="topmenu">' . $tools->displayPagination('?act=usersmilies&amp;cat=' . urlencode($cat) . '&amp;', $start2, $total, $kmess2) . '</div>';
}
echo '</div>';
echo '<div class="mrt-code card shadow--2dp"><div class="card__actions"><a href="' . $_SESSION['ref'] . '">' . _t('Back') . '</a></div></div>';
