<?php

switch ($mod) {
	case 'banking':
		$count_banking = $db->query("SELECT COUNT(*) FROM `banking` WHERE `user_id` = '0'")->fetchColumn();
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Chuyển khoản ngân hàng</h4></div>';
		if (!$count_banking) {
			echo '<div class="card__actions">Đang cập nhật thông tin...</div></div>';
		} else {
			echo '<div class="card__actions">Để nạp tiền vào <strong>SoiCauLoDe.Club</strong> bạn chọn một trong các tài khoản sau với nội dung chuyển khoản<br/><br/>
				<strong>Nội dung</strong>: Chuyển khoản cho tk ID-' . $systemUser->id . '</div>'.
				'</div>';
				$req = $db->query("SELECT * FROM `banking` WHERE `user_id` = '0'");

			if ($req->rowCount()) {
				echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
					'<thead>' .
					'<tr>' .
					'<th class="data-table__cell--non-numeric">Ngân hàng</th>' .
					'<th>Chi nhánh</th>' .
					'<th>Chủ tài khoản</th>' .
					'<th>Số tài khoản</th>' .
					'</tr>' .
					'</thead>' .
					'<tbody>';
				while ($res = $req->fetch()) {
					echo '<tr>' .
				        '<td class="data-table__cell--non-numeric">' . $tools->checkout($res['name']) . '</td>' .
				        '<td>' . $tools->checkout($res['branch']) . '</td>' .
				        '<td>' . $tools->checkout($res['owner']) . '</td>' .
				        '<td>' . $tools->checkout($res['number']) . '</td>' .
				      	'</tr>';
				}
				echo '</tbody>' .
					'</table></div><br />';
			}
		}

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=naptien">Nạp tiền</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';

		break;

	case 'thenap':
		$req = $db->query("SELECT * FROM `card_type` ");
		echo '<div class="mrt-code card shadow--2dp" style="z-index: 3;"><div class="phdr"><h4>Nạp tiền</h4></div>';
		if (!$req->rowCount()) {
			echo '<div class="card__actions">Đang cập nhật thông tin...</div>';
		} else {
			if (isset($_POST['submit'])) {
				$type   = isset($_POST['type'])   ? abs(intval($_POST['type']))   : 0;
				$number = isset($_POST['number']) ? abs(intval($_POST['number'])) : 0;
				$seri   = isset($_POST['seri'])   ? abs(intval($_POST['seri']))   : 0;
				if ($type && $number && $seri) {
					$check = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `type_active`='1' AND `number`=" . $db->quote($number) . " AND `seri`=" . $db->quote($seri) . " AND `acc_id`=" . $db->quote($type))->fetchColumn();
					if (!$check) {
						$db->prepare('
				          	INSERT INTO `banking_ipay` SET
				           	  `type_active` = \'1\',
				           	  `type_form`   = \'1\',
				           	  `user_id`     = ?,
				           	  `number`      = ?,
				           	  `seri`        = ?,
				           	  `acc_id`      = ?,
				           	  `time`        = ?
				        ')->execute([
				        	$systemUser->id,
				            $number,
				            $seri,
				            $type,
				            time(),
				        ]);

				        echo '<div class="gmenu text-center">' .
				        	'Đã gửi yêu cầu nạp thẻ đến Quản Trị Viên.!' .
							'</div>' .
							'<div class="card__actions">' .
				        	'<div class="button-container">' .
						    '<a href="?act=naptien&mod=thenap"><button class="button" type="submit" name="submit"><span>Tiếp tục</span></button></a>' .
						    '</div>' .
							'</div>';
					} else
					header('Location: ?act=naptien&mod=thenap');
				} else
					header('Location: ?act=naptien&mod=thenap');
			} else {
				echo '<div class="card__actions">Chiết khẩu vào: 4% giá trị thẻ nạp</div>';
				echo '<div class="card__actions card--border">' .
					'<form name="form" method="post">' .
					'<div class="form-group">' .
				    '<select name="type">';
				$i=0;
				while ($res = $req->fetch()) {
					echo '<option value="' . $res['id'] . '"' . (!$i ? ' selected="selected"' : '') . '>' . $res['name'] . '</option>';
					++$i;
				}
				echo '</select></div>' .
				    '<div class="form-group">' .
				    '<input type="number"  min="0" name="number" value="" required="required" />' .
				    '<label class="control-label" for="input">Mã thẻ</label><i class="bar"></i>' .
				    '</div>' .
				    '<div class="form-group">' .
				    '<input type="number" min="0" name="seri" value="" required="required" />' .
				    '<label class="control-label" for="input">Số seri</label><i class="bar"></i>' .
				    '</div>' .
					'<div class="button-container t10">' .
				    '<button class="button" type="submit" name="submit"><span>Nạp</span></button>' .
				    '</div>' .
				    '</form>' .
				    '</div>';
			}
		}
		echo '</div>' .
			'<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=naptien">Nạp tiền</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';

		break;
	
	default:
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Nạp tiền</h4></div>' .
			'<div class="card__actions"> - Chọn hình thức mà bạn muốn nạp tiền.?</div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=naptien&mod=banking">Chuyển khoản ngân hàng</a></div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=naptien&mod=thenap">Thẻ nạp</a></div>' .
			'</div>';

		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';

		break;
}
