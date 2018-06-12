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
    $notice = 'forum.buyfile';

    // Скачивание прикрепленного файла Форума
    $req = $db->query("SELECT * FROM `cms_forum_files` WHERE `id` = '$id'");

    if ($req->rowCount()) {
        $res = $req->fetch();
        $post = $db->query("SELECT `forum`.*, `users`.`name`, `users`.`sex`, `users`.`rights`, `users`.`lastdate`, `users`.`status`, `users`.`datereg`
            FROM `forum` LEFT JOIN `users` ON `forum`.`user_id` = `users`.`id`
            WHERE `forum`.`type` = 'm' AND `forum`.`id` = '" . $res['post'] . "' LIMIT 1")->fetch();
        $them = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `id` = '" . $post['refid'] . "'")->fetch();
        $page = ceil($db->query("SELECT COUNT(*) FROM `forum` WHERE `refid` = '" . $post['refid'] . "' AND `id` " . ($set_forum['upfp'] ? ">=" : "<=") . " '" . $res['post'] . "'")->fetchColumn() / $kmess);

        $linkfile = '../files/forum/attach/' . $res['filelink'];
        if (file_exists($linkfile)) {
            $fsize = filesize($linkfile);
            $fls = round($fsize / 1024, 2);
            if ($systemUser->id == $res['user_id']) {
                require('../system/head.php');
                if (isset($_POST['submit'])) {
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename=' . $res['filename']);
                    header('Content-Length: ' . $fsize);
                    header('Pragma: public');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

                    $range = '';

                    if(isset($_SERVER['HTTP_RANGE'])) {
                        list($size_unit, $rangeOrig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

                        if ($size_unit == 'bytes') {
                            // Multiple ranges could be specified at the same time, but for simplicity only serve the first range
                            // http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt

                            $range = explode(',', $rangeOrig);
                            $range = explode('-', $range[0]);

                            @list($range, $extraRanges) = $range;

                            if (is_numeric($range) == false)
                                $range = '0';
                        } else {
                            header('HTTP/1.1 416 Requested Range Not Satisfiable');
                            exit;
                        }
                    } else {
                        $range = '-';
                    }

                    $ranges    = explode('-', $range, 2);
                    $seekStart = null;
                    $seekEnd   = null;

                    if (count($ranges) >= 2)
                        list($seekStart, $seekEnd) = $ranges;

                    // Set start and end based on range (if set), else set defaults
                    // Also check for invalid ranges.

                    if (empty($seekEnd) || is_null($seekEnd))
                        $seekEnd = $fsize - 1;
                    else
                        $seekEnd = min(abs(intval($seekEnd)), $fsize - 1);

                    if (empty($seekStart) || is_null($seekStart) || $seekEnd < abs(intval($seekStart)))
                        $seekStart = 0;
                    else
                        $seekStart = max(abs(intval($seekStart)), 0);

                    // Only send partial content header if downloading a piece of the file (IE workaround)
                    if ($seekStart > 0 || $seekEnd < ($fsize - 1)) {
                        header('HTTP/1.1 206 Partial Content');
                        header('Content-Range: bytes ' . $seekStart . '-' . $seekEnd . '/' . $fsize);
                        header('Content-Length: ' . ($seekEnd - $seekStart + 1));
                    } else {
                        header('Content-Length: ' . $fsize);
                    }

                    header('Accept-Ranges: bytes');

                    $file = @fopen($linkfile, 'rb');

                    if (function_exists('set_time_limit'))
                        @set_time_limit(0);

                    @fseek($file, $seekStart);

                    while(@feof($file) == false) {
                        echo(@fread($file, 1024 * 8));
                        @ob_flush();
                        @flush();

                        if (connection_status() != 0) {
                            @fclose($file);
                            exit;
                        }
                    }

                    // File save was a success
                    @fclose($file);
                    exit();
                }

                echo '<div class="mrt-code card shadow--2dp">
                    <div class="phdr"><strong>Tập Tin</strong></div>
                    <div class="card__actions card--border">
                    - Tên: <span class="red">' . $res['filename'] . '</span><br />
                    - Giá: <span class="red">' . number_format($res['balans'], 0, ",", ".") . ' VNĐ</span><br />
                    - Kích thước: <span class="red">' . $fls . ' kb</span><br />
                    - Tải lên lúc: <span class="red">' . $tools->thoigian($res['time']) . '</span><br />
                    </div>
                    <div class="card__actions">
                    <form method="post">
                    <div class="button-container"><button class="button" type="submit" name="submit" style="margin:0;">
                    <span class="flex-center">Tải về</span></button>
                    </div>
                    </form>
                    </div>
                    <div class="list1">
                    <a href="' . $config['homeurl'] . '/forum/' . $post['refid'] . '/' . $them['seo'] . ($page > 1 ? '_p' . $page : '') . '.html#post-' . $post['id'] . '">' . _t('Back to topic') . '</a>
                    </div>
                    </div>';
            } else {

                if ($res['balans'] == 0) {
                    $dlcount = $res['dlcount'] + 1;
                    $db->exec("UPDATE `cms_forum_files` SET  `dlcount` = '$dlcount' WHERE `id` = '$id'");

                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename=' . $res['filename']);
                    header('Content-Length: ' . $fsize);
                    header('Pragma: public');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

                    $range = '';

                    if(isset($_SERVER['HTTP_RANGE'])) {
                        list($size_unit, $rangeOrig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

                        if ($size_unit == 'bytes') {
                            // Multiple ranges could be specified at the same time, but for simplicity only serve the first range
                            // http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt

                            $range = explode(',', $rangeOrig);
                            $range = explode('-', $range[0]);

                            @list($range, $extraRanges) = $range;

                           if (is_numeric($range) == false)
                               $range = '0';
                        } else {
                            header('HTTP/1.1 416 Requested Range Not Satisfiable');
                            exit;
                        }
                    } else {
                        $range = '-';
                    }

                    $ranges    = explode('-', $range, 2);
                    $seekStart = null;
                    $seekEnd   = null;

                    if (count($ranges) >= 2)
                        list($seekStart, $seekEnd) = $ranges;

                    // Set start and end based on range (if set), else set defaults
                    // Also check for invalid ranges.

                    if (empty($seekEnd) || is_null($seekEnd))
                        $seekEnd = $fsize - 1;
                    else
                        $seekEnd = min(abs(intval($seekEnd)), $fsize - 1);

                    if (empty($seekStart) || is_null($seekStart) || $seekEnd < abs(intval($seekStart)))
                        $seekStart = 0;
                    else
                        $seekStart = max(abs(intval($seekStart)), 0);

                    // Only send partial content header if downloading a piece of the file (IE workaround)
                    if ($seekStart > 0 || $seekEnd < ($fsize - 1)) {
                        header('HTTP/1.1 206 Partial Content');
                        header('Content-Range: bytes ' . $seekStart . '-' . $seekEnd . '/' . $fsize);
                        header('Content-Length: ' . ($seekEnd - $seekStart + 1));
                    } else {
                      header('Content-Length: ' . $fsize);
                    }

                    header('Accept-Ranges: bytes');

                    $file = @fopen($linkfile, 'rb');

                    if (function_exists('set_time_limit'))
                        @set_time_limit(0);

                    @fseek($file, $seekStart);

                    while(@feof($file) == false) {
                        echo(@fread($file, 1024 * 8));
                        @ob_flush();
                        @flush();

                        if (connection_status() != 0) {
                            @fclose($file);
                            exit;
                        }
                    }

                    // File save was a success
                    @fclose($file);

                    exit();
                } else if ($res['balans'] > 0 && $systemUser->balans < $res['balans']) {
                    require('../system/head.php');
                    $user = $tools->getUser($res['user_id']);
                    echo '<div class="mrt-code card shadow--2dp">
                        <div class="phdr"><strong>Mua Tập Tin</strong></div>
                        <div class="rmenu text-center">Bạn không đủ tiền.!!</div>
                        <div class="card__actions card--border">
                        - Tên: <span class="red">' . $res['filename'] . '</span><br />
                        - Giá: <span class="red">' . number_format($res['balans'], 0, ",", ".") . ' VNĐ</span><br />
                        - Kích thước: <span class="red">' . $fls . ' kb</span><br />
                        - Chủ sở hữu: <span class="red">' . $user['name'] . '</span><br />
                        - Tải lên lúc: <span class="red">' . $tools->thoigian($res['time']) . '</span><br />
                        </div>
                        <div class="list1">
                        <a href="' . $config['homeurl'] . '/forum/' . $post['refid'] . '/' . $them['seo'] . ($page > 1 ? '_p' . $page : '') . '.html#post-' . $post['id'] . '">' . _t('Back to topic') . '</a>
                        </div>
                        </div>';
                } else if ($res['balans'] > 0 && $systemUser->balans >= $res['balans']) {
                    if (isset($_POST['submit'])) {
                        $dlcount = $res['dlcount'] + 1;
                        $db->exec("UPDATE `cms_forum_files` SET  `dlcount` = '$dlcount' WHERE `id` = '$id'");
                        $db->exec("UPDATE `users` SET `balans` = `balans` - " . $db->quote($res['balans']) . " WHERE `id`='" . $systemUser->id . "' ");
                        $db->exec("UPDATE `users` SET `balans` = `balans` + " . $db->quote($res['balans']) . " WHERE `id`='" . $res['user_id'] . "' ");

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
                            $res['user_id'],
                            $id,
                            time(),
                            $post['id'],
                            $notice,
                        ]);

                        // DOWNLOAD
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename=' . $res['filename']);
                        header('Content-Length: ' . $fsize);
                        header('Pragma: public');
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

                        $range = '';

                        if(isset($_SERVER['HTTP_RANGE'])) {
                            list($size_unit, $rangeOrig) = explode('=', $_SERVER['HTTP_RANGE'], 2);

                            if ($size_unit == 'bytes') {
                                // Multiple ranges could be specified at the same time, but for simplicity only serve the first range
                                // http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt

                                $range = explode(',', $rangeOrig);
                                $range = explode('-', $range[0]);

                                @list($range, $extraRanges) = $range;

                               if (is_numeric($range) == false)
                                   $range = '0';
                            } else {
                                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                                exit;
                            }
                        } else {
                            $range = '-';
                        }

                        $ranges    = explode('-', $range, 2);
                        $seekStart = null;
                        $seekEnd   = null;

                        if (count($ranges) >= 2)
                            list($seekStart, $seekEnd) = $ranges;

                        // Set start and end based on range (if set), else set defaults
                        // Also check for invalid ranges.

                        if (empty($seekEnd) || is_null($seekEnd))
                            $seekEnd = $fsize - 1;
                        else
                            $seekEnd = min(abs(intval($seekEnd)), $fsize - 1);

                        if (empty($seekStart) || is_null($seekStart) || $seekEnd < abs(intval($seekStart)))
                            $seekStart = 0;
                        else
                            $seekStart = max(abs(intval($seekStart)), 0);

                        // Only send partial content header if downloading a piece of the file (IE workaround)
                        if ($seekStart > 0 || $seekEnd < ($fsize - 1)) {
                            header('HTTP/1.1 206 Partial Content');
                            header('Content-Range: bytes ' . $seekStart . '-' . $seekEnd . '/' . $fsize);
                            header('Content-Length: ' . ($seekEnd - $seekStart + 1));
                        } else {
                          header('Content-Length: ' . $fsize);
                        }

                        header('Accept-Ranges: bytes');

                        $file = @fopen($linkfile, 'rb');

                        if (function_exists('set_time_limit'))
                            @set_time_limit(0);

                        @fseek($file, $seekStart);

                        while(@feof($file) == false) {
                            echo(@fread($file, 1024 * 8));
                            @ob_flush();
                            @flush();

                            if (connection_status() != 0) {
                                @fclose($file);
                                exit;
                            }
                        }

                        // File save was a success
                        @fclose($file);

                        exit();
                    } else {
                        require('../system/head.php');
                        $user = $tools->getUser($res['user_id']);
                        echo '<div class="mrt-code card shadow--2dp">
                            <div class="phdr"><strong>Mua Tập Tin</strong></div>
                            <div class="gmenu text-center">Xác thực mua hàng.!!</div>
                            <div class="card__actions card--border">
                            - Tên: <span class="red">' . $res['filename'] . '</span><br />
                            - Giá: <span class="red">' . number_format($res['balans'], 0, ",", ".") . ' VNĐ</span><br />
                            - Kích thước: <span class="red">' . $fls . ' kb</span><br />
                            - Chủ sở hữu: <span class="red">' . $user['name'] . '</span><br />
                            - Tải lên lúc: <span class="red">' . $tools->thoigian($res['time']) . '</span><br />
                            </div>
                            <div class="card__actions">
                            <form method="post">
                            <div class="button-container"><button class="button" type="submit" name="submit" style="margin:0;">
                            <span class="flex-center">Mua</span></button>
                            </div>
                            </form>
                            </div>
                            <div class="list1">
                            <a href="' . $config['homeurl'] . '/forum/' . $post['refid'] . '/' . $them['seo'] . ($page > 1 ? '_p' . $page : '') . '.html#post-' . $post['id'] . '">' . _t('Back to topic') . '</a>
                            </div>
                            </div>';
                    }
                }
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
