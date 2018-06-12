<?php
define('_IN_JOHNCMS', 1);
require('../../system/bootstrap.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

$db = $container->get(PDO::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

// dirname(__DIR__) . DIRECTORY_SEPARATOR
require('../../system/head.php');
if (!$systemUser->isValid()) {
    echo $tools->displayError(_t('For registered users only'));
    require('../../system/end.php');
    exit;
}
$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$check = $db->query("SELECT COUNT(*) AS `count` FROM `cms_image` WHERE `id` = '$id'")->fetch();
$check = $check['count'];
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thông tin ảnh</h4></div>';
if($check > 0){
    $res = $db->query("SELECT * FROM `cms_image` WHERE `id` = '$id'")->fetch();
    echo '<div class="list1"><div class="text-center full-margin">' .
        '<img src="'.$res['url'].'" alt="Upload ảnh miễn phí" class="max-width-500" /><br /><a href="'.$res['url'].'"><div style="display:inline-block;background:#9C27B0;border:2px solid #9C27B0;padding:4px;margin-top: 3px;width:45%;text-align:center;border-radius:2px"><b><font color=#ffffff>Download ảnh ('.$res['size'].'KB)</font></b></div></a>' .
        '</div></div>';

    $user = $tools->getUser($res['user']);
    echo '<div class="list1">• ' . $user['name'] . ' lúc: '.$tools->thoigian($res['time']) .
        (($systemUser->id == $res['user'] || $systemUser->rights > $user['rights']) ? ' • <a href="/tool/image-upload/delete.php?id='.$res['id'].'"><b>Xóa</b></a>' : '') .
        '</div><div class="list1"><form><div class="form-group"><input value="[img='.$res['url'].']" /><label class="control-label" for="input">BBCode</label><i class="bar"></i></div></form>
    <form><div class="form-group"><input value="[img]'.$res['url'].'[/img]" /><label class="control-label" for="input">BBCode</label><i class="bar"></i></div></form></div>';
} else {
    echo '<div class="rmenu text-center">File ảnh không tồn tại hoặc đã bị xóa</div>';
}
echo '</div><div class="mrt-code card shadow--2dp">';
echo '<div class="card__actions"><a href="'.$config['homeurl'].'/upload-hinhanh">Danh sách chung.</a></div>';
echo '<div class="list1"><a href="'.$config['homeurl'].'/upload-hinhanh/album.html">Ảnh của tôi.</a></div>';
echo '</div>';
require('../../system/end.php');

