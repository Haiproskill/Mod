<?php
if ($systemUser->isValid()) {
    $timelineId = trim($_POST['uid']);
    $user = $tools->getUser($timelineId);
    if (($systemUser->id != $user['id'] && $systemUser->rights < 7)
        || $user['rights'] > $systemUser->rights
    ) {
        // Если не хватает прав, выводим ошибку
        echo $tools->displayError(_t('Bạn không có quyền.!'));
        exit;
    }

    if ($_FILES['imagefile']['size'] > 0) {
            // Проверка загрузки с обычного браузера
            $do_file = true;
            $file = $tools->rusLat($_FILES['fail']['name']);
            $fsize = $_FILES['fail']['size'];
    }


    if ($_FILES['imagefile']['size'] > 0){
        $image = $_FILES['imagefile'];
        if($image['size'] <= 0 || $image['size'] > 1024 * $config['flsz']) {
            echo $tools->displayError('Hãy kiển tra lại tập tin.!!! Kích thước không cho phép.');
            exit;
        }
        $avatar = $tools->registerMedia($image, $user['id'], 'files/users/avatar', 'avatar');
        if (isset($avatar['id']) && $avatar['id'] == '1')
        {
            $db->exec("UPDATE `users` SET
                `avatar_extension` = '" . $avatar['extension'] . "'
                WHERE `id` = '" . $user['id'] . "'
            ");
            echo '<div class="gmenu text-center">Avatar tải lên thành công....<br /><a href="/profile/?act=images&amp;user=' . $timelineId . '">Tiếp tục</a></div><div class="text-center"><img src="' . $config['homeurl'] . '/files/users/avatar/' . $avatar['url'] . '?' . $time . '" class="max-width" /></div>';
        } else {
            echo $tools->displayError(_t('File không đúng định dạng.!'));
        }
    }
}
