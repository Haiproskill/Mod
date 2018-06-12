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

$out = '';
$total = 0;
$mod = isset($_GET['mod']) ? trim($_GET['mod']) : '';

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var PDO $db */
$db = $container->get(PDO::class);

/** @var Johncms\Api\UserInterface $systemUser */
$systemUser = $container->get(Johncms\Api\UserInterface::class);

/** @var Johncms\Api\ToolsInterface $tools */
$tools = $container->get(Johncms\Api\ToolsInterface::class);

if ($mod == 'clear') {
    if (isset($_POST['clear'])) {
        $count_message = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1';")->fetchColumn();

        if ($count_message) {
            $req = $db->query("SELECT `id` FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1' LIMIT " . $count_message);
            $mass_del = [];

            while ($row = $req->fetch()) {
                $mass_del[] = $row['id'];
            }

            if ($mass_del) {
                $result = implode(',', $mass_del);
                $db->exec("DELETE FROM `cms_mail` WHERE `id` IN (" . $result . ")");
            }
        }
        $out .= '<div class="gmenu text-center">' . _t('Messages are deleted') . '</div>';
    } else {
        $out .= '
		<div class="rmenu text-center">' . _t('Confirm the deletion of messages') . '</div>
		<div class="gmenu">
		<form action="index.php?act=systems&amp;mod=clear" method="post">
		<div class="button-container"><button class="button" type="submit" name="clear"><span>' . _t('Delete') . '</span></button></div>
		</form>
		</div>';
    }
} else {
    $total = $db->query("SELECT COUNT(*) FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1' AND `delete`!='" . $systemUser->id . "'")->fetchColumn();

    if ($total) {
        function time_parce($var)
        {
            global $tools;

            return $tools->displayDate($var[1]);
        }

        if ($total > $kmess) {
            $out .= '<div class="topmenu">' . $tools->displayPagination('index.php?act=systems&amp;', $start, $total, $kmess) . '</div>';
        }

        $req = $db->query("SELECT * FROM `cms_mail` WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1' AND `delete`!='" . $systemUser->id . "' ORDER BY `time` DESC LIMIT " . $start . "," . $kmess);
        $mass_read = [];

        for ($i = 0; ($row = $req->fetch()) !== false; ++$i) {
            $out .= '<div class="list1">';

            if ($row['read'] == 0 && $row['from_id'] == $systemUser->id) {
                $mass_read[] = $row['id'];
            }
            $user = $tools->getUser($row['user_id']);
            $avatar_name = $tools->avatar_name($row['user_id']);
            if (file_exists(('../files/users/avatar/' . $avatar_name))) {
                $avatar = '/files/users/avatar/' . $avatar_name . '';
            } else {
                $avatar = '/images/empty' . ($user['sex'] ? ($user['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '';
            }
            $data = @explode(".", $row['type']);
            if($data[0] != 'ipay') {
                if($row['user_id'])
                    $out .= '<a href="/profile/?user=' . $row['user_id'] . '"><span class="m-chip m-chip--contact m-chip--deletable"><img class="m-chip__contact" src="' . $avatar . '" /><span class="nickname">' . $user['name'] . '</span></span></a>&#160;';
            }

            $post = null;
            $sub_icon = null;
            $sub = null;
            if($data[0] == 'ipay') {
                if($data[1] == 'rut') {
                    if($data[2] == 'thenap') {
                        if ($row['them'] == '2') {
                            $post = '[color=#00838F]Yêu cầu rút tiền bằng thẻ lạp của bạn đã được chấp nhận....[/color] [url=' . $config['homeurl'] . '/tool/ipay/?act=lichsu&mod=xemthe&id=' . $row['reid'] . ']Lấy thẻ[/url]';
                        } elseif ($row['them'] == '1') {
                            $post = '[color=#F44336]Quản trị viên đã hủy yêu cầu rút tiền bằng thẻ lạp của bạn....[/color] [url=' . $config['homeurl'] . '/tool/ipay/?act=lichsu&mod=xemthe&id=' . $row['reid'] . ']Thông tin[/url]';
                        }
                    } else if ($data[2] == 'chuyenkhoan') {
                       if ($row['them'] == '2') {
                            $post = '[color=#00838F]Chúng tôi đã chuyển tiền về tài khoản ngân hàng của bạn....[/color] [url=' . $config['homeurl'] . '/tool/ipay/?act=lichsu&mod=xemck&id=' . $row['reid'] . ']Chi tiết[/url]';
                        } elseif ($row['them'] == '1') {
                            $post = '[color=#F44336]Yêu cầu rút tiền về tài khoản ngân hàng của bạn đã bị hủy....[/color] (Đã trả lại tiền cho bạn) [url=' . $config['homeurl'] . '/tool/ipay/?act=lichsu&mod=xemck&id=' . $row['reid'] . ']Chi tiết[/url]';
                        }
                    }
                } else if($data[1] == 'nap') {
                    if($data[2] == 'thenap') {
                        if ($row['them'] == '2') {
                            $post = '[color=#00838F]Nạp tiền thành công....[/color] [url=' . $config['homeurl'] . '/tool/ipay/?act=lichsu&mod=view&id=' . $row['reid'] . ']Thông tin[/url]';
                        } elseif ($row['them'] == '1') {
                            $post = '[color=#F44336]Mã thẻ bạn cung cấp không chính xác....[/color] [url=' . $config['homeurl'] . '/tool/ipay/?act=lichsu&mod=view&id=' . $row['reid'] . ']Thông tin[/url]';
                        }
                    }
                } else if($data[1] == 'chuyen') {
                    $out .= '<a href="/profile/?user=' . $row['user_id'] . '"><span class="m-chip m-chip--contact m-chip--deletable"><img class="m-chip__contact" src="' . $avatar . '" /><span class="nickname">' . $user['name'] . '</span></span></a>&#160;';
                    $dataBls = $db->query("SELECT `value` FROM `banking_ipay` WHERE `id`=" . $row['reid'])->fetch();
                    $post = 'đã chuyển cho bạn [color=#f44336]' . $tools->balans($dataBls['value']) . ' VNĐ[/color].';
                }
            } else if($data[0] == 'forum') {
                $baiviet = $db->query("SELECT `refid` FROM `forum` WHERE `id` = '" . $row['reid'] . "' ")->fetch();
                $chude = $db->query("SELECT * FROM `forum` WHERE `id` = '" . $baiviet['refid'] . "' ")->fetch();
                $tong = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='m' AND `refid`='" . $chude['id'] . "' AND `id` <= '" . $row['reid'] . "'" . ($systemUser->id >= 7 ? '' : " AND `close` != '1'") . " ")->fetchColumn();
                $sotrang = ceil($tong / $kmess);
                if($data[1] == 'tag') {
                    $sub = '<i class="material-icons valign-bottom">&#xE55A;</i>';
                    if($data[2] == 'nt') {
                        $post = 'đã nhắc đến bạn trong chủ đề ' . ($chude ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '.html][color=#00BCD4]' . $chude['text'] . '[/color][/url]' : '...... (chủ đề đã bị xoá)') . '.';
                    } else if ($data[2] == 'say') {
                        $post = 'đã nhắc đến bạn tại ' . ($baiviet ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '_p' . $sotrang . '.html#post-' . $row['reid'] . '][color=#00B0FF]' : '') . 'bài viết' . ($baiviet ? '[/color][/url]' : '') . ' trong chủ đề ' . ($chude ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '.html][color=#00BCD4]' . $chude['text'] . '[/color][/url]' : '...... (chủ đề đã bị xoá)') . '.';
                    } else if ($data[2] == 'quote') {
                        $post = 'đã nhắc đến bạn tại ' . ($baiviet ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '_p' . $sotrang . '.html#post-' . $row['reid'] . '][color=#00B0FF]' : '') . 'bài viết' . ($baiviet ? '[/color][/url]' : '') . ' trong chủ đề ' . ($chude ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '.html][color=#00BCD4]' . $chude['text'] . '[/color][/url]' : '...... (chủ đề đã bị xoá)') . '.';
                    }
                } else if ($data[1] == 'comment') {
                    $sub = '<i class="material-icons valign-bottom" style="font-size:16px;color: #510071">&#xE0CA;</i>';
                    if ($data[2] == 'say') {
                        $post = 'đã ' . ($baiviet ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '_p' . $sotrang . '.html#post-' . $row['reid'] . '][color=#00B0FF]' : '') . 'bình luận' . ($baiviet ? '[/color][/url]' : '') . ' trong chủ đề ' . ($chude ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '.html][color=#00BCD4]' . $chude['text'] . '[/color][/url]' : '...... (chủ đề đã bị xoá)') . '.';
                    } else if ($data[2] == 'quote') {
                        $sub = '<i class="material-icons valign-bottom" style="font-size:16px;color: #26b532">&#xE0B9;</i>';
                        $post = 'đã ' . ($baiviet ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '_p' . $sotrang . '.html#post-' . $row['reid'] . '][color=#00B0FF]' : '') . 'trả lời bình luận' . ($baiviet ? '[/color][/url]' : '') . ' của bạn trong chủ đề ' . ($chude ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '.html][color=#00BCD4]' . $chude['text'] . '[/color][/url]' : '...... (chủ đề đã bị xoá)') . '.';
                    }
                } else if ($data[1] == 'buyfile') {
                    $sub = '<i class="material-icons valign-bottom" style="font-size:16px;color: #510071">&#xE8CC;</i>';
                    $post = 'đã mua một [url=' . $config['homeurl'] . '/forum/index.php?act=file&id=' . $row['them'] . '][color=#FF5722]tập tin[/color][/url] trong ' . ($baiviet ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '_p' . $sotrang . '.html#post-' . $row['reid'] . '][color=#00B0FF]' : '') . 'bài viết' . ($baiviet ? '[/color][/url]' : '') . ' của bạn, thuộc chủ đề ' . ($chude ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '.html][color=#00BCD4]' . $chude['text'] . '[/color][/url]' : '...... (chủ đề đã bị xoá)') . '.';
                } else if ($data[1] == 'reaction') {
                    if ($row['them'] == 'Like') {
                        $sub_icon = 'icon-like-new--18';
                        $post = 'đã thích ' . ($baiviet ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '_p' . $sotrang . '.html#post-' . $row['reid'] . '][color=#00B0FF]' : '') . 'bình luận' . ($baiviet ? '[/color][/url]' : '') . ' của bạn trong chủ đề ' . ($chude ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '.html][color=#00BCD4]' . $chude['text'] . '[/color][/url]' : '...... (chủ đề đã bị xoá)') . '.';
                    } else if($row['them'] == 'Love') {
                        $sub_icon = 'icon-love-new--18';
                    } else if($row['them'] == 'Haha') {
                        $sub_icon = 'icon-haha-new--18';
                    } else if($row['them'] == 'Hihi') {
                        $sub_icon = 'icon-mmmm-new--18';
                    } else if($row['them'] == 'Woww') {
                        $sub_icon = 'icon-wowww-new--18';
                    } else if($row['them'] == 'Cry') {
                        $sub_icon = 'icon-crying-new--18';
                    } else if($row['them'] == 'Angry') {
                        $sub_icon = 'icon-angry-new--18';
                    } else if($row['them'] == 'WTF') {
                        $sub_icon = 'icon-like-blf--18';
                    }
                    $sub = '<div class="reaction_total-style ' . $sub_icon . ' valign-bottom"></div>';
                    if ($row['them'] != 'Like')
                        $post = 'đã bày tỏ cảm xúc về ' . ($baiviet ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '_p' . $sotrang . '.html#post-' . $row['reid'] . '][color=#00B0FF]' : '') . 'bình luận' . ($baiviet ? '[/color][/url]' : '') . ' của bạn trong chủ đề ' . ($chude ? '[url=' . $config['homeurl'] . '/forum/' . $chude['id'] . '/' . $chude['seo'] . '.html][color=#00BCD4]' . $chude['text'] . '[/color][/url]' : '...... (chủ đề đã bị xoá)') . '.';
                }
            } else if($data[0] == 'status') {
                if ($data[1] == 'reaction') {
                    if ($data[2] == 'status') {
                        if ($row['them'] == 'Like') {
                            $sub_icon = 'icon-like-new--18';
                            $post = 'đã thích [url=' . $config['homeurl'] . '/profile/?act=status&id=' . $row['reid'] . '][color=#00B0FF] bài viết [/color][/url] trên dòng thời gian của bạn.!!';
                        } else if($row['them'] == 'Love') {
                            $sub_icon = 'icon-love-new--18';
                        } else if($row['them'] == 'Haha') {
                            $sub_icon = 'icon-haha-new--18';
                        } else if($row['them'] == 'Hihi') {
                            $sub_icon = 'icon-mmmm-new--18';
                        } else if($row['them'] == 'Woww') {
                            $sub_icon = 'icon-wowww-new--18';
                        } else if($row['them'] == 'Cry') {
                            $sub_icon = 'icon-crying-new--18';
                        } else if($row['them'] == 'Angry') {
                            $sub_icon = 'icon-angry-new--18';
                        } else if($row['them'] == 'WTF') {
                            $sub_icon = 'icon-like-blf--18';
                        }
                        $sub = '<div class="reaction_total-style ' . $sub_icon . ' valign-bottom"></div>';
                        if ($row['them'] != 'Like')
                            $post = 'đã bày tỏ cảm xúc về [url=' . $config['homeurl'] . '/profile/?act=status&id=' . $row['reid'] . '][color=#00B0FF] bài viết [/color][/url] trên dòng thời gian của bạn.!';
                    } else if ($data[2] == 'comment') {
                        $sttcmt = $db->query("SELECT `user_id`, `post_id` FROM `cms_users_guestbook_comments` WHERE `id`='" . $row['reid'] . "' AND `type`='cmt' ")->fetch();

                        if ($row['them'] == 'Like') {
                            $sub_icon = 'icon-like-new--18';
                            $post = 'đã thích [url=' . $config['homeurl'] . '/profile/?act=status&id=' . $sttcmt['post_id'] . '&comment=' . $row['reid'] . '][color=#00B0FF] bình luận [/color][/url] của bạn trong một tâm tâm trạng.';
                        } else if($row['them'] == 'Love') {
                            $sub_icon = 'icon-love-new--18';
                        } else if($row['them'] == 'Haha') {
                            $sub_icon = 'icon-haha-new--18';
                        } else if($row['them'] == 'Hihi') {
                            $sub_icon = 'icon-mmmm-new--18';
                        } else if($row['them'] == 'Woww') {
                            $sub_icon = 'icon-wowww-new--18';
                        } else if($row['them'] == 'Cry') {
                            $sub_icon = 'icon-crying-new--18';
                        } else if($row['them'] == 'Angry') {
                            $sub_icon = 'icon-angry-new--18';
                        } else if($row['them'] == 'WTF') {
                            $sub_icon = 'icon-like-blf--18';
                        }
                        $sub = '<div class="reaction_total-style ' . $sub_icon . ' valign-bottom"></div>';
                        if ($row['them'] != 'Like')
                            $post = 'đã bày tỏ cảm xúc về [url=' . $config['homeurl'] . '/profile/?act=status&id=' . $sttcmt['post_id'] . '&comment=' . $row['reid'] . '][color=#00B0FF] bình luận [/color][/url] của bạn trong một tâm trạng.';
                    } else if ($data[2] == 'reply') {
                        $sttrep = $db->query("SELECT `user_id`, `post_id` FROM `cms_users_guestbook_comments` WHERE `id`='" . $row['reid'] . "' AND `type`='reply' ")->fetch();
                        $sttcmt = $db->query("SELECT `user_id`, `post_id` FROM `cms_users_guestbook_comments` WHERE `id`='" . $sttrep['post_id'] . "' AND `type`='cmt' ")->fetch();
                        if ($row['them'] == 'Like') {
                            $sub_icon = 'icon-like-new--18';
                            $post = 'đã thích câu [url=' . $config['homeurl'] . '/profile/?act=status&id=' . $sttcmt['post_id'] . '&reply=' . $row['reid'] . '][color=#00B0FF] trả lời [/color][/url] của bạn.!!';
                        } else if($row['them'] == 'Love') {
                            $sub_icon = 'icon-love-new--18';
                        } else if($row['them'] == 'Haha') {
                            $sub_icon = 'icon-haha-new--18';
                        } else if($row['them'] == 'Hihi') {
                            $sub_icon = 'icon-mmmm-new--18';
                        } else if($row['them'] == 'Woww') {
                            $sub_icon = 'icon-wowww-new--18';
                        } else if($row['them'] == 'Cry') {
                            $sub_icon = 'icon-crying-new--18';
                        } else if($row['them'] == 'Angry') {
                            $sub_icon = 'icon-angry-new--18';
                        } else if($row['them'] == 'WTF') {
                            $sub_icon = 'icon-like-blf--18';
                        }
                        $sub = '<div class="reaction_total-style ' . $sub_icon . ' valign-bottom"></div>';
                        if ($row['them'] != 'Like')
                            $post = 'đã bày tỏ cảm xúc về câu [url=' . $config['homeurl'] . '/profile/?act=status&id=' . $sttcmt['post_id'] . '&reply=' . $row['reid'] . '][color=#00B0FF] trả lời [/color][/url] của bạn.!';
                    }
                }
            } else {
                $post = $row['text'];
            }
            $post = $tools->checkout($post, 1, 1, 0, 1);
            $out .= '' . $post . '<br /><span class="fsize--12 gray">';
            $out .= $sub . ' ' . $tools->thoigian($row['time']) . ' • <a href="index.php?act=delete&amp;id=' . $row['id'] . '" class="gray">' . _t('Delete') . '</a></span>';
            $out .= '</div>';
        }

        //Ставим метку о прочтении
        if ($mass_read) {
            $result = implode(',', $mass_read);
            $db->exec("UPDATE `cms_mail` SET `read`='1' WHERE `from_id`='" . $systemUser->id . "' AND `sys`='1' AND `id` IN (" . $result . ")");
        }
    } else {
        $out .= '<div class="rmenu text-center">' . _t('The list is empty') . '</div>';
    }

    $out .= '</div><div class="mrt-code card shadow--2dp"><div class="phdr">' . _t('Total') . ': ' . $total . '</div>';

    if ($total > $kmess) {
        $out .= '<div class="topmenu">' . $tools->displayPagination('index.php?act=systems&amp;', $start, $total, $kmess) . '</div>';
    }
}

$textl = _t('System messages');
require_once('../system/head.php');
echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>' . _t('System messages') . '</h4></div>';
echo $out;

if ($total) {
    echo '<div class="list1"><a href="index.php?act=systems&amp;mod=clear">' . _t('Clear messages') . '</a></div>';
}
echo '</div>';