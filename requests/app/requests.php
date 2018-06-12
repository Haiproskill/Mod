<?php

if ($systemUser->isValid()) {
	// menu
	$taixiu    = isset($_POST['taixiu'])    ? true : false;
	$baucua    = isset($_POST['baucua'])    ? true : false;
	$messenger = isset($_POST['messenger']) ? true : false;
	$chatbox   = isset($_POST['chatbox'])   ? true : false;

	// data default
	$game_data = array(
		'status'   => 0
	);
	$mess_data = array(
		'status'   => 0
	);
	$data_chatbox = array(
		'chatbox' => 0
	);

	// var default
	$game_status      = 0;
	$listGame         = array();
	$status_messenger = 0;
	$notice_img       = null;
	$count_messenger  = 0;
	$count_notice     = 0;
	$online_list      = array();
	$ipay             = 0;
	$sql              = '';
	$set_karma        = $config['karma'];


	/**
	 * Game
	*/

	// Tai Xiu
	if ($taixiu) {
		if ($systemUser->rights == 9) {
			require(ROOT_PATH . 'game/taixiu/method/game.php');
			if ($datagame['sucsac1'] > 0 && $datagame['sucsac2'] > 0 && $datagame['sucsac3'] > 0) {
				$listGame['taixiuSet'] = array(
					'status' => 1,
					's1'     => $datagame['sucsac1'],
					's2'     => $datagame['sucsac2'],
					's3'     => $datagame['sucsac3']
				);
			} else
				$listGame['taixiuSet'] = array(
					'status' => 0
				);
		}
		$tai     = 0;
		$xiu     = 0;
		$cuocxiu = 0;
		$cuoctai = 0;

		$res_gtxOld = $db->query('SELECT `id` FROM `taixiu_log` ORDER BY `id` DESC LIMIT 1')->fetch();
		if ($res_gtxOld) {
			$req = $db->query("SELECT `type`, `bets` FROM `taixiu_user` WHERE `id_log`=" . $res_gtxOld['id']);
			while (($res = $req->fetch()) !== false) {
				if ($res['type'] == 2) {
					$tai += $res['bets'];
				} else {
					$xiu += $res['bets'];
				}
			}

			// Số tiền cược của người dùng hiện đang đăng nhập.
			$req_gtaixiuI = $db->query("SELECT `type`, `bets` FROM `taixiu_user` WHERE `user_id`=" . $systemUser->id . " AND `id_log`=" . $res_gtxOld['id']);
			while (($res_gtaixiu_I = $req_gtaixiuI->fetch()) !== false) {
				if ($res_gtaixiu_I['type'] == 2) {
					$cuoctai += $res_gtaixiu_I['bets'];
				} else {
					$cuocxiu += $res_gtaixiu_I['bets'];
				}
			}
		}

		$game_status = 1;
		$listGame['taixiu'] = array(
			'tai'     => $tools->balans($tai),
			'xiu'     => $tools->balans($xiu),
			'cuoctai' => $tools->balans($cuoctai),
			'cuocxiu' => $tools->balans($cuocxiu),
			'balans'  => $systemUser->balans,
			'tien'    => $tools->balans($systemUser->balans)
		);
	}

	// Bầu Cua
	if ($baucua) {
		if ($systemUser->rights == 9) {
			require(ROOT_PATH . 'game/baucua/method/game.php');
			if ($datagame['sucsac1'] > 0 && $datagame['sucsac2'] > 0 && $datagame['sucsac3'] > 0) {
				$listGame['baucuaSet'] = array(
					'status' => 1,
					's1'     => $datagame['sucsac1'],
					's2'     => $datagame['sucsac2'],
					's3'     => $datagame['sucsac3']
				);
			} else
				$listGame['baucuaSet'] = array(
					'status' => 0
				);
		}
		$res_baucua = $db->query('SELECT * FROM `baucua_log` ORDER BY `id` DESC LIMIT 1')->fetch();
		// Số tiền cược của người dùng hiện đang đăng nhập.
		$req_Ibaucua = $db->query("SELECT `type`, `bets` FROM `baucua_user` WHERE `user_id`=" . $systemUser->id . " AND `id_log`=" . $res_baucua['id']);
		while (($res_Ibaucua = $req_Ibaucua->fetch()) !== false) {
			if ($res_Ibaucua['type'] == 1) {
				$cuocHuou = $res_Ibaucua['bets'];
			} elseif ($res_Ibaucua['type'] == 2) {
				$cuocBau  = $res_Ibaucua['bets'];
			} elseif ($res_Ibaucua['type'] == 3) {
				$cuocGa   = $res_Ibaucua['bets'];
			} elseif ($res_Ibaucua['type'] == 4) {
				$cuocCa   = $res_Ibaucua['bets'];
			} elseif ($res_Ibaucua['type'] == 5) {
				$cuocCua  = $res_Ibaucua['bets'];
			} elseif ($res_Ibaucua['type'] == 6) {
				$cuocTom  = $res_Ibaucua['bets'];
			}
		}

		// Tổng số tiền cược của tất cả thành viên...
		$allHuou = 0;
		$allBau  = 0;
		$allGa   = 0;
		$allCa   = 0;
		$allCua  = 0;
		$allTom  = 0;

		$req_allBaucua = $db->query("SELECT `type`, `bets` FROM `baucua_user` WHERE `id_log`=" . $res_baucua['id']);
		while (($res_allBaucua = $req_allBaucua->fetch()) !== false) {
			if ($res_allBaucua['type'] == 1) {
				$allHuou += $res_allBaucua['bets'];
			} elseif ($res_allBaucua['type'] == 2) {
				$allBau  += $res_allBaucua['bets'];
			} elseif ($res_allBaucua['type'] == 3) {
				$allGa   += $res_allBaucua['bets'];
			} elseif ($res_allBaucua['type'] == 4) {
				$allCa   += $res_allBaucua['bets'];
			} elseif ($res_allBaucua['type'] == 5) {
				$allCua  += $res_allBaucua['bets'];
			} elseif ($res_allBaucua['type'] == 6) {
				$allTom  += $res_allBaucua['bets'];
			}
		}

		// Dữ liệu trả về.
		$game_status = 1;
		$listGame['baucua'] = array(
			'allHuou'  => $tools->gBalans($allHuou),
			'allBau'   => $tools->gBalans($allBau),
			'allGa'    => $tools->gBalans($allGa),
			'allCa'    => $tools->gBalans($allCa),
			'allCua'   => $tools->gBalans($allCua),
			'allTom'   => $tools->gBalans($allTom),

			'cuocHuou' => $tools->gBalans($cuocHuou),
			'cuocBau'  => $tools->gBalans($cuocBau),
			'cuocGa'   => $tools->gBalans($cuocGa),
			'cuocCa'   => $tools->gBalans($cuocCa),
			'cuocCua'  => $tools->gBalans($cuocCua),
			'cuocTom'  => $tools->gBalans($cuocTom)
		);

	}
	$game_data = array(
		'status'   => $game_status,
		'listGame' => $listGame
	);

	/**
	 * Messenger
	*/
	if ($messenger) {
		$messbefore = "";
		$avatar = null;
		$mess_list = array();

		if (isset($_POST['messuid'])) {
			$messuid = trim($_POST['messuid']);
		}

		if (isset($_POST['messbefore']) && $_POST['messbefore'] > 0) {
			$messbefore_id = trim($_POST['messbefore']);
			$messbefore = "`cms_mail`.`id` > " . $db->quote($messbefore_id) . " AND";
		}
		$req = $db->query("SELECT `cms_mail`.*, `cms_mail`.`id` AS `mid`, `cms_mail`.`time` AS `mtime`, `users`.`sex`, `users`.`id`
			FROM `cms_mail`
			LEFT JOIN `users` ON `cms_mail`.`user_id`=`users`.`id`
			WHERE ".$messbefore."
			((`cms_mail`.`user_id`=" . $db->quote($messuid) . " AND `cms_mail`.`from_id`='" . $systemUser->id . "') OR (`cms_mail`.`user_id`='" . $systemUser->id . "' AND `cms_mail`.`from_id`=" . $db->quote($messuid) . "))
			AND `cms_mail`.`delete`!='" . $systemUser->id . "'
			AND `cms_mail`.`sys`!='1'
			AND `cms_mail`.`spam`='0'
			ORDER BY `cms_mail`.`time` ASC
		");
		$countMessNew = $req->rowCount();
		$mass_read = [];
		$i = 0;
		while ($row = $req->fetch()) {
			if ($row['from_id'] == $systemUser->id) {
				$avatar_name = $tools->avatar_name($row['user_id']);
				if (file_exists(('files/users/avatar/' . $avatar_name))) {
					$avatar = '/files/users/avatar/' . $avatar_name . '); ';
				} else {
					$avatar = '/images/empty' . ($row['sex'] ? ($row['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
				}
			} else {
				$avatar = '0';
			}
			if ($row['read'] == 0 && $row['from_id'] == $systemUser->id) {
				$mass_read[] = $row['mid'];
			}
			$post = $row['text'];
			$post = $tools->checkout($post, 1, 1, 0, 1);

			if ($row['file_name']) {
				$post .= '<div class="func">' . _t('File') . ': <a href="index.php?act=load&amp;id=' . $row['mid'] . '">' . $row['file_name'] . '</a> (' . formatsize($row['size']) . ')(' . $row['count'] . ')</div>';
			}

			$mess_list[$i] = array(
				'id'         => $row['mid'],
				'avatar'     => $avatar,
				'text'       => $post,
				'time'       => $tools->thoigian($row['mtime']),
				'timestamp'  => (round((time()-$row['mtime'])/3600) < 1 ? $tools->timestamp($row['mtime']) : 0)
			);

			$i++;
		}

		if ($mass_read) {
			$result = implode(',', $mass_read);
			$db->exec("UPDATE `cms_mail` SET `read`='1' WHERE `from_id`='" . $systemUser->id . "' AND `id` IN (" . $result . ")");
		}

		$mess_data = array(
			'status' => $countMessNew,
			'data'   => $mess_list
		);
	}

	/**
	 * Chatbox
	*/
	if ($chatbox) {
		$chatbox_start_row = 0;
		$chatbox_before = "";
		$chatbox_list   = array();

		if (isset($_POST['admin']) && $_POST['admin'] == '1' && $systemUser->isValid() && $systemUser->rights >= 1) {
			$adm = '1';
		} else {
			$adm = '0';
		}
		if (isset($_POST['start_row']) && $_POST['start_row'] > 0) {
			$chatbox_start_row = trim($_POST['start_row']);
		}
		if (isset($_POST['before_id']) && $_POST['before_id'] > 0) {
			$chatbox_before_id = trim($_POST['before_id']);
			$chatbox_before = "`guest`.`id` > " . $db->quote($chatbox_before_id) . " AND";
		}

		$req = $db->query("SELECT `guest`.*, `guest`.`id` AS `gid`, `users`.`rights`, `users`.`sex`, `users`.`id`
			FROM `guest`
			LEFT JOIN `users` ON `guest`.`user_id` = `users`.`id`
			WHERE " . $chatbox_before . " `guest`.`adm`=" . $adm . " ORDER BY `time` DESC LIMIT " . $chatbox_start_row . ", " . $kmess . "");
		$newChatCount = $req->rowCount();
		$i = 0;
		while ($gres = $req->fetch()) {
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
				$chatbox_list[$i]['avatar'] =  $config['homeurl'] . '/files/users/avatar/' . $avatar_name;
			} else {
				$chatbox_list[$i]['avatar'] =  $config['homeurl'] . '/images/empty' . ($gres['sex'] ? ($gres['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
			}
			$chatbox_list[$i]['id']         = $gres['gid'];
			$chatbox_list[$i]['name']       = $gres['name'];
			$chatbox_list[$i]['user_id']    = $gres['user_id'];
			$chatbox_list[$i]['rights']     = $gres['rights'];
			$chatbox_list[$i]['link']       = ($systemUser->isValid() && $gres['user_id'] != $systemUser->id ? 1 : 0);
			$chatbox_list[$i]['panel']      = ($systemUser->isValid() && $systemUser->rights >= 6 ? 1 : 0);
			$chatbox_list[$i]['panel_more'] = ($systemUser->isValid() && ($systemUser->rights > $gres['rights'] || $systemUser->id == $gres['user_id']) ? 1 : 0);
			$chatbox_list[$i]['time']       = $tools->thoigian($gres['time']);
			$chatbox_list[$i]['timestamp']  = (round((time()-$gres['time'])/3600) < 1 ? $tools->timestamp($gres['time']) : 0);
			$chatbox_list[$i]['text']       = $post;
			if (!empty($gres['otvet'])) {
				$chatbox_list[$i]['reply']['time'] = $tools->displayDate($gres['otime']);
				$chatbox_list[$i]['reply']['name'] = $gres['admin'];
				$chatbox_list[$i]['reply']['text'] = $tools->checkout($gres['otvet'], 1, 1, 0, 1);
			} else
				$chatbox_list[$i]['reply']['time'] = 0;

			$i++;
		}

		$data_chatbox = array(
			'chatbox'      => $newChatCount,
			'chatbox_list' => $chatbox_list
		);
	}

	/**
	 * Thông báo khác
	*/
	// IPay
	if ($systemUser->rights == 9) {
		$ipay = intval($db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`='0'")->fetchColumn());
	}
	// online list
	$db->query("UPDATE `users` SET `lastdate` = '" . time() . "' WHERE `id` = " . $systemUser->id);
	$ontime = time() - 10;
	$requ   = $db->query("SELECT `id`, `name`, `rights` FROM `users` WHERE `lastdate` > '" . $ontime . "' ORDER BY `name`");
	$i = 0;
	while ($resu = $requ->fetch()){
		if ($db->query("SELECT COUNT(*) FROM `cms_ban_users` WHERE `user_id` = '" . $resu['id'] . "' AND `ban_time` > '" . time() . "' ")->fetchColumn())
		{
			$online_list[$i]['ban'] = 1;
		} else
			$online_list[$i]['ban'] = 0;
		$online_list[$i]['my']     = ($systemUser->id == $resu['id'] ? 1 : 0);
		$online_list[$i]['id']     = $resu['id'];
		$online_list[$i]['name']   = $resu['name'];
		$online_list[$i]['rights'] = $resu['rights'];

		 $i++;
	}

	// thông báo
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
			$new_count_message = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `cms_mail`.`user_id`='" .$info['user_id'] . "' AND `cms_mail`.`from_id`='" . $systemUser->id . "' AND `read`='0' AND `delete`!='" . $systemUser->id . "' AND `spam`='0' AND `sys`!='1' ")->fetchColumn();
			$count_messenger = $new_count_message;
		} else if ($total > 1) {
			$count_messenger = $total . '+';
		}
	}

	$notice = array(
		'notice'           => $count_notice,
		'notice_messenger' => $count_messenger,
		'data_messenger'   => $notice_img,
		'data_online'      => $online_list
	);

	/**
	 * Tổng dữ liệu
	*/
	$data = array(
		'status'    => 1,
		'game'      => $game_data,
		'messenger' => $mess_data,
		'chatbox'   => $data_chatbox,
		'notice'    => $notice,
		'ipay'      => $ipay,
		'users'     => array(
			'balans'  => $systemUser->balans,
			'tien'    => $tools->balans($systemUser->balans)
		)
	);
	header("Content-type: application/json; charset=utf-8");
	echo json_encode($data);
	exit();
} else {
	$data = array(
		'status'    => 0
	);
	header("Content-type: application/json; charset=utf-8");
	echo json_encode($data);
	exit();
}
