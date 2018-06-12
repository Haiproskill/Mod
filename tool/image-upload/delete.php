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
$check = $db->query("SELECT * FROM `cms_image` WHERE `id` = '$id'")->fetch();
if(!$check){
    echo $tools->displayError(_t('Ảnh không tồn tại.!'));
    require('../../system/end.php');
    exit;
}

$user = $tools->getUser($check['user']);

if($check['user'] != $systemUser->id && $systemUser->rights < 8 && $user['rights'] > $systemUser->rights){
    echo $tools->displayError(_t('Bạn không có quyền.!'));
    require('../../system/end.php');
    exit;
}
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Xoá ảnh</h4></div>';

if(isset($_POST['submit'])) {
    echo '<div class="rmenu text-center">Xoá thành công.!</div>';
    $db->exec("DELETE FROM `cms_image` WHERE `id`='$id'");
} else {
    echo '<div class="list1"><div class="text-center full-margin">' .
        '<img src="'.$check['url'].'" class="max-width" />' .
        '</div>';
    echo '<br />• Người tải lên: ' . $user['name'] . '<br />• Thời gian: &#160;&#160;&#160;&#160;&#160; '.$tools->thoigian($check['time']);

    echo '<form method="post"><div class="button-container"><button type="submit" name="submit" class="button"><span>Xoá</span></button></div></form>' .
        '</div>';
}
echo '</div>';

echo '<div class="mrt-code card shadow--2dp">';
echo '<div class="card__actions"><a href="'.$config['homeurl'].'/upload-hinhanh">Danh sách chung.</a></div>';
echo '<div class="list1"><a href="'.$config['homeurl'].'/upload-hinhanh/album.html">Ảnh của tôi.</a></div>';
echo '</div>';

require('../../system/end.php');
