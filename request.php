<?php
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

/** @var Johncms\Api\EnvironmentInterface $env */
$env = $container->get(Johncms\Api\EnvironmentInterface::class);

/** @var Johncms\Api\BbcodeInterface $bbcode */
$bbcode = $container->get(Johncms\Api\BbcodeInterface::class);

/** @var Johncms\Api\ConfigInterface $config */
$config = $container->get(Johncms\Api\ConfigInterface::class);

/** @var Zend\I18n\Translator\Translator $translator */
$translator = $container->get(Zend\I18n\Translator\Translator::class);
$translator->addTranslationFilePattern('gettext', __DIR__ . '/locale', '/%s/default.mo');

$time = time();

function formatsize($size) {
    // Форматирование размера файлов
    if ($size >= 1073741824) {
        $size = round($size / 1073741824 * 100) / 100 . ' Gb';
    } else if ($size >= 1048576) {
        $size = round($size / 1048576 * 100) / 100 . ' Mb';
    } else if ($size >= 1024) {
        $size = round($size / 1024 * 100) / 100 . ' Kb';
    } else {
        $size = $size . ' b';
    }

    return $size;
}

$t = (!isset($_POST['t']) ? "" : addslashes($_POST['t']));
$a = (!isset($_POST['a']) ? "" : addslashes($_POST['a']));

$data = array(
    'status' => 0
);

if (empty($t) || empty($a))
{
    exit('error');
}

include('requests/' . $t . '.php');