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

    define('_IN_JOHNCMS', 1);
    require('../system/bootstrap.php');

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

    $rArticle = $db->query("SELECT COUNT(*) FROM `forum` WHERE `type`='t' AND `close` != '1'")->fetchColumn();
    $qArticle = $db->query("SELECT * FROM `forum` WHERE `type` = 't' AND `close` != '1' ORDER BY `time` DESC LIMIT $rArticle");
    $xmlCt = file_get_contents(ROOT_PATH . 'sitemap/sitemap.xml.tpl');
    $tab = '    ';
    $outXml = '';

    if($rArticle > 0) while(($fArticle = $qArticle->fetch()) != false){
        $nameArt = $fArticle['text'];
        $urlArt = $config['homeurl'] . '/forum/' . $fArticle['id'] . '/' . $fArticle['seo'] . '.html';
        $outXml .= "$tab<url>\n$tab$tab<loc>$urlArt</loc>\n$tab$tab<changefreq>daily</changefreq>\n$tab$tab<priority>0.5</priority>\n$tab</url>\n";
        $outHtml .= "\n$tab$tab$tab<div class=\"list\"><a href=\"$urlArt\" title=\"$nameArt\">$nameArt</a></div>";
    }
    $sitemapXml = ROOT_PATH . 'sitemap.xml';
    @unlink($sitemapXml);
    file_put_contents($sitemapXml, str_replace('/*DataInsert*/', $outXml, $xmlCt));
require('../system/head.php');
    echo '<div class="mrt-code card shadow--2dp"><div class="phdr"><h4>Sitemap</h4></div>' .
    '<div class="list1 text-center">Cập nhật <strong>'.$rArticle.'</strong> bài viết xong.!</div></div>';
require('../system/end.php');
  
