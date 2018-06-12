<?php

echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Tường nhà</h4></div>';
echo '<div class="jserror rmenu" style="display:none;"></div>';
echo '<div class="list1"><div class="phieubac-chat">' .
     '<form id="ajaxStatus" method="post">';
echo '<div class="form-group"><textarea id="stextarea" name="text" required="required"></textarea>' .
    '<label class="control-label" for="stextarea">Bạn đang nghĩ gì???</label><i class="bar"></i>' .
    '</div>';
echo '<div class="button-container"><button class="button" type="submit" name="submit"><span class="flex-center">' . _t('Send') . '</span></button></div>' .
    '<input type="hidden" name="t" value="status" />' .
    '<input type="hidden" name="a" value="addStatus" />' .
    '<input type="hidden" name="user" value="' . $user['id'] . '" />' .
    '</form></div></div>' .
    '</div>' .
    '<div class="list_status" user-id="' . $user['id'] . '">';


if (file_exists((ROOT_PATH . 'files/users/avatar/' . $systemUser->id . '_100x100.' . $systemUser->avatar_extension))) {
    $mcavatar = $config['homeurl'] . '/files/users/avatar/' . $systemUser->id . '_100x100.' . $systemUser->avatar_extension;
} else {
    $mcavatar = $config['homeurl'] . '/images/default-'.($systemUser->sex == 'm' ? 'male' : 'female').'-avatar.png';
}

$total = $db->query("SELECT COUNT(*) FROM `cms_users_guestbook` WHERE `from_id` = '" . $user['id'] . "' OR `user_id` = '" . $user['id'] . "'")->fetchColumn();
if ($total) {
    $req = $db->query('SELECT `cms_users_guestbook`.*, `cms_users_guestbook`.`id` AS `stid`, `users`.`rights`, `users`.`sex`, `users`.`name`, `users`.`id`, `users`.`avatar_extension`
        FROM `cms_users_guestbook` LEFT JOIN `users` ON `cms_users_guestbook`.`user_id` = `users`.`id`
        WHERE `from_id` = ' . $user['id'] . ' OR `user_id` = ' . $user['id'] . ' ORDER BY `time` DESC LIMIT ' . $start . ',' . $kmess);
    while ($res = $req->fetch()) {
        echo '<div class="profile_status ps' . $res['stid'] . ' mrt-code card shadow--2dp" stt-id="' . $res['stid'] . '">' .
            '<div class="status_more"><i class="material-icons"></i>' .
            '<div class="status_moreActive"><span>Chỉnh sửa</span> - <span>Xóa</span></div>' .
            '</div>' .
            '<div class="card__actions">';
        $post = $tools->checkout($res['text'], 1, 1, 0, 1);

        if (file_exists((ROOT_PATH . 'files/users/avatar/' . $res['user_id'] . '_100x100.' . $res['avatar_extension']))) {
            $avatari = $config['homeurl'] . '/files/users/avatar/'.$res['id'].'_100x100.' . $res['avatar_extension'];
        } else {
            $avatari = $config['homeurl'] . '/images/default-'.($res['sex'] == 'm' ? 'male' : 'female').'-avatar.png';
        }
        if ($res['from_id'] != $res['user_id']) {
            $from = $tools->getUser($res['from_id']);
        }
        echo '<table border="0" cellspacing="0" cellpadding="0"><tbody>' .
            '<tr><td width="48px">' .
            '<img class="avatar" src="' . $avatari . '" alt="' . $res['name'] . '" />' .
            '</td><td>' . 
            ($res['from_id'] != $res['user_id'] ? '<a href="' . $config['homeurl'] . '/profile/?user=' . $res['user_id'] . '" class="tload nickname' . ($res['rights'] == 9 ? ' red' : '') . '"><strong>' . $res['name'] . '</strong></a> > <a href="' . $config['homeurl'] . '/profile/?user=' . $res['from_id'] . '" class="tload nickname' . ($from['rights'] == 9 ? ' red' : '') . '"><strong>' . $from['name'] . '</strong></a>' : '<a href="' . $config['homeurl'] . '/profile/?user=' . $res['user_id'] . '" class="tload nickname' . ($res['rights'] == 9 ? ' red' : '') . '"><strong>' . $res['name'] . '</strong></a>') .
            '<div class="status_other_data gray"><i class="material-icons">&#xE192;</i> '.(round((time()-$res['time'])/3600) < 2 ? '<span class="ajax-time" title="' . $tools->timestamp($res['time']) . '">':'') . $tools->thoigian($res['time']) . (round((time()-$res['time'])/3600) < 2 ? '</span>':'') . '</div></td></tr></tbody></table>';

        echo '</div><div class="card__actions post">' . $post . '</div>' .
            '<div class="reactions card__actions">';




        $sep = null;
        $lstyle = null;
        $allCheck = $tools->Like_CountTotal($res['stid'], 'status');
        if(!$allCheck){
            $lstyle = "display:none;";
        }
        echo "\n" . '<ul class="who-likes-this-post" id="stt_reactions'.$res['stid'].'" style="'.$lstyle.'">';
        //Like Started
        if($tools->Like_CountT($res['stid'], 'Like', 'status')>0) { 
            echo '<li class="likes reaction_wrap-style icon-newL reaction18px like lpos" id="stt_elike'.$res['stid'].'"><span class="reaction_use">' . $tools->Reactions_URel($res['stid'], 'Like', 'status') . '</span></li>'; 
        } else {
            echo '<li class="likes reaction_wrap-style icon-newL reaction18px like lpos" id="stt_elike'.$res['stid'].'" style="display:none"></li>';
        }
        //Love Started
        if($tools->Like_CountT($res['stid'], 'Love', 'status')){
            echo '<li class="loves reaction_wrap-style icon-newL reaction18px love lpos" id="stt_elove'.$res['stid'].'"><span class="reaction_use">' . $tools->Reactions_URel($res['stid'], 'Love', 'status') . '</span></li>'; 
        } else {
            echo '<li class="loves reaction_wrap-style icon-newL reaction18px love lpos" id="stt_elove'.$res['stid'].'" style="display:none"></li>';
        }
        //Haha Started
        if($tools->Like_CountT($res['stid'], 'Haha', 'status')){
            echo '<li class="hahas reaction_wrap-style icon-newL reaction18px haha lpos" id="stt_ehaha'.$res['stid'].'"><span class="reaction_use">' . $tools->Reactions_URel($res['stid'], 'Haha', 'status') . '</span></li>'; 
        } else {
            echo '<li class="hahas reaction_wrap-style icon-newL reaction18px haha lpos" id="stt_ehaha'.$res['stid'].'" style="display:none"></li>';
        }
        //Hihi Started
        if($tools->Like_CountT($res['stid'], 'Hihi', 'status')){
            echo '<li class="hihis reaction_wrap-style icon-newL icon-hihi-new--18 lpos" id="stt_ehihi'.$res['stid'].'"><span class="reaction_use">' . $tools->Reactions_URel($res['stid'], 'Hihi', 'status') . '</span></li>'; 
        } else {
            echo '<li class="hihis reaction_wrap-style icon-newL icon-hihi-new--18 lpos" id="stt_ehihi'.$res['stid'].'" style="display:none"></li>';
        }
        //Woww Started
        if($tools->Like_CountT($res['stid'], 'Woww', 'status')){
            echo '<li class="wowws reaction_wrap-style icon-newL reaction18px woww lpos" id="stt_ewoww'.$res['stid'].'"><span class="reaction_use">' . $tools->Reactions_URel($res['stid'], 'Woww', 'status') . '</span></li>'; 
        } else {
            echo '<li class="wowws reaction_wrap-style icon-newL reaction18px woww lpos" id="stt_ewoww'.$res['stid'].'" style="display:none"></li>';
        }
        //Cry Started
        if($tools->Like_CountT($res['stid'], 'Cry', 'status')){
            echo '<li class="crys reaction_wrap-style icon-newL reaction18px cry lpos" id="stt_ecry'.$res['stid'].'"><span class="reaction_use">' . $tools->Reactions_URel($res['stid'], 'Cry', 'status') . '</span></li>'; 
        } else {
            echo '<li class="crys reaction_wrap-style icon-newL reaction18px cry lpos" id="stt_ecry'.$res['stid'].'" style="display:none"></li>';
        }
        //Angry Started
        if($tools->Like_CountT($res['stid'], 'Angry', 'status')){
            echo '<li class="angrys reaction_wrap-style icon-newL reaction18px angry lpos" id="stt_eangry'.$res['stid'].'"><span class="reaction_use">' . $tools->Reactions_URel($res['stid'], 'Angry', 'status') . '</span></li>'; 
        } else {
            echo '<li class="angrys reaction_wrap-style icon-newL reaction18px angry lpos" id="stt_eangry'.$res['stid'].'" style="display:none"></li>';
        }
        //WTF Started
        if($tools->Like_CountT($res['stid'], 'WTF', 'status')){
            echo '<li class="wtfs reaction_wrap-style icon-newL icon-like-blf--18 lpos" id="stt_ewtf'.$res['stid'].'"><span class="reaction_use">' . $tools->Reactions_URel($res['stid'], 'WTF', 'status') . '</span></li>'; 
        } else {
            echo '<li class="wtfs reaction_wrap-style icon-newL icon-like-blf--18 lpos" id="stt_ewtf'.$res['stid'].'" style="display:none"></li>';
        }
        echo '<li class="totalco" id="stt_totalco'.$res['stid'].'">'.$tools->Like_CountTotal($res['stid'], 'status').'</li>'; 
        echo "\n" . '</ul>';






        $like = $tools->Like_Check($res['stid'], "Like", 'status');
        $like_statusicon = 'icon-like-blf--18';
        $lostyle = 'display:none;';
        $i_status = 'Thích'; 
        if($like) {
            $like_status = 'UnLike';
            $i_status = 'Like';
            $like_statusicon = 'icon-like-new--18';
            $lostyle = 'display:inline-block;';
        } else {
            $like_status = 'Like';
        }

            // Reaction status check for "Love"
        $love = $tools->Like_Check($res['stid'], "Love", 'status');
        if($love){
            $love_status = 'UnLove';
            $i_status = 'Love';
            $like_statusicon = 'icon-love-new--18'; 
            $lostyle = 'display:inline-block;';
        } else {
            $love_status = 'Love';
        }

        // Reaction status check for "Haha"
        $haha = $tools->Like_Check($res['stid'], "Haha", 'status');
        if($haha){
            $haha_status = 'UnHaha';
            $i_status = 'Haha';
            $like_statusicon = 'icon-haha-new--18'; 
            $lostyle = 'display:inline-block;';
        } else {
            $haha_status = 'Haha';
        }

        // Reaction status check for "Hihi"
        $hihi = $tools->Like_Check($res['stid'], "Hihi", 'status');
        if($hihi){
            $hihi_status = 'UnHihi';
            $i_status = 'Hihi';
            $like_statusicon = 'icon-hihi-new--18'; 
            $lostyle = 'display:inline-block;';
        } else {
            $hihi_status = 'Hihi';
        }

        // Reaction status check for "Woww"
        $woww = $tools->Like_Check($res['stid'], "Woww", 'status');
        if($woww){
            $woww_status = 'UnWoww';
            $i_status = 'Woww';
            $like_statusicon = 'icon-woww-new--18'; 
            $lostyle = 'display:inline-block;';
        } else {
            $woww_status = 'Woww';
        }

        // Reaction status check for "Cry"
        $cry = $tools->Like_Check($res['stid'], "Cry", 'status');
        if($cry){
            $cry_status = 'UnCry';
            $i_status = 'Cry';
            $like_statusicon = 'icon-cry-new--18'; 
            $lostyle = 'display:inline-block;';
        } else {
            $cry_status = 'Cry'; 
        }

        // Reaction status check for "Angry"
        $angry = $tools->Like_Check($res['stid'], "Angry", 'status');
        if($angry){
            $angry_status = 'UnAngry';
            $i_status = 'Angry';
            $like_statusicon = 'icon-angry-new--18'; 
            $lostyle = 'display:inline-block;';
        } else {
            $angry_status = 'Angry';
        }

        // Reaction status check for "Angry"
        $wtf = $tools->Like_Check($res['stid'], "WTF", 'status');
        if($wtf){
            $wtf_status = 'UnWTF';
            $i_status = 'WTF';
            $like_statusicon = 'icon-like-blf--18'; 
            $lostyle = 'display:inline-block;';
        } else {
            $wtf_status = 'WTF';
        }


        echo '</div>' .
            '<div class="status_active list1">' .
            '<ul class="status_ul">' .
                '<li id="likehover" map="1" data="' . $res['stid'] . '">' .
                    '<div class="icon-lpn ' . $like_statusicon . ' reaction_grap-style reactionTrans" id="stt_ulk' . $res['stid'] . '" style="' . $lostyle . '"></div><div class="reatext" id="stt_reatext' . $res['stid'] . '">' . $i_status . '</div>';

                echo "\n" . '<div id="STTReactions' . $res['stid'] . '" class="STTReactions new_like_items">' .
                    "\n" . '<div class="like_hover op-lw like_button reactionTrans" id="sttlike' . $res['stid'] . '" rel="' . $like_status  . '" map="status"><div class="reactionTrans icon-newL icon-like-new"></div></div>'  .
                    "\n" . '<div class="love_hover op-lw like_button reactionTrans" id="sttlove' . $res['stid'] . '" rel="' . $love_status  . '" map="status"><div class="reactionTrans icon-newL icon-love-new"></div></div>'  .
                    "\n" . '<div class="haha_hover op-lw like_button reactionTrans" id="stthaha' . $res['stid'] . '" rel="' . $haha_status  . '" map="status"><div class="reactionTrans icon-newL icon-haha-new"></div></div>'  .
                    "\n" . '<div class="hihi_hover op-lw like_button reactionTrans" id="stthihi' . $res['stid'] . '" rel="' . $hihi_status  . '" map="status"><div class="reactionTrans icon-newL icon-hihi-new"></div></div>'  .
                    "\n" . '<div class="woww_hover op-lw like_button reactionTrans" id="sttwoww' . $res['stid'] . '" rel="' . $woww_status  . '" map="status"><div class="reactionTrans icon-newL icon-woww-new"></div></div>'  .
                    "\n" . '<div class="cry_hover op-lw like_button reactionTrans" id="sttcry' .  $res['stid'] . '" rel="' . $cry_status   . '" map="status"><div class="reactionTrans icon-newL icon-cry-new"></div></div>'   .
                    "\n" . '<div class="angry_hover op-lw like_button reactionTrans" id="sttangry'. $res['stid'] . '" rel="' . $angry_status . '" map="status"><div class="reactionTrans icon-newL icon-angry-new"></div></div>' .
                    "\n" . '<div class="wtf_hover op-lw like_button reactionTrans" id="sttwtf' .  $res['stid'] . '" rel="' . $wtf_status   . '" map="status"><div class="reactionTrans icon-newL icon-like-blf"></div></div>'  .
                "\n" . '</div>';

            if ($res['from_id'] != $res['user_id']) {
                $from = $tools->getUser($res['from_id']);
            }

            $countComment = $db->query("SELECT COUNT(*) FROM `cms_users_guestbook_comments` WHERE `post_id`='" . $res['stid'] . "' AND `type`='cmt' ")->fetchColumn();
            $limitComment = "";
            if($countComment > 4){
                $numlimit = $countComment - 4;
                $limitComment = " LIMIT " . $numlimit . ", 4";
            }

                echo '</li>' .
                '<li onclick="statusComment(' . $res['stid'] . ');">' .
                    'Bình Luận<span class="numCMT_' . $res['stid'] . ' numCMT' . ($countComment > 0 ? '' : ' hidden') . '"> (' . $countComment . ')</span>' .
                '</li>' .
                '<li>' .
                    'Chia sẻ' .
                '</li>' .
            '</ul>';
        echo '</div>';

            echo '<div class="status_comments list1">' .
                ($countComment > 4 ? '<div class="like-it like-pit" onclick="loadComments(' . $res['stid'] . ', true, this);" style="margin: 0px 0 10px 29px;">Xem thêm bình luận...</div>' : '') .
                '<div class="list_comment_' . $res['stid'] . '">';

            $reqComments = $db->query("SELECT `cms_users_guestbook_comments`.*, `cms_users_guestbook_comments`.`id` AS `scid`, `users`.`rights`, `users`.`sex`, `users`.`name`, `users`.`id`, `users`.`avatar_extension`
                FROM `cms_users_guestbook_comments` LEFT JOIN `users` ON `cms_users_guestbook_comments`.`user_id` = `users`.`id` WHERE `post_id` = '" . $res['stid'] . "' AND `type`='cmt' ORDER BY `scid` ASC" . $limitComment);

            // $reqComments = mysql_query("SELECT * FROM `cms_users_guestbook_comments` WHERE `post_id` = '" . $res['stid'] . "' ORDER BY `id` ASC" . $limitComment);

            if ($countComment) {
                while ($resComments = $reqComments->fetch()) {
                    
                    if (file_exists((ROOT_PATH . 'files/users/avatar/' . $resComments['user_id'] . '_100x100.' . $resComments['avatar_extension']))) {
                        $avatari = $config['homeurl'] . '/files/users/avatar/'.$resComments['id'].'_100x100.' . $resComments['avatar_extension'];
                    } else {
                        $avatari = $config['homeurl'] . '/images/default-'.($resComments['sex'] == 'm' ? 'male' : 'female').'-avatar.png';
                    }
                    $post = $tools->checkout($resComments['text'], 1, 1, 0, 1);

                    echo '<div class="comment' . $resComments['scid'] . ' comment-wrapper" ss-id="' . $resComments['scid'] . '">' .
                            '<div>' .
                                '<div class="fleft">' .
                                    '<img class="avatar" src="' . $avatari . '" alt="' . $resComments['name'] . '" />' .
                                '</div>' .
                                '<div class="comment_more">' .
                                    '<div class="comment_content">' .
                                        '<div class="comment">' .
                                            '<div>' .
                                                '<a href="' . $config['homeurl'] . '/profile/?user=' . $resComments['user_id'] . '" class="tload nickname' . ($resComments['rights'] == 9 ? ' red' : '') . '"><strong>' . $resComments['name'] . '</strong></a>:' .
                                                ' ' . $post .
                                            '</div>' .
                                            '<div class="cm_time"><span class="status_other_data gray">'.(round((time()-$resComments['time'])/3600) < 2 ? '<span class="ajax-time" title="' . $tools->timestamp($resComments['time']) . '">':'') . $tools->thoigian($resComments['time']) . (round((time()-$resComments['time'])/3600) < 2 ? '</span>':'') . '</span>' .
                                            '</div>' .
                                            '<div class="comment_reactions">';

                                            $sep = null;
                                            $lstyle = null;
                                            $allCheck = $tools->Like_CountTotal($resComments['scid'], 'status_comment');
                                            if(!$allCheck){
                                                $lstyle = "display:none;";
                                            }
                                            echo "\n" . '<ul class="who-likes-this-post" id="cmt_reactions'.$resComments['scid'].'" style="'.$lstyle.'">';
                                            //Like Started
                                            if($tools->Like_CountT($resComments['scid'], 'Like', 'status_comment')>0) { 
                                                echo '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="cmt_elike'.$resComments['scid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resComments['scid'], 'Like', 'status_comment') . '</span></li>'; 
                                            } else {
                                                echo '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="cmt_elike'.$resComments['scid'].'" style="display:none"></li>';
                                            }
                                            //Love Started
                                            if($tools->Like_CountT($resComments['scid'], 'Love', 'status_comment')){
                                                echo '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="cmt_elove'.$resComments['scid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resComments['scid'], 'Love', 'status_comment') . '</span></li>'; 
                                            } else {
                                                echo '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="cmt_elove'.$resComments['scid'].'" style="display:none"></li>';
                                            }
                                            //Haha Started
                                            if($tools->Like_CountT($resComments['scid'], 'Haha', 'status_comment')){
                                                echo '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="cmt_ehaha'.$resComments['scid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resComments['scid'], 'Haha', 'status_comment') . '</span></li>'; 
                                            } else {
                                                echo '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="cmt_ehaha'.$resComments['scid'].'" style="display:none"></li>';
                                            }
                                            //Hihi Started
                                            if($tools->Like_CountT($resComments['scid'], 'Hihi', 'status_comment')){
                                                echo '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="cmt_ehihi'.$resComments['scid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resComments['scid'], 'Hihi', 'status_comment') . '</span></li>'; 
                                            } else {
                                                echo '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="cmt_ehihi'.$resComments['scid'].'" style="display:none"></li>';
                                            }
                                            //Woww Started
                                            if($tools->Like_CountT($resComments['scid'], 'Woww', 'status_comment')){
                                                echo '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="cmt_ewoww'.$resComments['scid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resComments['scid'], 'Woww', 'status_comment') . '</span></li>'; 
                                            } else {
                                                echo '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="cmt_ewoww'.$resComments['scid'].'" style="display:none"></li>';
                                            }
                                            //Cry Started
                                            if($tools->Like_CountT($resComments['scid'], 'Cry', 'status_comment')){
                                                echo '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="cmt_ecry'.$resComments['scid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resComments['scid'], 'Cry', 'status_comment') . '</span></li>'; 
                                            } else {
                                                echo '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="cmt_ecry'.$resComments['scid'].'" style="display:none"></li>';
                                            }
                                            //Angry Started
                                            if($tools->Like_CountT($resComments['scid'], 'Angry', 'status_comment')){
                                                echo '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="cmt_eangry'.$resComments['scid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resComments['scid'], 'Angry', 'status_comment') . '</span></li>'; 
                                            } else {
                                                echo '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="cmt_eangry'.$resComments['scid'].'" style="display:none"></li>';
                                            }
                                            //WTF Started
                                            if($tools->Like_CountT($resComments['scid'], 'WTF', 'status_comment')){
                                                echo '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="cmt_ewtf'.$resComments['scid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resComments['scid'], 'WTF', 'status_comment') . '</span></li>'; 
                                            } else {
                                                echo '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="cmt_ewtf'.$resComments['scid'].'" style="display:none"></li>';
                                            }
                                            echo '<li class="totalco" id="cmt_totalco'.$resComments['scid'].'">'.$tools->Like_CountTotal($resComments['scid'], 'status_comment').'</li>'; 
                                            echo "\n" . '</ul>';


                                            echo '</div>' .
                                        '</div>' .
                                        '<span class="cm_more"><i class="material-icons">&#xE5D3;</i></span>' .
                                    '</div>';


                                    $like = $tools->Like_Check($resComments['scid'], "Like", 'status_comment');
                                    $like_statusicon = 'icon-like-blf--18';
                                    $lostyle = 'display:none;';
                                    $i_status = 'Thích'; 
                                    if($like) {
                                        $like_status = 'UnLike';
                                        $i_status = 'Like';
                                        $like_statusicon = 'icon-like-new--18';
                                        $lostyle = 'display:block;';
                                    } else {
                                        $like_status = 'Like';
                                    }

                                    // Reaction status check for "Love"
                                    $love = $tools->Like_Check($resComments['scid'], "Love", 'status_comment');
                                    if($love){
                                        $love_status = 'UnLove';
                                        $i_status = 'Love';
                                        $like_statusicon = 'icon-love-new--18'; 
                                        $lostyle = 'display:block;';
                                    } else {
                                        $love_status = 'Love';
                                    }

                                    // Reaction status check for "Haha"
                                    $haha = $tools->Like_Check($resComments['scid'], "Haha", 'status_comment');
                                    if($haha){
                                        $haha_status = 'UnHaha';
                                        $i_status = 'Haha';
                                        $like_statusicon = 'icon-haha-new--18'; 
                                        $lostyle = 'display:block;';
                                    } else {
                                        $haha_status = 'Haha';
                                    }

                                    // Reaction status check for "Hihi"
                                    $hihi = $tools->Like_Check($resComments['scid'], "Hihi", 'status_comment');
                                    if($hihi){
                                        $hihi_status = 'UnHihi';
                                        $i_status = 'Hihi';
                                        $like_statusicon = 'icon-hihi-new--18'; 
                                        $lostyle = 'display:block;';
                                    } else {
                                        $hihi_status = 'Hihi';
                                    }

                                    // Reaction status check for "Woww"
                                    $woww = $tools->Like_Check($resComments['scid'], "Woww", 'status_comment');
                                    if($woww){
                                        $woww_status = 'UnWoww';
                                        $i_status = 'Woww';
                                        $like_statusicon = 'icon-woww-new--18'; 
                                        $lostyle = 'display:block;';
                                    } else {
                                        $woww_status = 'Woww';
                                    }

                                    // Reaction status check for "Cry"
                                    $cry = $tools->Like_Check($resComments['scid'], "Cry", 'status_comment');
                                    if($cry){
                                        $cry_status = 'UnCry';
                                        $i_status = 'Cry';
                                        $like_statusicon = 'icon-cry-new--18'; 
                                        $lostyle = 'display:block;';
                                    } else {
                                        $cry_status = 'Cry'; 
                                    }

                                    // Reaction status check for "Angry"
                                    $angry = $tools->Like_Check($resComments['scid'], "Angry", 'status_comment');
                                    if($angry){
                                        $angry_status = 'UnAngry';
                                        $i_status = 'Angry';
                                        $like_statusicon = 'icon-angry-new--18'; 
                                        $lostyle = 'display:block;';
                                    } else {
                                        $angry_status = 'Angry';
                                    }

                                    // Reaction status check for "Angry"
                                    $wtf = $tools->Like_Check($resComments['scid'], "WTF", 'status_comment');
                                    if($wtf){
                                        $wtf_status = 'UnWTF';
                                        $i_status = 'WTF';
                                        $like_statusicon = 'icon-like-blf--18'; 
                                        $lostyle = 'display:block;';
                                    } else {
                                        $wtf_status = 'WTF';
                                    }

                                    echo '<ul class="comment_active">' .
                                        '<li id="likehover" map="2" data="' . $resComments['scid'] . '">' .
                                            '<div class="reatext" id="cmt_reatext' . $resComments['scid'] . '">' . $i_status . '</div>';

                                        echo "\n" . '<div id="CMTReactions' . $resComments['scid'] . '" class="CMTReactions new_like_items">' .
                                            "\n" . '<div class="like_hover op-lw like_button reactionTrans" id="cmtlike' . $resComments['scid'] . '" rel="' . $like_status  . '" map="status_comment"><div class="reactionTrans icon-newL icon-like-new"></div></div>'  .
                                            "\n" . '<div class="love_hover op-lw like_button reactionTrans" id="cmtlove' . $resComments['scid'] . '" rel="' . $love_status  . '" map="status_comment"><div class="reactionTrans icon-newL icon-love-new"></div></div>'  .
                                            "\n" . '<div class="haha_hover op-lw like_button reactionTrans" id="cmthaha' . $resComments['scid'] . '" rel="' . $haha_status  . '" map="status_comment"><div class="reactionTrans icon-newL icon-haha-new"></div></div>'  .
                                            "\n" . '<div class="hihi_hover op-lw like_button reactionTrans" id="cmthihi' . $resComments['scid'] . '" rel="' . $hihi_status  . '" map="status_comment"><div class="reactionTrans icon-newL icon-hihi-new"></div></div>'  .
                                            "\n" . '<div class="woww_hover op-lw like_button reactionTrans" id="cmtwoww' . $resComments['scid'] . '" rel="' . $woww_status  . '" map="status_comment"><div class="reactionTrans icon-newL icon-woww-new"></div></div>'  .
                                            "\n" . '<div class="cry_hover op-lw like_button reactionTrans" id="cmtcry' .  $resComments['scid'] . '" rel="' . $cry_status   . '" map="status_comment"><div class="reactionTrans icon-newL icon-cry-new"></div></div>'   .
                                            "\n" . '<div class="angry_hover op-lw like_button reactionTrans" id="cmtangry'. $resComments['scid'] . '" rel="' . $angry_status . '" map="status_comment"><div class="reactionTrans icon-newL icon-angry-new"></div></div>' .
                                            "\n" . '<div class="wtf_hover op-lw like_button reactionTrans" id="cmtwtf' .  $resComments['scid'] . '" rel="' . $wtf_status   . '" map="status_comment"><div class="reactionTrans icon-newL icon-like-blf"></div></div>'  .
                                        "\n" . '</div>';

                        $countReply = $db->query("SELECT COUNT(*) FROM `cms_users_guestbook_comments` WHERE `post_id`='" . $resComments['scid'] . "' AND `type`='reply' ")->fetchColumn();
                        $limitReply = "";
                        if($countReply > 4){
                            $numlimit = $countReply - 4;
                            $limitReply = " LIMIT " . $numlimit . ", 4";
                        }
                                        echo '</li>' .
                                        '<li onclick="comment_reply(' . $resComments['scid'] . ');">' .
                                            'Trả lời<span class="numREP_' . $resComments['scid'] . ' numCMT' . ($countReply > 0 ? '' : ' hidden') . '"> (' . $countReply . ')</span>' .
                                        '</li>' .
                                    '</ul>';


                        echo '<div class="comment_replys">' .
                            ($countReply > 4 ? '<div class="like-it like-pit" onclick="loadReplys(' . $resComments['scid'] . ', true, this);" style="margin: 0px 0 10px 17px;">Xem thêm trả lời...</div>' : '') .
                            '<div class="list_reply_' . $resComments['scid'] . ' list_reply">';

                        $reqReplys = $db->query("SELECT `cms_users_guestbook_comments`.*, `cms_users_guestbook_comments`.`id` AS `crid`, `users`.`rights`, `users`.`sex`, `users`.`name`, `users`.`id`, `users`.`avatar_extension`
                            FROM `cms_users_guestbook_comments` LEFT JOIN `users` ON `cms_users_guestbook_comments`.`user_id` = `users`.`id` WHERE `post_id` = '" . $resComments['scid'] . "' AND `type`='reply' ORDER BY `crid` ASC" . $limitReply);

                        if ($countReply) {
                            while ($resReplys = $reqReplys->fetch()) {

                                if (file_exists((ROOT_PATH . 'files/users/avatar/' . $resReplys['user_id'] . '_100x100.' . $resReplys['avatar_extension']))) {
                                    $avatari = $config['homeurl'] . '/files/users/avatar/'.$resReplys['id'].'_100x100.' . $resReplys['avatar_extension'];
                                } else {
                                    $avatari = $config['homeurl'] . '/images/default-'.($resReplys['sex'] == 'm' ? 'male' : 'female').'-avatar.png';
                                }
                                $post = $tools->checkout($resReplys['text'], 1, 1, 0, 1);

                                echo '<div class="reply' . $resReplys['crid'] . ' reply-wrapper" ss-id="' . $resReplys['crid'] . '">' .
                                        '<div>' .
                                            '<div class="fleft">' .
                                                '<img class="avatar" src="' . $avatari . '" alt="' . $resReplys['name'] . '" />' .
                                            '</div>' .
                                            '<div class="comment_more">' .
                                                '<div class="comment_content">' .
                                                    '<div class="comment">' .
                                                        '<div>' .
                                                            '<a href="' . $config['homeurl'] . '/profile/?user=' . $resReplys['user_id'] . '" class="tload nickname' . ($resReplys['rights'] == 9 ? ' red' : '') . '"><strong>' . $resReplys['name'] . '</strong></a>:' .
                                                            ' ' . $post .
                                                        '</div>' .
                                                        '<div class="cm_time"><span class="status_other_data gray">'.(round((time()-$resReplys['time'])/3600) < 2 ? '<span class="ajax-time" title="' . $tools->timestamp($resReplys['time']) . '">':'') . $tools->thoigian($resReplys['time']) . (round((time()-$resReplys['time'])/3600) < 2 ? '</span>':'') . '</span>' .
                                                        '</div>' .
                                                        '<div class="comment_reactions">';


                                                        $sep = null;
                                                        $lstyle = null;
                                                        $allCheck = $tools->Like_CountTotal($resReplys['crid'], 'status_reply');
                                                        if(!$allCheck){
                                                            $lstyle = "display:none;";
                                                        }
                                                        echo "\n" . '<ul class="who-likes-this-post" id="rep_reactions'.$resReplys['crid'].'" style="'.$lstyle.'">';
                                                        //Like Started
                                                        if($tools->Like_CountT($resReplys['crid'], 'Like', 'status_reply')>0) { 
                                                            echo '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="rep_elike'.$resReplys['crid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resReplys['crid'], 'Like', 'status_reply') . '</span></li>'; 
                                                        } else {
                                                            echo '<li class="likes reaction_wrap-style icon-newL reaction16px like lpos" id="rep_elike'.$resReplys['crid'].'" style="display:none"></li>';
                                                        }
                                                        //Love Started
                                                        if($tools->Like_CountT($resReplys['crid'], 'Love', 'status_reply')){
                                                            echo '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="rep_elove'.$resReplys['crid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resReplys['crid'], 'Love', 'status_reply') . '</span></li>'; 
                                                        } else {
                                                            echo '<li class="loves reaction_wrap-style icon-newL reaction16px love lpos" id="rep_elove'.$resReplys['crid'].'" style="display:none"></li>';
                                                        }
                                                        //Haha Started
                                                        if($tools->Like_CountT($resReplys['crid'], 'Haha', 'status_reply')){
                                                            echo '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="rep_ehaha'.$resReplys['crid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resReplys['crid'], 'Haha', 'status_reply') . '</span></li>'; 
                                                        } else {
                                                            echo '<li class="hahas reaction_wrap-style icon-newL reaction16px haha lpos" id="rep_ehaha'.$resReplys['crid'].'" style="display:none"></li>';
                                                        }
                                                        //Hihi Started
                                                        if($tools->Like_CountT($resReplys['crid'], 'Hihi', 'status_reply')){
                                                            echo '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="rep_ehihi'.$resReplys['crid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resReplys['crid'], 'Hihi', 'status_reply') . '</span></li>'; 
                                                        } else {
                                                            echo '<li class="hihis reaction_wrap-style icon-newL reaction16px hihi lpos" id="rep_ehihi'.$resReplys['crid'].'" style="display:none"></li>';
                                                        }
                                                        //Woww Started
                                                        if($tools->Like_CountT($resReplys['crid'], 'Woww', 'status_reply')){
                                                            echo '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="rep_ewoww'.$resReplys['crid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resReplys['crid'], 'Woww', 'status_reply') . '</span></li>'; 
                                                        } else {
                                                            echo '<li class="wowws reaction_wrap-style icon-newL reaction16px woww lpos" id="rep_ewoww'.$resReplys['crid'].'" style="display:none"></li>';
                                                        }
                                                        //Cry Started
                                                        if($tools->Like_CountT($resReplys['crid'], 'Cry', 'status_reply')){
                                                            echo '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="rep_ecry'.$resReplys['crid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resReplys['crid'], 'Cry', 'status_reply') . '</span></li>'; 
                                                        } else {
                                                            echo '<li class="crys reaction_wrap-style icon-newL reaction16px cry lpos" id="rep_ecry'.$resReplys['crid'].'" style="display:none"></li>';
                                                        }
                                                        //Angry Started
                                                        if($tools->Like_CountT($resReplys['crid'], 'Angry', 'status_reply')){
                                                            echo '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="rep_eangry'.$resReplys['crid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resReplys['crid'], 'Angry', 'status_reply') . '</span></li>'; 
                                                        } else {
                                                            echo '<li class="angrys reaction_wrap-style icon-newL reaction16px angry lpos" id="rep_eangry'.$resReplys['crid'].'" style="display:none"></li>';
                                                        }
                                                        //WTF Started
                                                        if($tools->Like_CountT($resReplys['crid'], 'WTF', 'status_reply')){
                                                            echo '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="rep_ewtf'.$resReplys['crid'].'"><span class="reaction_use">' . $tools->Reactions_URel($resReplys['crid'], 'WTF', 'status_reply') . '</span></li>'; 
                                                        } else {
                                                            echo '<li class="wtfs reaction_wrap-style icon-newL reaction16px wtf lpos" id="rep_ewtf'.$resReplys['crid'].'" style="display:none"></li>';
                                                        }
                                                        echo '<li class="totalco" id="rep_totalco'.$resReplys['crid'].'">'.$tools->Like_CountTotal($resReplys['crid'], 'status_reply').'</li>'; 
                                                        echo "\n" . '</ul>';



                                                        echo '</div>' .
                                                    '</div>' .
                                                    '<span class="cm_more"><i class="material-icons">&#xE5D3;</i></span>' .
                                                '</div>';



                                                $like = $tools->Like_Check($resReplys['crid'], "Like", 'status_reply');
                                                $like_statusicon = 'icon-like-blf--18';
                                                $lostyle = 'display:none;';
                                                $i_status = 'Thích'; 
                                                if($like) {
                                                    $like_status = 'UnLike';
                                                    $i_status = 'Like';
                                                    $like_statusicon = 'icon-like-new--18';
                                                    $lostyle = 'display:block;';
                                                } else {
                                                    $like_status = 'Like';
                                                }

                                                // Reaction status check for "Love"
                                                $love = $tools->Like_Check($resReplys['crid'], "Love", 'status_reply');
                                                if($love){
                                                    $love_status = 'UnLove';
                                                    $i_status = 'Love';
                                                    $like_statusicon = 'icon-love-new--18'; 
                                                    $lostyle = 'display:block;';
                                                } else {
                                                    $love_status = 'Love';
                                                }

                                                // Reaction status check for "Haha"
                                                $haha = $tools->Like_Check($resReplys['crid'], "Haha", 'status_reply');
                                                if($haha){
                                                    $haha_status = 'UnHaha';
                                                    $i_status = 'Haha';
                                                    $like_statusicon = 'icon-haha-new--18'; 
                                                    $lostyle = 'display:block;';
                                                } else {
                                                    $haha_status = 'Haha';
                                                }

                                                // Reaction status check for "Hihi"
                                                $hihi = $tools->Like_Check($resReplys['crid'], "Hihi", 'status_reply');
                                                if($hihi){
                                                    $hihi_status = 'UnHihi';
                                                    $i_status = 'Hihi';
                                                    $like_statusicon = 'icon-hihi-new--18'; 
                                                    $lostyle = 'display:block;';
                                                } else {
                                                    $hihi_status = 'Hihi';
                                                }

                                                // Reaction status check for "Woww"
                                                $woww = $tools->Like_Check($resReplys['crid'], "Woww", 'status_reply');
                                                if($woww){
                                                    $woww_status = 'UnWoww';
                                                    $i_status = 'Woww';
                                                    $like_statusicon = 'icon-woww-new--18'; 
                                                    $lostyle = 'display:block;';
                                                } else {
                                                    $woww_status = 'Woww';
                                                }

                                                // Reaction status check for "Cry"
                                                $cry = $tools->Like_Check($resReplys['crid'], "Cry", 'status_reply');
                                                if($cry){
                                                    $cry_status = 'UnCry';
                                                    $i_status = 'Cry';
                                                    $like_statusicon = 'icon-cry-new--18'; 
                                                    $lostyle = 'display:block;';
                                                } else {
                                                    $cry_status = 'Cry'; 
                                                }

                                                // Reaction status check for "Angry"
                                                $angry = $tools->Like_Check($resReplys['crid'], "Angry", 'status_reply');
                                                if($angry){
                                                    $angry_status = 'UnAngry';
                                                    $i_status = 'Angry';
                                                    $like_statusicon = 'icon-angry-new--18'; 
                                                    $lostyle = 'display:block;';
                                                } else {
                                                    $angry_status = 'Angry';
                                                }

                                                // Reaction status check for "Angry"
                                                $wtf = $tools->Like_Check($resReplys['crid'], "WTF", 'status_reply');
                                                if($wtf){
                                                    $wtf_status = 'UnWTF';
                                                    $i_status = 'WTF';
                                                    $like_statusicon = 'icon-like-blf--18'; 
                                                    $lostyle = 'display:block;';
                                                } else {
                                                    $wtf_status = 'WTF';
                                                }

                                                echo '<ul class="comment_active">' .
                                                    '<li id="likehover" map="3" data="' . $resReplys['crid'] . '">' .
                                                        '<div class="reatext" id="rep_reatext' . $resReplys['crid'] . '">' . $i_status . '</div>';

                                                        echo "\n" . '<div id="ReplyReactions' . $resReplys['crid'] . '" class="ReplyReactions new_like_items">' .
                                                        "\n" . '<div class="like_hover op-lw like_button reactionTrans" id="replike' . $resReplys['crid'] . '" rel="' . $like_status  . '" map="status_reply"><div class="reactionTrans icon-newL icon-like-new"></div></div>'  .
                                                        "\n" . '<div class="love_hover op-lw like_button reactionTrans" id="replove' . $resReplys['crid'] . '" rel="' . $love_status  . '" map="status_reply"><div class="reactionTrans icon-newL icon-love-new"></div></div>'  .
                                                        "\n" . '<div class="haha_hover op-lw like_button reactionTrans" id="rephaha' . $resReplys['crid'] . '" rel="' . $haha_status  . '" map="status_reply"><div class="reactionTrans icon-newL icon-haha-new"></div></div>'  .
                                                        "\n" . '<div class="hihi_hover op-lw like_button reactionTrans" id="rephihi' . $resReplys['crid'] . '" rel="' . $hihi_status  . '" map="status_reply"><div class="reactionTrans icon-newL icon-hihi-new"></div></div>'  .
                                                        "\n" . '<div class="woww_hover op-lw like_button reactionTrans" id="repwoww' . $resReplys['crid'] . '" rel="' . $woww_status  . '" map="status_reply"><div class="reactionTrans icon-newL icon-woww-new"></div></div>'  .
                                                        "\n" . '<div class="cry_hover op-lw like_button reactionTrans" id="repcry' .  $resReplys['crid'] . '" rel="' . $cry_status   . '" map="status_reply"><div class="reactionTrans icon-newL icon-cry-new"></div></div>'   .
                                                        "\n" . '<div class="angry_hover op-lw like_button reactionTrans" id="repangry'. $resReplys['crid'] . '" rel="' . $angry_status . '" map="status_reply"><div class="reactionTrans icon-newL icon-angry-new"></div></div>' .
                                                        "\n" . '<div class="wtf_hover op-lw like_button reactionTrans" id="repwtf' .  $resReplys['crid'] . '" rel="' . $wtf_status   . '" map="status_reply"><div class="reactionTrans icon-newL icon-like-blf"></div></div>'  .
                                                    "\n" . '</div>';

                                                    echo '</li>' .
                                                    '<li onclick="re_reply(' . $resComments['scid'] . ', \'' . $resReplys['name'] . '\');">Trả lời</li>' .
                                                '</ul>';

                                        echo '</div>' .
                                        '</div>' .
                                    '</div>';
                            }
                        }

                        echo '</div>' .
                            '<div class="comment-write">' .
                                '<div class="fleft">' .
                                    '<img class="avatar" src="' . $mcavatar . '" >' .
                                '</div>' .
                                '<div class="comment-textarea">' .
                                    '<textarea class="js_add_nick" name="text" placeholder="Trả lời.?" onkeyup="commentReply(this.value,' . $resComments['scid'] . ',event);"></textarea>' .
                                '</div>' .
                            '</div>' .
                            '</div>' .
                        '</div>';
                    echo '</div></div>';
                }
            }

            echo '</div>' .
                '<div class="comment-write">' .
                    '<div class="fleft">' .
                        '<img class="avatar" src="' . $mcavatar . '" >' .
                    '</div>' .
                    '<div class="comment-textarea">' .
                        '<textarea name="text" placeholder="Bạn thấy sao.?" onkeyup="postComment(this.value,' . $res['stid'] . ',event);"></textarea>' .
                    '</div>' .
                '</div>' .
                '</div>';

        echo '</div>';

        $i++;
    }
}
echo '</div>';
if($total > 10){
    echo '<div class="phieubacChat button-container" data="2">' .
        '<button class="button" onclick="loadStatus(true, this);" style="margin: 0px 0 20px 0;"><span>Xem thêm</span></button>' .
        '</div>';
}
