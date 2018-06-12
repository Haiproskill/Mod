<?php

switch ($mod) {
	case 'view':
		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='1' AND `type_form`='1' AND `user_id`=" . $systemUser->id . " AND `id`='$id'");
		if ($req->rowCount()) {
			$res = $req->fetch();
			$res_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
			$user = $tools->getUser($res['user_id']);
			echo '<div class="mrt-code card shadow--2dp" style="z-index: 3;"><div class="phdr"><h4>Nạp tiền</h4></div>';
			echo '<div class="card__actions">
				+ Nhà mạng: <strong>' . $res_type['name'] . '</strong><br />
				+ Mã thẻ: <strong>' . $tools->checkout($res['number']) . '</strong><br />
				+ Seri: <strong>' . $tools->checkout($res['seri']) . '</strong><br />
				<span class="red fsize--12">+ ' . $tools->thoigian($res['time']) . '</span><br /><br />
				+ Tình trạng: ';
			if(!$res['status'])
		 		echo '<span class="red">Chờ xét duyệt</span>';
		 	elseif ($res['status'] == 1)
		 		echo '<span class="red">Thẻ sai</span>';
			else
	 			echo '<span class="wgreen">Đã nạp</span>';

	 		if($res['status'] == 2) {
	 			if ($res['value']) {
	 				$total = $res['value'] * 4 / 100;
	 				echo '<br />- Đã sác nhận thẻ với mệnh giá: <strong>' . $tools->balans($res['value']) . '</strong> VNĐ<br />' .
	 					'- Bạn đã nạp thành công <strong>' . $tools->balans($res['value'] - $total) . ' VNĐ</strong> vào tài khoản.<br />' .
	 					'- Chiết khẩu <strong>4%</strong> - <strong>' . $tools->balans($total) . ' VNĐ</strong> .';
	 			}
	 		}

			echo '</div>';
			echo '</div>';

			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu&mod=nap">Lịch sử nạp tiền</a></div>' .
				'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu">Lịch sử giao dịch</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=lichsu&mod=nap');
		break;

	case 'xemthe':
		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='2' AND `type_form`='1' AND `user_id`=" . $systemUser->id . " AND `id`='$id'");
		if ($req->rowCount()) {
			$res = $req->fetch();
			$res_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
			$res_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $res['value'])->fetch();
			$user = $tools->getUser($res['user_id']);
			echo '<div class="mrt-code card shadow--2dp" style="z-index: 3;"><div class="phdr"><h4>Yêu cầu rút thẻ</h4></div>';
			echo '<div class="card__actions">
				+ Nhà mạng: <strong>' . $res_type['name'] . '</strong><br />
				+ Mệnh giá: <strong>' . $tools->balans($res_price['price']) . ' VNĐ</strong><br />';
			if (!$res['status']) {
				echo '<strong class="red">Chờ xét duyệt</strong><br /><br />';
			} elseif ($res['status'] == 1) {
				echo '<strong class="red">Bị hủy</strong><br /><br />';
			} elseif ($res['status'] == 2) {
				echo '+ Mã thẻ: <strong>' . $tools->checkout($res['number']) . '</strong><br />
					+ Seri: <strong>' . $tools->checkout($res['seri']) . '</strong><br />';
				echo '<strong class="wgreen">Đã thanh toán</strong><br /><br />';
			}
			echo '<span class="red fsize--12">+ ' . $tools->thoigian($res['time']) . '</span>';
			echo '</div>' .
				'</div>';

			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu&mod=rut">Lịch sử rút tiền</a></div>' .
				'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu">Lịch sử giao dịch</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=lichsu&mod=rut');

		break;

	case 'xemck':
		$res = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='2' AND `type_form`='2' AND `user_id`=" . $systemUser->id . " AND `id`='$id'")->fetch();
		if ($res) {
			$banking = $db->query("SELECT * FROM `banking` WHERE `id`=" . $res['acc_id'])->fetch();
			$user = $tools->getUser($res['user_id']);
			echo '<div class="mrt-code card shadow--2dp" style="z-index: 3;"><div class="phdr"><h4>Yêu cầu rút tiền - Chuyển khoản</h4></div>';
			echo '<div class="card__actions">
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
					'<tbody>' .
					'<tr' . (!$banking ? ' class="red"' : '') . '>' .
					'<td class="data-table__cell--non-numeric">' . (!$banking ? '---' : $tools->checkout($banking['name'])) . '</td>' .
					'<td>' . (!$banking ? '---' : $tools->checkout($banking['branch'])) . '</td>' .
	 				'<td>' . (!$banking ? '---' : $tools->checkout($banking['owner'])) . '</td>' .
					'<td>' . (!$banking ? '---' : $tools->checkout($banking['number'])) . '</td>' .
		 			'<td>' . $tools->balans($res['value']) . ' VNĐ</td>' .
					'</tr>' .
					'</tbody>' .
					'</table></div>';

			if (!$res['status']) {
				echo '<strong class="red">Chờ xét duyệt</strong><br /><br />';
			} elseif ($res['status'] == 1) {
				echo '<strong class="red">Bị hủy</strong><br /><br />';
			} elseif ($res['status'] == 2) {
				echo '<strong class="wgreen">Đã thanh toán cho thành viên</strong><br /><br />';
			}
			echo '<span class="red fsize--12">+ ' . $tools->thoigian($res['time']) . '</span>';
			echo '</div>' .
				'</div>';

			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu&mod=rut">Lịch sử rút tiền</a></div>' .
				'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu">Lịch sử giao dịch</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=lichsu&mod=rut');

		break;

	case 'rut_huy':
		$req = $db->query("SELECT * FROM `banking_ipay` WHERE `id`=" . $db->quote($id) . " AND `status`='0' AND `type_active`='2' AND `user_id`=" . $systemUser->id);
		if ($req->rowCount()) {
			$res = $req->fetch();
			echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Hủy giao dịch</h4></div><form method="POST">';
			if ($res['type_form'] == 1) {
				$res_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id'])->fetch();
				$res_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $res['value'])->fetch();
				if (isset($_POST['submit'])) {
					$db->exec("UPDATE `users` SET `balans`=`balans`+" . $db->quote($res_price['price']) . " WHERE `id`=" . $systemUser->id);
					$db->exec("UPDATE `banking_ipay` SET `status`='1' WHERE `id`=" . $id . "");
					header('Location: ?act=lichsu&mod=rut');
				} else {
					echo '<div class="rmenu text-center">Đang chờ xét duyệt</div>
						<div class="card__actions card--border">
						- Bạn đã yêu cầu rút thẻ nạp:<br />
						+ Nhà mạng: <strong>' . $res_type['name'] . '</strong><br />
						+ Mệnh giá: <strong>' . $tools->balans($res_price['price']) . ' VNĐ</strong><br /><br />
						-  Nếu hủy giao dịch, chúng tôi sẽ gửi trả bạn <strong class="red">' . $tools->balans($res_price['price']) . ' VNĐ</strong> vào tài khoản.<br /><br />
						<div class="button-container t10"><button type="submit" name="submit" class="button"><span>Hủy giao dịch</span></button></div>
						</div>';
				}
			} else {
				$res_banking = $db->query("SELECT * FROM `banking` WHERE `id`=" . $res['acc_id'])->fetch();
				if (isset($_POST['submit'])) {
					$db->exec("UPDATE `users` SET `balans`=`balans`+".$db->quote($res['value']) . " WHERE `id`=" . $systemUser->id);
					$db->exec("UPDATE `banking_ipay` SET `status`='1' WHERE `id`=" . $id . "");
					header('Location: ?act=lichsu&mod=rut');
				} else {
					echo '<div class="rmenu text-center">Đang chờ xét duyệt</div>
						<div class="card__actions card--border">
						- Bạn đã yêu cầu rút tiền về tài khoản ngân hàng:<br />
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
						'</table></div><br />

						-  Nếu hủy giao dịch, chúng tôi sẽ gửi trả bạn <strong class="red">' . $tools->balans($res['value']) . ' VNĐ</strong> vào tài khoản.<br /><br />
						<div class="button-container t10"><button type="submit" name="submit" class="button"><span>Hủy giao dịch</span></button></div>
						</div>';
				}
			}
			echo '</form></div>';
			echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu&mod=rut">Lịch sử rút</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		} else
			header('Location: ?act=lichsu&mod=rut');

		break;

	case 'rut':
		$reqNap    = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='2' AND `type_form`='1' AND `user_id`=" . $systemUser->id . " ORDER BY `id` DESC ");
		$reqChuyen = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='2' AND `type_form`='2' AND `user_id`=" . $systemUser->id . " ORDER BY `id` DESC ");
		
		$countNap = $reqNap->rowCount();
		$countChuyen = $reqChuyen->rowCount();

		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Lịch sử rút tiền</h4></div>' . (!$countChuyen && !$countNap ? '' : '</div>');
		if ($countNap) {
			echo '<div class="mrt-code card shadow--4dp" style="margin-bottom: 0;"><div class="phdr forum-title"><span>Rút thẻ nạp</span></div></div>';
			echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
				'<thead>' .
				'<tr>' .
				'<th class="data-table__cell--non-numeric">Nhà mạng</th>' .
				'<th>Mệnh giá</th>' .
				'<th>Mã thẻ</th>' .
				'<th>Sêri</th>' .
				'<th>Tình trạng</th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>';
			while ($res = $reqNap->fetch()) {
				$req_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id']);
				$req_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $res['value']);
				if ($req_type->rowCount()) {
					$res_type = $req_type->fetch();
					$res_price = $req_price->fetch();

					echo '<tr' . ($res['status'] == 1 ? ' class="red"' : '') . '>' .
						'<td class="data-table__cell--non-numeric">' . $tools->checkout($res_type['name']) . '</td>' .
	 					'<td>' . $tools->balans($res_price['price']) . ' VNĐ</td>' .
						'<td>' . (empty($res['number']) ? '---' : $tools->checkout($res['number'])) . '</td>' .
	 					'<td>' . (empty($res['seri']) ? '---' : $tools->checkout($res['seri'])) . '</td>' .
	 					'<td>';
	 				if(!$res['status'])
		 				echo '<span class="red">Chờ xét duyệt</span>';
		 			elseif ($res['status'] == 1)
		 				echo '<span class="red">Bị hủy</span>';
					else
	 					echo '<span class="wgreen">Đã duyệt</span>';
	 				echo (!$res['status'] ? '<a href="?act=lichsu&mod=rut_huy&id=' . $res['id'] . '"><i class="material-icons">&#xE14C;</i></a>' : '') . '</td>' .
					    '</tr>';
				}
			}
			echo '</tbody>' .
				'</table></div><br />';
		}

		if ($countChuyen) {
			echo '<div class="mrt-code card shadow--4dp" style="margin-bottom: 0;"><div class="phdr forum-title"><span>Chuyển khoản ngân hàng</span></div></div>';
			echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
				'<thead>' .
				'<tr>' .
				'<th class="data-table__cell--non-numeric">Ngân hàng</th>' .
				'<th>Số tài khoản</th>' .
				'<th>Rút</th>' .
				'<th>Tình trạng</th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>';
			while ($res = $reqChuyen->fetch()) {
				$banking = $db->query("SELECT * FROM `banking` WHERE `id`=" . $res['acc_id']);
				$res_banking = $banking->fetch();
				echo '<tr' . (!$res_banking ? ' class="red"' : '') . '>' .
					'<td class="data-table__cell--non-numeric">' . (!$res_banking ? '---' : $tools->checkout($res_banking['name'])) . '</td>' .
	 				'<td>' . (!$res_banking ? '---' : $tools->checkout($res_banking['number'])) . '</td>' .
	 				'<td>' . (!$res['value'] ? '...' : $tools->balans($res['value'])) . ' VNĐ</td>' .
	 				'<td>';

	 			if(!$res['status'])
		 			echo '<span class="red">Chờ xét duyệt</span>';
		 		elseif ($res['status'] == 1)
		 			echo '<span class="red">Bị hủy</span>';
				else
	 				echo '<span class="wgreen">Đã duyệt</span>';

	 			echo (!$res['status'] ? '<a href="?act=lichsu&mod=rut_huy&id=' . $res['id'] . '"><i class="material-icons">&#xE14C;</i></a>' : '') . '</td>' .
	 				'</td>' .
					   '</tr>';
			}
			echo '</tbody>' .
				'</table></div><br />';
		}
		if (!$countChuyen && !$countNap) {
			echo '<div class="rmenu text-center">Không có giao dịch</div></div>';
		}



		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu">Lịch sử giao dịch</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;

	case 'nap':
		$reqNap    = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='1' AND `type_form`='1' AND `user_id`=" . $systemUser->id . " ORDER BY `id` DESC ");
		$reqChuyen = $db->query("SELECT * FROM `banking_ipay` WHERE `type_active`='1' AND `type_form`='2' AND `user_id`=" . $systemUser->id . " ORDER BY `id` DESC ");

		$countNap    = $reqNap->rowCount();
		$countChuyen = $reqChuyen->rowCount();

		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Lịch sử Nạp</h4></div>' . (!$countChuyen && !$countNap ? '' : '</div>');
		if ($countNap) {
			echo '<div class="mrt-code card shadow--4dp" style="margin-bottom: 0;"><div class="phdr forum-title"><span>Thẻ nạp</span></div></div>';
			echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
				'<thead>' .
				'<tr>' .
				'<th class="data-table__cell--non-numeric">Nhà mạng</th>' .
				'<th>Mã thẻ</th>' .
				'<th>Sêri</th>' .
				'<th>Mệnh giá</th>' .
				'<th>Tình trạng</th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>';
			while ($res = $reqNap->fetch()) {
				$req_price = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id']);
				if ($req_price->rowCount()) {
					$res_price = $req_price->fetch();
					echo '<tr' . ($res['status'] == 1 ? ' class="red"' : '') . '>' .
						'<td class="data-table__cell--non-numeric">' . $tools->checkout($res_price['name']) . '</td>' .
						'<td>' . $tools->checkout($res['number']) . '</td>' .
	 					'<td>' . $tools->checkout($res['seri']) . '</td>' .
	 					'<td>' . (!$res['value'] ? '---': $tools->balans($res['value']) . ' VNĐ') .
	 					'</td>' .
		 				'<td>';
		 			if(!$res['status'])
		 				echo '<span class="red">Chờ xét duyệt</span>';
		 			elseif ($res['status'] == 1)
		 				echo '<span class="red">Thẻ sai</span>';
					else
	 					echo '<span class="wgreen">OK</span>';

		 			echo '</td>' .
					    '</tr>';
				}
			}
			echo '</tbody>' .
				'</table></div><br />';
		}

		if ($countChuyen) {
			echo '<div class="mrt-code card shadow--4dp" style="margin-bottom: 0;"><div class="phdr forum-title"><span>Chuyển khoản ngân hàng</span></div></div>';
			echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
				'<thead>' .
				'<tr>' .
				'<th class="data-table__cell--non-numeric">Nhà mạng</th>' .
				'<th>Mã thẻ</th>' .
				'<th>Sêri</th>' .
				'<th>Mệnh giá</th>' .
				'</tr>' .
				'</thead>' .
				'<tbody>';
			while ($res = $reqChuyen->fetch()) {
				$req_price = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $res['acc_id']);
				if ($req_price->rowCount()) {
					$res_price = $req_price->fetch();
					echo '<tr' . (!$res['status'] ? ' class="red"' : '') . '>' .
						'<td class="data-table__cell--non-numeric">' . $tools->checkout($res_price['name']) . '</td>' .
						'<td>' . $tools->checkout($res['number']) . '</td>' .
	 					'<td>' . $tools->checkout($res['seri']) . '</td>' .
	 					'<td>' . (!$res['value'] ? '...' : $tools->balans($res['value'])) . '</td>' .
					    '</tr>';
				}
			}
			echo '</tbody>' .
				'</table></div><br />';
		}
		if (!$countChuyen && !$countNap) {
			echo '<div class="rmenu text-center">Không có giao dịch</div></div>';
		}
		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=lichsu">Lịch sử giao dịch</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;
	
	default:
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Lịch sử giao dịch</h4></div>' .
			'<div class="card__actions"><img class="icon" src="/assets/images/mt.gif"><a href="?act=lichsu&mod=nap">Lịch sử Nạp</a></div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=lichsu&mod=rut">Lịch sử Rút</a></div>' .
			'</div>';

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';
		break;
}
