<?php
switch ($mod) {
	case 'edit':
		$req = $db->query("SELECT * FROM `banking` WHERE `id` = " . $db->quote($id) . " AND `user_id`=" . $db->quote($systemUser->id));
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
					header('Location: ?act=set&mod=edit&id=' . $id);
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
				    '<label class="control-label" for="input">Chủ sở hữu</label><i class="bar"></i>' .
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
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=set&mod=add">Thêm tài khản ngân hàng</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?">IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=set&mod=add');

		break;

	case 'dell':
		$req = $db->query("SELECT * FROM `banking` WHERE `id` = " . $db->quote($id) . " AND `user_id`=" . $db->quote($systemUser->id));
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
				'<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=set&mod=add">Thêm tài khản ngân hàng</a></div>' .
				'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?">IPay</a></div>' .
				'</div>';
		} else
			header('Location: ?act=set&mod=add');

		break;

	case 'add':
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thêm tài khoản ngân hàng</h4></div>';
		if (isset($_POST['submit'])) {
			$name   = isset($_POST['name'])   ? trim($_POST['name'])   : 0;
			$owner  = isset($_POST['owner'])  ? trim($_POST['owner'])  : 0;
			$branch = isset($_POST['branch']) ? trim($_POST['branch']) : 0;
			$number = isset($_POST['number']) ? trim($_POST['number']) : 0;
			if ($name && $owner && $branch && $number) {
				$check = $db->query("SELECT COUNT(*) FROM `banking` WHERE `user_id`='" . $systemUser->id . "' AND `name`=" . $db->quote($name) . " AND `number`=" . $db->quote($number))->fetchColumn();
				if (!$check) {
					$db->prepare('
			          INSERT INTO `banking` SET
			           `user_id` = ?,
			           `name` = ?,
			           `owner` = ?,
			           `branch` = ?,
			           `number` = ?
			        ')->execute([
			        	$systemUser->id,
			            $name,
			            $owner,
			            $branch,
			            $number,
			        ]);

			        echo '<div class="gmenu text-center">' .
			        	'Thêm tài khoản ngân hàng thành công.!' .
						'</div>';
				} else
					header('Location: ?act=set&mod=add');
			} else
				header('Location: ?act=set&mod=add');
		} else {
			$req = $db->query("SELECT * FROM `banking` WHERE `user_id` = '" . $systemUser->id . "'");
			echo '<div class="card__actions"> - Có thể rút tiền qua hình thức <strong>chuyển khoản ngân hàng</strong> bằng cách thêm tài khoản ngân hàng của bạn ở đây!</div>' .
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
				        '<td class="data-table__cell--non-numeric">' . $tools->checkout($res['name']) . '</td>' .
				        '<td>' . $tools->checkout($res['branch']) . '</td>' .
				        '<td>' . $tools->checkout($res['owner']) . '</td>' .
				        '<td>' . $tools->checkout($res['number']) . '</td>' .
				        '<td class="op"><a href="?act=set&mod=edit&id=' . $res['id'] . '"><i class="material-icons">&#xE254;</i></a>' .
				        '<a href="?act=set&mod=dell&id=' . $res['id'] . '"><i class="material-icons">&#xE14C;</i></a></td>' .
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
			(isset($_POST['submit']) ? '<div class="card__actions"><img class="icon" src="/assets/images/back.png"><a href="?act=set&mod=add">Thêm tài khản ngân hàng</a></div>' : '') .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a class="red" href="?">IPay</a></div>' .
			'</div>';

		break;
	
	default:
		echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Thiết lập thanh toán</h4></div>' .
			'<div class="card__actions"><img class="icon" src="/assets/images/mt.gif"><a href="?act=set&mod=add">Thêm tài khản ngân hàng của bạn</a></div>' .
			'</div>';
		echo '<div class="mrt-code card shadow--2dp">' .
			'<div class="list1"><img class="icon" src="/assets/images/back.png"><a href="/tool/ipay/">IPay</a></div>' .
			'</div>';

		break;
}
