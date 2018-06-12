<?php

if ($systemUser->isValid()) {
    if ($_FILES['image']['size'] > 0)
    {
        $coverImage = $_FILES['image'];
        $timelineId = $_POST['timeline_id'];
        $coverData  = $tools->registerCoverImage($coverImage);

        if ($coverData) {
            $db->exec("UPDATE `users` SET `cover_extension`='" . $coverData['extension'] . "', `cover_position`=0 WHERE `id` = " . $systemUser->id);
            $data = array(
                'status' => 1,
                'cover_url' => $config['homeurl'] . '/' . $coverData['cover_url'],
                'actual_cover_url' => $config['homeurl'] . '/' . $coverData['url']. '.' . $coverData['extension']
            );
        }

        header("Content-type: application/json; charset=utf-8");
        echo json_encode($data);
        exit();
    }
}
