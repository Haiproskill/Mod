<?php

$other    = '';
$row      = '';
$restCMT  = 0;
$st       = 0;
$type     = 'cmt';
$comments = array();

$data  = array(
    'status' => 0
);

if ($systemUser->isValid()) {
	$post_id   = isset($_REQUEST['post_id'])   ? abs(intval($_REQUEST['post_id']))   : 0;
	$ss_id     = isset($_REQUEST['ss_id'])     ? abs(intval($_REQUEST['ss_id']))     : 0;
    $start_row = isset($_REQUEST['start_row']) ? abs(intval($_REQUEST['start_row'])) : 0;
	$view      = isset($_REQUEST['view'])      ? trim($_REQUEST['view'])             : 'new';

    if (!$post_id || ($start_row && !$ss_id)) {
		$data = array(
			'status' => 0
		);
	} else {
        $countComment = $db->query("SELECT COUNT(*) FROM `cms_users_guestbook_comments` WHERE `post_id`=" . $db->quote($post_id) . " AND `type`='cmt' ")->fetchColumn();

        if ($countComment) {
            if ($view == 'old') {
                if ($countComment > 4) {
                    $restCMT = $countComment - $start_row - 4;
                    if (($countComment - $start_row) < 4) {
                    } else {
                        $st = $countComment - $start_row - 4;
                    }
                }

                $other = '`cms_users_guestbook_comments`.`id` < ' . $db->quote($ss_id) . ' AND ';
                $row   = ' LIMIT ' . $st . ', 4';
            } else {
                $other = '`cms_users_guestbook_comments`.`id` > ' . $db->quote($ss_id) . ' AND ';
            }

            $reqComments = $db->query("SELECT `cms_users_guestbook_comments`.*, `cms_users_guestbook_comments`.`id` AS `scid`, `users`.`rights`, `users`.`sex`, `users`.`name`, `users`.`id`, `users`.`avatar_extension`
                FROM `cms_users_guestbook_comments` LEFT JOIN `users` ON `cms_users_guestbook_comments`.`user_id` = `users`.`id` WHERE " . $other . "`post_id` = " . $db->quote($post_id) . " AND `type`='cmt' ORDER BY `time` ASC" . $row);

            $j = 0;
            while ($resComments = $reqComments->fetch()) {
                $reactionCMT = array();
                if (file_exists((ROOT_PATH . 'files/users/avatar/' . $resComments['user_id'] . '_100x100.' . $resComments['avatar_extension']))) {
                    $avatarc = $config['homeurl'] . '/files/users/avatar/'.$resComments['id'].'_100x100.' . $resComments['avatar_extension'];
                } else {
                    $avatarc = $config['homeurl'] . '/images/default-'.($resComments['sex'] == 'm' ? 'male' : 'female').'-avatar.png';
                }

                $postcmt  = $tools->checkout($resComments['text'], 1, 1, 0, 1);
                $allCheck = $tools->Like_CountTotal($resComments['scid'], 'status_comment');
                $like     = $tools->Like_Check($resComments['scid'], "Like",  'status_comment');
                $love     = $tools->Like_Check($resComments['scid'], "Love",  'status_comment');
                $haha     = $tools->Like_Check($resComments['scid'], "Haha",  'status_comment');
                $hihi     = $tools->Like_Check($resComments['scid'], "Hihi",  'status_comment');
                $woww     = $tools->Like_Check($resComments['scid'], "Woww",  'status_comment');
                $cry      = $tools->Like_Check($resComments['scid'], "Cry",   'status_comment');
                $angry    = $tools->Like_Check($resComments['scid'], "Angry", 'status_comment');
                $wtf      = $tools->Like_Check($resComments['scid'], "WTF",   'status_comment');

                $reactionCMT = array(
                    'check' => (!$allCheck ? 0 : 1),
                    'info'  => $allCheck,
                    'like'  => array(
                        'u'  => $tools->Like_CountT($resComments['scid'], 'Like',  'status_comment'),
                        'i'  => $like,
                        'd'  => $tools->Reactions_URel($resComments['scid'], 'Like', 'status_comment', true)
                    ),
                    'love'  => array(
                        'u'  => $tools->Like_CountT($resComments['scid'], 'Love',  'status_comment'),
                        'i'  => $love,
                        'd'  => $tools->Reactions_URel($resComments['scid'], 'Love', 'status_comment', true)
                    ),
                    'haha'  => array(
                        'u'  => $tools->Like_CountT($resComments['scid'], 'Haha',  'status_comment'),
                        'i'  => $haha,
                        'd'  => $tools->Reactions_URel($resComments['scid'], 'Haha', 'status_comment', true)
                    ),
                    'hihi'  => array(
                        'u'  => $tools->Like_CountT($resComments['scid'], 'Hihi',  'status_comment'),
                        'i'  => $hihi,
                        'd'  => $tools->Reactions_URel($resComments['scid'], 'Hihi', 'status_comment', true)
                    ),
                    'woww'  => array(
                        'u'  => $tools->Like_CountT($resComments['scid'], 'Woww',  'status_comment'),
                        'i'  => $woww,
                        'd'  => $tools->Reactions_URel($resComments['scid'], 'Woww', 'status_comment', true)
                    ),
                    'cry'   => array(
                        'u'  => $tools->Like_CountT($resComments['scid'], 'Cry',  'status_comment'),
                        'i'  => $cry,
                        'd'  => $tools->Reactions_URel($resComments['scid'], 'Cry', 'status_comment', true)
                    ),
                    'angry' => array(
                        'u'  => $tools->Like_CountT($resComments['scid'], 'Angry',  'status_comment'),
                        'i'  => $angry,
                        'd'  => $tools->Reactions_URel($resComments['scid'], 'Angry', 'status_comment', true)
                    ),
                    'wtf'   => array(
                        'u'  => $tools->Like_CountT($resComments['scid'], 'WTF',  'status_comment'),
                        'i'  => $wtf,
                        'd'  => $tools->Reactions_URel($resComments['scid'], 'WTF', 'status_comment', true)
                    )
                );

                $countReply = $db->query("SELECT COUNT(*) FROM `cms_users_guestbook_comments` WHERE `post_id`='" . $resComments['scid'] . "' AND `type`='reply' ")->fetchColumn();
                $restREPLY  = 0;
                $limitReply = "";
                if($countReply > 4){
                    $restREPLY = 1;
                    $numlimit = $countReply - 4;
                    $limitReply = " LIMIT " . $numlimit . ", 4";
                }
                $reqReplys = $db->query("SELECT `cms_users_guestbook_comments`.*, `cms_users_guestbook_comments`.`id` AS `crid`, `users`.`rights`, `users`.`sex`, `users`.`name`, `users`.`id`, `users`.`avatar_extension`
                    FROM `cms_users_guestbook_comments` LEFT JOIN `users` ON `cms_users_guestbook_comments`.`user_id` = `users`.`id` WHERE `post_id` = '" . $resComments['scid'] . "' AND `type`='reply' ORDER BY `time` ASC" . $limitReply);

                $replys = array();
                if ($countReply) {
                    $ij=0;
                    while ($resReplys = $reqReplys->fetch()) {
                        $reactionRep = array();
                        if (file_exists((ROOT_PATH . 'files/users/avatar/' . $resReplys['user_id'] . '_100x100.' . $resReplys['avatar_extension']))) {
                            $avatarRep = $config['homeurl'] . '/files/users/avatar/'.$resReplys['id'].'_100x100.' . $resReplys['avatar_extension'];
                        } else {
                            $avatarRep = $config['homeurl'] . '/images/default-'.($resReplys['sex'] == 'm' ? 'male' : 'female').'-avatar.png';
                        }

                        $postRep  = $tools->checkout($resReplys['text'], 1, 1, 0, 1);
                        $allCheck = $tools->Like_CountTotal($resReplys['crid'], 'status_reply');
                        $like     = $tools->Like_Check($resReplys['crid'], "Like",  'status_reply');
                        $love     = $tools->Like_Check($resReplys['crid'], "Love",  'status_reply');
                        $haha     = $tools->Like_Check($resReplys['crid'], "Haha",  'status_reply');
                        $hihi     = $tools->Like_Check($resReplys['crid'], "Hihi",  'status_reply');
                        $woww     = $tools->Like_Check($resReplys['crid'], "Woww",  'status_reply');
                        $cry      = $tools->Like_Check($resReplys['crid'], "Cry",   'status_reply');
                        $angry    = $tools->Like_Check($resReplys['crid'], "Angry", 'status_reply');
                        $wtf      = $tools->Like_Check($resReplys['crid'], "WTF",   'status_reply');

                        $reactionRep = array(
                            'check' => (!$allCheck ? 0 : 1),
                            'info'  => $allCheck,
                            'like'  => array(
                                'u'  => $tools->Like_CountT($resReplys['crid'], 'Like',  'status_reply'),
                                'i'  => $like,
                                'd'  => $tools->Reactions_URel($resReplys['crid'], 'Like', 'status_reply', true)
                            ),
                            'love'  => array(
                                'u'  => $tools->Like_CountT($resReplys['crid'], 'Love',  'status_reply'),
                                'i'  => $love,
                                'd'  => $tools->Reactions_URel($resReplys['crid'], 'Love', 'status_reply', true)
                            ),
                            'haha'  => array(
                                'u'  => $tools->Like_CountT($resReplys['crid'], 'Haha',  'status_reply'),
                                'i'  => $haha,
                                'd'  => $tools->Reactions_URel($resReplys['crid'], 'Haha', 'status_reply', true)
                            ),
                            'hihi'  => array(
                                'u'  => $tools->Like_CountT($resReplys['crid'], 'Hihi',  'status_reply'),
                                'i'  => $hihi,
                                'd'  => $tools->Reactions_URel($resReplys['crid'], 'Hihi', 'status_reply', true)
                            ),
                            'woww'  => array(
                                'u'  => $tools->Like_CountT($resReplys['crid'], 'Woww',  'status_reply'),
                                'i'  => $woww,
                                'd'  => $tools->Reactions_URel($resReplys['crid'], 'Woww', 'status_reply', true)
                            ),
                            'cry'   => array(
                                'u'  => $tools->Like_CountT($resReplys['crid'], 'Cry',  'status_reply'),
                                'i'  => $cry,
                                'd'  => $tools->Reactions_URel($resReplys['crid'], 'Cry', 'status_reply', true)
                            ),
                            'angry' => array(
                                'u'  => $tools->Like_CountT($resReplys['crid'], 'Angry',  'status_reply'),
                                'i'  => $angry,
                                'd'  => $tools->Reactions_URel($resReplys['crid'], 'Angry', 'status_reply', true)
                            ),
                            'wtf'   => array(
                                'u'  => $tools->Like_CountT($resReplys['crid'], 'WTF',  'status_reply'),
                                'i'  => $wtf,
                                'd'  => $tools->Reactions_URel($resReplys['crid'], 'WTF', 'status_reply', true)
                            )
                        );

                        $replys[$ij] = array(
                            'id'        => $resReplys['crid'],
                            'reid'      => $resComments['scid'],
                            'uid'       => $resReplys['id'],
                            'name'      => $resReplys['name'],
                            'rights'    => $resReplys['rights'],
                            'avatar'    => $avatarRep,
                            'text'      => $postRep,
                            'reactions' => $reactionRep,
                            'time'      => $tools->thoigian($resReplys['time']),
                            'timestamp' => (round((time()-$resReplys['time'])/3600) < 1 ? $tools->timestamp($resReplys['time']) : 0)
                        );

                        $ij++;
                    }
                }

                $comments[$j] = array(
                    'id'        => $resComments['scid'],
                    'uid'       => $resComments['id'],
                    'name'      => $resComments['name'],
                    'rights'    => $resComments['rights'],
                    'avatar'    => $avatarc,
                    'text'      => $postcmt,
                    'reactions' => $reactionCMT,
                    'reply'     => $replys,
                    'rest'      => $restREPLY,
                    'time'      => $tools->thoigian($resComments['time']),
                    'timestamp' => (round((time()-$resComments['time'])/3600) < 1 ? $tools->timestamp($resComments['time']) : 0)
                );

                $j++;
            }
        }

    	$data = array(
        	'status' => 1,
            'rest'   => $restCMT,
            'num'    => $countComment,
        	'data'   => $comments
    	);
    }

    header("Content-type: application/json; charset=utf-8");
    echo json_encode($data);
    exit();
}
