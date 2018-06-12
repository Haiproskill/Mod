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

require('../../system/head.php');
if (!$systemUser->isValid()) {
    echo $tools->displayError(_t('For registered users only'));
    require('../../system/end.php');
    exit;
}

$id = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : $systemUser->id;
$user = $tools->getUser($id);
if(!$user) {
    echo $tools->displayError('Thành viên không tồn tại.');
    require('../../system/end.php');
    exit;
}
$kmess2 = 28;
$start2 = isset($_REQUEST['page']) ? $page * $kmess2 - $kmess2 : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);

$tong = $db->query("SELECT COUNT(*) AS `count` FROM `cms_image` WHERE `user` = '$id' ")->fetch();
$tong = $tong['count'];
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Bộ sưu tập của ' . ($systemUser->id == $id ? 'tôi.' : $user['name']) . '</h4></div>' .
    ($systemUser->id == $id ? '<div class="list1"><a href="'.$config['homeurl'].'/upload-hinhanh/upload.html" title="Upload ảnh miễn phí">' .
    '<button class="fileupload-example-4-label">Upload Ảnh</button></a>&#160;' .
    '<button class="fileupload-example-4-label mi">Ảnh [<b>'.$tong.'</b>]</button></div>' : '') .
    '</div>';
if($tong > 0){
    echo '<ul class="category">';
    $req = $db->query("SELECT * FROM `cms_image` WHERE `user` = '$id' ORDER BY `time` DESC LIMIT $start2,$kmess2");
    while($res = $req->fetch()){
        echo '<a href="/upload-hinhanh/'.$res['id'].'.html" class="listcat shadow--2dp" style="background: url('.$res['thumbnail'].') center / cover;"></a>';
    }
    echo '</ul>';
} else {
    echo '<center>Danh sách trống.!</center>';
}
if ($tong > $kmess2){
    echo '<br /><div class="list3 text-center">' . $tools->displayPaginationSeo($config['homeurl'] . '/upload-hinhanh/album-' . $id, $start2, $tong, $kmess2) . '</div>' ;
}
echo '<br /><div class="mrt-code card shadow--2dp">';
echo '<div class="card__actions"><a href="'.$config['homeurl'].'/upload-hinhanh">Danh sách chung.</a></div>';
echo ($systemUser->id != $id ? '<div class="list1"><a href="'.$config['homeurl'].'/upload-hinhanh/album.html">Ảnh của tôi.</a></div>' : '');
echo '</div>';
require('../../system/end.php');
