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

if ($id && $systemUser->isValid()) {
    /** @var Psr\Container\ContainerInterface $container */
    $container = App::getContainer();

    /** @var PDO $db */
    $db = $container->get(PDO::class);

    /** @var Johncms\Api\ToolsInterface $tools */
    $tools = $container->get(Johncms\Api\ToolsInterface::class);

    $error = false;

    // Скачивание прикрепленного файла Форума
    $req = $db->query("SELECT * FROM `cms_forum_files` WHERE `id` = '$id'");

    if ($req->rowCount()) {
        $res = $req->fetch();

        if (file_exists('../files/forum/attach/' . $res['filename'])) {
        	if ($res['balans'] == 0) {
        		$dlcount = $res['dlcount'] + 1;
            	$db->exec("UPDATE `cms_forum_files` SET  `dlcount` = '$dlcount' WHERE `id` = '$id'");
            	header('location: ../files/forum/attach/' . $res['filename']);
        	} else if ($res['balans'] > 0 && $systemUser->balans < $res['balans']) {
        		echo 'bạn chưa đủ tiền';
        	} else if ($res['balans'] > 0 && $systemUser->balans >= $res['balans']) {
        		echo 'sác nhận mua';
        	}
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if ($error) {
        require('../system/head.php');
        echo $tools->displayError(_t('File does not exist'), '<a href="index.php">' . _t('Forum') . '</a>');
        require('../system/end.php');
        exit;
    }
} else {
    header('location: index.php');
}
