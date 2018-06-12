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
$kmess2 = 28;

$start2 = isset($_REQUEST['page']) ? $page * $kmess2 - $kmess2 : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);

$res = $db->query("SELECT COUNT(*) AS `count` FROM `cms_image` WHERE `user`='" . $systemUser->id . "' ")->fetch();

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thư viện ảnh</h4></div>' .
    '<div class="list1"><a href="'.$config['homeurl'].'/upload-hinhanh/upload.html" title="Upload ảnh miễn phí">' .
    '<button class="fileupload-example-4-label">Upload Ảnh</button></a>&#160;' .
    '<a href="'.$config['homeurl'].'/upload-hinhanh/album.html"><button class="fileupload-example-4-label mi">Của bạn [<b>'.$res['count'].'</b>]</button></a></div>';
echo '</div>';
$total = $db->query("SELECT COUNT(*) AS `count` FROM `cms_image` ")->fetch();
$total = $total['count'];
if($total){
    echo '<ul class="category">';
    $data = $db->query("SELECT * FROM `cms_image` ORDER BY `time` DESC LIMIT $start2,$kmess2");
    while($img = $data->fetch()){
        echo '<a href="/upload-hinhanh/' . $img['id'] . '.html" class="listcat shadow--2dp" style="background: url('.$img['thumbnail'].') center / cover;"></a>';
    }
    echo '</ul>';
    if ($total > $kmess2){
        echo '<br /><div class="list3 text-center">' . $tools->displayPaginationSeo($config['homeurl'] . '/upload-hinhanh/index', $start2, $total, $kmess2) . '</div>' ;
    }
}
require('../../system/end.php');
?>