<?php
    /** Mod ReaCtions for JohnCMS By MrT98
    * PhieuBac.Net Team CMS
    * Copyright by MrT98
    */


    define('_IN_JOHNCMS', 1);
    require('system/bootstrap.php');

    /** @var Psr\Container\ContainerInterface $container */
    $container = App::getContainer();

    /** @var PDO $db */
    $db = $container->get(PDO::class);

    /** @var Johncms\Api\UserInterface $systemUser */
    $systemUser = $container->get(Johncms\Api\UserInterface::class);

    /** @var Johncms\Api\ToolsInterface $tools */
    $tools = $container->get(Johncms\Api\ToolsInterface::class);

    /** @var Johncms\Api\ConfigInterface $config */
    $config = $container->get(Johncms\Api\ConfigInterface::class);

    $msg_id = trim($_POST['msg_id']);
    $rel    = trim($_POST['rel']);
    $map    = trim($_POST['map']);
    $cdata  = null;
    $notice = '';

    if($systemUser->isValid() && !empty($msg_id) && !empty($rel) && !empty($map)) {
        switch ($map) {
            case 'forum':
                $data = $db->query("SELECT `user_id` FROM `forum` WHERE `id` = " . $db->quote($msg_id) . " ")->fetch();
                $notice = 'forum.reaction';

                break;

             case 'status':
                $data = $db->query("SELECT `user_id` FROM `cms_users_guestbook` WHERE `id` = " . $db->quote($msg_id) . " ")->fetch();
                $notice = 'status.reaction.status';

                break;

             case 'status_comment':
                $data = $db->query("SELECT `user_id` FROM `cms_users_guestbook_comments` WHERE `id` = " . $db->quote($msg_id) . " AND `type`='cmt' ")->fetch();
                $notice = 'status.reaction.comment';

                break;

             case 'status_reply':
                $data = $db->query("SELECT `user_id` FROM `cms_users_guestbook_comments` WHERE `id` = " . $db->quote($msg_id) . " AND `type`='reply' ")->fetch();
                $notice = 'status.reaction.reply.';

                break;
            
            default:
                // code...
                break;
        }
        if ($data && $data['user_id'] != $systemUser->id) {
            $row = $db->query("SELECT `id` FROM `cms_mail` WHERE `user_id` = '" . $systemUser->id . "' AND `from_id` = '" . $data['user_id'] . "' AND `reid` = " . $db->quote($msg_id) . " AND `type` = '" . $notice . "' AND `sys` = '1' ")->fetch();
            if (in_array($rel, array('Like','Love','Haha','Hihi','Woww','Cry','Angry','WTF'))){
                /** Begin notice */
                if($row) {
                    $db->exec("UPDATE `cms_mail` SET `them` = " . $db->quote($rel) . " WHERE `id` = '" . $row['id'] . "' ");
                } else {
                    $db->prepare('
                        INSERT INTO `cms_mail` SET
                            `user_id` = ?,
                            `from_id` = ?,
                            `them` = ?,
                            `sys` = \'1\',
                            `time` = ?,
                            `reid` = ?,
                            `type` = ?
                    ')->execute([
                        $systemUser->id,
                        $data['user_id'],
                        $rel,
                        time(),
                        $msg_id,
                        $notice,
                    ]);
                }
                /** End notice */
            } else if ($row && in_array($rel, array('UnLike','UnLove','UnHaha','UnHihi','UnWoww','UnCry','UnAngry','UnWTF'))) {
                $db->exec("DELETE FROM `cms_mail` WHERE `id` = '" . $row['id'] . "' ");
            }
        }

        if (in_array($rel, array('Like','Love','Haha','Hihi','Woww','Cry','Angry','WTF'))){
            $cdata = $tools->Like($msg_id, $rel, $map);
        } else if (in_array($rel, array('UnLike','UnLove','UnHaha','UnHihi','UnWoww','UnCry','UnAngry','UnWTF'))) {
            $cdata = $tools->Unlike($msg_id, $map);
        }
        $data = array(
            'text'   => $cdata,
            'count'    => array(
                'Like'  => $tools->Like_CountT($msg_id, 'Like',  $map),
                'Love'  => $tools->Like_CountT($msg_id, 'Love',  $map),
                'Haha'  => $tools->Like_CountT($msg_id, 'Haha',  $map),
                'Hihi'  => $tools->Like_CountT($msg_id, 'Hihi',  $map),
                'Woww'  => $tools->Like_CountT($msg_id, 'Woww',  $map),
                'Cry'   => $tools->Like_CountT($msg_id, 'Cry',   $map),
                'Angry' => $tools->Like_CountT($msg_id, 'Angry', $map),
                'WTF'   => $tools->Like_CountT($msg_id, 'WTF',   $map)
            )
        );
        header("Content-type: application/json; charset=utf-8");
        echo json_encode($data);
        exit();
    }
