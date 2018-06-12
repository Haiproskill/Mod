<?php

define('_IN_JOHNCMS', 1);
require(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'system/bootstrap.php');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

$textl = 'IPay - StyleVietNam.Net';

require(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'system/head.php');
if (!$systemUser->isValid()) {
    echo $tools->displayError(_t('For registered users only'));
    require(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'system/end.php');
    exit;
}

$id  = isset($_REQUEST['id']) ? abs(intval($_REQUEST['id'])) : 0;
$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';
$set = isset($_GET['set']) ? trim($_GET['set']) : '';

// Переключаем режимы работы
$array = [
    'admin'      => 'includes',
    'naptien'    => 'includes',
    'ruttien'    => 'includes',
    'chuyentien' => 'includes',
    'lichsu'     => 'includes',
    'set'       => 'includes',
    'thanhtoan'  => 'includes',
];

$path = !empty($array[$act]) ? $array[$act] . '/' : '';

if (isset($array[$act]) && file_exists($path . $act . '.php')) {
    require_once($path . $act . '.php');
} else {
	echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>IPay</h4></div>' .
		'<div class="card__actions"><img class="icon" src="/assets/images/mt.gif"><a href="?act=naptien">Nạp tiền</a></div>' .
		'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=ruttien"' . ($systemUser->balans < 100000 ? ' class="red"' : '') . '>Rút tiền</a></div>' .
        '<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=chuyentien"' . ($systemUser->balans < 10000 ? ' class="red"' : '') . '>Chuyển tiền</a></div>' .
		'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=lichsu">Lịch sử giao dịch</a></div>' .
		'</div>';

	echo '<div class="mrt-code card shadow--2dp">' .
		'<div class="card__actions"><img class="icon" src="/assets/images/mt.gif"><a href="?act=set">Thiết lập thanh toán</a></div>' .
		'</div>';

    if ($systemUser->id == 1) {
        echo '<div class="mrt-code card shadow--2dp"><div class="phdr forum-title"><a class="red" href="?act=admin">Quản lý IPay</a></div></div>';
    }
}
require(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'system/end.php');
