<?php

$other     = '';
$row       = '';
$st        = 0;
$restREPLY = 0;
$type      = 'reply';
$replys    = array();

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
        $countReply = $db->query("SELECT COUNT(*) FROM `cms_users_guestbook_comments` WHERE `post_id`=" . $db->quote($post_id) . " AND `type`='reply' ")->fetchColumn();

        if ($countReply) {
            if ($view == 'old') {
                if ($countReply > 4) {
                    $restREPLY = $countReply - $start_row - 4;
                    if (($countReply - $start_row) < 4) {
                     } else {
                        $st = $countReply - $start_row - 4;
                    }
                }

                $other = '`cms_users_guestbook_comments`.`id` < ' . $db->quote($ss_id) . ' AND ';
                $row   = ' LIMIT ' . $st . ', 4';
            } else {
                $other = '`cms_users_guestbook_comments`.`id` > ' . $db->quote($ss_id) . ' AND ';
            }
            $reqReplys = $db->query("SELECT `cms_users_guestbook_comments`.*, `cms_users_guestbook_comments`.`id` AS `crid`, `users`.`rights`, `users`.`sex`, `users`.`name`, `users`.`id`, `users`.`avatar_extension`
                FROM `cms_users_guestbook_comments` LEFT JOIN `users` ON `cms_users_guestbook_comments`.`user_id` = `users`.`id` WHERE " . $other . "`post_id` = " . $db->quote($post_id) . " AND `type`='reply' ORDER BY `time` ASC" . $row);

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
                    'reid'      => $post_id,
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

        $data = array(
            'status' => 1,
            'rest'   => $restREPLY,
            'num'    => $countReply,
            'data'   => $replys
        );
    }

    header("Content-type: application/json; charset=utf-8");
    echo json_encode($data);
    exit();
}
