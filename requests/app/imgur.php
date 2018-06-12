<?php
if ($systemUser->isValid()){
    if($_FILES['imagefile']['size'] <= 0){
        die('<div class="rmenu text-center">Files không tồn tại!</div>');
     }
    //uploaded file info we need to proceed
    $image = $_FILES['imagefile'];
    $image_temp = $image['tmp_name']; //file temp
    if($image['size'] <= 0 || $image['size'] > 1024 * $config['flsz']) {
        echo $tools->displayError('Hãy kiển tra lại tập tin.!!! Kích thước không cho phép.');
        exit;
    }
    $file = $image['tmp_name'];
    $handle = fopen($file, "r");
    $pvars = array(
        'image' => base64_encode(
            fread($handle, filesize($file))));
    fclose($handle);
    $client_id = "efe8365a1a23765";
    $timeout = 30;
    $time = time();
    $random = mt_rand(99999, 111111);
    $thumb = $tools->registerMedia($image, $random, 'files/tmp/imgur', 'size_thumb', 150, 150);
    if (isset($thumb['id']) && $thumb['id'] == '1')
    {
        $path = 'files/tmp/imgur/' . $random . '.' . $thumb['extension'];
        $dimg = file_get_contents($path);
        $pvars_t = array('image' => base64_encode($dimg));
        $out_t = $tools->curl_imgur($timeout, $client_id, $pvars_t);
        $pms_t = json_decode($out_t, true);
        $url_t = $pms_t['data']['link'];

        $out = $tools->curl_imgur($timeout, $client_id, $pvars);
        $pms = json_decode($out, true);
        $url = $pms['data']['link'];
        $size = substr(($pms['data']['size']/1024), 0, 4);


        if(!empty($url) || !empty($url_t)){
            $db->prepare("INSERT INTO `cms_image` SET
                `user` = ?,
                `time` = ?,
                `size` = ?,
                `url` = ?,
                `thumbnail` = ?
            ")->execute([
                $systemUser->id,
                time(),
                $size,
                $url,
                $url_t,
            ]);

            $id_new = $db->lastInsertId();

            @unlink($path);
            @unlink($filename);

            echo '<div class="list1 text-center">
            <center>
            <b><font size="4"><font color="red">Tải Ảnh Lên Thành Công!!</font></font></b>' .
                '<form><div class="form-group"><input value="[img='.$url.']" /><label class="control-label">BBCode</label><i class="bar"></i></div></form>' .
                '<form><div class="form-group"><input value="[img]'.$url.'[/img]" /><label class="control-label">BBCode</label><i class="bar"></i></div></form></center></div>';
            echo '<center><img class="max-width-500" src="'.$url.'"/></center>' .
                '<center><div style="background:#9C27B0;border:2px solid #9C27B0;margin-top: 2px;padding:4px;width:45%;text-align:center;border-radius:2px;"><a href="'.$url.'"><b><font color=#ffffff>Download ảnh ('.$size.'KB)</font></b></a></div><br />';
        }else{
            echo '<div class="rmenu text-center">' . $pms['data']['error'] . '<br />' . $pms_t['data']['error'] . '</div>';
        }
    } else {
        echo $tools->displayError(_t('File không đúng định dạng.!'));
    }
}else{
    echo '<div class="rmenu text-center">Bạn chưa đăng nhập.</div>';
}


