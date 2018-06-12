<?php

if ($systemUser->balans < 100000)
{
	echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Rút tiền</h4></div>' .
		'<div class="rmenu text-center">Bạn phải có ít nhất 100.000 VNĐ trong tài khoản</div>' .
		'</div>';

	echo '<div class="mrt-code card shadow--2dp">' .
		'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
		'</div>';
	exit();
}

switch ($mod) {
	case 'banking':
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Chuyển khoản ngân hàng</h4></div>';
		if (isset($_POST['submit'])) {
			$bank  = isset($_POST['banking']) ? abs(intval($_POST['banking'])) : 0;
			$price = isset($_POST['price'])   ? abs(intval($_POST['price']))   : 0;
			if ($bank && $price && $price > 5000) {
				$total = $price + 5000;
				if ($systemUser->balans < $total) {
					echo '<div class="rmenu text-center">' .
						    'Bạn không đủ tiền.!' .
							'</div>' .
							'</div>';
				} else {
					$db->exec("UPDATE `users` SET `balans`=`balans`-" . $db->quote($total) . " WHERE `id`=" . $systemUser->id);
					$db->prepare('
						INSERT INTO `banking_ipay` SET
						  `type_active` = \'2\',
						  `type_form`   = \'2\',
						  `user_id`     = ?,
						  `acc_id`      = ?,
						  `value`       = ?,
						  `time`        = ?
						')->execute([
						 	$systemUser->id,
						    $bank,
						    $price,
						    time(),
						]);

					echo '<div class="gmenu text-center">' .
						'Đã gửi yêu cầu rút tiền đến Quản Trị Viên.!' .
						'</div>' .
						'<div class="card__actions">' .
						'<div class="button-container">' .
						'<a href="?act=ruttien&mod=banking"><button class="button" type="submit" name="submit"><span>Tiếp tục</span></button></a>' .
						'</div>' .
						'</div>' .
						'</div>';
				}
			} else
				header('Location: ?act=ruttien&mod=banking');
		} else {
			$count_banking = $db->query("SELECT COUNT(*) FROM `banking` WHERE `user_id` = '" . $systemUser->id . "'")->fetchColumn();
			if (!$count_banking) {
				echo '<div class="rmenu text-center">Bạn chưa thêm tài khoản ngân hàng ....</div>' .
					'<div class="card__actions"><div class="button-container"><a href="?act=set&mod=add"><button class="button"><span>Thêm ngay</span></button></a></div></div>' .
					'</div>';
			} else {
				echo '<div class="card__actions">Để rút tiền từ <strong>SoiCauLoDe.Club</strong> bạn chọn tài khoản ngân hàng mà bạn muốn rút về.<br/><br />
					- Bạn có: <strong class="wgreen">' . $tools->balans($systemUser->balans) . ' VNĐ</strong><br />
					- Phí rút: <strong class="red">5.000 VNĐ</strong>.<br /><br />
					> Rút tối đa: <strong class="red">' . ($systemUser->balans >= 10000 ? $tools->balans($systemUser->balans - 5000) : '0') . ' VNĐ</strong><br />
					> Rút tối thiểu: <strong class="red">' . ($systemUser->balans >= 10000 ? $tools->balans(5000) : '0') . ' VNĐ</strong>' .
					'</div>' .
					'</div>';

				if ($systemUser->balans >= 10000) {
					$req = $db->query("SELECT * FROM `banking` WHERE `user_id`=" . $systemUser->id);
					if ($req->rowCount()) {
						echo '<div class="shadow--2dp"><form method="POST">' .
						'<div class="scroll_x"><table class="data-table data-table--selectable">' .
							'<thead>' .
							'<tr>' .
							'<th class="data-table__select"></th>' .
							'<th class="data-table__cell--non-numeric">Ngân hàng</th>' .
							'<th>Chi nhánh</th>' .
							'<th>Chủ tài khoản</th>' .
							'<th>Số tài khoản</th>' .
							'</tr>' .
							'</thead>' .
							'<tbody>';
						$i=1;
						while ($res = $req->fetch()) {
							echo '<tr>' .
								'<td class="data-table__select">' .
								'<div class="form-radio">' .
								'<div class="radio">' .
								'<label><input type="radio" value="' . $res['id'] . '" name="banking"' . ($i == 1 ? ' checked="checked"' : '') . '><i class="helper"></i></label>' .
								'</div>' .
								'</div>' .
								'</td>' .
						        '<td class="data-table__cell--non-numeric">' . $tools->checkout($res['name']) . '</td>' .
						        '<td>' . $tools->checkout($res['branch']) . '</td>' .
						        '<td>' . $tools->checkout($res['owner']) . '</td>' .
						        '<td>' . $tools->checkout($res['number']) . '</td>' .
						      	'</tr>';
						    $i++;
						}
						echo '</tbody>' .
							'</table></div>' .
							'<div class="mrt-code card">' .
							'<div class="card__actions card--border">' .
							'<div class="form-group">' .
						    '<input type="number" name="price" value="" min="5000" max="' . ($systemUser->balans - 5000) . '" required="required" />' .
						    '<label class="control-label" for="input">Nhập số tiền</label><i class="bar"></i>' .
						    '</div>' .
							'<div class="button-container t10">' .
							'<button class="button" type="submit" name="submit"><span>Rút</span></button>' .
							'</div>' .
							'</div>' .
							'</div>';
						echo '</form></div>';
					}
				} else {
					echo '<div class="rmenu text-center">Có tối thiểu 10.000 VNĐ trong tài khoản.</div><br />';
				}
			}
		}

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=ruttien">Rút tiền</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';

		break;

	case 'thenap':
		$req = $db->query("SELECT * FROM `card_type` ");
		echo '<div class="mrt-code card shadow--2dp" style="z-index: 3;"><div class="phdr"><h4>Rút tiền - Thẻ nạp</h4></div>';
		if (!$req->rowCount()) {
			echo '<div class="card__actions">Đang cập nhật thông tin...</div>';
		} else {
			if ($set) {
				$req_price = $db->query("SELECT * FROM `card_price` WHERE `card_id`=" . $db->quote($set) . " ORDER BY `price` ASC");
				if (!$req_price->rowCount()) {
					echo '<div class="card__actions">Đang cập nhật thông tin...</div>';
				} else {
					if (isset($_POST['submit'])) {
						$card_type  = isset($_POST['card_type'])  ? abs(intval($_POST['card_type']))  : 0;
						$card_price = isset($_POST['card_price']) ? abs(intval($_POST['card_price'])) : 0;
						if ($card_type && $card_price) {
							$res_price = $db->query("SELECT * FROM `card_price` WHERE `id`=" . $card_price)->fetch();
							$total = $res_price['price'] + ($res_price['price'] * 4 / 100);

							if ($systemUser->balans < $total) {
								echo '<div class="rmenu text-center">' .
						        	'Bạn không đủ tiền.!' .
									'</div>' .
									'</div>';
							} else {
								$db->exec("UPDATE `users` SET `balans`=`balans`-" . $db->quote($total) . " WHERE `id`=" . $systemUser->id);
								$db->prepare('
						          	INSERT INTO `banking_ipay` SET
						           	  `type_active` = \'2\',
						           	  `type_form`   = \'1\',
						           	  `user_id`     = ?,
						           	  `acc_id`      = ?,
						           	  `value`       = ?,
						           	  `time`        = ?
						        ')->execute([
						        	$systemUser->id,
						            $card_type,
						            $card_price,
						            time(),
						        ]);

						        echo '<div class="gmenu text-center">' .
						        	'Đã gửi yêu cầu rút thẻ đến Quản Trị Viên.!' .
									'</div>' .
									'<div class="card__actions">' .
						        	'<div class="button-container">' .
								    '<a href="?act=ruttien&mod=thenap&set=' . $tools->checkout($set) . '"><button class="button" type="submit" name="submit"><span>Tiếp tục</span></button></a>' .
								    '</div>' .
									'</div>';
							}
						}
					} else {
						$req_type = $db->query("SELECT * FROM `card_type` WHERE `id`=" . $db->quote($set))->fetch();
						echo '<div class="card__actions">Chiết khẩu ra: 4% giá trị thẻ nạp.</div>';
						echo '<div class="card__actions">Bạn đang chọn nhà mạng <strong class="red">' . $req_type['name'] .'</strong></div>';
						

						echo '<div class="card__actions card--border"> - Chọn mệnh giá thẻ.<br />' .
							'<form name="form" method="post">' .
							'<div class="">' .
						    '<select name="card_price">';
						$i=0;
						while ($res_price = $req_price->fetch()) {
						    echo '<option value="' . $res_price['id'] . '"' . (!$i ? ' selected="selected"' : '') .'>' . $tools->balans($res_price['price']) . ' VNĐ</option>';
							++$i;
						}
						echo '</select></div>' .
							'<input type="hidden" name="card_type" value="' . $tools->checkout($set) . '">';

						echo '<div class="button-container t10">' .
						    '<button class="button" type="submit" name="submit"><span>Rút</span></button>' .
						    '</div>' .
						    '</form>' .
						    '</div>';

					}
				}
			} else {
				echo '<div class="card__actions">- Chọn nhà mạng bạn muốn...</div>';
				$i=0;
				while ($res = $req->fetch()) {
					echo '<div class="card__actions card--border">' .
						'<img class="icon" src="/assets/images/mt.gif"><a href="?act=ruttien&mod=thenap&set=' . $res['id'] . '">' . $tools->checkout($res['name']) . '</a>' .
						'</div>';
					++$i;
				}
			}
		}
		echo '</div>' .
			'<div class="mrt-code card shadow--2dp">' .
			($set ? '<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=ruttien&mod=thenap">Chọn nhà mạng</a></div>' : '') .
			'<div class="card__actions' . ($set ? ' card--border' : '') . '"><img class="icon" src="/assets/images/back.png"><a href="?act=ruttien">Rút tiền</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';

		break;
	
	default:
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Rút tiền</h4></div>' .
			'<div class="card__actions"> - Chọn hình thức mà bạn muốn rút tiền.?</div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=ruttien&mod=banking">Chuyển khoản ngân hàng</a></div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=ruttien&mod=thenap">Thẻ nạp</a></div>' .
			'</div>';

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';

		break;
}
