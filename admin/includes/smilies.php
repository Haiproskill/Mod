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

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

$total = $db->query("SELECT COUNT(*) FROM `smileys` ")->fetchColumn();
$req = $db->query("SELECT * FROM `smileys` ");

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$ext = ['gif', 'jpg', 'jpeg', 'png', 'webp']; // Список разрешенных расширений

$smileys = [];

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

if(isset($_GET['update_smile'])){
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php?act=smilies"><h4><b>' . _t('Smilies') . '</b></a>&#160;|&#160;Cập nhật</h4></div>';
/**
    // Обрабатываем Админские смайлы
    foreach (glob(ROOT_PATH . 'images' . DIRECTORY_SEPARATOR . 'smileys' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . '*') as $var) {
        $file = basename($var);
        $name = explode(".", $file);
        if (in_array($name[1], $ext)) {
            $smileys['adm'][':' . $tools->trans($name[0]) . ':'] = '<img src="' . $config['homeurl'] . '/images/smileys/admin/' . $file . '" alt="" />';
            $smileys['adm'][':' . $name[0] . ':'] = '<img src="' . $config['homeurl'] . '/images/smileys/admin/' . $file . '" alt="" />';
        }
    }
*/
    $i = 0;
    while ($res = $req->fetch()) {
        $file = $res['file'];
        if (file_exists((ROOT_PATH . $file))) {
            $cat = basename(dirname(ROOT_PATH.$res['file']));
            $count_key = @explode(',', $res['key']);
            foreach ($count_key AS $key => $value) {
                $data = trim($value);
                $smileys['usr'][$data] = '[smilies=' . (array_search($cat, $google) !== false ? 'smilies20' : (array_search($cat, $facebook) !== false ? ' smilies80' : 'none')) . ']' . $file . '[/smilies]';
            }
        } else {
            $db->exec("DELETE FROM `smileys` WHERE `id` = " . $res['id']);
        }
        ++$i;
    }

    // Записываем в файл Кэша
    if (file_put_contents(ROOT_PATH . 'files/cache/smileys.dat', serialize($smileys))) {
        echo '<div class="gmenu text-center"><p>Smilies cập nhật thành công.</p></div>';
    } else {
        echo '<div class="rmenu text-center"><p>' . _t('Error updating cache') . '</p></div>';
    }
    echo '</div>';
} else if(isset($_GET['update_sql'])){
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php?act=smilies"><h4><b>' . _t('Smilies') . '</b></a>&#160;|&#160;Cập nhật database</h4></div>';

    foreach (glob(ROOT_PATH . 'images' . DIRECTORY_SEPARATOR . 'smileys' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*') as $var) {
        $file = basename($var);
        $name = explode(".", $file);
        $key = ':' . $name[0] . ':';
        if (in_array($name[1], $ext)) {
            $path = 'images/smileys/user/' . basename(dirname($var)) . '/' . $file;
            $check = $db->query("SELECT COUNT(*) FROM `smileys` WHERE `file` = '$path' ")->fetchColumn();
            if (!$check) {
                $cat = basename(dirname($var));
                $add = null;
                if(array_search($cat, $google) !== false)
                    $type = 20;
                else if (array_search($cat, $facebook) !== false)
                    $type = 80;
                else
                    $type = 0;
                $add = $db->prepare("INSERT INTO `smileys` SET
                     `file`     = ?,
                     `name` = ?,
                     `key` = ?,
                     `type` = ?
                     ");
                $add->execute([
                    $path,
                    $file,
                    $key,
                    $type,
                ]);
            }
        }
    }
    echo '<div class="gmenu text-center">Cập nhật cơ sở dữ liệu thành công.</div>';
    echo '</div>';
} else if(isset($_GET['edit'])){
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php?act=smilies"><h4><b>' . _t('Smilies') . '</b></a>&#160;|&#160; Chỉnh sửa</h4></div>';
    if (isset($_POST['submit'])) {
        $keys = isset($_POST['key']) ? trim($_POST['key']) : '';
        if (!$keys) {
            echo $tools->displayError('Bạn chưa nhập key');
        } else {
            $keys = $tools->checkout($keys);
            $db->exec("UPDATE `smileys` SET  `key`=" . $db->quote($keys) . " WHERE `id`='" . $id . "' ");
            echo '<div class="gmenu text-center">Sửa key thành công.<br />Hãy cập nhật smileys để áp dụng thay đổi.</div>';
        }
    }
    $res = $db->query("SELECT * FROM `smileys` WHERE `id` = '$id' ")->fetch();
    echo '<div class="list1"><img class="smilies' . ($res['type'] >= '20' ? ' smilies' . $res['type'] : '') . '" src="' . $config['homeurl'] . '/' . $res['file'] . '" alt="" /> ' . trim($res['key']) . '</div>' .
        '<div class="list1"><form method="post">' .
        '<div class="form-group">' .
        '<input type="text" name="key" value="' . $res['key'] . '" required="required" />' .
        '<label class="control-label" for="input">Nhập key</label><i class="bar"></i>' .
        '</div>' .
        '<div class="button-container">' .
        '<button class="button" type="submit" name="submit"><span>' . _t('Save') . '</span></button>' .
        '</div>' .
        '</form></div>' .
        '<div class="list1"><a href="index.php?act=smilies&list">Danh sách</a></div>';
    echo '</div>';
} else if(isset($_GET['list'])){
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php?act=smilies"><h4><b>' . _t('Smilies') . '</b></a>&#160;|&#160; Danh sách</h4></div>' .
        '<div class="list1 color--brown">Tổng số: ' . $total . '</div>';
    $i = 1;
    while ($res = $req->fetch()) {
        echo '<div class="card__actions card--border">' .
            '<a href="index.php?act=smilies&edit&id=' . $res['id'] . '">Edit</a>: <img class="smilies' . ($res['type'] >= '20' ? ' smilies' . $res['type'] : '') . '" src="' . $config['homeurl'] . '/' . $res['file'] . '" alt="" />';
        $count_key = @explode(',', $res['key']);
        foreach ($count_key AS $key => $value) {
            $data = trim($value);
            echo ' ' . $data;
        }
        echo '</div>';
        ++$i;
    }
    echo '</div>';
} else if(isset($_GET['reset'])){
    $db->query("TRUNCATE TABLE `smileys` ");
} else {
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a href="index.php"><h4><b>' . _t('Admin Panel') . '</b></a>&#160;|&#160;' . _t('Smilies') . '</h4></div>' .
        '<div class="list1 color--brown">Tổng số: ' . $total . '</div>' .
        '<div class="list1"><a href="index.php?act=smilies&list">Danh sách</a></div>' .
        ($total ? '<div class="list1"><a href="index.php?act=smilies&update_smile">Cập nhật smile</a></div>' : '') .
        '<div class="list1"><a href="index.php?act=smilies&update_sql">Cập nhật vào database</a></div>';
    echo '</div>';
}

