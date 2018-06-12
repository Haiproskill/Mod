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

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

/** @var Johncms\Counters $counters */
$counters = $container->get('counters');

/** @var Johncms\Api\BbcodeInterface $bbcode */
$bbcode = $container->get(Johncms\Api\BbcodeInterface::class);

$mp = new Johncms\NewsWidget();

echo $mp->news;

echo '<div class="text-center"><img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"><a href="' . $config['homeurl'] . '/forum/38/thong-ke-doc-thu-lo-vip-hang-ngay.html" class="tload"><font color="#c238dd"> ĐỘC THỦ LÔ CAO CẤP HẰNG NGÀY <img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"></font></a><br/><img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"><a href="' . $config['homeurl'] . '/forum/40/thong-ke-cau-xien-2-sieu-vip-hang-ngay.html" class="tload"><font color="#c238dd"> CẦU XIÊN 2 SIÊU VÍP HẰNG NGÀY <img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"></font></a><br /><img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"><a href="' . $config['homeurl'] . '/forum/42/thong-ke-dan-de-3-cang-cao-cap-hang-ngay.html" class="tload"><font color="#c238dd"> DÀN ĐỀ 3 CÀNG CAO CẤP <img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"></font></a><br/><img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"><a href="' . $config['homeurl'] . '/forum/44/thong-ke-dan-de-2-con-cao-cap.html" class="tload"><font color="#c238dd"> DÀN ĐỀ 2 SỐ MIỀN BẮC <img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"></font></a><br /><br/><img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"><a href="' . $config['homeurl'] . '/game/game-top.php" class="tload"><font color="#c238dd"> Đua Top cùng SoiCauLoDe.Club <img alt="" src="/assets/images/vip.gif" style="height:24px; width:24px"></font></a><br /><br /></div>';

// begin chat box
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><a class="tload" href="' . $config['homeurl'] . '/guestbook/index.php"><h4>CHÉM GIÓ SOI CẦU DỰ ĐOÁN KẾT QUẢ SỔ XỐ</h4></a></div>';
if($systemUser->isValid()){
	echo '<div class="jserror" style="display:none;"></div>';
    echo '<div class="list1"><div class="phieubac-chat">' .
        '<form id="ajaxChat" name="form" method="post">';
    echo $bbcode->buttons('form', 'msg');
    echo '<div class="form-group"><textarea id="autoztextarea" name="msg" required="required"></textarea>' .
        '<label class="control-label" for="autoztextarea">' . _t('Message') . '</label><i class="bar"></i>' .
        '</div>';
    echo '<div class="button-container"><button class="button" type="submit" name="submit"><span class="flex-center">' . _t('Send') . '</span></button></div>' .
        '<input type="hidden" name="t" value="chatbox" />' .
        '<input type="hidden" name="a" value="new" />' .
        '</form></div></div>';
}else{
    echo '<div class="gmenu text-center"><a href="' . $config['homeurl'] . '/login.php" class="tload">Đăng nhập</a> để chat cùng thành viên <span class=""><strong>Soi Cầu Lô Đề</strong></span>.!!</div>';
}

$totalchat = $db->query("SELECT COUNT(*) FROM `guest` WHERE `adm`='0'")->fetchColumn();
if ($totalchat || $systemUser->isValid()) {
echo '<div class="phieubac-boxchat">';
$req = $db->query("SELECT `guest`.*, `guest`.`id` AS `gid`, `users`.`rights`, `users`.`sex`, `users`.`id` FROM `guest` LEFT JOIN `users` ON `guest`.`user_id` = `users`.`id` WHERE `guest`.`adm`='0' ORDER BY `time` DESC LIMIT 10");
while ($gres = $req->fetch()) {
    $subtext = null;
    echo '<div id="chat'.$gres['gid'].'" class="list1" data-id="'.$gres['gid'].'"><div class="fauthor">';

    if ($gres['user_id']) {
        $post = $tools->checkout($gres['text'], 1, 1, 0, 1);
    } else {
        $gres['name'] = $tools->checkout($gres['name']);
        $post = $tools->checkout($gres['text'], 0, 2);
        $post = preg_replace('~\\[url=(https?://.+?)\\](.+?)\\[/url\\]|(https?://(www.)?[0-9a-z\.-]+\.[0-9a-z]{2,6}[0-9a-zA-Z/\?\.\~&amp;_=/%-:#]*)~', '###', $post);
        $replace = [
            '.ru'   => '***',
            '.com'  => '***',
            '.biz'  => '***',
            '.cn'   => '***',
            '.in'   => '***',
            '.net'  => '***',
            '.org'  => '***',
            '.info' => '***',
            '.mobi' => '***',
            '.wen'  => '***',
            '.kmx'  => '***',
            '.h2m'  => '***',
        ];
        $post = strtr($post, $replace);
    }

    $avatar_name = $tools->avatar_name($gres['user_id']);
    if (file_exists(('files/users/avatar/' . $avatar_name))) {
        echo '<img src="' . $config['homeurl'] . '/files/users/avatar/' . $avatar_name . '" class="thumb" alt="' . $gres['name'] . '" />';
    } else {
        echo '<img src="' . $config['homeurl'] . '/images/empty' . ($gres['sex'] ? ($gres['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="thumb" alt="' . $gres['name'] . '" />';
    }

    if (!empty($gres['otvet'])) {
        // Ответ Администрации
        $otvet = $tools->checkout($gres['otvet'], 1, 1, 0, 1);
        $post .= '<div class="reply"><strong class="nickadmin">' . $gres['admin'] . '</strong> <span class="fsize--11-5">' . $tools->displayDate($gres['otime']) . '</span><br>' . $otvet . '</div>';
    }
    echo '<ul><li>';
    if($systemUser->isValid() && $gres['user_id'] && $gres['user_id'] != $systemUser->id){
        echo '<a href="' . $config['homeurl'] . '/profile/?user=' . $gres['user_id'] . '" class="tload ' . ($gres['rights'] == 9 ? 'nickadmin' : 'nickname') . '"><strong>' . $gres['name'] . '</strong></a>';
    } else {
        echo '<strong class="' . ($gres['rights'] == 9 ? 'nickadmin' : 'nickname') . '">' . $gres['name'] . '</strong>';
    }
    echo '</li><li><span class="text--italic gray fsize--12">'.(round((time()-$gres['time'])/3600) < 1 ? '<span class="ajax-time" title="' . $tools->timestamp($gres['time']) . '">':'').$tools->thoigian($gres['time']).''.(round((time()-$gres['time'])/3600) < 1 ? '</span>':'').'</span>';
    echo '</li></ul>';
    if ($systemUser->rights >= 6) {
        $subtext = '<a class="tload" href="' . $config['homeurl'] . '/guestbook/index.php?act=otvet&amp;id=' . $gres['gid'] . '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE15E;</i></a>' .
            ($systemUser->rights >= $gres['rights'] ? ' <a class="tload" href="' . $config['homeurl'] . '/guestbook/index.php?act=edit&amp;id=' . $gres['gid'] . '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE254;</i></a> <a class="tload" href="' . $config['homeurl'] . '/guestbook/index.php?act=delpost&amp;id=' . $gres['gid'] . '"><i class="material-icons valign-bottom" style="font-size:16px;">&#xE872;</i></a>' : '');
        echo '<span class="chatmore">' .$subtext . '</span>';
    }
    echo '</div>' . $post . '</div>';
    $i++;
}
echo '</div>';
}
if($totalchat > 10){
    echo '<div class="list2"><div class="phieubacChat button-container" data="2">' .
        '<button class="button" onclick="viewChatLoad();"><span>Xem thêm</span></button>' .
        '</div></div>';
}
echo '</div>';

// end chat box
if($config->mod_moderation) {
    $moderation = "AND `moderation` = '1' ";
} else {
    $moderation = "";
}
$thaoluanmoi = '';
$total_thaoluanmoi = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type` = 't' " . $moderation . "AND `close` != '1' ")->fetchColumn();
$req = $db->query("SELECT `id`, `user_id`, `vip`, `realid`, `edit`, `thumb_extension`, `from`, `text`, `bai`, `seo` FROM `forum` WHERE `type` = 't' " . $moderation . "AND `close` != '1' ORDER BY `time` DESC LIMIT $start, $kmess");
if ($req->rowCount()) {
    for ($i = 0; $res = $req->fetch(); ++$i) {
    	$userpost = $db->query("SELECT `sex` FROM `users` WHERE `id`='$res[user_id]' LIMIT 1")->fetch();
        $nikuser = $db->query("SELECT `from`, `time` FROM `forum` WHERE `type` = 'm' AND `close` != '1' AND `refid` = '" . $res['id'] . "'ORDER BY `time` DESC");
        $colmes1 = $nikuser->rowCount();
        $cpg = ceil($colmes1 / $kmess);
        $nam = $nikuser->fetch();
        $thaoluanmoi .= '<div class="list3 fauthor">';
        // Значки
        //$icons = [
            //($res['vip'] ? $tools->image('pt.gif') : ''),
            //($res['realid'] ? $tools->image('rate.gif') : ''),
            //($res['edit'] ? $tools->image('tz.gif') : ''),
        //];

        if($res['thumb_extension'] == 'none') {
            $avatar_name = $tools->avatar_name($res['user_id']);
            if (file_exists(('files/users/avatar/' . $avatar_name))) {
                $thaoluanmoi .= '<img src="' . $config['homeurl'] . '/files/users/avatar/' . $avatar_name . '" class="thumb" alt="' . $res['from'] . '" />';
            } else {
                $thaoluanmoi .= '<img src="' . $config['homeurl'] . '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="thumb" alt="' . $res['from'] . '" />';
            }
        } else {
            $thumb_file = $res['id'] . '.' . $res['thumb_extension'];
            if (file_exists(('files/forum/thumbnail/' . $thumb_file))) {
                $thaoluanmoi .= '<img src="' . $config['homeurl'] . '/files/forum/thumbnail/' . $thumb_file . '" class="thumb" alt="thumbnail" />';
            } else {
                $thaoluanmoi .= '<img src="' . $config['homeurl'] . '/images/empty' . ($userpost['sex'] ? ($userpost['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="thumb" alt="thumbnail" />';
            }
        }
        $thaoluanmoi .= '<ul><li>';
        //$thaoluanmoi .= implode('', array_filter($icons));
        $thaoluanmoi .= '<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '.html">' . ($res['bai'] ? 'Bài ' . $res['bai'] . ': ' : '') . (empty($res['text']) ? '-----' : $res['text']) . '</a>&#160;[' . $colmes1 . ']';

        if ($cpg > 1) {
            $thaoluanmoi .= '&#160;<a class="tload" href="' . $config['homeurl'] . '/forum/' . $res['id'] . '/' . $res['seo'] . '_clip_p' . $cpg . '.html">&gt;&gt;</a>';
        }
        $thaoluanmoi .= '</li><li class="sub">';
        $thaoluanmoi .= $res['from'];

        if (!empty($nam['from'])) {
            $thaoluanmoi .= '&#160;/&#160;' . $nam['from'];
        }

        $thaoluanmoi .= ' <span class="gray">(' . $tools->thoigian($nam['time']) . ')</span>' .
            '</li></ul></div>';
    }
}
?>

<div class="components">
  <aside class="components__nav">
    <span get="thaoluanmoi-section" class="reactionTrans components__link get-component is-active">
      <span class="components__link-text">Thảo luận mới</span>
    </span>
    <span get="baivietmoi-section" class="reactionTrans components__link get-component">
      <span class="components__link-text">Bài viết mới</span>
    </span>
  </aside>
  <main class="components__pages">
    <section id="thaoluanmoi-section" class="shadow--2dp components__page is-active" load="1">
      <?php
      echo $thaoluanmoi;
      if ($total_thaoluanmoi > $kmess) {
        echo '<div class="list3 text-center">' . $tools->displayPagination('index.php?', $start, $total_thaoluanmoi, $kmess) . '</div>';
      }
      ?>
    </section>
    <section id="baivietmoi-section" class="shadow--2dp components__page" load="0">
      <div class="components__load"><div class="loader red"></div></div>
    </section>
  </main>
</div>

<?php
        ////////////////////////////////////////////////////////////
        // Список Категорий форума                                //
        ////////////////////////////////////////////////////////////

        $req = $db->query("SELECT `id`, `text`, `soft`, `seo` FROM `forum` WHERE `type`='f' ORDER BY `realid`");
        $i = 0;
        while ($res = $req->fetch()) {
            $count = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='r' AND `refid`='" . $res['id'] . "'")->fetchColumn();
            echo '<div class="mrt-code card shadow--2dp">' .
                '<div class="phdr"><h4>' . $res['text'] . '</h4></div>';
            $reqc = $db->query("SELECT `id`, `seo`, `text`, `soft` FROM `forum` WHERE `type`='r' AND `refid`='$res[id]' ORDER BY `realid`");
            $totalc = $reqc->rowCount();
            if ($totalc) {
                $ii = 0;
                while ($resc = $reqc->fetch()) {
                    echo '<div class="card__actions card--border">';
                    $coltemc = $db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $resc['id'] . "' " . $moderation . ($systemUser->rights >= 7 ? '' : "AND `close`!='1'") . "")->fetchColumn();
                    echo '<img class="icon" src="/assets/images/mt.gif"><a class="tload" href="' . $config['homeurl'] . '/forum/' . $resc['id'] . '/' . $resc['seo'] . '.html">' . $resc['text'] . '</a>';

                    if ($coltemc) {
                        echo " [$coltemc]";
                    }
                    if (!empty($resc['soft'])) {
                        echo '<div class="sub"><span class="gray">' . $resc['soft'] . '</span></div>';
                    }
                    echo '</div>';
                    ++$ii;
                }
            } else {
                echo '<div class="card__actions card--border">' . _t('There are no sections in this category') . '</div>';
            }
            echo '</div>';
            ++$i;
        }

/**
echo '<div class="mrt-code card shadow--4dp">' .
    '<div class="phdr"><a class="tload" href="' . $config['homeurl'] . '/upload-hinhanh/">' .
    '<h4>Thư viện ảnh</h4>' .
    '</a></div>';

$total = $db->query("SELECT COUNT(*) AS `count` FROM `cms_image`")->fetch();
$total = $total['count'];
if($total){
    echo '<div class="m-components"><aside class="m-components__nav docs-text-styling">';
    $data = $db->query("SELECT * FROM `cms_image` ORDER BY `time` DESC LIMIT 10");
    while($datas = $data->fetch()){
        $user = $db->query("SELECT `name` FROM `users` WHERE `id`='$datas[user]' LIMIT 1")->fetch();
        echo '<a href="' . $config['homeurl'] . '/upload-hinhanh/'.$datas['id'].'.html" class="tload m-components__link m-component">' .
            '<div class="card-image card shadow--2dp" style="background: url('.$datas['thumbnail'].') center / cover;">' .
            '<div class="card__title card--expand"></div>' .
            '<div class="card__actions">' .
            '<span class="card-image__filename">' . $user['name'] . '</span>' .
            '</div>' .
            '</div>' .
            '</a>';
    }
    echo '</aside></div>';
}
echo '</div>';
*/