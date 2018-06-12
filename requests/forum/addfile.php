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

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;

if (!$id || !$systemUser->isValid()) {
    echo $tools->displayError(_t('Wrong data'));
    exit;
}

// Список расширений файлов, разрешенных к выгрузке

// Файлы архивов
$ext_arch = [
    'zip',
    'rar'
];

// Другие типы файлов (что не перечислены выше)
$ext_other = [
    'sty',
    'mid',
    'stl'
];

// Ограничиваем доступ к Форуму
$error = '';

// Проверяем, тот ли юзер заливает файл и в нужное ли место
$res = $db->query("SELECT * FROM `forum` WHERE `id` = '$id'")->fetch();
$user = $db->query("SELECT `rights` FROM `users` WHERE `id`='" . $res['user_id'] . "' LIMIT 1")->fetch();
if ($res['type'] != 'm' || ($res['user_id'] != $systemUser->id && $user['rights'] >= $systemUser->rights)) {
    echo $tools->displayError(_t('Wrong data'));
    exit;
}
$topid = $res['refid'];
$resmrt = $db->query("SELECT * FROM `forum` WHERE `id` = '$topid'")->fetch();
// Проверяем лимит времени, отведенный для выгрузки файла
/**
if ($res['time'] < (time() - 33800)) {
    echo $tools->displayError(_t('The time allotted for the file upload has expired'), '<a href="/forum/' . $res['refid'] . '/' . $resmrt['seo'] . '_p' . $page . '.html">' . _t('Back') . '</a>');
    exit;
}
*/
// Проверяем, был ли файл уже загружен
$exist = $db->query("SELECT COUNT(*) FROM `cms_forum_files` WHERE `post` = '$id'")->fetchColumn();

if ($exist > 10) {
    echo $tools->displayError(_t('The number of files exceeds the limit', 'system'));
    exit;
}

if (isset($_POST['submit'])) {
    // Проверка, был ли выгружен файл и с какого браузера
    $do_file = false;
    $file = '';
    $balans = isset($_REQUEST['balans']) ? abs(intval($_REQUEST['balans'])) : 0;
    if ($_FILES['fail']['size'] > 0) {
        // Проверка загрузки с обычного браузера
        $do_file = true;
        $file = $tools->rusLat($_FILES['fail']['name']);
        $fsize = $_FILES['fail']['size'];
    }
    $da_upload = null;
    $error = [];
    // Обработка файла (если есть), проверка на ошибки
    if ($do_file) {
        // Список допустимых расширений файлов.
        $al_ext = array_merge($ext_arch, $ext_other);
        $ext = explode(".", $file);

        // Проверка на допустимый размер файла
        if ($fsize > 1024 * $config['flsz']) {
            $error[] = _t('File size exceed') . ' ' . $config['flsz'] . 'kb.';
        }

        // Проверка файла на наличие только одного расширения
        if (count($ext) != 2) {
            $error[] = _t('You may upload only files with a name and one extension <b>(name.ext</b>). Files without a name, extension, or with double extension are forbidden.', 'system');
        }

        // Проверка допустимых расширений файлов
        if (!in_array($ext[1], $al_ext)) {
            $error[] = _t('The forbidden file format.<br>You can upload files of the following extension', 'system') . ':<br>' . implode(', ', $al_ext);
        }

        // Обработка названия файла
        if (mb_strlen($ext[0]) == 0) {
            $ext[0] = '---';
        }

        $ext[0] = str_replace(" ", "_", $ext[0]);
        $fname = mb_substr($ext[0], 0, 32) . '.' . $ext[1];

        // Проверка на запрещенные символы
        if (preg_match("/[^\da-z_\-.]+/", $fname)) {
            $error[] = _t('File name contains invalid characters', 'system');
        }

        $flink = time() . mt_rand(1, 9999) . '_' . $fname;

        // Окончательная обработка
        if (!$error && $do_file) {
            // Для обычного браузера
            if ((move_uploaded_file($_FILES["fail"]["tmp_name"], ROOT_PATH . "files/forum/attach/$flink")) == true) {
                @chmod("$flink", 0777);
                @chmod(ROOT_PATH . "files/forum/attach/$flink", 0777);
                $da_upload = 'Tập tin: <span class="green">' . $fname . ' (' . round($fsize / 1024, 2) . 'kb)</span> đính kèm thành công';
            } else {
                $error[] = _t('Error uploading file', 'system');
            }
        }

        if (!$error) {
            // Определяем тип файла
            $ext = strtolower($ext[1]);
            if (in_array($ext, $ext_arch)) {
                $type = 6;
            } else {
                $type = 9;
            }

            // Определяем ID субкатегории и категории
            $res2 = $db->query("SELECT * FROM `forum` WHERE `id` = '" . $res['refid'] . "'")->fetch();
            $res3 = $db->query("SELECT * FROM `forum` WHERE `id` = '" . $res2['refid'] . "'")->fetch();

            // Заносим данные в базу
            $db->exec("
              INSERT INTO `cms_forum_files` SET
              `cat` = '" . $res3['refid'] . "',
              `subcat` = '" . $res2['refid'] . "',
              `topic` = '" . $res['refid'] . "',
              `post` = '$id',
              `user_id` = '" . $systemUser->id . "',
              `time` = '" . $res['time'] . "',
              `filename` = " . $db->quote($fname) . ",
              `filelink` = " . $db->quote($flink) . ",
              `balans` = " . $db->quote($balans) . ",
              `filetype` = '$type'
            ");
        }
    } else {
        $error[] = 'Chưa chọn tập tin.!!';
    }
}
    $pa2 = $db->query("SELECT `id` FROM `forum` WHERE `type` = 'm' AND `refid` = '" . $res['refid'] . "'")->rowCount();
    $page = ceil($pa2 / $kmess);
    // Форма выбора файла для выгрузки
    if($error){
        echo $tools->displayError($error);
    }
     if($da_upload){
        echo '<div class="gmenu">' . $da_upload . '</div>';
    }
