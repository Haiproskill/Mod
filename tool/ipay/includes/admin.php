<?php

if ($systemUser->id != 1) {
	header('Location: /?err');
    exit;
}

switch ($mod) {
	case 'dellprice':
		$req = $db->query("SELECT * FROM `card_price` WHERE `id` = " . $db->quote($id));
		if ($req->rowCount()) {
			echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Xoá mệnh giá thẻ nạp</h4></div>';
			if (isset($_POST['submit'])) {
				$db->exec("DELETE FROM `card_price` WHERE `id` = " .  $db->quote($id));
				echo '<div class="gmenu text-center">Xóa thành công.!!</div>' .
					'</div>';
			} else {
				echo '<div class="rmenu text-center">Bạn thực sự muốn xóa mệnh giá này.?</div>' .
					'<div class="card__actions">' .
						'<form name="form" method="post">' .
							'<div class="button-container">' .
								'<button class="button" type="submit" name="submit"><span>Xóa</span></button>' .
							'</div>' .
						'</form>' .
					'</div>' .
				'</div>';

			}
			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addcard">Thêm nhà mạng</a></div>' .
				'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addprice">Thêm mệnh giá thẻ nạp</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?act=admin">Quản lý IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=admin&mod=addprice');

		break;

	case 'addprice':
	echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thêm mệnh giá thẻ nạp</h4></div>';
		if (isset($_POST['submit'])) {
			$card_id    = isset($_POST['card_id'])    ? abs(intval($_POST['card_id']))    : 0;
			$card_price = isset($_POST['card_price']) ? abs(intval($_POST['card_price'])) : 0;

			if ($card_id && $card_price) {
				$check = $db->query("SELECT COUNT(*) FROM `card_price` WHERE `card_id`=" . $db->quote($card_id) . " AND `price` = " . $db->quote($card_price))->fetchColumn();
				if (!$check) {
					$db->prepare('
			          INSERT INTO `card_price` SET
			           `card_id` = ?,
			           `price`   = ?
			        ')->execute([
			            $card_id,
			            $card_price,
			        ]);

			        echo '<div class="gmenu text-center">' .
			        	'Thêm thành công.!' .
						'</div>';
				} else
					header('Location: ?act=admin&mod=addprice');
			} else
				header('Location: ?act=admin&mod=addprice');
		} else {
			echo '</div>';
			$req = $db->query("SELECT * FROM `card_type` ");
			while ($res = $req->fetch()) {
				echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
					'<thead>' .
					'<tr>' .
					'<th class="data-table__cell--non-numeric">' . $tools->checkout($res['name']) . '</th>' .
					'<th></th>' .
					'</tr>' .
					'</thead>' .
					'<tbody>';

					$req_price = $db->query("SELECT * FROM `card_price` WHERE `card_id`=" . $res['id'] . " ORDER BY `price` ASC");
					if ($req_price->rowCount()) {
						while ($res_price = $req_price->fetch()) {
							echo '<tr>' .
							    '<td class="data-table__cell--non-numeric">' . $tools->balans($res_price['price']) . ' VNĐ</td>' .
							    '<td class="op">' .
						        '<a href="?act=admin&mod=dellprice&id=' . $res_price['id'] . '"><i class="material-icons">&#xE14C;</i></a>' .
						        '</td>' .
						      	'</tr>';
						}
					}
				echo '</tbody>' .
					'</table></div><br />';
			}
			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions card--border">' .
				'<form name="form" method="post">' .
				'<div class="form-group">' .
			    '<select name="card_id">';
			   $req = $db->query("SELECT * FROM `card_type` ");
			$i=0;
			while ($ress = $req->fetch()) {
			    echo '<option value="' . $ress['id'] . '"' . (!$i ? ' selected="selected"' : '') .'>' . $tools->checkout($ress['name']) . '</option>';
			    $i++;
			}
			echo '</select></div>' .
			    '<div class="form-group">' .
			    '<input type="number" name="card_price" value="" required="required" />' .
			    '<label class="control-label" for="input">Mệnh giá</label><i class="bar"></i>' .
			    '</div>';

			echo '<div class="button-container t10">' .
			    '<button class="button" type="submit" name="submit"><span>' . _t('Add') . '</span></button>' .
			    '</div>' .
			    '</form>' .
			    '</div>';
		}
		echo '</div>';

		echo '<div class="mrt-code card shadow--2dp">' .
			(isset($_POST['submit']) ? '<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addprice">Thêm mệnh giá thẻ nạp</a></div>' : '') .
			'<div class="card__actions' . (isset($_POST['submit']) ? ' card--border' : '') . '"><img class="icon" src="/assets/images/mt.gif"><a href="?act=admin&mod=addcard">Thêm nhà mạng</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?act=admin">Quản lý IPay</a></div>' .
			'</div>';

		break;

	case 'editcard':
		$req = $db->query("SELECT * FROM `card_type` WHERE `id` = " . $db->quote($id));
		if ($req->rowCount()) {
			$res = $req->fetch();
			echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Chỉnh sửa nhà mạng</h4></div>';
			if (isset($_POST['submit'])) {
				$name   = isset($_POST['name']) ? trim($_POST['name']) : 0;
				if ($name) {
					$db->exec("UPDATE `card_type` SET `name` = " . $db->quote($name) . " WHERE `id` = '$id'");
					echo '<div class="gmenu text-center">Lưu thành công.!!</div>' .
						'</div>';
				} else
					header('Location: ?act=admin&mod=editcardt&id=' . $id);
			} else {
				echo '<div class="card__actions card--border">' .
					'<form name="form" method="post">' .
				    '<div class="form-group">' .
				    '<input type="text" name="name" value="' . $tools->checkout($res['name']) . '" required="required" />' .
				    '<label class="control-label" for="input">Nhà mạng</label><i class="bar"></i>' .
				    '</div>' .
					'<div class="button-container t10">' .
				    '<button class="button" type="submit" name="submit"><span>Lưu</span></button>' .
				    '</div>' .
				    '</form>' .
				    '</div>';
			}
			echo '</div>' .
				'<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addcard">Thêm nhà mạng</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?act=admin">Quản lý IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=admin&mod=addcard');

		break;

	case 'dellcard':
		$req = $db->query("SELECT * FROM `card_type` WHERE `id` = " . $db->quote($id));
		if ($req->rowCount()) {
			echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Xoá nhà mạng</h4></div>';
			if (isset($_POST['submit'])) {
				$db->exec("DELETE FROM `card_type` WHERE `id` = " .  $db->quote($id));
				$db->exec("DELETE FROM `card_price` WHERE `card_id` = " .  $db->quote($id));
				echo '<div class="gmenu text-center">Xóa thành công.!!</div>' .
					'</div>';
			} else {
				echo '<div class="rmenu text-center">Bạn thực sự muốn xóa nhà mạng này.?</div>' .
					'<div class="card__actions">' .
						'<form name="form" method="post">' .
							'<div class="button-container">' .
								'<button class="button" type="submit" name="submit"><span>Xóa</span></button>' .
							'</div>' .
						'</form>' .
					'</div>' .
				'</div>';

			}
			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addcard">Thêm nhà mạng</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?act=admin">Quản lý IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=admin&mod=addcard');

		break;

	case 'addcard':
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thêm nhà mạng</h4></div>';
		if (isset($_POST['submit'])) {
			$name   = isset($_POST['name']) ? trim($_POST['name']) : 0;
			if ($name) {
				$check = $db->query("SELECT COUNT(*) FROM `card_type` WHERE `name`=" . $db->quote($name))->fetchColumn();
				if (!$check) {
					$db->prepare('
			          INSERT INTO `card_type` SET
			           `name` = ?
			        ')->execute([
			            $name,
			        ]);

			        echo '<div class="gmenu text-center">' .
			        	'Thẻ nạp loại <strong>' . $tools->checkout($name, 2, 2) . '</strong> thêm thành công.!' .
						'</div>';
				} else
					header('Location: ?act=admin&mod=addcard');
			} else
				header('Location: ?act=admin&mod=addcard');
		} else {
			$req = $db->query("SELECT * FROM `card_type` ");
			echo '<div class="card__actions"> - Thêm các nhà mạng mà <strong>SoiCauLoDe.Club</strong> hỗ trợ thẻ nạp từ nhà mạng đó</div>' .
				'</div>';
			if ($req->rowCount()) {
				echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
					'<thead>' .
					'<tr>' .
					'<th class="data-table__cell--non-numeric">Nhà mạng</th>' .
					'<th></th>' .
					'</tr>' .
					'</thead>' .
					'<tbody>';
				while ($res = $req->fetch()) {
					echo '<tr>' .
				        '<td class="data-table__cell--non-numeric">' . $tools->checkout($res['name']) . '</td>' .
				        '<td class="op">' .
				        '<a href="?act=admin&mod=editcard&id=' . $res['id'] . '"><i class="material-icons">&#xE254;</i></a>' .
				        '<a href="?act=admin&mod=dellcard&id=' . $res['id'] . '"><i class="material-icons">&#xE14C;</i></a>' .
				        '</td>' .
				      	'</tr>';
				}
				echo '</tbody>' .
					'</table></div><br />';
			}
			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions card--border">' .
				'<form name="form" method="post">' .
			    '<div class="form-group">' .
			    '<input type="text" name="name" value="" required="required" />' .
			    '<label class="control-label" for="input">Nhà mạng</label><i class="bar"></i>' .
			    '</div>' .
				'<div class="button-container t10">' .
			    '<button class="button" type="submit" name="submit"><span>Thêm</span></button>' .
			    '</div>' .
			    '</form>' .
			    '</div>';
		}
		echo '</div>' .
			'<div class="mrt-code card shadow--2dp">' .
			(isset($_POST['submit']) ? '<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addcard">Thêm nhà mạng</a></div>' : '') .
			'<div class="card__actions' . (isset($_POST['submit']) ? ' card--border' : '') . '"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addprice">Thêm mệnh giá thẻ nạp</a></div>' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?act=admin">Quản lý IPay</a></div>' .
			'</div>';

		break;

	case 'editbanking':
		$req = $db->query("SELECT * FROM `banking` WHERE `id` = " . $db->quote($id));
		if ($req->rowCount()) {
			$res = $req->fetch();
			echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Sửa tài khoản ngân hàng</h4></div>';
			if (isset($_POST['submit'])) {
				$name   = isset($_POST['name'])   ? trim($_POST['name'])   : 0;
				$owner  = isset($_POST['owner'])  ? trim($_POST['owner'])  : 0;
				$branch = isset($_POST['branch']) ? trim($_POST['branch']) : 0;
				$number = isset($_POST['number']) ? trim($_POST['number']) : 0;
				if ($name && $owner && $branch && $number) {
					$db->exec("UPDATE `banking` SET `name` = " . $db->quote($name) . ", `owner` = " . $db->quote($owner) . ", `branch` = " . $db->quote($branch) . ", `number` = " . $db->quote($number) . " WHERE `id` = '$id'");
					echo '<div class="gmenu text-center">Lưu thành công.!!</div>' .
						'</div>';
				} else
					header('Location: ?act=admin&mod=editbanking&id=' . $id);
			} else {
				echo '<div class="card__actions card--border">' .
					'<form name="form" method="post">' .
				    '<div class="form-group">' .
				    '<input type="text" name="name" value="' . $tools->checkout($res['name']) . '" required="required" />' .
				    '<label class="control-label" for="input">Tên ngân hàng</label><i class="bar"></i>' .
				    '</div>' .
				    '<div class="form-group">' .
				    '<input type="text" name="branch" value="' . $tools->checkout($res['branch']) . '" required="required" />' .
				    '<label class="control-label" for="input">Chi nhánh</label><i class="bar"></i>' .
				    '</div>' .
				    '<div class="form-group">' .
				    '<input type="text" name="owner" value="' . $tools->checkout($res['owner']) . '" required="required" />' .
				    '<label class="control-label" for="input">Chủ tài khoản</label><i class="bar"></i>' .
				    '</div>' .
				    '<div class="form-group">' .
				    '<input type="number" name="number" value="' . $tools->checkout($res['number']) . '" required="required" />' .
				    '<label class="control-label" for="input">Số tài khoản</label><i class="bar"></i>' .
				    '</div>'.
					'<div class="button-container t10">' .
				    '<button class="button" type="submit" name="submit"><span>Lưu</span></button>' .
				    '</div>' .
				    '</form>' .
				    '</div>';
			}
			echo '</div>' .
				'<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addbanking">Thêm tài khản ngân hàng</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?act=admin">Quản lý IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=admin&mod=addbanking');

		break;

	case 'dellbanking':
		$req = $db->query("SELECT * FROM `banking` WHERE `id` = " . $db->quote($id));
		if ($req->rowCount()) {
			echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Xoá tài khoản ngân hàng</h4></div>';
			if (isset($_POST['submit'])) {
				$db->exec("DELETE FROM `banking` WHERE `id` = " .  $db->quote($id));
				echo '<div class="gmenu text-center">Xóa thành công.!!</div>' .
					'</div>';
			} else {
				echo '<div class="rmenu text-center">Bạn thực sự muốn xóa tài khoản ngân hàng này.?</div>' .
					'<div class="card__actions">' .
						'<form name="form" method="post">' .
							'<div class="button-container">' .
								'<button class="button" type="submit" name="submit"><span>Xóa</span></button>' .
							'</div>' .
						'</form>' .
					'</div>' .
				'</div>';

			}
			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addbanking">Thêm tài khản ngân hàng</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?act=admin">Quản lý IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=admin&mod=addbanking');

		break;

	case 'addbanking':
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thêm tài khoản ngân hàng</h4></div>';
		if (isset($_POST['submit'])) {

			$name   = isset($_POST['name'])   ? trim($_POST['name'])   : 0;
			$owner  = isset($_POST['owner'])  ? trim($_POST['owner'])  : 0;
			$branch = isset($_POST['branch']) ? trim($_POST['branch']) : 0;
			$number = isset($_POST['number']) ? trim($_POST['number']) : 0;
			if ($name && $owner && $branch && $number) {
				$check = $db->query("SELECT COUNT(*) FROM `banking` WHERE `user_id`='0' AND `number` = " . $db->quote($number))->fetchColumn();
				if (!$check) {
					$db->prepare('
			          INSERT INTO `banking` SET
			           `name` = ?,
			           `owner` = ?,
			           `branch` = ?,
			           `number` = ?
			        ')->execute([
			            $name,
			            $owner,
			            $branch,
			            $number,
			        ]);

			        echo '<div class="gmenu text-center">' .
			        	'Thêm tài khoản ngân hàng thành công.!' .
						'</div>';
				} else
					header('Location: ?act=admin&mod=addbanking');
			} else
				header('Location: ?act=admin&mod=addbanking');
		} else {
			$req = $db->query("SELECT * FROM `banking` WHERE `user_id` = '0'");
			echo '<div class="card__actions"> - Quản trị thêm tài khoản ngân hàng để khách hàng nạp tiền vào đó, sau đó sẽ cộng tiền trên web cho người nạp.</div>' .
				'</div>';
			if ($req->rowCount()) {
				echo '<div class="scroll_x shadow--2dp"><table class="data-table data-table--selectable">' .
					'<thead>' .
					'<tr>' .
					'<th class="data-table__cell--non-numeric">Ngân hàng</th>' .
					'<th>Chi nhánh</th>' .
					'<th>Chủ tài khoản</th>' .
					'<th>Số tài khoản</th>' .
					'<th></th>' .
					'</tr>' .
					'</thead>' .
					'<tbody>';
				while ($res = $req->fetch()) {
					echo '<tr>' .
				        '<td class="data-table__cell--non-numeric">' . $res['name'] . '</td>' .
				        '<td>' . $res['branch'] . '</td>' .
				        '<td>' . $res['owner'] . '</td>' .
				        '<td>' . $res['number'] . '</td>' .
				        '<td class="op"><a href="?act=admin&mod=editbanking&id=' . $res['id'] . '"><i class="material-icons">&#xE254;</i></a>' .
				        '<a href="?act=admin&mod=dellbanking&id=' . $res['id'] . '"><i class="material-icons">&#xE14C;</i></a></td>' .
				      	'</tr>';
				}
				echo '</tbody>' .
					'</table></div><br />';
			}
			echo '<div class="mrt-code card shadow--2dp">' .
				'<div class="card__actions card--border">' .
				'<form name="form" method="post">' .
			    '<div class="form-group">' .
			    '<input type="text" name="name" value="" required="required" />' .
			    '<label class="control-label" for="input">Tên ngân hàng</label><i class="bar"></i>' .
			    '</div>' .
			    '<div class="form-group">' .
			    '<input type="text" name="branch" value="" required="required" />' .
			    '<label class="control-label" for="input">Chi nhánh</label><i class="bar"></i>' .
			    '</div>' .
			    '<div class="form-group">' .
			    '<input type="text" name="owner" value="" required="required" />' .
			    '<label class="control-label" for="input">Chủ tài khoản</label><i class="bar"></i>' .
			    '</div>' .
			    '<div class="form-group">' .
			    '<input type="number" name="number" value="" required="required" />' .
			    '<label class="control-label" for="input">Số tài khoản</label><i class="bar"></i>' .
			    '</div>';

			echo '<div class="button-container t10">' .
			    '<button class="button" type="submit" name="submit"><span>' . _t('Add') . '</span></button>' .
			    '</div>' .
			    '</form>' .
			    '</div>';
		}
		echo '</div>';

		echo '<div class="mrt-code card shadow--2dp">' .
			(isset($_POST['submit']) ? '<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=admin&mod=addbanking">Thêm tài khản ngân hàng</a></div>' : '') .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?act=admin">Quản lý IPay</a></div>' .
			'</div>';

		break;

	default:
		$count_banking = $db->query("SELECT COUNT(*) FROM `banking` WHERE `user_id` = '0'")->fetchColumn();
		$count_card    = $db->query("SELECT COUNT(*) FROM `card_type`")->fetchColumn();

		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Quản lý IPay</h4></div></div>' .

			'<div class="mrt-code card shadow--4dp"><div class="phdr forum-title"><span>Ngân hàng - Chuyển khoản</span></div>' .
			(!$count_banking ? '<div class="card__actions"><span class="red">Thêm tài khoản ngân hàng để khách hàng nạp tiền vào <strong>SoiCauLoDe.Club</strong> qua hình thức chuyển khoản!!</span></div>' : '') .
			'<div class="card__actions' . (!$count_banking ? ' card--border' : '') . '"><img class="icon" src="/assets/images/mt.gif"><a href="?act=admin&mod=addbanking">Thêm tài khản ngân hàng</a></div>' .
			'</div>' .
			'<div class="mrt-code card shadow--4dp"><div class="phdr forum-title"><span>Thẻ nạp - Nhà mạng</span></div>' .
			(!$count_card ? '<div class="card__actions"><span class="red">Thêm các nhà mạng mà <strong>SoiCauLoDe.Club</strong> hỗ trợ thẻ nạp từ nhà mạng đó!!</span></div>' : '') .
			'<div class="card__actions' . (!$count_card ? ' card--border' : '') . '"><img class="icon" src="/assets/images/mt.gif"><a href="?act=admin&mod=addcard">Thêm nhà mạng</a></div>' .
			($count_card ? '<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=admin&mod=addprice">Thêm mệnh giá thẻ nạp</a></div>' : '') .
			'</div>';

		$ipay     = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`='0'")->fetchColumn();
		$ipay_nap = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`='0' AND `type_active` = '1' ")->fetchColumn();
		$ipay_rut = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`='0' AND `type_active` = '2' ")->fetchColumn();
		$log      = $db->query("SELECT COUNT(*) FROM `banking_ipay` WHERE `status`!='0'")->fetchColumn();

		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thanh toán</h4></div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=thanhtoan&mod=nap">Nạp vào</a>' . ($ipay_nap ? ' (<strong class="red">' . $ipay_nap . '</strong>)' : '') . '</div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=thanhtoan&mod=rut">Rút ra</a>' . ($ipay_rut ? ' (<strong class="red">' . $ipay_rut . '</strong>)' : '') . '</div>' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/mt.gif"><a href="?act=thanhtoan&mod=lichsu">Lịch sử</a> (' . $log . ')</div>' .
			'</div>';
		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="card__actions card--border"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';

		break;
}
