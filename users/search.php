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

$headmod = 'usersearch';
require('../system/bootstrap.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

$textl = _t('User Search');
require('../system/head.php');

// Принимаем данные, выводим форму поиска
$search_post = isset($_POST['search']) ? trim($_POST['search']) : false;
$search_get = isset($_GET['search']) ? rawurldecode(trim($_GET['search'])) : '';
$search = $search_post ? $search_post : $search_get;
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php"><strong>' . _t('Community') . '</strong></a>&#160;|&#160;' . _t('User Search') . '</div>' .
    '<form action="search.php" method="post">' .
    '<div class="list1"><div class="form-group">' .
    '<input type="text" name="search" value="' . $tools->checkout($search) . '" required="required" />' .
    '<label class="control-label" for="input">' . _t('Look for the User') . '</label><i class="bar"></i>' .
    '</div>' .
    '<div class="button-container"><button  type="submit" name="submit" class="button"><span>' . _t('Search') . '</span></button></div>' .
    '</div></form>';
echo '<div class="list1"><small>' . _t('Search by Nickname are case insensitive. For example <strong>UsEr</strong> and <strong>user</strong> are identical.') . '</small></div></div>';

// Проверям на ошибки
$error = [];

if (!empty($search) && (mb_strlen($search) < 2 || mb_strlen($search) > 20)) {
    $error[] = _t('Nickname') . ': ' . _t('Invalid length');
}

if (preg_match("/[^1-9a-z\-\@\*\(\)\?\!\~\_\=\[\]]+/", $tools->rusLat($search))) {
    $error[] = _t('Nickname') . ': ' . _t('Invalid characters');
}

if ($search && !$error) {
    /** @var PDO $db */
    $db = $container->get(PDO::class);

    // Выводим результаты поиска
    $search_db = $tools->rusLat($search);
    $search_db = strtr($search_db, [
        '_' => '\\_',
        '%' => '\\%',
    ]);
    $search_db = '%' . $search_db . '%';
    $total = $db->query("SELECT COUNT(*) FROM `users` WHERE `name_lat` LIKE " . $db->quote($search_db))->fetchColumn();
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><strong>' . _t('Searching results') . '</strong></div>';

    if ($total > $kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('search.php?search=' . urlencode($search) . '&amp;', $start, $total, $kmess) . '</div>';
    }

    if ($total) {
        $req = $db->query("SELECT * FROM `users` WHERE `name_lat` LIKE " . $db->quote($search_db) . " ORDER BY `name` ASC LIMIT $start, $kmess");
        $i = 0;
        while ($res = $req->fetch()) {
            echo '<div class="list1">';
            $res['name'] = mb_strlen($search) < 2 ? $res['name'] : preg_replace('|(' . preg_quote($search, '/') . ')|siu', '$1', $res['name']);
            echo $tools->displayUser($res);
            echo '</div>';
            ++$i;
        }
    } else {
        echo '<div class="rmenu">' . _t('Your search did not match any results') . '</div>';
    }
    echo '</div>';
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    if ($total > $kmess) {
        echo '<div class="topmenu">' . $tools->displayPagination('search.php?search=' . urlencode($search) . '&amp;', $start, $total, $kmess) . '</div>';
    }
    echo '</div>';
} else {
    if ($error) {
        echo $tools->displayError($error);
    }
}

echo '<div class="mrt-code card shadow--2dp">' . ($search && !$error ? '<div class="card__actions"><a href="search.php">' . _t('New search') . '</a></div>' : '') .
    '<div class="list1"><a href="index.php">' . _t('Back') . '</a></div></div>';

require('../system/end.php');
