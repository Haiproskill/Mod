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

/** @var Johncms\Api\EnvironmentInterface $env */
$env = $container->get(Johncms\Api\EnvironmentInterface::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

$act          = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';
$headmod      = isset($headmod) ? $headmod : '';
$textl        = isset($textl) ? $textl : $config['copyright'];
$keywords     = isset($keywords) ? htmlspecialchars($keywords) : $config->meta_key;
$descriptions = isset($descriptions) ? htmlspecialchars($descriptions) : $config->meta_desc;
$canonical    = isset($canonical) ? $canonical : false;

echo '<!DOCTYPE html>' .
	"\n" . '<html lang="' . $config->lng . '">' .
	"\n" . '<head>' .
	"\n" . '<meta charset="utf-8">' .
	"\n" . '<meta http-equiv="X-UA-Compatible" content="IE=edge">' .
	"\n" . '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes">' .
	"\n" . '<meta name="theme-color" content="#212121" />' .
	"\n" . '<meta name="HandheldFriendly" content="true">' .
	"\n" . '<meta name="MobileOptimized" content="width">' .
	"\n" . '<meta content="yes" name="apple-mobile-web-app-capable">' .
	"\n" . '<meta name="google-site-verification" content="zyJertGRSBUz9-mws6_jZj2bZxEbe84Ones-qeyC0ic" />' .
	"\n" . '<meta name="Generator" content="SoiCauLoDe, https://soicaulode.club">' .
	"\n" . '<meta name="keywords" content="' . $keywords . '">' .
	"\n" . '<meta name="description" content="' . $descriptions . '">' .
	"\n" . '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>' .
	"\n" . '<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>' .
	"\n" . '<script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.2/jquery.ui.touch-punch.min.js"></script>' .
	"\n" . '<script type="text/javascript">var isLogin = ' . ($systemUser->id ? 'true' : 'false') . ', isMobile = ' . ($detect->isMobile() ? 'true' : 'false') . ';</script>' .
	"\n" . '<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">' .
	"\n" . '<link rel="stylesheet" href="' . $config->homeurl . '/theme/' . $tools->getSkin() . '/style.css">' .
	"\n" . '<link rel="stylesheet" href="' . $config->homeurl . '/theme/' . $tools->getSkin() . '/more.css">' .
	"\n" . '<link rel="stylesheet prefetch" href="//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.min.css">' .
	"\n" . '<link rel="stylesheet" href="' . $config->homeurl . '/assets/css/main.css">' .
	"\n" . '<link rel="shortcut icon" href="' . $config->homeurl . '/favicon.ico">' .
	/*
	"\n" . '<link rel="apple-touch-icon" sizes="57x57" href="/assets/images/favicon/favicon-57x57.png" />' .
	"\n" . '<link rel="apple-touch-icon" sizes="114x114" href="/assets/images/favicon/favicon-144x144.png" />' .
	"\n" . '<link rel="apple-touch-icon" sizes="72x72" href="/assets/images/favicon/favicon-72x72.png" />' .
	"\n" . '<link rel="apple-touch-icon" sizes="144x144" href="/assets/images/favicon/favicon-144x144.png" />' .
	"\n" . '<link rel="apple-touch-icon" sizes="60x60" href="/assets/images/favicon/favicon-60x60.png" />' .
	"\n" . '<link rel="apple-touch-icon" sizes="120x120" href="/assets/images/favicon/favicon-120x120.png" />' .
	"\n" . '<link rel="apple-touch-icon" sizes="76x76" href="/assets/images/favicon/favicon-76x76.png" />' .
	"\n" . '<link rel="apple-touch-icon" sizes="152x152" href="/assets/images/favicon/favicon-152x152.png" />' .
	"\n" . '<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/assets/images/favicon/favicon-72x72.png" />' .
	"\n" . '<link rel="apple-touch-icon-precomposed" sizes="76x76" href="/assets/images/favicon/favicon-76x76.png" />' .
	"\n" . '<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/assets/images/favicon/favicon-114x114.png" />' .
	"\n" . '<link rel="apple-touch-icon-precomposed" sizes="120x120" href="/assets/images/favicon/favicon-120x120.png" />' .
	"\n" . '<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/assets/images/favicon/favicon-144x144.png" />' .
	"\n" . '<link rel="apple-touch-icon-precomposed" sizes="152x152" href="/assets/images/favicon/favicon-152x152.png" />' .
	"\n" . '<link rel="apple-touch-icon-precomposed" sizes="180x180" href="/assets/images/favicon/favicon-180x180.png" />' .
	*/

	"\n" . '<link rel="icon" type="image/png" href="/assets/images/favicon/favicon-196x196.png" sizes="196x196" />' .
	"\n" . '<link rel="icon" type="image/png" href="/assets/images/favicon/favicon-192x192.png" sizes="192x192" />' .
	"\n" . '<link rel="icon" type="image/png" href="/assets/images/favicon/favicon-128x128.png" sizes="128x128" />' .
	"\n" . '<link rel="icon" type="image/png" href="/assets/images/favicon/favicon-96x96.png" sizes="96x96" />' .
	"\n" . '<link rel="icon" type="image/png" href="/assets/images/favicon/favicon-48x48.png" sizes="48x48" />' .
	"\n" . '<link rel="icon" type="image/png" href="/assets/images/favicon/favicon-32x32.png" sizes="32x32" />' .
	"\n" . '<link rel="icon" type="image/png" href="/assets/images/favicon/favicon-16x16.png" sizes="16x16" />' .

	/**
	"\n" . '<meta name="apple-mobile-web-app-title" content="Quantrimang.com">' .
	"\n" . '<meta name="application-name" content="SoiCauLoDe"/>' .

	"\n" . '<meta property="og:image" content="https://i.quantrimang.com/photos/image/2018/05/03/matlab-640.jpg">' .
	"\n" . '<meta property="og:type" content="article">' .
	"\n" . '<meta property="og:title" content="' . $textl . '">' .

	"\n" . '<meta name="msapplication-TileColor" content="#FFFFFF" />' .
	"\n" . '<meta name="msapplication-TileImage" content="/assets/images/favicon/favicon-144x144.png" />' .
	"\n" . '<meta name="msapplication-square70x70logo" content="/assets/images/favicon/favicon-70x70.png" />' .
	"\n" . '<meta name="msapplication-square150x150logo" content="/assets/images/favicon/favicon-150x150.png" />' .
	"\n" . '<meta name="msapplication-wide310x150logo" content="/assets/images/favicon/favicon-310x150.png" />' .
	"\n" . '<meta name="msapplication-square310x310logo" content="/assets/images/favicon/favicon-310x310.png" />' .
	*/

	"\n" . '<link rel="alternate" type="application/rss+xml" title="RSS | ' . _t('Site News', 'system') . '" href="' . $config->homeurl . '/rss/rss.php">' .
	"\n" . ($canonical ? '<link rel="canonical" href="' . $canonical . '">' : '') .
	"\n" . '<title>' . $textl . '</title>' .
	"\n" . '</head><body>';
	
echo "\n" . '<header class="navbar cd-auto-hide-header">
	<div class="logo"><a class="tload" href="' . $config['homeurl'] . '"><i class="material-icons">home</i>&#160;SoiCauLoDe.Club</a></div>
	<form action="' . $config['homeurl'] . '/forum/search.php" method="post">
		<div class="search-wrapper">
			<div class="input-holder">
				<input type="text" name="search" class="search-input" placeholder="Tìm kiếm..." required="required" />
				<button class="search-icon" onclick="searchToggle(this, event);"><span></span></button>
			</div>
			<span class="close" onclick="searchToggle(this, event);"></span>
		</div>
	</form>
	<nav class="cd-primary-nav">
		<span class="nav-trigger">
			<span>
				<em aria-hidden="true"></em>
			</span>
		</span>
		<ul id="cd-navigation">
			' . (!$systemUser->id ? '
			<li><a class="tload" href="' . $config['homeurl'] . '/forum/index.html">Diễn đàn</a></li>
			<li><a class="tload" href="' . $config['homeurl'] . '/login.php">Đăng nhập</a></li>
			<li><a class="tload" href="' . $config['homeurl'] . '/registration/index.php">Đăng ký</a></li>
			' : '
			<li><a class="tload" href="' . $config['homeurl'] . '/forum/index.html">Diễn đàn</a></li>
			<li><a class="tload" href="' . $config['homeurl'] . '/profile">' . _t('Personal', 'system') . '</a></li>
			<li><a class="tload" href="' . $config['homeurl'] . '/tool/ipay/">IPay</a></li>
			<li><a class="tload" href="' . $config['homeurl'] . '/exit.php">' . _t('Logout', 'system') . '</a></li>
			') . '
		</ul>
	</nav>
	</header>';
	
?>
<div class="page-loading-bar"></div>
<div id="container" class="maintxt">
	<div id="containerPage" class="page1">
<!-- begin -->
<?php

// Рекламный модуль
$cms_ads = [];

if (!isset($_GET['err']) && $act != '404' && $headmod != 'admin') {
	$view = $systemUser->id ? 2 : 1;
	$layout = ($headmod == 'mainpage' && !$act) ? 1 : 2;
	$req = $db->query("SELECT * FROM `cms_ads` WHERE `to` = '0' AND (`layout` = '$layout' or `layout` = '0') AND (`view` = '$view' or `view` = '0') ORDER BY  `mesto` ASC");

	if ($req->rowCount()) {
		while ($res = $req->fetch()) {
			$name = explode("|", $res['name']);
			$name = htmlentities($name[mt_rand(0, (count($name) - 1))], ENT_QUOTES, 'UTF-8');

			if (!empty($res['color'])) {
				$name = '<span style="color:#' . $res['color'] . '">' . $name . '</span>';
			}

			// Если было задано начертание шрифта, то применяем
			$font = $res['bold'] ? 'font-weight: bold;' : false;
			$font .= $res['italic'] ? ' font-style:italic;' : false;
			$font .= $res['underline'] ? ' text-decoration:underline;' : false;

			if ($font) {
				$name = '<span style="' . $font . '">' . $name . '</span>';
			}

			@$cms_ads[$res['type']] .= '<a href="' . ($res['show'] ? $tools->checkout($res['link']) : $config['homeurl'] . '/go.php?id=' . $res['id']) . '">' . $name . '</a><br>';

			if (($res['day'] != 0 && time() >= ($res['time'] + $res['day'] * 3600 * 24))
				|| ($res['count_link'] != 0 && $res['count'] >= $res['count_link'])
			) {
				$db->exec('UPDATE `cms_ads` SET `to` = 1  WHERE `id` = ' . $res['id']);
			}
		}
	}
}

// Рекламный блок сайта
if (isset($cms_ads[0])) {
	echo $cms_ads[0];
}



// Рекламный блок сайта
if (!empty($cms_ads[1])) {
	echo '<div class="gmenu">' . $cms_ads[1] . '</div>';
}

// Фиксация местоположений посетителей
$sql = '';
$set_karma = $config['karma'];

if ($systemUser->isValid()) {
	// Фиксируем местоположение авторизованных
	if (!$systemUser->karma_off && $set_karma['on'] && $systemUser->karma_time <= (time() - 86400)) {
		$sql .= " `karma_time` = " . time() . ", ";
	}

	$movings = $systemUser->movings;

	if ($systemUser->lastdate < (time() - 60)) {
		$movings = 0;
		$sql .= " `sestime` = " . time() . ", ";
	}

	if ($systemUser->place != $headmod) {
		++$movings;
		$sql .= " `place` = " . $db->quote($headmod) . ", ";
	}

	if ($systemUser->browser != $env->getUserAgent()) {
		$sql .= " `browser` = " . $db->quote($env->getUserAgent()) . ", ";
	}

	$totalonsite = $systemUser->total_on_site;

	if ($systemUser->lastdate > (time() - 60)) {
		$totalonsite = $totalonsite + time() - $systemUser->lastdate;
	}

	$db->query("UPDATE `users` SET $sql
		`movings` = '$movings',
		`total_on_site` = '$totalonsite',
		`lastdate` = '" . time() . "'
		WHERE `id` = " . $systemUser->id);
} else {
	// Фиксируем местоположение гостей
	$movings = 0;
	$session = md5($env->getIp() . $env->getIpViaProxy() . $env->getUserAgent());
	$req = $db->query("SELECT * FROM `cms_sessions` WHERE `session_id` = " . $db->quote($session) . " LIMIT 1");

	if ($req->rowCount()) {
		// Если есть в базе, то обновляем данные
		$res = $req->fetch();
		$movings = ++$res['movings'];

		if ($res['sestime'] < (time() - 60)) {
			$movings = 1;
			$sql .= " `sestime` = '" . time() . "', ";
		}

		if ($res['place'] != $headmod) {
			$sql .= " `place` = " . $db->quote($headmod) . ", ";
		}

		$db->exec("UPDATE `cms_sessions` SET $sql
			`movings` = '$movings',
			`lastdate` = '" . time() . "'
			WHERE `session_id` = " . $db->quote($session) . "
		");
	} else {
		// Если еще небыло в базе, то добавляем запись
		$db->exec("INSERT INTO `cms_sessions` SET
			`session_id` = '" . $session . "',
			`ip` = '" . $env->getIp() . "',
			`ip_via_proxy` = '" . $env->getIpViaProxy() . "',
			`browser` = " . $db->quote($env->getUserAgent()) . ",
			`lastdate` = '" . time() . "',
			`sestime` = '" . time() . "',
			`place` = " . $db->quote($headmod) . "
		");
	}
}

// Выводим сообщение о Бане
if (!empty($systemUser->ban)) {
	echo '<div class="card__actions text-center"><strong>' . _t('Ban', 'system') . '</strong>: &#160;<a href="' . $config['homeurl'] . '/profile/?act=ban">' . _t('Details', 'system') . '</a></div><br />';
}

// Ссылки на непрочитанное
if ($systemUser->id) {
	$list = [];

	if ($systemUser->comm_count > $systemUser->comm_old) {
		$list[] = '<a href="' . $config['homeurl'] . '/profile/?act=guestbook&amp;user=' . $systemUser->id . '">' . _t('Guestbook', 'system') . '</a> (' . ($systemUser->comm_count - $systemUser->comm_old) . ')';
	}
	if (!empty($list)) {
		echo '<div class="card__actions rmenu text-center">' . _t('Unread', 'system') . ': ' . implode(', ', $list) . '</div>';
	}
}

if ($systemUser->isValid()
	&& $config->mod_moderation
	&& ($systemUser->rights >= 6 || $systemUser->rights == 3)
) {
	$count_post = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' AND `moderation` = '0'")->fetchColumn();
	if($count_post){
		echo "\n" . '<div class="rmenu text-center">Có ' . $count_post . ' bài viết đang chờ <a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=moderation">phê duyệt</a>.</div><br />';
	}
} else if ($systemUser->isValid() && $config->mod_moderation){
	$count_post = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' AND `moderation` = '0' AND `user_id` = '" . $systemUser->id . "' ")->fetchColumn();
	if($count_post){
		echo "\n" . '<div class="rmenu text-center">Bài viết của bạn đang chờ được <a class="tload" href="' . $config['homeurl'] . '/forum/index.php?act=moderation">phê duyệt</a>.</div><br />';
	}
}
