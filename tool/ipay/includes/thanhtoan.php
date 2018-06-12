<?php
if ($systemUser->id != 1) {
	header('Location: /?err');
	exit;
}
switch ($mod) {
	case 'lichsu':
		echo '<div class="mrt-code card shadow--2dp" style="margin-bottom: 0;"><div class="phdr"><h4>Lịch sử thanh toán</h4></div>' .
			'</div>';
		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `status`!='0' ORDER BY `time` DESC");
		if ($req->rowCount()) {
			echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
				'<thead>' .
				'<tr>' .
				'<th class="data-table__cell--non-numeric">Thành viên</th>' .
				'<th>Hành động</th>' .
				'<th>Hình thức</th>' .
				'<th>Số tiền</th>' .
				'<th>Tình trạng</th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>';
			while ($res = $req->fetch()) {
				$user = $tools->getUser($res['user_id']);
				echo '<tr' . ($res['status'] == 1 ? ' class="red"' : '') . '>' .
				'<td class="data-table__cell--non-numeric"><a href="/profile/?user=' . $res['user_id'] . '"><strong class="nickname">' . $user['name'] . '</strong></a></td>' .
				'<td>';
				$res_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
				$res_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $res['value'])->fetch();

				if($res['type_active'] == 1) {
					echo 'Nạp tiền';
				} else if ($res['type_active'] == 2) {
					echo 'Rút tiền';
				}

				echo '</td>' .
					'<td>';
				if($res['type_form'] == 1) {
					echo 'Thẻ nạp';
				} else if ($res['type_form'] == 2) {
					echo 'Chuyển khoản NN';
				}
				echo '</td>' .
					'<td>';
				if ($res['type_active'] == 1) {
						echo $tools->balans($res['value']);
				} else {
					if ($res['type_form'] == 1) {
						echo $tools->balans($res_price['price']);
					} else
						echo $tools->balans($res['value']);
				}

				echo ' VNĐ</td>' .
					'</td>' .
					'<td>';
				if ($res['status'] == 1)
					echo '<span class="red">Bị hủy</span>';
				else
					echo '<span class="wgreen">Đã thanh toán</span>';
				echo '</td>' .
						'</tr>';
			}
			echo '</tbody>' .
				'</table></div><br />';
		}

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan">Thanh toán khác</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;

	case 'nap_edit':
		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='1' AND `type_form`='1' AND `id`='$id'");
		if ($req->rowCount()) {
			$res = $req->fetch();
			$res_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
			$user = $tools->getUser($res['user_id']);
			echo '<div class="mrt-code card shadow--2dp" style="z-index:3"><div class="phdr"><h4>Chỉnh sửa</h4></div>';
			if (isset($_POST['submit'])) {
				$value  = isset($_POST['value'])  ? abs(intval($_POST['value']))  : 0;
				$status = isset($_POST['status']) ? abs(intval($_POST['status'])) : 0;
				if ($status) {
					if ($status == 2 && !$value) {
						header('Location: ?act=thanhtoan&mod=nap_check&id=' . $id);
					} else {
						$db->exec("UPDATE `banking_ipay` SET `status`=" . $db->quote($status) . ", `value`=" . $db->quote($value) . " WHERE `id`=" . $id);
						echo '<div class="gmenu">' .
							'Lưu thành công...' .
							'</div></div>';
					}
				} else
					header('Location: ?act=thanhtoan&mod=rut_edit&id=' . $id);
			} else {
				echo '<div class="card__actions">' .
					'- Người nạp: <strong class="nickname">' . $user['name'] . '</strong><br />
					- Số dư trong tài khoản: <strong>' . $tools->balans($user['balans']) . '</strong> VNĐ<br /><br />

					+ Nhà mạng: <strong>' . $res_type['name'] . '</strong><br />
					<span class="red">+ Mệnh giá cũ: <strong>' . $tools->balans($res['value']) . '</strong> VNĐ</span><br />
					+ Mã thẻ: <strong>' . $tools->checkout($res['number']) . '</strong></span><br />
					+ Seri:   <strong>' . $tools->checkout($res['seri']) . '</strong></span><br /><br /><br />
					+ Tình trạng: <strong>';

				if(!$res['status'])
					echo '<span class="red">Chờ xét duyệt</span>';
				elseif ($res['status'] == 1)
					echo '<span class="red">Thẻ sai</span>';
				else
					echo '<span class="wgreen">Đã nạp</span>';
					
				echo '</strong><br /><br /><span class="red fsize--12">+ ' . $tools->thoigian($res['time']) . '</span>' .
					'</div>';
				echo '<div class="card__actions card--border">' .
					'<form name="form" method="post">' .
					'<div class="form-group">' .
					'<input type="number" name="value" value="" />' .
					'<label class="control-label" for="input">Nhập giá trị thẻ mà admin đã nạp!</label><i class="bar"></i>' .
					'</div>' .
					'<div class="form-group">' .
					'Cập nhật tình trạng:' .
					'<select name="status">' .
					'<option value="0" selected="selected">Chờ xét duyệt</option>' .
					'<option value="1">Thẻ lỗi</option>' .
					'<option value="2">Đã nạp</option>' .
					'</select>' .
					'</div>';
				echo '<div class="button-container t10">' .
					'<button class="button" type="submit" name="submit"><span>Thay đổi</span></button>' .
					'</div>' .
					'</form>' .
					'</div>' .
					'</div>';
			}
			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan">Thanh toán</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=thanhtoan');
		break;

	case 'nap_check':
		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='1' AND `type_form`='1' AND `id`='$id'");
		if ($req->rowCount()) {
			$res = $req->fetch();
			if ($res['status'])
				header('Location: ?act=thanhtoan&mod=nap_edit&id=' . $res['id']);

			$res_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
			$res_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $res['value'])->fetch();
			$user = $tools->getUser($res['user_id']);

			echo '<div class="mrt-code card shadow--2dp" style="z-index: 3;"><div class="phdr"><h4>Yêu cầu nạp tiền</h4></div>';

			if (isset($_POST['submit'])) {
				$value  = isset($_POST['value'])  ? abs(intval($_POST['value']))  : 0;
				$status = isset($_POST['status']) ? abs(intval($_POST['status'])) : 0;
				if ($status) {
					if ($status == 2 && !$value) {
						header('Location: ?act=thanhtoan&mod=nap_check&id=' . $id);
					} else {
						$notice = 'ipay.nap.thenap';

						$total = $value * 4 / 100; //chiết khẩu 4%
						$setvl = $value - $total;

						$db->exec("UPDATE `banking_ipay` SET `value`=" . $db->quote($value) . ", `status`=" . $db->quote($status) . " WHERE `id`=" .$id);
						$db->exec("UPDATE `users` SET
							`balans`=`balans`+" . $db->quote($setvl) . ",
							`balansAdd`=`balansAdd`+" . $db->quote($setvl) . "
							WHERE `id`=" . $res['user_id']);

						$db->prepare('
							INSERT INTO `cms_mail` SET
								`user_id` = ?,
								`from_id` = ?,
								`them`    = ?,
								`sys`     = \'1\',
								`time`    = ?,
								`reid`    = ?,
								`type`    = ?
						')->execute([
							$systemUser->id,
							$res['user_id'],
							$status,
							time(),
							$id,
							$notice,
						]);

						echo '<div class="gmenu">' .
							'Thông báo đã được gửi tới thành viên...' .
							'</div></div>';
					}
				} else
					header('Location: ?act=thanhtoan&mod=nap_check&id=' . $id);
			} else {
				echo '<div class="card__actions">
					- Người nạp: <strong class="nickname">' . $user['name'] . '</strong><br />
					- Số dư trong tài khoản: <strong>' . $tools->balans($user['balans']) . '</strong> VNĐ<br /><br />

					+ Nhà mạng: <strong>' . $res_type['name'] . '</strong><br />
					+ Mã thẻ: <strong>' . $tools->checkout($res['number']) . '</strong><br />
					+ Seri: <strong>' . $tools->checkout($res['seri']) . '</strong><br />
					<span class="red fsize--12">+ ' . $tools->thoigian($res['time']) . '</span>
					</div>';
				echo '<div class="card__actions card--border">' .
					'<form name="form" method="post">' .
					'<div class="form-group">' .
					'<input type="number" name="value" value="" />' .
					'<label class="control-label" for="input">Nhập giá trị thẻ mà admin đã nạp!</label><i class="bar"></i>' .
					'</div>' .
					'<div class="form-group">' .
					'Cập nhật tình trạng:' .
					'<select name="status">' .
					'<option value="0" selected="selected">Chờ xét duyệt</option>' .
					'<option value="1">Thẻ lỗi</option>' .
					'<option value="2">Đã nạp</option>' .
					'</select>' .
					'</div>';

				echo '<div class="button-container t10">' .
					'<button class="button" type="submit" name="submit"><span>Hoàn thành</span></button>' .
					'</div>' .
					'</form>' .
					'</div>' .
					'</div>';
			}
		} else
			header('Location: ?act=thanhtoan&mod=nap');

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan&mod=nap">Các yêu cầu nạp</a></div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan">Thanh toán khác</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;

	case 'nap':
		echo '<div class="mrt-code card shadow--2dp" style="margin-bottom: 0;"><div class="phdr"><h4>Yêu cầu nạp tiền</h4></div>';
		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='1' AND `type_form`='1' AND `status`='0'");
		if ($req->rowCount()) {
			echo '</div><div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
				'<thead>' .
				'<tr>' .
				'<th class="data-table__cell--non-numeric">Thành viên</th>' .
				'<th>Nhà mạng</th>' .
				'<th>Mã thẻ</th>' .
				'<th>Seri</th>' .
				'<th></th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>';
			while ($res = $req->fetch()) {
				$req_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id']);
				$req_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $res['value']);
				if ($req_type->rowCount()) {
					$res_type = $req_type->fetch();
					$res_price = $req_price->fetch();
					$user = $tools->getUser($res['user_id']);

					echo '<tr' . ($res['status'] == 1 ? ' class="red"' : '') . '>' .
						'<td class="data-table__cell--non-numeric"><a href="/profile/?user=' . $res['user_id'] . '"><strong class="nickname">' . $user['name'] . '</strong></a></td>' .
						'<td>' . $tools->checkout($res_type['name']) . '</td>' .
						'<td>' . $tools->checkout($res['number']) . '</td>' .
						'<td>' . $tools->checkout($res['seri']) . '</td>' .
						'</td>' .
						'<td><a href="?act=thanhtoan&mod=nap_check&id=' . $res['id'] . '">' .
						'<span class="red">Duyệt</span>' .
						'</a></td>' .
						'</tr>';
				}
			}
			echo '</tbody>' .
				'</table></div><br />';
		} else
			echo '<div class="card__actions text-center">Không có thành viên nạp tiền</div></div><br />';

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan">Thanh toán khác</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;

	case 'rut_edit':
		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='2' AND `type_form`='1' AND `id`='$id'");
		if ($req->rowCount()) {
			$res = $req->fetch();
			$res_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
			$res_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $res['value'])->fetch();
			$user = $tools->getUser($res['user_id']);
			echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Chỉnh sửa</h4></div>';
			if (isset($_POST['submit'])) {
				$pass = isset($_POST['pass']) ? abs(intval($_POST['pass'])) : 0;
				$seri = isset($_POST['seri']) ? abs(intval($_POST['seri'])) : 0;
				if ($pass && $seri) {
					$db->exec("UPDATE `banking_ipay` SET `number` = " . $db->quote($pass) . ", `seri` = " . $db->quote($seri) . " WHERE `id` = " . $id);
					echo '<div class="gmenu">' .
						'Lưu thành công...' .
						'</div></div>';
				} else
					header('Location: ?act=thanhtoan&mod=rut_edit&id=' . $id);
			} else {
				echo '<div class="card__actions">' .
					'- Người rút: <strong class="nickname">' . $user['name'] . '</strong><br />
					- Số dư trong tài khoản: <strong>' . $tools->balans($user['balans']) . '</strong> VNĐ<br /><br />

					+ Nhà mạng: <strong>' . $res_type['name'] . '</strong><br />
					+ Mệnh giá: <strong>' . $tools->balans($res_price['price']) . '</strong> VNĐ<br />
					<span class="red">+ Mã thẻ cũ: <strong>' . $tools->checkout($res['number']) . '</strong></span><br />
					<span class="red">+ Seri cũ:   <strong>' . $tools->checkout($res['seri']) . '</strong></span><br /><br />
					<span class="red fsize--12">+ ' . $tools->thoigian($res['time']) . '</span>' .
					'</div>';
				echo '<div class="card__actions card--border">' .
					'<form name="form" method="post">' .
					'<div class="form-group">' .
					'<input type="number" name="pass" value="" required="required" />' .
					'<label class="control-label" for="input">Mã thẻ</label><i class="bar"></i>' .
					'</div>' .
					'<div class="form-group">' .
					'<input type="number" name="seri" value="" required="required" />' .
					'<label class="control-label" for="input">Seri</label><i class="bar"></i>' .
					'</div>';
				echo '<div class="button-container t10">' .
					'<button class="button" type="submit" name="submit"><span>Thay đổi</span></button>' .
					'</div>' .
					'</form>' .
					'</div>' .
					'</div>';
			}
			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan">Thanh toán</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=thanhtoan');
		break;

	case 'rut_check':
		$res = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='2' AND `id`='$id'")->fetch();
		if ($res) {
			if ($res['status'])
				header('Location: ?act=thanhtoan&mod=rut_edit&id=' . $res['id']);

			echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Yêu cầu rút tiền</h4></div>';
			if ($res['type_form'] == 1) {
				$res_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
				$res_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $res['value'])->fetch();
				$user = $tools->getUser($res['user_id']);
				if (isset($_POST['submit'])) {
					$pass = isset($_POST['pass']) ? abs(intval($_POST['pass'])) : 0;
					$seri = isset($_POST['seri']) ? abs(intval($_POST['seri'])) : 0;
					if ($pass && $seri) {
						$notice = 'ipay.rut.thenap';

						$db->exec("UPDATE `banking_ipay` SET `number` = " . $db->quote($pass) . ", `seri` = " . $db->quote($seri) . ", `status`='2' WHERE `id` = '$id'");
						$db->exec("UPDATE `users` SET `balansReturn`=`balansReturn`+" . $db->quote($res_price['price']) . " WHERE `id`=" . $res['user_id']);

						$db->prepare('
							INSERT INTO `cms_mail` SET
								`user_id` = ?,
								`from_id` = ?,
								`them`    = \'2\',
								`sys`     = \'1\',
								`time`    = ?,
								`reid`    = ?,
								`type`    = ?
						')->execute([
							$systemUser->id,
							$res['user_id'],
							time(),
							$id,
							$notice,
						]);

						echo '<div class="gmenu text-center">' .
							'Thanh toán thẻ cho thành viên thành công.!! Thông báo đã được gửi tới thành viên...' .
							'</div>';
					} else
						header('Location: ?act=thanhtoan&mod=rut_check&id=' . $id);
				} else {
					echo '<div class="card__actions">
						- Người rút: <strong class="nickname">' . $user['name'] . '</strong><br />
						- Số dư trong tài khoản: <strong>' . $tools->balans($user['balans']) . '</strong> VNĐ<br /><br />

						+ Nhà mạng: <strong>' . $res_type['name'] . '</strong><br />
						+ Mệnh giá: <strong>' . $tools->balans($res_price['price']) . '</strong> VNĐ<br />
						<span class="red fsize--12">+ ' . $tools->thoigian($res['time']) . '</span>
						</div>';
					echo '<div class="card__actions card--border">' .
						'<form name="form" method="post">' .
						'<div class="form-group">' .
						'<input type="number" name="pass" value="" required="required" />' .
						'<label class="control-label" for="input">Mã thẻ</label><i class="bar"></i>' .
						'</div>' .
						'<div class="form-group">' .
						'<input type="number" name="seri" value="" required="required" />' .
						'<label class="control-label" for="input">Seri</label><i class="bar"></i>' .
						'</div>';

					echo '<div class="button-container t10">' .
						'<button class="button" type="submit" name="submit"><span>Hoàn thành</span></button>' .
						'</div>' .
						'</form>' .
						'</div>';
				}
			} else {
				if (isset($_POST['submit'])) {
					$status = isset($_POST['status']) ? trim($_POST['status']) : 0;
					if ($status) {
						$notice = 'ipay.rut.chuyenkhoan';

						if ($status == 1){
							$db->exec("UPDATE `users` SET `balans`=`balans`+" . $db->quote($res['value']) . " WHERE `id`=" . $res['user_id']);
						} else {
							$db->exec("UPDATE `users` SET `balansReturn`=`balansReturn`+" . $db->quote($res['value']) . " WHERE `id`=" . $res['user_id']);
						}

						$db->exec("UPDATE `banking_ipay` SET `status` = " . $db->quote($status) . " WHERE `id` = '$id'");
						$db->prepare('
							INSERT INTO `cms_mail` SET
								`user_id` = ?,
								`from_id` = ?,
								`them`    = ?,
								`sys`     = \'1\',
								`time`    = ?,
								`reid`    = ?,
								`type`    = ?
						')->execute([
							$systemUser->id,
							$res['user_id'],
							$status,
							time(),
							$id,
							$notice,
						]);

						echo '<div class="gmenu text-center">' .
							'Cập nhật thông tin thành công.!! Thông báo đã được gửi tới thành viên...' .
							'</div>';
					} else
						header('Location: ?act=thanhtoan&mod=rut_check&id=' . $id);
				} else {
					$user = $tools->getUser($res['user_id']);
					$res_banking = $db->query("SELECT * FROM `banking` WHERE `id`=" . $res['acc_id'])->fetch();
					echo '<div class="card__actions">
						- Người rút: <strong class="nickname">' . $user['name'] . '</strong><br />
						- Lúc: <span class="red">' . $tools->thoigian($res['time']) . '</span>
						<br />
						<br />
						<div class="scroll_x full-margin"><table class="data-table data-table--selectable">' .
						'<thead>' .
						'<tr>' .
						'<th class="data-table__cell--non-numeric">Ngân hàng</th>' .
						'<th>Chi nhánh</th>' .
						'<th>Chủ tài khoản</th>' .
						'<th>Số tài khoản</th>' .
						'<th>Rút</th>' .
						'</tr>' .
						'</thead>' .
						'<tbody>
						<tr' . (!$res_banking ? ' class="red"' : '') . '>' .
						'<td class="data-table__cell--non-numeric">' . (!$res_banking ? '---' : $tools->checkout($res_banking['name'])) . '</td>' .
						'<td>' . (!$res_banking ? '---' : $tools->checkout($res_banking['branch'])) . '</td>' .
						'<td>' . (!$res_banking ? '---' : $tools->checkout($res_banking['owner'])) . '</td>' .
						'<td>' . (!$res_banking ? '---' : $tools->checkout($res_banking['number'])) . '</td>' .
						'<td>' . $tools->balans($res['value']) . ' VNĐ</td>' .
						'</tr>' .
						'</tbody>' .
						'</table></div>
						</div>';
					echo '<div class="card__actions card--border">' .
						'<form name="form" method="post">' .
						'<div class="form-radio">
						<div class="radio">
						<label><input type="radio" value="0" name="status" checked="checked"><i class="helper"></i>&nbsp;<strong class="red">Chờ duyệt</strong></label>
						</div>
						<div class="radio">
						<label><input type="radio" value="1" name="status"><i class="helper"></i>&nbsp;<strong class="wgreen">Hủy giao dịch</strong></label>
						</div>
						<div class="radio">
						<label><input type="radio" value="2" name="status"><i class="helper"></i>&nbsp;<strong class="wgreen">Đã giao dịch</strong></label>
						</div>
						</div>';

					echo '<div class="button-container t10">' .
						'<button class="button" type="submit" name="submit"><span>Cập nhật</span></button>' .
						'</div>' .
						'</form>' .
						'</div>';
				}
			}
			echo '</div>';
		} else
			header('Location: ?act=thanhtoan&mod=rut');

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan&mod=rut">Các yêu cầu rút</a></div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan">Thanh toán khác</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;

	case 'rut':
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Yêu cầu rút tiền</h4></div></div>';



		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='2' AND `type_form`='1' AND `status`='0' ORDER BY `id` DESC ");
		$thenapC = $req->rowCount();
		echo '<div class="mrt-code card shadow--4dp"' . ($thenapC ? ' style="margin-bottom: 0;"' : '') . '><div class="phdr forum-title"><span>Rút thẻ nạp</span></div>';
		if ($thenapC) {
			echo '</div><div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
				'<thead>' .
				'<tr>' .
				'<th class="data-table__cell--non-numeric">Thành viên</th>' .
				'<th>Nhà mạng</th>' .
				'<th>Mệnh giá</th>' .
				'<th></th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>';
			while ($res = $req->fetch()) {
				$res_type  = $db->query("SELECT `name` FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
				$res_price = $db->query("SELECT `price` FROM `card_price` WHERE `id`=" . $res['value'])->fetch();
				$user = $tools->getUser($res['user_id']);

				echo '<tr' . (!$res_type || !$res_price || !$user ? ' class="red"' : '') . '>' .
					'<td class="data-table__cell--non-numeric"><a href="/profile/?user=' . $res['user_id'] . '"><strong class="nickname">' . $user['name'] . '</strong></a></td>' .
					'<td>' . $tools->checkout($res_type['name']) . '</td>' .
					'<td>' . $tools->balans($res_price['price']) . ' VNĐ</td>' .
					'<td><a href="?act=thanhtoan&mod=rut_check&id=' . $res['id'] . '">' .
					'<span class="red">Duyệt</span>' .
					'</a></td>' .
					  '</tr>';
			}
			echo '</tbody>' .
				'</table></div><br />';
		} else
			echo '<div class="card__actions text-center">Không có yêu cầu rút tiền</div></div>';

		$req_nganhang = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='2' AND `type_form`='2' AND `status`='0' ORDER BY `id` DESC ");
		$nganhangC = $req_nganhang->rowCount();
		echo '<div class="mrt-code card shadow--4dp"' . ($nganhangC ? ' style="margin-bottom: 0;"' : '') . '><div class="phdr forum-title"><span>Chuyển khoản ngân hàng</span></div>';
		if ($nganhangC) {
			echo '</div><div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
				'<thead>' .
				'<tr>' .
				'<th class="data-table__cell--non-numeric">Thành viên</th>' .
				'<th>Ngân hàng</th>' .
				'<th>Số tài khoản</th>' .
				'<th>Rút</th>' .
				'<th></th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>';
			while ($res_nganhang = $req_nganhang->fetch()) {
				$banking = $db->query("SELECT * FROM `banking` WHERE `id`=" . $res_nganhang['acc_id'])->fetch();
				$user = $tools->getUser($res_nganhang['user_id']);
				echo '<tr' . (!$banking || !$user? ' class="red"' : '') . '>' .
					'<td class="data-table__cell--non-numeric"><a href="/profile/?user=' . $res_nganhang['user_id'] . '"><strong class="nickname">' . $user['name'] . '</strong></a></td>' .
					'<td>' . (!$banking ? '---' : $tools->checkout($banking['name'])) . '</td>' .
					'<td>' . (!$banking ? '---' : $tools->checkout($banking['number'])) . '</td>' .
					'<td>' . (!$res_nganhang['value'] ? '...' : $tools->balans($res_nganhang['value'])) . ' VNĐ</td>' .
					'<td><a href="?act=thanhtoan&mod=rut_check&id=' . $res_nganhang['id'] . '">' .
					'<span class="red">Duyệt</span>' .
					'</a></td>' .
					'</tr>';
			}
			echo '</tbody>' .
				'</table></div><br />';
		} else
			echo '<div class="card__actions text-center">Không có yêu cầu rút tiền</div></div>';

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=thanhtoan">Thanh toán khác</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;

	case 'lichsu':
		break;
	
	default:
		$ipay     = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`='0'")->fetchColumn();
		$ipay_nap = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`='0' AND `type_active` = '1' ")->fetchColumn();
		$ipay_rut = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`='0' AND `type_active` = '2' ")->fetchColumn();
		$log      = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`!='0'")->fetchColumn();

		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thanh toán</h4></div>' .
			'<div class="rmenu text-center">Có ' . $ipay . ' giao dịch đang chờ được xét duyệt.</div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=thanhtoan&mod=nap">Nạp vào</a>' . ($ipay_nap ? ' (<strong class="red">' . $ipay_nap . '</strong>)' : '') . '</div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=thanhtoan&mod=rut">Rút ra</a>' . ($ipay_rut ? ' (<strong class="red">' . $ipay_rut . '</strong>)' : '') . '</div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=thanhtoan&mod=lichsu">Lịch sử</a> (' . $log . ')</div>' .
			'</div>';
		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/?act=admin">Quản lý IPay</a></div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;
}
