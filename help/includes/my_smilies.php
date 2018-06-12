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

$kmess2 = 100;

$start2 = isset($_REQUEST['page']) ? $page * $kmess2 - $kmess2 : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);

// Список своих смайлов
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4><a href="?act=smilies"><b>' . _t('Smilies') . '</b></a>&#160;|&#160;' . _t('My smilies') . '</h4></div>';
$smileys = !empty($systemUser->smileys) ? unserialize($systemUser->smileys) : [];
$total = count($smileys);

if ($total) {
    echo '<form action="?act=set_my_sm&amp;start=' . $start . '" method="post">';
}

if ($total > $kmess2) {
    $smileys = array_chunk($smileys, $kmess2, true);

    if ($start2) {
        $key = ($start2 - $start2 % $kmess2) / $kmess2;
        $smileys_view = $smileys[$key];

        if (!count($smileys_view)) {
            $smileys_view = $smileys[0];
        }

        $smileys = $smileys_view;
    } else {
        $smileys = $smileys[0];
    }
}

$i = 0;

foreach ($smileys as $value) {
    $smiles = $tools->checkout($value, 0, 1, 0, 1);
    echo '<div class="list1">' .
        '<input type="checkbox" name="delete_sm[]" value="' . $value . '" />' .
        '&#160;' . $smiles . ' ' . $value . '</div>';
    $i++;
}

if ($total) {
    echo '<div class="rmenu"><input type="submit" name="delete" value=" ' . _t('Delete') . ' "/></div></form>';
} else {
    echo '<div class="gmenu"><a href="?act=smilies">' . _t('Add Smilies') . '</a></div><div class="menu">' . _t('The list is empty') . '</div>';
}
echo '</div><div class="mrt-code card shadow--2dp">';
echo '<div class="phdr">' . _t('Total') . ': ' . $total . ' / ' . $user_smileys . '</div>';

if ($total > $kmess2) {
    echo '<div class="topmenu">' . $tools->displayPagination('?act=my_smilies&amp;', $start2, $total, $kmess2) . '</div>';
}
echo '</div>';
echo '<div class="mrt-code card shadow--2dp"><div class="card__actions"><a href="' . $_SESSION['ref'] . '">' . _t('Back') . '</a></div></div>';
