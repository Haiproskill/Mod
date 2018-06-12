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

namespace Johncms;

use Psr\Container\ContainerInterface;

class Bbcode implements Api\BbcodeInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var Api\ConfigInterface
     */
    protected $config;

     /**
     * @var Api\ToolsInterface::class
     */
    protected $tools;

    /**
     * @var Api\UserInterface::class
     */
    protected $user;

    /**
     * @var seo
     */
    protected $seo;

    /**
     * @var UserConfig
     */
    protected $userConfig;

    /**
     * @var \GeSHi
     */
    protected $geshi;

    protected $homeUrl;

    public function __invoke(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Api\ConfigInterface::class);
        $this->db = $container->get(\PDO::class);
        $this->user = $container->get(Api\UserInterface::class);
        $this->tools = $container->get(Api\ToolsInterface::class);
        $this->userConfig = $this->user->getConfig();
        $this->homeUrl = $this->config['homeurl'];

        return $this;
    }

    // Обработка тэгов и ссылок
    public function tags($var, $seo = false)
    {
        $this->seo = $seo;
        $var = $this->parseTime($var);               // Обработка тэга времени
        $var = $this->highlightCode($var);           // Подсветка кода
        $var = $this->highlightBb($var);             // Обработка ссылок
        $var = $this->highlightUrl($var);            // Обработка ссылок
        $var = $this->highlightBbcodeUrl($var);      // Обработка ссылок в BBcode
        $var = $this->youtube($var);
        $var = $this->tagUsers($var);
        $var = $this->download($var);
        $var = $this->googleChap($var);
        $var = $this->VideoHTML5($var);
        $var = $this->Mp3($var);
        $var = $this->BbcodeSmilies($var);
        if ($this->seo) {
            $var = $this->pBBCode($var);
            $var = $this->p2BBCode($var);
        }

        return $var;
    }

    public function notags($var = '')
    {
        $var = preg_replace('#\[color=(.+?)\](.+?)\[/color]#si', '$2', $var);
        $var = preg_replace('#\[timestamp\](.+?)\[/timestamp]#si', '$1', $var);
        $var = preg_replace('#\[time\](.+?)\[/time]#si', '$1', $var);
        $var = preg_replace('#\[thoigian\](.+?)\[/thoigian]#si', '$1', $var);
        $var = preg_replace('#\[code=(.+?)\](.+?)\[/code]#si', '$2', $var);
        $var = preg_replace('#\[url=(.+?)\](.+?)\[/url]#si', '$1', $var);
        $var = preg_replace('!\[bg=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/bg]!is', '$2', $var);
        $var = preg_replace('#\[spoiler=(.+?)\](.+?)\[/spoiler\]#si', '$2', $var);
        $var = preg_replace('#\[img=(.+?)\]#si', '$1', $var);
        $var = preg_replace('#\[b=(.+?)\](.+?)\[/b]#si', '$2', $var);
        $var = preg_replace('#\[i=(.+?)\](.+?)\[/i]#si', '$2', $var);
        $var = preg_replace('#\[u=(.+?)\](.+?)\[/u]#si', '$2', $var);
        $var = preg_replace('#\[s=(.+?)\](.+?)\[/s]#si', '$2', $var);
        $var = preg_replace('#\[small=(.+?)\](.+?)\[/small]#si', '$2', $var);
        $var = preg_replace('#\[big=(.+?)\](.+?)\[/big]#si', '$2', $var);
        $replace = [
            '[img]'      => '',
            '[/img]'     => '',
            '[d]'        => '',
            '[/d]'       => '',
            '[small]'    => '',
            '[/small]'   => '',
            '[big]'      => '',
            '[/big]'     => '',
            '[green]'    => '',
            '[/green]'   => '',
            '[red]'      => '',
            '[/red]'     => '',
            '[blue]'     => '',
            '[/blue]'    => '',
            '[b]'        => '',
            '[/b]'       => '',
            '[i]'        => '',
            '[/i]'       => '',
            '[u]'        => '',
            '[/u]'       => '',
            '[s]'        => '',
            '[/s]'       => '',
            '[quote]'    => '',
            '[/quote]'   => '',
            '[youtube]'  => '',
            '[/youtube]' => '',
            '[php]'      => '',
            '[/php]'     => '',
            '[c]'        => '',
            '[/c]'       => '',
            '[*]'        => '',
            '[/*]'       => '',
            '[br]'       => '',
        ];

        return strtr($var, $replace);
    }

    /**
     * BbCode Toolbar
     *
     * @param string $form
     * @param string $field
     * @return string
     */
    public function buttons($form, $field)
    {
        $colors = [
            'ffffff',
            'bcbcbc',
            '708090',
            '6c6c6c',
            '454545',
            'fcc9c9',
            'fe8c8c',
            'fe5e5e',
            'fd5b36',
            'f82e00',
            'ffe1c6',
            'ffc998',
            'fcad66',
            'ff9331',
            'ff810f',
            'd8ffe0',
            '92f9a7',
            '34ff5d',
            'b2fb82',
            '89f641',
            'b7e9ec',
            '56e5ed',
            '21cad3',
            '03939b',
            '039b80',
            'cac8e9',
            '9690ea',
            '6a60ec',
            '4866e7',
            '173bd3',
            'f3cafb',
            'e287f4',
            'c238dd',
            'a476af',
            'b53dd2',
        ];
        $font_color = '';
        $bg_color = '';

        foreach ($colors as $value) {
            $font_color .= '<span onclick="bbTag(\'[color=#' . $value . ']\', \'[/color]\'); show_hide(\'.bb-box #color\');" style="background-color:#' . $value . ';padding:0px 16px 0 0;"></span>';
            $bg_color .= '<span onclick="bbTag(\'[bg=#' . $value . ']\', \'[/bg]\'); show_hide(\'.bb-box #bg\');" style="background-color:#' . $value . ';padding:0px 16px 0 0;"></span>';
        }

        // Смайлы
        $smileys = !empty($this->user->smileys) ? unserialize($this->user->smileys) : [];

        if (!empty($smileys)) {
            $res_sm = '';
            $bb_smileys = '';
            $bb_smileysLink = '/help/?act=my_smilies';

            foreach ($smileys as $value) {
                $res_sm .= '<span onclick="bbTag(\'' . $value . '\', \'\'); show_hide(\'.bb-box #sm\');">' . $this->BbcodeSmilies($this->tools->smilies($value)) . '</span> ';
            }

            $bb_smileys .= $res_sm;
        } else {
            $bb_smileysLink = '/help/?act=smilies';
        }

        // Код
        $code = [
            'php',
            'css',
            'js',
            'html',
            'sql',
            'xml',
        ];

        $codebtn = '';

        foreach ($code as $val) {
            $codebtn .= '<a href="javascript:bbTag(\'[code=' . $val . ']\', \'[/code]\'); show_hide(\'.bb-box #code\');">' . strtoupper($val) . '</a>';
        }

        $out = '<script type="text/javascript">
            function bbTag(text1, text2) {
              if ((document.selection)) {
                document.' . $form . '.' . $field . '.focus();
                document.' . $form . '.document.selection.createRange().text = text1+document.' . $form . '.document.selection.createRange().text+text2;
              } else if(document.forms[\'' . $form . '\'].elements[\'' . $field . '\'].selectionStart!=undefined) {
                var element = document.forms[\'' . $form . '\'].elements[\'' . $field . '\'];
                var str = element.value;
                var start = element.selectionStart;
                var length = element.selectionEnd - element.selectionStart;
                element.value = str.substr(0, start) + text1 + str.substr(start, length) + text2 + str.substr(start + length);
              } else {
                document.' . $form . '.' . $field . '.value += text1+text2;
              }
            }
            </script>
            <div class="bb-box">
            <span onclick="bbTag(\'[b]\', \'[/b]\')"><i class="material-icons">&#xE238;</i></span>
            <span onclick="bbTag(\'[i]\', \'[/i]\')"><i class="material-icons">&#xE23F;</i></span>
            <span onclick="bbTag(\'[u]\', \'[/u]\')"><i class="material-icons">&#xE249;</i></span>
            <span onclick="bbTag(\'[s]\', \'[/s]\')"><i class="material-icons">&#xE257;</i></span>
            <span onclick="show_hide(\'.bb-box #color\');"><i class="material-icons">&#xE23C;</i></span>&#160;
            <span onclick="show_hide(\'.bb-box #bg\');"><i class="material-icons">&#xE23A;</i></span>&#160;
            <span onclick="bbTag(\'[*]\', \'[/*]\')"><i class="material-icons">&#xE241;</i></span>&#160;
            <span onclick="bbTag(\'[spoiler=]\', \'[/spoiler]\');"><i class="material-icons">&#xE548;</i></span>&#160;
            <span onclick="bbTag(\'[c]\', \'[/c]\')"><i class="material-icons">&#xE244;</i></span>&#160;
            <span onclick="bbTag(\'[url=]\', \'[/url]\')"><i class="material-icons">&#xE157;</i></span>&#160;
            <span onclick="show_hide(\'.bb-box #code\');"><i class="material-icons">&#xE86F;</i></span>&#160;
            <span onclick="bbTag(\'[youtube]\', \'[/youtube]\')"><i class="material-icons">&#xE039;</i></span>&#160;
            <span onclick="bbTag(\'[img]\', \'[/img]\')"><i class="material-icons">&#xE410;</i></span>&#160;
            <span onclick="bbTag(\'[d=]\', \'\')"><i class="material-icons">&#xE2C4;</i></span>&#160;
            <span onclick="bbTag(\'[@ \', \']\')"><i class="material-icons">&#xE853;</i></span>&#160;';

        if ($this->user->isValid()) {
            $out .= '&#160;<span onclick="show_hide(\'.bb-box #sm\');"><i class="material-icons">&#xE24E;</i></span>
                <div id="sm" style="display:none"><table cellpadding="0" cellspacing="0"><tr><td style="padding-right: 5px;"><a href="' . $this->homeUrl . $bb_smileysLink . '"><i class="material-icons">&#xE24E;</i></a></td><td>' . $bb_smileys . '</td></tr></table></div>';
        } else {
            $out .= '';
        }
        $out .= '<div id="code" class="codepopup">' . $codebtn . '</div>' .
            '<div id="color" class="bbpopup"><table cellpadding="0" cellspacing="0"><tr><td style="padding-right: 5px;"><i class="material-icons">&#xE23C;</i></td><td>' . $font_color . '</td></tr></table></div>' .
            '<div id="bg" class="bbpopup"><table cellpadding="0" cellspacing="0"><tr><td style="padding-right: 5px;"><i class="material-icons">&#xE23A;</i></td><td>' . $bg_color . '</td></tr></table></div></div>';

        return $out;
    }

    /**
     * Обработка тэга [time]
     *
     * @param string $var
     * @return string
     */
    protected function parseTime($var)
    {
        $var = preg_replace_callback(
            '#\[time\](.+?)\[\/time\]#s',
            function ($matches) {
                $shift = ($this->config['timeshift'] + $this->userConfig->timeshift) * 3600;

                if (($out = strtotime($matches[1])) !== false) {
                    return date("d.m.Y / H:i", $out + $shift);
                } else {
                    return $matches[1];
                }
            },
            $var
        );

        $var = preg_replace_callback(
            '#\[timestamp\](.+?)\[\/timestamp\]#s',
            function ($matches) {
                $shift = ($this->config['timeshift'] + $this->userConfig->timeshift) * 3600;

                if (($out = strtotime($matches[1])) !== false) {
                    return '<small class="gray">' . _t('Added', 'system') . ': ' . date("d.m.Y / H:i", $out + $shift) . '</small>';
                } else {
                    return $matches[1];
                }
            },
            $var
        );

        return $var;
    }

    /**
     * Парсинг ссылок
     * За основу взята доработанная функция от форума phpBB 3.x.x
     *
     * @param $text
     * @return mixed
     */
    protected function highlightUrl($text)
    {
        $homeurl = $this->homeUrl;

        // Обработка внутренних ссылок
        $text = preg_replace_callback(
            '#(^|[\n\t (>.])(' . preg_quote($homeurl,
                '#') . ')/((?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*(?:/(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#iu',
            function ($matches) {
                return $this->urlCallback(1, $matches[1], $matches[2], $matches[3]);
            },
            $text
        );

        // Обработка обычных ссылок типа xxxx://aaaaa.bbb.cccc. ...
        $text = preg_replace_callback(
            '#(^|[\n\t (>.])([a-z][a-z\d+]*:/{2}(?:(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})+|[0-9.]+|\[[a-zа-яё0-9.]+:[a-zа-яё0-9.]+:[a-zа-яё0-9.:]+\])(?::\d*)?(?:/(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@|]+|%[\dA-F]{2})*)*(?:\?(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?(?:\#(?:[a-zа-яё0-9\-._~!$&\'(*+,;=:@/?|]+|%[\dA-F]{2})*)?)#iu',
            function ($matches) {
                return $this->urlCallback(2, $matches[1], $matches[2], '');
            },
            $text
        );

        return $text;
    }

    private function urlCallback($type, $whitespace, $url, $relative_url)
    {
        $orig_url = $url;
        $orig_relative = $relative_url;
        $url = htmlspecialchars_decode($url);
        $relative_url = htmlspecialchars_decode($relative_url);
        $text = '';
        $chars = ['<', '>', '"'];
        $split = false;

        foreach ($chars as $char) {
            $next_split = strpos($url, $char);
            if ($next_split !== false) {
                $split = ($split !== false) ? min($split, $next_split) : $next_split;
            }
        }

        if ($split !== false) {
            $url = substr($url, 0, $split);
            $relative_url = '';
        } else {
            if ($relative_url) {
                $split = false;
                foreach ($chars as $char) {
                    $next_split = strpos($relative_url, $char);
                    if ($next_split !== false) {
                        $split = ($split !== false) ? min($split, $next_split) : $next_split;
                    }
                }
                if ($split !== false) {
                    $relative_url = substr($relative_url, 0, $split);
                }
            }
        }

        $last_char = ($relative_url) ? $relative_url[strlen($relative_url) - 1] : $url[strlen($url) - 1];

        switch ($last_char) {
            case '.':
            case '?':
            case '!':
            case ':':
            case ',':
                $append = $last_char;
                if ($relative_url) {
                    $relative_url = substr($relative_url, 0, -1);
                } else {
                    $url = substr($url, 0, -1);
                }
                break;

            default:
                $append = '';
                break;
        }

        $short_url = (mb_strlen($url) > 40) ? mb_substr($url, 0, 30) . ' ... ' . mb_substr($url, -5) : $url;

        switch ($type) {
            case 1:
                $relative_url = preg_replace('/[&?]sid=[0-9a-f]{32}$/', '', preg_replace('/([&?])sid=[0-9a-f]{32}&/', '$1', $relative_url));
                $url = $url . '/' . $relative_url;
                $text = $relative_url;
                if (!$relative_url) {
                    return $whitespace . $orig_url . '/' . $orig_relative;
                }
                break;

            case 2:
                $text = $short_url;
                if (!$this->userConfig->directUrl) {
                    $url = $this->homeUrl . '/go.php?url=' . rawurlencode($url);
                }
                break;

            case 4:
                $text = $short_url;
                $url = 'mailto:' . $url;
                break;
        }
        $url = htmlspecialchars($url);
        $text = htmlspecialchars($text);
        $append = htmlspecialchars($append);

        return $whitespace . '<a ' . ($type == 2 ? 'target="_blank" ' : '') . 'href="' . $url . '" class="' . ($type == 1 ? 'tload' : '') . '">' . $text . '</a>' . $append;
    }

    /**
     * Подсветка кода
     *
     * @param string $var
     * @return mixed
     */
    protected function highlightCode($var)
    {
        $var = preg_replace_callback('#\[php\](.+?)\[\/php\]#s', [$this, 'phpCodeCallback'], $var);
        $var = preg_replace_callback('#\[code=(.+?)\](.+?)\[\/code]#is', [$this, 'codeCallback'], $var);

        return $var;
    }

    private function phpCodeCallback($code)
    {
        return $this->codeCallback([1 => 'php', 2 => $code[1]]);
    }

    private function codeCallback($code)
    {
        $parsers = [
            'php'  => 'php',
            'css'  => 'css',
            'html' => 'html5',
            'js'   => 'javascript',
            'sql'  => 'sql',
            'xml'  => 'xml',
        ];

        $parser = isset($code[1]) && isset($parsers[$code[1]]) ? $parsers[$code[1]] : 'php';

        if (null === $this->geshi) {
            $this->geshi = new \GeSHi;
            $this->geshi->set_link_styles(GESHI_LINK, 'text-decoration: none');
            $this->geshi->set_link_target('_blank');
            $this->geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
            $this->geshi->set_line_style('background: #152b39;', 'background: #193344;', false);
            $this->geshi->set_code_style('padding-left: 6px; white-space: pre-wrap');
        }

        $this->geshi->set_language($parser);
        $php = strtr($code[2], ['<br />' => '']);
        $php = html_entity_decode(trim($php), ENT_QUOTES, 'UTF-8');
        $this->geshi->set_source($php);

        return '<div class="phpcode" style="overflow-x: auto">' . $this->geshi->parse_code() . '</div>';
    }

    /**
     * Обработка URL в тэгах BBcode
     *
     * @param $var
     * @return mixed
     */
    protected function highlightBbcodeUrl($var)
    {
        return preg_replace_callback('~\[url=(https?://.+?|//.+?)](.+?)\[/url]~iu',
            function ($url) {
                $home = parse_url($this->homeUrl);
                $tmp = parse_url($url[1]);

                if ($home['host'] == $tmp['host'] || $this->userConfig->directUrl) {
                    return '<a href="' . $url[1] . '" class="tload">' . $url[2] . '</a>';
                } else {
                    return '<a href="' . $this->homeUrl . '/go.php?url=' . urlencode(htmlspecialchars_decode($url[1])) . '" target="_blank">' . $url[2] . '</a>';
                }
            },
            $var);
    }

    /**
     * Список замен для основных тегов BB-кода.
     *
     * @return array
     */
    protected function replacements()
    {
        return [
            // Жирный
            'b'       => [
                'from' => '#\[b](.+?)\[/b]#is',
                'to'   => '<strong>$1</strong>',
            ],
            // Курсив
            'i'       => [
                'from' => '#\[i](.+?)\[/i]#is',
                'to'   => '<span class="text--italic">$1</span>',
            ],
            // Подчёркнутый
            'u'       => [
                'from' => '#\[u](.+?)\[/u]#is',
                'to'   => '<span class="text--underline">$1</span>',
            ],
            // Зачёркнутый
            's'       => [
                'from' => '#\[s](.+?)\[/s]#is',
                'to'   => '<span class="text--through">$1</span>',
            ],
            // Маленький шрифт
            'small'   => [
                'from' => '#\[small](.+?)\[/small]#is',
                'to'   => '<span class="font--xsmall">$1</span>',
            ],
            // Большой шрифт
            'big'     => [
                'from' => '#\[big](.+?)\[/big]#is',
                'to'   => '<span class="font--large">$1</span>',
            ],
            // b smart
            'b-smart'       => [
                'from' => '!\[b=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/b]!is',
                'to'   => '<strong style="color:$1">$2</strong>',
            ],
            // i smart
            'i-smart'       => [
                'from' => '!\[i=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/i]!is',
                'to'   => '<span class="text--italic" style="color:$1">$2</span>',
            ],
            // u smart
            'u-smart'       => [
                'from' => '!\[u=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/u]!is',
                'to'   => '<span class="text--underline" style="color:$1">$2</span>',
            ],
            // s smart
            's-smart'       => [
                'from' => '!\[s=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/s]!is',
                'to'   => '<span class="text--through" style="color:$1">$2</span>',
            ],
            // small smart
            'small-smart'   => [
                'from' => '!\[small=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/small]!is',
                'to'   => '<span class="font--xsmall" style="color:$1">$2</span>',
            ],
            // big smart
            'big-smart'     => [
                'from' => '!\[big=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/big]!is',
                'to'   => '<span class="font--large" style="color:$1">$2</span>',
            ],
            // Красный
            'red'     => [
                'from' => '#\[red](.+?)\[/red]#is',
                'to'   => '<span style="color:red">$1</span>',
            ],
            // Зеленый
            'green'   => [
                'from' => '#\[green](.+?)\[/green]#is',
                'to'   => '<span style="color:green">$1</span>',
            ],
            // Синий
            'blue'    => [
                'from' => '#\[blue](.+?)\[/blue]#is',
                'to'   => '<span style="color:blue">$1</span>',
            ],
            // Цвет шрифта
            'color'   => [
                'from' => '!\[color=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/color]!is',
                'to'   => '<span style="color:$1">$2</span>',
            ],
            // Цвет фона
            'bg'      => [
                'from' => '!\[bg=(#[0-9a-f]{3}|#[0-9a-f]{6}|[a-z\-]+)](.+?)\[/bg]!is',
                'to'   => '<span style="background-color:$1">$2</span>',
            ],
            // Цитата
            'quotebase'   => [
                'from' => '#\[(quote|c)=(.+?)](.+?)\[/(quote|c)]#is',
                'to'   => ($this->seo ? '</p>' : '') . '<div class="quote" style="display:block">$2 đã nói:<br />$3</div>' . ($this->seo ? '<p>' : ''),
            ],
            // Цитата
            'quote'   => [
                'from' => '#\[(quote|c)](.+?)\[/(quote|c)]#is',
                'to'   => ($this->seo ? '</p>' : '') . '<div class="quote" style="display:block">$2</div>' . ($this->seo ? '<p>' : ''),
            ],
            // Список
            'list'    => [
                'from' => '#\[\*](.+?)\[/\*]#is',
                'to'   => '<span class="bblist">$1</span>',
            ],
            // Спойлер
            'spoiler' => [
                'from' => '#\[spoiler=(.+?)](.+?)\[/spoiler]#is',
                'to'   => ($this->seo ? '</p>' : '') . '<div><div class="spoilerhead" onclick="var _n = this.parentNode.getElementsByTagName(\'div\')[1];if(_n.style.display==\'none\'){_n.style.display=\'\';}else{_n.style.display=\'none\';}">$1 (+/-)</div><div class="spoilerbody" style="display:none;">$2</div></div>' . ($this->seo ? '<p>' : ''),
            ],
            // br
            'br' => [
                'from' => '#\[br]#is',
                'to'   => '<br />',
            ],
            // image
            'image' => [
                'from' => '#\[img](.+?)\[/img]#is',
                'to'   => ($this->seo ? '</p>' : '') . '<div class="text-center full-margin" data-fancybox="images" data-src="$1" title=""><img src="$1" class="max-width-500" onerror="imgError(this);" /></div>' . ($this->seo ? '<p>' : ''),
            ],
            // image
            'image2' => [
                'from' => '#\[img=(.+?)]#is',
                'to'   => ($this->seo ? '</p>' : '') . '<div class="text-center full-margin" data-fancybox="images" data-src="$1" title=""><img src="$1" class="max-width-500" onerror="imgError(this);" /></div>' . ($this->seo ? '<p>' : ''),
            ],
        ];
    }

    /**
     * Обработка bbCode
     *
     * @param string $var
     * @return string
     */
    protected function highlightBb($var)
    {
        $replacements = array_values($this->replacements());
        $search = array_column($replacements, 'from');
        $replace = array_column($replacements, 'to');

        return preg_replace($search, $replace, $var);
    }

    /**
     * Youtube bbcode
     *
     * @param string $var
     * @return string
     */
    protected function youtube($var)
    {
        return preg_replace_callback(
            '#\[youtube\](.+?)\[\/youtube\]#s',
            function ($matches) {
                if (preg_match('/youtube.com/', $matches[1])) {
                    $values = explode('=', $matches[1]);
                    $valuesto = explode('&', $values[1]);

                    return $this->youtubePlayer($valuesto[0]);
                } elseif (preg_match('/youtu.be/', $matches[1])) {
                    return $this->youtubePlayer(trim(parse_url($matches[1])['path'], '//'));
                } else {
                    $valuesto = explode('&', $matches[1]);

                    return $this->youtubePlayer($valuesto[0]);
                }
            },
            $var
        );
    }

    protected function youtubePlayer($result)
    {
        if ($this->userConfig->youtube) {
            return ($this->seo ? '</p>' : '') . '<div class="full-margin">' .
                '<div class="video-container">' .
                '<iframe allowfullscreen="allowfullscreen" src="//www.youtube.com/embed/' . $result . '" frameborder="0"></iframe>' .
                '</div></div>' . ($this->seo ? '<p>' : '');
        } else {
            return ($this->seo ? '</p>' : '') . '<div class="full-margin">' .
                '<a target="_blank" href="//m.youtube.com/watch?v=' . $result . '"><img src="//img.youtube.com/vi/' . $result . '/maxresdefault.jpg" style="width:100%;border:none" alt="youtube.com/embed/' . $result . '"></a>' .
                '</div>' . ($this->seo ? '<p>' : '');
        }
    }

    protected function download($var)
    {
        return preg_replace_callback(
            '#\[d=(http(?:s*)\:\/\/.+?)\]#s',
            function ($download) {
                $value = trim($download[1]);
                $text = basename($value);
                return '<a target="_blank" href="' . $value . '" class="m-chip m-chip--contact"><i class="m-chip__contact material-icons">&#xE2C4;</i><span class="m-chip__text">' . $text . '</span></a>';
            },
            $var
        );
    }

    /**
     * Bbcode Tag User
     *
     * @param string $var
     * @return string
     */
    protected function tagUsers($var)
    {
        $var = preg_replace_callback(
            '#\[\@(.+?)\]#s',
            function ($userData) {
                $value = trim($userData[1]);
                return $this->tagtv($value, '1');
            },
            $var
        );

        $var = preg_replace_callback(
            '#@([\w\d]{3,})#s',
            function ($usersData) {
                $values = $usersData[1];
                return $this->tagtv($values, '2');
            },
            $var
        );

        return $var;
    }

    protected function tagtv($result, $t){
        $ra = null;
        $countu = null;
        $req = null;
        $countu = $this->db->query("SELECT COUNT(*) FROM `users` WHERE `name` = " . $this->db->quote($result) . " ")->fetchColumn();
        if($countu){
            $req = $this->db->query("SELECT * FROM `users` WHERE `name` = " . $this->db->quote($result) . " ")->fetch();
            $avatar_name = $this->tools->avatar_name($req['id']);
            if (file_exists((ROOT_PATH . 'files/users/avatar/' . $avatar_name))) {
                $avt = '/files/users/avatar/' . $avatar_name;
            } else {
                 $avt = '/images/empty' . ($req['sex'] ? ($req['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
             }
            $ra = ($this->user->isValid() ? '<a id="tload" href="/profile/?user=' . $req['id'] . '">' : '') . '<span class="m-chip m-chip--contact"><img class="m-chip__contact" src="' . $avt . '" alt="' . $req['name'] . '" /><span class="m-chip__text nickname">' . $req['name'] . '</span></span>' . ($this->user->isValid() ? '</a>' : '');
        }else{
            if($t == 1)
                $ra = '[@' . $result . ']';
            else $ra = '@' . $result;
        }

        return $ra;
    }

    /**
     *Bbcode [br]]
     * @param $var
     * @return <br />

     protected function pBBCode($var)
    {
        return preg_replace_callback('~\<p\>\<\/p\>~iu',
            function ($data) {
                return '';
            },
            $var
        );
    }

    */
    protected function pBBCode($var)
    {
        return preg_replace_callback('~\<p\>\<\/p\>~iu',
            function () {
                return '<br />';
            },
            $var
        );
    }
    protected function p2BBCode($var)
    {
        return preg_replace_callback('~\<p\>(.+?)\<\/p\>~iu',
            function ($value) {
                $value = $value[1];
                $value = trim($value);
                if (empty($value))
                    return '';
                else
                    return '<p>' . $value . '</p>';
            },
            $var
        );
    }

    /**
     * Bbcode Smilies
     *
     * @param $var
     * @return image smilies
     */
    protected function BbcodeSmilies($var)
    {
        return preg_replace_callback('~\[smilies=(.+?)\](.+?)\[/smilies\]~iu',
            function ($smilies) {
                $restyle = $smilies[1];
                $path = $smilies[2];
                $cat = basename(dirname(ROOT_PATH.$path));
                $style = '';
                if ($restyle != 'none') {
                    $style = ' ' . $restyle;
                }
                //return ($this->seo ? '</p>' : '') . '<img class="smilies' . $style . '" src="' . $this->homeUrl . '/' . $path . '" alt="' . $cat . '" />' . ($this->seo ? '<p>' : '');
                return '<img class="smilies' . $style . '" src="' . $this->homeUrl . '/' . $path . '" alt="' . $cat . '" />';
            },
            $var
        );
    }

    protected function googleChap($var)
    {
        return preg_replace_callback(
            '#\[gchap\](\([0-9]+\|+(http(?:s*)\:\/\/(?:drive|docs)\.google\.com\/file\/d\/.+?)+\))\[/gchap\]#si',
            function ($data) {
                $begin     = trim($data[1]);
                $random = mt_rand(99999, 111111);
                $a             = null;
                $chap      = null;

                if (preg_match_all('/(?:\(([0-9])+\|+(http(?:s*)\:\/\/(?:drive|docs)\.google\.com\/file\/d\/(.+?))+\))/si', $begin, $matches)) {
                    $count = count($matches[3]);

                    foreach ($matches[3] AS $index => $endlink) {
                        $endlink = @explode('/', $endlink);
                        $endlink = $endlink[0];
                        if ($index === 0) {
                            $a .= '<div class="full-margin">' .
                                '<div class="video-container">' .
                                '<iframe id="gchap' . $random . '" allowfullscreen="allowfullscreen" src="https://drive.google.com/file/d/' . $endlink . '/preview" frameborder="0"></iframe>' .
                                '</div></div>';
                        }

                        if ($count > 1)
                            $chap .= '<span class="' . ($index === 0 ? 'gchap' . $random . ' currentpage"' : 'pagenav" id="gchap"') . ' data="' . $random . '" data-id="' . $endlink . '" >' . $matches[1][$index] . '</span> ';
                    }

                    return $a . ($count > 1 ? '<div class="text-center" id="sm">Chap: ' . $chap . '</div>' : '');
                }
            },
            $var
        );
    }
    protected function VideoHTML5($var)
    {
        return preg_replace_callback(
            '#\[video=(http(?:s*)\:\/\/.+?\|)\]#s',
            function ($data) {
                $link = trim($data[1]);
                
                return '<div class="text-center full-margin"><video src="' . $link . '" controls></video></div>';
            },
            $var
        );
    }
    protected function Mp3($var)
    {
        return preg_replace_callback(
            '#\[mp3=(http(?:s*)\:\/\/.+?)\]#si',
            function ($allLink) {

                /** làm sạch link */
                $link = trim($allLink[1]);
                $link = str_replace(' ', '', $link);
                $link = str_replace("\r\n", '', $link);
                $link = str_replace('<br>', '', $link);
                $link = str_replace('<br/>', '', $link);

                /** danh sách link */
                $plist = @explode('|', $link);

                $truth   = 0;
                $zingMp3 = false;
                $random  = mt_rand(99999, 111111);
                $main    = '';
                $html    = null;

                foreach ($plist AS $index => $endlink) {
                    $goi_js = false;

                    // kiểm tra link
                    if (!preg_match('/http(?:s*)\:\/\//', $endlink)) {
                        continue;
                    }

                    // link zing mp3
                    if (preg_match('/mp3\.zing\.vn/', $endlink)) {
                        if (!preg_match('/m\.mp3\.zing\.vn/', $endlink))
                            $endlink = str_replace('mp3.zing.vn', 'm.mp3.zing.vn', $endlink);

                        $type = @explode('.vn/', $endlink);
                        $type = @explode('/', $type[1])[0];

                        $dataPage = $this->tools->grab($endlink);

                        /** key bài hát */
                        $datakey = @explode('data-source="', $dataPage);

                        unset($dataPage); // dọn bộ nhớ

                        $datakey = @explode('"', $datakey[1])[0];

                        if ($type == 'album') {
                            // $datakey = str_replace('&amp;', '&', $datakey);
                        }

                        /** Lấy kết quả json */
                        $jsond   = 'https://m.mp3.zing.vn/xhr' . $datakey;
                        $zingMp3 = true;

                        unset($datakey); // dọn bộ nhớ

                        $data = $this->tools->grab($jsond);
                        $data = @json_decode($data, true);

                        if ($type == 'album') {
                            $countAlbum = count($data['data']['items']);
                            $truth = $truth + $countAlbum;
                            $main .= ($truth > $countAlbum ? ', ' : '');
                            $data = $data['data']['items'];
                            for ($i=0; $i < $countAlbum; $i++) {
                                $ooo = $data[$i];
                                $main .= '{' .
                                    // 'zingMp3: ' . ($zingMp3 ? '1,' : '0,') . 
                                    'file: "' . $ooo['source']['128'] . '", ' .
                                    'thumb: "' . $ooo['thumbnail'] . '", ' .
                                    'cover: "' . $ooo['artist']['cover'] . '", ' .
                                    'trackName: "' . $ooo['name'] . '", ' .
                                    'trackArtist: "' . $ooo['artists_names'] . '", ' .
                                    'trackAlbum: "PhieuBac"' .
                                    '}'. ($i < $countAlbum - 1 ? ', ': '');
                            }
                        } else {
                            /** lấy link nhạc, tên bài hát, ảnh bìa, ... */
                        
                            $file      = $jsond;                               // link nhạc $data['data']['source']['128'];
                            $cover     = $data['data']['artist']['cover'];     // ảnh bìa
                            $name      = $data['data']['name'];                // bài hát
                            $thumbnail = $data['data']['thumbnail'];           // thumbnail
                            $artist    = $data['data']['artists_names'];       // ca sĩ
                            /** Kiểm tra lần cuối */
                            if(!empty($file) && !empty($name)) {
                                $goi_js = true;
                                $truth++;

                                if (empty($cover))
                                    $cover = 'https://i.imgur.com/wbrzyvc.jpg';
                                            //http://i.imgur.com/O8AiMJJ.jpg
                            }

                        }

                    } else if (preg_match('/nhaccuatui\.com/', $endlink)) { /** link nhaccuatoi */
                        $zingMp3 = false;

                        $endlink = str_replace('https://www.nhaccuatui.com', 'http://m.nhaccuatui.com', $endlink);

                        $dataPage = $this->tools->grab($endlink);

                        /** link nhạc */
                        $file = @explode('.mp3"', $dataPage);
                        $file = @explode('"', $file[0]);
                        $file = $file[count($file) - 1] . '.mp3';

                        /** Bài hát */
                        $data1 = @explode('border="0" alt="', $dataPage);
                        $name = @explode('"', $data1[1])[0];

                        unset($dataPage); // dọn bộ nhớ

                        /** ca sĩ */
                        $artist = @explode('"', $data1[2])[0];

                        unset($data1); // dọn bộ nhớ

                        /** thumb */
                        $thumbnail = '/images/unnamed.jpg';

                        $cover = 'http://i.imgur.com/O8AiMJJ.jpg';

                        if(!empty($file) && !empty($name) && !empty($artist))
                            $goi_js = true;

                        $truth++;
                    }
                    if($goi_js) {
                        if ($type != 'album') {
                            $main .= ($truth > 1 ? ', ': '') . '{' .
                                'zingMp3: ' . ($zingMp3 ? '1,' : '0,') . 
                                'file: "' . trim($file) . '", ' .
                                'thumb: "' . $thumbnail . '", ' .
                                'cover: "' . $cover . '", ' .
                                'trackName: "' . trim($name) . '", ' .
                                'trackArtist: "' . trim($artist) . '", ' .
                                'trackAlbum: "PhieuBac"' .
                                '}';
                        }
                    }
                }

                if(!empty($main)) {
                    $js = '<script type="text/javascript">$(document).ready(function(){' .
                        'var t' . $random . ' = {' .
                            'playlist: [' . $main . '], ' .
                            'defaultAlbum: undefined, ' .
                            'defaultArtist: undefined, ' .
                            'defaultTrack: 0, ' .
                            'autoPlay: false, ' .
                            'debug: false' .
                        '}; ' .
                        '$(".jAudio' . $random . '").jAudio(t' . $random . '); });</script>';
                    $html = ($this->seo ? '</p>' : '') . '<div class="jAudio' . $random . ' jAudio full-margin">' .
                        '<audio></audio>' .
                        '<div class="jAudio--ui">' .
                        '<div class="jAudio--cover"><div></div></div>' .
                        '<div class="jAudio--status-bar">' .
                        '<table><tr><td>' .
                        '<div class="jAudio--thumb"></div>' .
                        '</td><td>' .
                        '<div class="jAudio--details"></div>' .
                        '</td></tr></table>' .
                        '<div class="jAudio--volume"></div>' .
                        '<div class="jAudio--timeBase">' .
                        '<div class="jAudio--time">' .
                        '<span class="jAudio--time-elapsed">00:00</span>' .
                        '<span class="jAudio--time-total">00:00</span>' .
                        '</div>' .
                        '</div>' .
                        '<div class="jAudio--progress-bar">' .
                        '<div class="jAudio--progress-bar-wrapper">' .
                        '<div class="jAudio--progress-bar-played">' .
                        '<span class="jAudio--progress-bar-pointer"></span>' .
                        '<span class="jAudio--progress-bar-loaded"></span>' .
                        '</div>' .
                        '</div>' .
                        '<div class="jAudio--progress-wrapper-click"></div>' .
                        '</div>' .
                        '</div>' .
                        '<div class="jAudio--controls">' .
                        '<ul>' .
                        ($truth > 1 ?'<li><div class="jAudio--control jAudio--control-prev" data-action="prev"><span></span></div></li>' : '') .
                        '<li' . ($truth > 1 ? '' : ' style="width:100%"') . '><div class="jAudio--control jAudio--control-play" data-action="play"><span></span></div></li>' .
                        ($truth > 1 ?'<li><div class="jAudio--control jAudio--control-next" data-action="next"><span></span></div></li>' : '') .
                        '</ul>' .
                        '</div>' .
                        '</div>' .
                        '<div class="jAudio--playlist">' .
                        '</div>' .
                        '</div>' . "\n" . $js . "\n" . ($this->seo ? '<p>' : '');
                } else {
                    $html = $allLink[0];
                }
                return $html;
            },
            $var
        );
    }
}
