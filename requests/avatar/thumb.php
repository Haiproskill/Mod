<?php
if ($systemUser->isValid()) {
    $id = trim($_POST['id']);
    if (!$id) {
        // Если не хватает прав, выводим ошибку
        echo $tools->displayError(_t('Bạn không có quyền.!'));
        exit;
    }

    $res = $db->query("SELECT * FROM `forum` WHERE `id` = '$id' AND `type` = 't'")->fetch();
    $user = $db->query("SELECT `rights` FROM `users` WHERE `id`='" . $res['user_id'] . "' LIMIT 1")->fetch();
    if (!$res || ($res['user_id'] != $systemUser->id && $user['rights'] >= $systemUser->rights)) {
        echo $tools->displayError(_t('Wrong data'));
        exit;
    }

    if (isset($_FILES['imagefile']['tmp_name'])){
        $image = $_FILES['imagefile'];
        if($image['size'] <= 0 || $image['size'] > 1024 * $config['flsz']) {
            echo $tools->displayError('Hãy kiển tra lại tập tin.!!! Kích thước không cho phép.');
            exit;
        }
        $avatar = $tools->registerMedia($image, $id, 'files/forum/thumbnail', 'thumb');
        if (isset($avatar['id']) && $avatar['id'] == '1')
        {
            $db->exec("UPDATE `forum` SET
                `thumb_extension` = '" . $avatar['extension'] . "'
                WHERE `id` = '" . $id . "'
            ");
            echo '<div class="gmenu text-center">Thumbnail tải lên thành công....</div><div class="text-center"><img src="' . $config['homeurl'] . '/files/forum/thumbnail/' . $avatar['url'] . '?' . $time . '" class="max-width" /></div>';
        } else {
            echo $tools->displayError(_t('File không đúng định dạng.!'));
        }
    }
}
