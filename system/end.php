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

defined('_IN_JOHNCMS') or die('Error: restricted access');

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);
?>

<!-- end -->
	
<?php
echo '</div>';
/**
echo '<div class="page2">';
if(!$detect->isMobile()) {
	
	
}
echo '</div>';
*/

echo "\n" . '</div>';
$ontime = time() - 10;
$requ = $db->query("SELECT `id`, `name`, `rights` FROM `users` WHERE `lastdate` > '" . $ontime . "' ORDER BY `name`");
$userOnline = $requ->rowCount();
if($userOnline) {
	echo '<br /><div id="onlineList" class="text-center">';
	$i = 1;
	while ($resu = $requ->fetch()){
		$next = null;
		if($i < $userOnline) $next = ', ';

		echo ($systemUser->isValid() && $systemUser->id != $resu['id'] ? '<a href="' . $config['homeurl'] . '/profile/?user=' . $resu['id'] . '">' : '');
		echo '<span class="' . ($resu['rights'] == 9 ? 'nickadmin' : '');
		if ($db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $resu['id'] . "' AND `ban_time` > '" . time() . "' ")->fetchColumn())
		{
			echo ' text--through';
		}
		echo '">' . $resu['name'] . '</span>';
		echo ($systemUser->isValid() && $systemUser->id != $resu['id'] ? '</a>': '');
		echo $next;
		$i++;
	}
	echo '</div><br />';
}

if ($systemUser->isValid()) {
	$lhtml = '<div class="window-list-wrapper">Build</div>';
	?>

<div class="window-container">
	<div class="window-background" onclick="closeWindow();"></div>

	<div class="window-wrapper">
		<div class="window-header-wrapper">
			<i class="fa fa-thumbs-up"></i> 
			
			Ai đã thích điều này?

			<span class="window-close-btn" title="Đóng" onclick="closeWindow();">
				<i class="material-icons">&#xE5CD;</i>
			</span>
		</div>

		<div class="window-content-wrapper">
			<?php echo $lhtml; ?>
		</div>
	</div>
</div>

<div id="miniGame" bugg="0" offsetbugg="2">
<a href="/game/taixiu/" class="miniTaixiu"></a>
<a href="/game/baucua/" class="miniBaucua"></a>
<div class="test3"></div>
<div class="test4"></div>
<div class="mask2"></div>
</div>
<?php
	// thong bao
	$count_notice = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `read`='0' AND `sys`='1' AND `delete`!='" . $systemUser->id . "'")->fetchColumn();
	// tin nhắn
	$total = $db->query('SELECT COUNT(DISTINCT `user_id`) FROM `cms_mail` WHERE `from_id` = ' . $systemUser->id . ' AND `delete` != ' . $systemUser->id . ' AND `read` = 0 AND `sys` != 1')->fetchColumn();
	$count_messenger = $total;
	if ($total) {
		$status_messenger = '1';
		$info = $db->query("SELECT DISTINCT `user_id` FROM `cms_mail` WHERE `from_id` = '" . $systemUser->id . "' AND `delete` != '" . $systemUser->id . "' AND `read` = '0' AND `sys` != '1' ORDER BY `time` DESC ")->fetch();
		$userpost = $db->query("SELECT `sex` FROM `users` WHERE `id`='$info[user_id]' LIMIT 1")->fetch();
		$avatar_name = $tools->avatar_name($info['user_id']);
		if (file_exists(('files/users/avatar/' . $avatar_name))) {
			$notice_img = '/files/users/avatar/' . $avatar_name . '';
		} else {
			$notice_img = '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
		}
		if ($total == 1) {
			$new_count_message = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `cms_mail`.`user_id`='{$info['user_id']}' AND `cms_mail`.`from_id`='" . $systemUser->id . "' AND `read`='0' AND `delete`!='" . $systemUser->id . "' AND `spam`='0' AND `sys`!='1' ")->fetchColumn();
			$count_messenger = $new_count_message;
		} else if ($total > 1) {
			$count_messenger = $total . '+';
		}
	}

	echo '<div id="pbNotice">';
	// messenger new
	echo '<a href="' . $config['homeurl'] . '/mail/index.php?act=new" class="tin-nhan-moi"' . ($count_messenger ? ' style="display: block"' : '') . '><div id="notice-img" class="m-button m-button--fab" style="background: url(' . ($count_messenger ? $notice_img : '/images/empty_m.jpg') . ');background-size: 56px 56px;background-repeat: no-repeat;"></div><span id="notice" class="m-badge" style="top:-14px;right:24px" data-badge="' . ($count_messenger ? $count_messenger : '0') . '"></span></a>';

	// notice new
	echo '<a href="' . $config['homeurl'] . '/mail/index.php?act=systems" class="thong-bao-moi"' . ($count_notice ? ' style="display: block"' : '') . '><div class="m-button m-button--fab"><i class="material-icons">&#xE7F4;</i></div><span id="so-thong-bao-moi" class="m-badge" style="top:-14px;right:58px" data-badge="' . ($count_notice ? $count_notice : '0') . '"></span></a>';

	// ipay new
	$ipay = 0;
	if ($systemUser->rights == 9)
		$ipay = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`='0'")->fetchColumn();

	echo '<a href="' . $config['homeurl'] . '/tool/ipay/?act=thanhtoan" class="notice_ipay"' . ($ipay ? ' style="display: block"' : '') . '><div class="m-button m-button--fab"><i class="material-icons">&#xE8A1;</i></div><span id="thanhtoanmoi" class="m-badge" style="top:-14px;right:58px" data-badge="' . ($ipay ? $ipay : '0') . '"></span></a>';
	echo '</div>';
}

//if($detect->isMobile()) {
	echo '<div class="text-center sub gray">© 2018 Soi Cầu Lô Đề ( 01214272713 ).</div><br />';
//}

<div class="toTop"></div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.1/jquery.form.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.js"></script>
<script type="text/javascript" src="/assets/js/phieubac.js"></script>
<script type="text/javascript" src="/assets/js/jaudio.js"></script>
<script type="text/javascript" src="/assets/js/jquery.mod.min.js"></script>

<link  href="/assets/js/jquery.fancybox.min.css" rel="stylesheet">
<script type="text/javascript" src="/assets/js/jquery.fancybox.min.js"></script>
<?php
if ($systemUser->isValid()) {
?>
<script type="text/javascript" src="/assets/js/users.js"></script>
<?php
}
?>

</body></html>
