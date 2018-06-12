<?php
if ($systemUser->isValid()) {
    if ($_FILES['image']['size'] > 0){
        $image  = $_FILES['image'];
        $avatar = $tools->registerMedia($image, $systemUser->id, 'files/users/avatar', 'avatar');

        if ($avatar) {
            $db->exec("UPDATE `users` SET `avatar_extension`='" . $avatar['extension'] . "' WHERE `id` = " . $systemUser->id);
            $data = array(
                'status' => 1,
                'avatar_url' => $config['homeurl'] . '/files/users/avatar/' . $systemUser->id . '_100x100.' . $avatar['extension']
            );
        }
        header("Content-type: application/json; charset=utf-8");
        echo json_encode($data);
        exit();
    }
}
