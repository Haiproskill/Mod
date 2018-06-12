<?php
	if ($systemUser->balans < 10000)
	{
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Chuyển tiền</h4></div>' .
			'<div class="rmenu text-center">Bạn phải có ít nhất 10.000 VNĐ trong tài khoản</div>' .
			'</div>';

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		exit();
	}
	$user = false;
	$p2 = intval($systemUser->balans * 2 / 100);

	echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Chuyển tiền</h4></div>' .
		'<div class="card__actions"> - Chuyển tiền cho thành viên khác<br />' .
		'+ Bạn có: <strong>' . $tools->balans($systemUser->balans) . '</strong> VNĐ.<br />' .
		'+ Phí chuyển: 2% số tiền chuyển (<span class="red">' . $tools->balans($p2) . '</span> VNĐ).<br />' .
		'+ Có thể chuyển tối đa: <strong>' . $tools->balans($systemUser->balans - $p2) . '</strong> VNĐ.<br />' .
		'</div></div>';
	echo '<div class="mrt-code card shadow--2dp">';

	if ($id)
	{
		$user = $tools->getUser($id);
		if ($user)
		{
			if ($user['id'] == $systemUser->id)
			{
				echo '<div class="rmenu text-center">Bạn không thể chuyển cho chính mình.!</div>';
				$user = false;
			} else {
				$balans = empty($_POST['balans']) ? 0 : abs(intval($_POST['balans']));
				if ($balans && $balans > $systemUser->balans - $p2)
				{
					echo '<div class="rmenu text-center">Bạn không đủ tiền.!</div>';
				} else if ($balans && $balans < 5000)
				{
					echo '<div class="rmenu text-center">Chuyển ít nhất là 5.000 VNĐ!</div>';
				} else {
					echo '<div class="card__actions">' .
						'- ID: ' . $user['id'] . '.<br />' .
						'- Tìm thấy: <strong class="wgreen">' . $user['name'] . '</strong>';
					if (isset($_POST['chuyen']))
					{

						$notice = 'ipay.chuyen';

						$db->exec("UPDATE `users` SET `balans`=`balans`+" . $db->quote($balans) . " WHERE `id`=" . $user['id']);
						$db->exec("UPDATE `users` SET `balans`=`balans`-" . $db->quote($balans - $p2) . " WHERE `id`=" . $systemUser->id);

						$db->prepare('
							INSERT INTO `banking_ipay` SET
							  `type_active` = \'3\',
							  `status`      = \'1\',
							  `user_id`     = ?,
							  `acc_id`      = ?,
							  `value`       = ?,
							  `time`        = ?
						')->execute([
						 	$systemUser->id,
						    $user['id'],
						    $balans,
						    time(),
						]);

						$newAdd = $db->lastInsertId();

						$db->prepare('
	                        INSERT INTO `cms_mail` SET
	                          `user_id` = ?,
	                          `from_id` = ?,
	                          `sys`     = \'1\',
	                          `time`    = ?,
	                          `reid`    = ?,
	                          `type`    = ?
	                    ')->execute([
	                        $systemUser->id,
	                        $user['id'],
	                        time(),
	                        $newAdd,
	                        $notice,
	                    ]);

						echo '<div class="wgreen text-center">Chuyển thành công.!</div>';
					} else if($balans)
					{
						echo '<br /><br />' .
							'<center>Bạn thực sự muốn chuyển <strong class="red">' . $tools->balans($balans) . ' VNĐ</strong> cho <strong class="red">' . $user['name'] . '</strong> ???<br /><span class="red">Phí: ' . $tools->balans(intval($balans * 2 / 100)) . ' VNĐ</span></center>' .
							'<form method="post">' .
							'<input type="hidden" name="id" min="1" value="' . $user['id'] . '" />' .
							'<input type="hidden" name="balans" value="' . $balans . '" />' .
							'<div class="button-container t10">' .
							'<button class="button" type="submit" name="chuyen"><span>Chuyển</span></button>' .
							'</div>' .
							'</form>';
					}
					echo '</div>';
				}
			}
		} else {
			echo '<div class="rmenu text-center">Thành viên không tồn tại.!</div>';
		}
	}
	echo '<div class="card__actions' . ($id ? ' card--border' : '') . '">' .
		'<form name="form" method="post">' .
		'<div class="form-group">' .
		'<input type="number" name="id" min="1" value="' . ($id ? $id : '') . '" required="required" />' .
		'<label class="control-label" for="input">ID người nhận:</label><i class="bar"></i>' .
		'</div>';
	if ($user)
	{
		echo '<div class="form-group">' .
		'<input type="number" name="balans" value="' . ($balans ? $balans : '') . '" placeholder="0" />' .
		'<label class="control-label" for="input">Số tiền muốn chuyển:</label><i class="bar"></i>' .
		'</div>';
	}
	echo '<div class="button-container t10">' .
		'<button class="button" type="submit" name="submit"><span>Tiếp tục</span></button>' .
		'</div>' .
		'</form>';
	echo '</div>' .
		'</div>' .
		'<div class="mrt-code card shadow--2dp">' .
		'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
		'</div>';
