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

class Tools implements Api\ToolsInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \PDO
     */
    private $db;

    /**
     * @var Api\UserInterface::class
     */
    private $user;

    /**
     * @var UserConfig
     */
    private $userConfig;

    /**
     * @var Api\ConfigInterface
     */
    private $config;

    public function __invoke(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Api\ConfigInterface::class);
        $this->db = $container->get(\PDO::class);
        $this->user = $container->get(Api\UserInterface::class);
        $this->userConfig = $this->user->getConfig();

        return $this;
    }

    public function antiflood()
    {
        $config = $this->config['antiflood'];

        switch ($config['mode']) {
            // Адаптивный режим
            case 1:
                $adm = $this->db->query('SELECT COUNT(*) FROM `users` WHERE `rights` > 0 AND `lastdate` > ' . (time() - 60))->fetchColumn();
                $limit = $adm > 0 ? $config['day'] : $config['night'];
                break;
            // День
            case 3:
                $limit = $config['day'];
                break;
            // Ночь
            case 4:
                $limit = $config['night'];
                break;
            // По умолчанию день / ночь
            default:
                $c_time = date('G', time());
                $limit = $c_time > $config['day'] && $c_time < $config['night'] ? $config['day'] : $config['night'];
        }

        // Для Администрации задаем лимит в 4 секунды
        if ($this->user->rights > 0) {
            $limit = 4;
        }

        $flood = $this->user->lastpost + $limit - time();

        return $flood > 0 ? $flood : false;
    }

    /**
     * Обработка текстов перед выводом на экран
     *
     * @param string $str
     * @param int    $br   Параметр обработки переносов строк
     *                     0 - не обрабатывать (по умолчанию)
     *                     1 - обрабатывать
     *                     2 - вместо переносов строки вставляются пробелы
     * @param int    $tags Параметр обработки тэгов
     *                     0 - не обрабатывать (по умолчанию)
     *                     1 - обрабатывать
     *                     2 - вырезать тэги
     *
     * @return string
     */
    public function checkout($str, $br = 0, $tags = 0, $desc = 0, $smilies = 0, $seo = 0)
    {
        $str = htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');

        if ($seo == 1) {
            $str = '<p>' . trim($str) . '</p>';
            if ($br == 1) {
                $str = str_replace("\r\n", '</p><p>', $str);
                $str = str_replace("\n",   '</p><p>', $str);
                $str = str_replace("\r",   '</p><p>', $str);
                $str = preg_replace_callback('~\[br\]~iu',
                    function () {
                        return '</p><br /><p>';
                    },
                    $str
                );
            } else if ($br == 2) {
                $str = str_replace("\r\n", ' ', $str);
            }
        } else {
            if ($br == 1) {
                $str = nl2br($str);
            } else if ($br == 2) {
                $str = str_replace("\r\n", ' ', $str);
            }
        }
        if ($smilies == 1) $str = $this->smilies($str);
        if ($tags == 1) {
            $str = $this->container->get(Api\BbcodeInterface::class)->tags($str, $seo);
        } else if ($tags == 2) {
            $str = $this->container->get(Api\BbcodeInterface::class)->notags($str);
            if ($desc == 1) {
                $str = preg_replace('/\s{2,}/', ' ', $str);
            }
        }

        return trim($str);
    }

    /**
     * Показываем дату с учетом сдвига времени
     *
     * @param int $var Время в Unix формате
     * @return string Отформатированное время
     */
    public function displayDate($var)
    {
        $shift = ($this->config->timeshift + $this->userConfig->timeshift) * 3600;

        if (date('Y', $var) == date('Y', time())) {
            if (date('z', $var + $shift) == date('z', time() + $shift)) {
                return _t('Today', 'system') . ', ' . date("H:i", $var + $shift);
            }
            if (date('z', $var + $shift) == date('z', time() + $shift) - 1) {
                return _t('Yesterday', 'system') . ', ' . date("H:i", $var + $shift);
            }
        }

        return date("d.m.Y / H:i", $var + $shift);
    }
    public function timestamp($var)
    {
        $shift = ($this->config->timeshift + $this->userConfig->timeshift) * 3600;
        return date("Y-m-d H:i:s", $var + $shift);
    }
    public function timef()
    {
        return $_SERVER['REQUEST_TIME_FLOAT'];
    }
    public function thoigian($var)
    {
        $shift = ($this->config->timeshift + $this->userConfig->timeshift) * 3600;
        $lay_thu = date("N", $var + $shift);
        $ngay = date('z', time() + $shift);
        $lay_ngay = date('z', $var + $shift);
        
        $lay_tuan;
        $lay_thang;
        $thu_time = null;
        
        if ($lay_thu == 1) {
            $thu_time = 'Thứ Hai';
        }else if ($lay_thu == 2) {
            $thu_time = 'Thứ Ba';
        }else if ($lay_thu == 3) {
            $thu_time = 'Thứ Tư';
        }else if ($lay_thu == 4) {
            $thu_time = 'Thứ Năm';
        }else if ($lay_thu == 5) {
            $thu_time = 'Thứ Sáu';
        }else if ($lay_thu == 6) {
            $thu_time = 'Thứ Bảy';
        }else if ($lay_thu == 7) {
            $thu_time = 'Chủ Nhật';
        }
        if (date('Y', $var) == date('Y', time())) {
            if ($lay_ngay == $ngay) {
                $minutes = round((time() - $var) / 60);
                if ($minutes < 1) {
                    return 'Vừa xong';
                }else if($minutes >= 1 && $minutes < 60){
                    return $minutes . ' phút';
                }else{
                    return date("H:i", $var + $shift);
                }
            }else if ($lay_ngay == $ngay - 1) {
                return 'Hôm qua, ' . date("H:i", $var + $shift);
            }else if ($lay_ngay == $ngay - 2) {
                return $thu_time . ' lúc ' . date("H:i", $var + $shift);
            }else if ($lay_ngay == $ngay - 3) {
                return $thu_time . ' lúc ' . date("H:i", $var + $shift);
            }else if ($lay_ngay >= $ngay - 30) {
                return $thu_time . ' ' . date("j", $var + $shift) . ' thág ' . date("n", $var + $shift) . ', ' . date("H:i", $var + $shift);
            }else{
                return date("j", $var + $shift) . ' thág ' . date("n", $var + $shift) . ', ' . date("H:i", $var + $shift);
            }
        }
        return $thu_time . ' '.date("j", $var + $shift) . ' thág ' . date("n", $var + $shift) . ' ' . date("Y", $var + $shift);
    }

    /**
     * Сообщения об ошибках
     *
     * @param string|array $error Сообщение об ошибке (или массив с сообщениями)
     * @param string       $link  Необязательная ссылка перехода
     * @return string
     */
    public function displayError($error = '', $link = '', $align = '')
    {
        return '<div class="rmenu' . (empty($align) ? '' : ' ' . $align) . '"><p><b>' . _t('ERROR', 'system') . '!</b><br>'
            . (is_array($error) ? implode('<br>', $error) : $error) . '</p>'
            . (!empty($link) ? '<p>' . $link . '</p>' : '') . '</div>';
    }

    /**
     * Постраничная навигация
     * За основу взята доработанная функция от форума SMF 2.x.x
     *
     * @param string $url
     * @param int    $start
     * @param int    $total
     * @param int    $kmess
     * @return string
     */
    public function displayPagination($url, $start, $total, $kmess)
    {
        $neighbors = 2;
        if ($start >= $total) {
            $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
        } else {
            $start = max(0, (int)$start - ((int)$start % (int)$kmess));
        }

        $base_link = '<a class="pagenav" href="' . strtr($url, ['%' => '%%']) . 'page=%d' . '">%s</a>';
        $out[] = $start == 0 ? '' : sprintf($base_link, $start / $kmess, '&lt;');

        if ($start > $kmess * $neighbors) {
            $out[] = sprintf($base_link, 1, '1');
        }

        if ($start > $kmess * ($neighbors + 1)) {
            $out[] = '<strong>...</strong>';
        }

        for ($nCont = $neighbors; $nCont >= 1; $nCont--) {
            if ($start >= $kmess * $nCont) {
                $tmpStart = $start - $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        }

        $out[] = '<strong class="currentpage">' . ($start / $kmess + 1) . '</strong>';
        $tmpMaxPages = (int)(($total - 1) / $kmess) * $kmess;

        for ($nCont = 1; $nCont <= $neighbors; $nCont++) {
            if ($start + $kmess * $nCont <= $tmpMaxPages) {
                $tmpStart = $start + $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        }

        if ($start + $kmess * ($neighbors + 1) < $tmpMaxPages) {
            $out[] = '<strong>...</strong>';
        }

        if ($start + $kmess * $neighbors < $tmpMaxPages) {
            $out[] = sprintf($base_link, $tmpMaxPages / $kmess + 1, $tmpMaxPages / $kmess + 1);
        }

        if ($start + $kmess < $total) {
            $display_page = ($start + $kmess) > $total ? $total : ($start / $kmess + 2);
            $out[] = sprintf($base_link, $display_page, '&gt;');
        }

        return implode(' ', $out);
    }

    public function displayPaginationSeo($url, $start, $total, $kmess)
    {
        $neighbors = 2;
        if ($start >= $total) {
            $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
        } else {
            $start = max(0, (int)$start - ((int)$start % (int)$kmess));
        }

        $base_link = '<a class="tload pagenav" href="' . strtr($url, ['%' => '%%']) . '_p%d' . '.html">%s</a>';
        $out[] = $start == 0 ? '' : sprintf($base_link, $start / $kmess, '&lt;');

        if ($start > $kmess * $neighbors) {
            $out[] = sprintf($base_link, 1, '1');
        }

        if ($start > $kmess * ($neighbors + 1)) {
            $out[] = '<strong>...</strong>';
        }

        for ($nCont = $neighbors; $nCont >= 1; $nCont--) {
            if ($start >= $kmess * $nCont) {
                $tmpStart = $start - $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        }

        $out[] = '<strong class="currentpage">' . ($start / $kmess + 1) . '</strong>';
        $tmpMaxPages = (int)(($total - 1) / $kmess) * $kmess;

        for ($nCont = 1; $nCont <= $neighbors; $nCont++) {
            if ($start + $kmess * $nCont <= $tmpMaxPages) {
                $tmpStart = $start + $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        }

        if ($start + $kmess * ($neighbors + 1) < $tmpMaxPages) {
            $out[] = '<strong>...</strong>';
        }

        if ($start + $kmess * $neighbors < $tmpMaxPages) {
            $out[] = sprintf($base_link, $tmpMaxPages / $kmess + 1, $tmpMaxPages / $kmess + 1);
        }

        if ($start + $kmess < $total) {
            $display_page = ($start + $kmess) > $total ? $total : ($start / $kmess + 2);
            $out[] = sprintf($base_link, $display_page, '&gt;');
        }

        return implode(' ', $out);
    }

    /**
     * Показываем местоположение пользователя
     *
     * @param int    $user_id
     * @param string $place
     * @return mixed|string
     */
    public function displayPlace($user_id = 0, $place = '', $headmod = '')
    {
        $place = explode(",", $place);

        $placelist = [
            'admlist'          => '<a href="#home#/users/index.php?act=admlist">' . _t('List of Admins', 'system') . '</a>',
            'album'            => '<a href="#home#/album/index.php">' . _t('Watching the photo album', 'system') . '</a>',
            'birth'            => '<a href="#home#/users/index.php?act=birth">' . _t('List of birthdays', 'system') . '</a>',
            'downloads'        => '<a href="#home#/downloads/index.php">' . _t('Downloads', 'system') . '</a>',
            'faq'              => '<a href="#home#/help/">' . _t('Reading the FAQ', 'system') . '</a>',
            'forum'            => '<a href="#home#/forum/index.php">' . _t('Forum', 'system') . '</a>&#160;/&#160;<a href="#home#/forum/index.php?act=who">&gt;&gt;</a>',
            'forumfiles'       => '<a href="#home#/forum/index.php?act=files">' . _t('Forum Files', 'system') . '</a>',
            'forumwho'         => '<a href="#home#/forum/index.php?act=who">' . _t('Looking, who in Forum?', 'system') . '</a>',
            'guestbook'        => '<a href="#home#/guestbook/index.php">' . _t('Guestbook', 'system') . '</a>',
            'here'             => _t('Here, in the list', 'system'),
            'homepage'         => _t('On the Homepage', 'system'),
            'library'          => '<a href="#home#/library/index.php">' . _t('Library', 'system') . '</a>',
            'mail'             => _t('Personal correspondence', 'system'),
            'news'             => '<a href="#home#/news/index.php">' . _t('Reading the news', 'system') . '</a>',
            'online'           => '<a href="#home#/users/index.php?act=online">' . _t('Who is online?', 'system') . '</a>',
            'profile'          => _t('Profile', 'system'),
            'profile_personal' => _t('Personal Profile', 'system'),
            'registration'     => _t('Registered on the site', 'system'),
            'userlist'         => '<a href="#home#/users/index.php?act=userlist">' . _t('List of users', 'system') . '</a>',
            'userstop'         => '<a href="#home#/users/index.php?act=top">' . _t('Watching Top 10 Users', 'system') . '</a>',
        ];

        if (array_key_exists($place[0], $placelist)) {
            if ($place[0] == 'profile') {
                if ($place[1] == $user_id) {
                    return '<a href="' . $this->config['homeurl'] . '/profile/?user=' . $place[1] . '">' . $placelist['profile_personal'] . '</a>';
                } else {
                    $user = $this->getUser($place[1]);

                    return $placelist['profile'] . ': <a href="' . $this->config['homeurl'] . '/profile/?user=' . $user['id'] . '">' . $user['name'] . '</a>';
                }
            } elseif ($place[0] == 'online' && !empty($headmod) && $headmod == 'online') {
                return $placelist['here'];
            } else {
                return str_replace('#home#', $this->config['homeurl'], $placelist[$place[0]]);
            }
        }

        return '<a href="' . $this->config['homeurl'] . '/index.php">' . $placelist['homepage'] . '</a>';
    }

    /**
     * Отображения личных данных пользователя
     *
     * @param int   $user Массив запроса в таблицу `users`
     * @param array $arg  Массив параметров отображения
     *                    [lastvisit] (boolean)   Дата и время последнего визита
     *                    [stshide]   (boolean)   Скрыть статус (если есть)
     *                    [iphide]    (boolean)   Скрыть (не показывать) IP и UserAgent
     *                    [iphist]    (boolean)   Показывать ссылку на историю IP
     *
     *                    [header]    (string)    Текст в строке после Ника пользователя
     *                    [body]      (string)    Основной текст, под ником пользователя
     *                    [sub]       (string)    Строка выводится вверху области "sub"
     *                    [footer]    (string)    Строка выводится внизу области "sub"
     *
     * @return string
     */
    public function displayUser($user = 0, array $arg = [])
    {
        global $mod;
        $out = false;
        $homeurl = $this->config['homeurl'];

        if (!$user['id']) {
            $out = '<b>' . _t('Guest', 'system') . '</b>';

            if (!empty($user['name'])) {
                $out .= ': ' . $user['name'];
            }

            if (!empty($arg['header'])) {
                $out .= ' ' . $arg['header'];
            }
        } else {
            $avatar_name = $this->avatar_name($user['id']);
            if (file_exists((ROOT_PATH . 'files/users/avatar/' . $avatar_name))) {
                $out .= '<img src="' . $homeurl . '/files/users/avatar/' . $avatar_name . '" class="avatar" alt="" />';
            } else {
                $out .= '<img src="' . $homeurl . '/images/empty' . ($user['sex'] ? ($user['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png') . '" class="avatar" alt="" />';
            }

            $out .= '<ul class="finfo"><li>';
            $sexColor = null;
            if ($user['sex']) {
                if($user['sex'] == 'm'){
                    $sexColor = 'mColor';
                }else{
                    $sexColor = 'wColor';
                }
                $out .=  ($user['datereg'] > time() - 86400 ? '<i class="material-icons list__item-icon ' . $sexColor . '">&#xE7FE;</i>' : '<i class="material-icons list__item-icon ' . $sexColor . '">&#xE7FD;</i>');
            } else {
                $out .= $this->image('del.png');
            }

            $out .= !$this->user->isValid() || $this->user->id == $user['id'] ? $this->rightsColor($user['rights'], $user['name']) : '<a href="' . $homeurl . '/profile/?user=' . $user['id'] . '">' . $this->rightsColor($user['rights'], $user['name']) . '</a>';
            $rank = [
                0 => '',
                1 => '(GMod)',
                2 => '(CMod)',
                3 => '(FMod)',
                4 => '(DMod)',
                5 => '(LMod)',
                6 => '(Smd)',
                7 => '(Adm)',
                9 => '(SV!)',
            ];
            $rights = isset($user['rights']) ? $user['rights'] : 0;
            $out .= '&#160;' . $rank[$rights];
            $out .= (!$rights ? '' : '&#160;') . (time() > $user['lastdate'] + 60 ? '<span class="red"> [Off]</span>' : '<span class="wgreen"> [ON]</span>');

            if (!empty($arg['header'])) {
                $out .= ' ' . $arg['header'];
            }

            if (!isset($arg['stshide']) && !empty($user['status'])) {
                $out .= '</li><li><i class="material-icons list__item-icon gray">star</i><small>' . $user['status'] . '</small>';
            }

            if (isset($arg['balans'])) {
                $out .= '</li><li><i class="material-icons list__item-icon red">&#xE227;</i><small>' . $this->balans($user['balans']) . ' VNĐ</small>';
            }

            $out .= '</li></ul>';
        }

        if (isset($arg['body'])) {
            $out .= '<div>' . $arg['body'] . '</div>';
        }

        $ipinf = isset($arg['iphide']) ? !$arg['iphide'] : ($this->user->rights ? 1 : 0);
        $lastvisit = time() > $user['lastdate'] + 60 && isset($arg['lastvisit']) ? $this->displayDate($user['lastdate']) : false;

        if ($ipinf || $lastvisit || isset($arg['sub']) && !empty($arg['sub']) || isset($arg['footer'])) {
            $out .= '<div class="sub">';

            if (isset($arg['sub'])) {
                $out .= '<div>' . $arg['sub'] . '</div>';
            }

            if ($lastvisit) {
                $out .= '<div><span class="gray">' . _t('Last Visit', 'system') . ':</span> ' . $lastvisit . '</div>';
            }

            $iphist = '';

            if ($ipinf) {
                $out .= '<div><span class="gray">' . _t('Browser', 'system') . ':</span> ' . htmlspecialchars($user['browser']) . '</div>' .
                    '<div><span class="gray">' . _t('IP address', 'system') . ':</span> ';
                $hist = $mod == 'history' ? '&amp;mod=history' : '';
                $ip = long2ip($user['ip']);

                if ($this->user->rights && isset($user['ip_via_proxy']) && $user['ip_via_proxy']) {
                    $out .= '<b class="red"><a href="' . $homeurl . '/admin/index.php?act=search_ip&amp;ip=' . $ip . $hist . '">' . $ip . '</a></b>';
                    $out .= '&#160;[<a href="' . $homeurl . '/admin/index.php?act=ip_whois&amp;ip=' . $ip . '">?</a>]';
                    $out .= ' / ';
                    $out .= '<a href="' . $homeurl . '/admin/index.php?act=search_ip&amp;ip=' . long2ip($user['ip_via_proxy']) . $hist . '">' . long2ip($user['ip_via_proxy']) . '</a>';
                    $out .= '&#160;[<a href="' . $homeurl . '/admin/index.php?act=ip_whois&amp;ip=' . long2ip($user['ip_via_proxy']) . '">?</a>]';
                } elseif ($this->user->rights) {
                    $out .= '<b class="red"><a href="' . $homeurl . '/admin/index.php?act=search_ip&amp;ip=' . $ip . $hist . '">' . $ip . '</a></b>';
                    $out .= '&#160;[<a href="' . $homeurl . '/admin/index.php?act=ip_whois&amp;ip=' . $ip . '">?</a>]';
                } else {
                    $out .= $ip . $iphist;
                }

                if (isset($arg['iphist'])) {
                    $iptotal = $this->db->query("SELECT COUNT(*) FROM `cms_users_iphistory` WHERE `user_id` = '" . $user['id'] . "'")->fetchColumn();
                    $out .= '<div><span class="gray">' . _t('IP History', 'system') . ':</span> <a href="' . $homeurl . '/profile/?act=ip&amp;user=' . $user['id'] . '">[' . $iptotal . ']</a></div>';
                }

                $out .= '</div>';
            }

            if (isset($arg['footer'])) {
                $out .= $arg['footer'];
            }
            $out .= '</div>';
        }

        return $out;
    }

    /**
     * Получение флага для выбранной локали
     *
     * @param string $locale
     * @return string
     */
    public function getFlag($locale)
    {
        $file = ROOT_PATH . 'system' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'lng.png';
        $flag = is_file($file) ? 'data:image/png;base64,' . base64_encode(file_get_contents($file)) : false;

        return $flag !== false ? '<img src="' . $flag . '" style="margin-right: 8px; vertical-align: middle">' : '';
    }

    /**
     * @return string
     */
    public function getSkin()
    {
        return $this->user->isValid() && !empty($this->userConfig->skin)
            ? $this->userConfig->skin
            : $this->config->skindef;
    }

    /**
     * Получаем данные пользователя
     *
     * @param int $id Идентификатор пользователя
     * @return array|bool
     */
    public function getUser($id = 0)
    {
        if ($id && $id != $this->user->id) {
            $req = $this->db->query("SELECT * FROM `users` WHERE `id` = '$id'");

            if ($req->rowCount()) {
                return $req->fetch();
            } else {
                return false;
            }
        } else {
            return $this->user;
        }
    }

    /**
     * @param string $name
     * @param array  $args
     * @return bool|string
     */
    public function image($name, array $args = [])
    {
        $homeurl = $this->config['homeurl'];

        if (is_file(ROOT_PATH . 'theme/' . $this->getSkin() . '/images/' . $name)) {
            $src = $homeurl . '/theme/' . $this->getSkin() . '/images/' . $name;
        } elseif (is_file(ROOT_PATH . 'images/' . $name)) {
            $src = $homeurl . '/images/' . $name;
        } else {
            return false;
        }

        return '<img src="' . $src . '" alt="' . (isset($args['alt']) ? $args['alt'] : '') . '"' .
            (isset($args['width']) ? ' width="' . $args['width'] . '"' : '') .
            (isset($args['height']) ? ' height="' . $args['height'] . '"' : '') .
            ' class="' . (isset($args['class']) ? $args['class'] : 'icon') . '"/>';
    }

    /**
     * Проверка на игнор у получателя
     *
     * @param $id
     * @return bool
     */
    public function isIgnor($id)
    {
        static $user_id = null;
        static $return = false;

        if (!$this->user->isValid() && !$id) {
            return false;
        }

        if (is_null($user_id) || $id != $user_id) {
            $user_id = $id;
            $req = $this->db->query("SELECT * FROM `cms_contact` WHERE `user_id` = '$id' AND `from_id` = " . $this->user->id);

            if ($req->rowCount()) {
                $res = $req->fetch();
                if ($res['ban'] == 1) {
                    $return = true;
                }
            }
        }

        return $return;
    }

    /**
     * Транслитерация с Русского в латиницу
     *
     * @param string $str
     * @return string
     */
    public function rusLat($str)
    {
        $replace = [
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'j',
            'з' => 'z',
            'и' => 'i',
            'й' => 'i',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ъ' => "",
            'ы' => 'y',
            'ь' => "",
            'э' => 'ye',
            'ю' => 'yu',
            'я' => 'ya',
        ];

        return strtr(mb_strtolower($str), $replace);
    }

    /**
     * Обработка смайлов
     *
     * @param string $str
     * @param bool   $adm
     * @return string
     */
    public function smilies($str, $adm = false)
    {
        static $smiliesCache = [];

        if (empty($smiliesCache)) {
            $file = ROOT_PATH . 'files/cache/smileys.dat';

            if (file_exists($file) && ($smileys = file_get_contents($file)) !== false) {
                $smiliesCache = unserialize($smileys);

                return strtr($str, $smiliesCache['usr']);
            } else {
                return $str;
            }
        } else {
            return strtr($str, $smiliesCache['usr']);
        }
    }

    /**
     * Функция пересчета на дни, или часы
     *
     * @param int $var
     * @return bool|string
     */
    public function timecount($var)
    {
        if ($var < 0) {
            $var = 0;
        }

        $day = ceil($var / 86400);

        return $var >= 86400
            ? $day . ' ' . _p('Day', 'Days', $day, 'system')
            : date("G:i:s", mktime(0, 0, $var));
    }

    // Транслитерация текста
    public function trans($str)
    {
        $replace = [
            'a'  => 'а',
            'b'  => 'б',
            'v'  => 'в',
            'g'  => 'г',
            'd'  => 'д',
            'e'  => 'е',
            'yo' => 'ё',
            'zh' => 'ж',
            'z'  => 'з',
            'i'  => 'и',
            'j'  => 'й',
            'k'  => 'к',
            'l'  => 'л',
            'm'  => 'м',
            'n'  => 'н',
            'o'  => 'о',
            'p'  => 'п',
            'r'  => 'р',
            's'  => 'с',
            't'  => 'т',
            'u'  => 'у',
            'f'  => 'ф',
            'h'  => 'х',
            'c'  => 'ц',
            'ch' => 'ч',
            'w'  => 'ш',
            'sh' => 'щ',
            'q'  => 'ъ',
            'y'  => 'ы',
            'x'  => 'э',
            'yu' => 'ю',
            'ya' => 'я',
            'A'  => 'А',
            'B'  => 'Б',
            'V'  => 'В',
            'G'  => 'Г',
            'D'  => 'Д',
            'E'  => 'Е',
            'YO' => 'Ё',
            'ZH' => 'Ж',
            'Z'  => 'З',
            'I'  => 'И',
            'J'  => 'Й',
            'K'  => 'К',
            'L'  => 'Л',
            'M'  => 'М',
            'N'  => 'Н',
            'O'  => 'О',
            'P'  => 'П',
            'R'  => 'Р',
            'S'  => 'С',
            'T'  => 'Т',
            'U'  => 'У',
            'F'  => 'Ф',
            'H'  => 'Х',
            'C'  => 'Ц',
            'CH' => 'Ч',
            'W'  => 'Ш',
            'SH' => 'Щ',
            'Q'  => 'Ъ',
            'Y'  => 'Ы',
            'X'  => 'Э',
            'YU' => 'Ю',
            'YA' => 'Я',
        ];

        return strtr($str, $replace);
    }

    public function grab($url)
    {
        $uag = $_SERVER['HTTP_USER_AGENT'];
        $ch = @curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $uag);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Encoding: gzip,deflate,sdch',
                'Accept-Language: vi-VN,vi;q=0.8,fr-FR;q=0.6,fr;q=0.4,en-US;q=0.2,en;q=0.2',
                'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                'Connection: keep-alive',
                'Keep-Alive: 300'
            )
        );
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Expect:'
        ));
        $page = curl_exec($ch);
        curl_close($ch);
        return $page;
    }

    // balans
    public function balans($balans = 0)
    {
        return number_format($balans, 0, ",", ".");
    }

    // balans game
    public function gBalans($balans = 0)
    {
        $symb = '';
        if ($balans >= 1000000){
            $balans = number_format($balans/1000000, 2, ",", ".");
            $symb   = 'M';
        } else if ($balans < 1000000 && $balans >= 1000){
            $balans = number_format($balans/1000, 2, ",", ".");
            $symb   = 'K';
        }
        return $balans . $symb;
    }

    public function rightsColor($rights, $name, $bold = 1)
    {
        $listColor = array(
            '0' => 'nickname',
            '1' => 'nickname',
            '2' => 'nickname',
            '3' => 'nickname',
            '4' => 'nickname',
            '5' => 'nickname',
            '6' => 'nickname',
            '7' => 'nickname',
            '8' => 'nickname',
            '9' => 'nickadmin'
        );
        return $bold ? '<strong class="' . $listColor[$rights] . '">' . $name . '</strong>' : '<span class="' . $listColor[$rights] . '">' . $name . '</span>';
    }
    
    /* avatar name */
    public function avatar_name($uid)
    {
        $data = $this->db->query("SELECT `avatar_id`, `avatar_extension` FROM `users` WHERE `id`='$uid' LIMIT 1")->fetch();
        if ($data['avatar_extension'] != 'none') {
            if ($data['avatar_id'] == '1') {
                $bname = '';
            } else if ($data['avatar_id'] == '2') {
                $bname = '_100x100';
            } else if ($data['avatar_id'] == '3') {
                $bname = '_100x75';
            } else if ($data['avatar_id'] == '4') {
                $bname = '_thumb';
            } else {
                $bname = '_100x100';
            }
            return $uid . '_thumb.' . $data['avatar_extension'];
        }
        return $uid . '.png';
    }

    public function is_friend($id = FALSE)
    {
        if (!$this->user->id && !$id) {
            return FALSE;
        }

        if ($this->user->isValid() || $id != $this->user->id) {
            $query = $this->db->query("SELECT COUNT(*) FROM `cms_contact` WHERE `type` = '2' AND ((`from_id` = '$id' AND `user_id` = '" . self::$user_id . "') OR (`from_id` = '" . self::$user_id . "' AND `user_id` = '$id'))")->fetchColumn();
            $return = $query == 2 ? TRUE : FALSE;
        }
        return $return;
    }

    public function seourl($var){
        $var = preg_replace('/(â|ầ|ầ|ấ|ấ|ậ|ậ|ẩ|ẩ|ẫ|ẫ|ă|ằ|ằ|ắ|ắ|ặ|ặ|ẳ|ẳ|ẵ|ẵ|à|à|á|á|ạ|ạ|ả|ả|ã|ã)/', 'a', $var);
        $var = preg_replace('/(ê|ề|ề|ế|ế|ệ|ệ|ể|ể|ễ|ễ|è|è|é|é|ẹ|ẹ|ẻ|ẻ|ẽ|ẽ)/', 'e', $var);
        $var = preg_replace('/(ì|ì|í|í|ị|ị|ỉ|ỉ|ĩ|ĩ)/', 'i', $var);
        $var = preg_replace('/(ô|ồ|ồ|ố|ố|ộ|ộ|ổ|ổ|ỗ|ỗ|ơ|ờ|ờ|ớ|ớ|ợ|ợ|ở|ở|ỡ|ỡ|ò|ò|ó|ó|ọ|ọ|ỏ|ỏ|õ|õ)/', 'o', $var);
        $var = preg_replace('/(ư|ừ|ừ|ứ|ứ|ự|ự|ử|ử|ữ|ữ|ù|ù|ú|ú|ụ|ụ|ủ|ủ|ũ|ũ)/', 'u', $var);
        $var = preg_replace('/(ỳ|ỳ|ý|ý|ỵ|ỵ|ỷ|ỷ|ỹ|ỹ)/', 'y', $var);
        $var = preg_replace('/(đ)/', 'd', $var);
        $var = preg_replace('/(B)/', 'b', $var);
        $var = preg_replace('/(C)/', 'c', $var);
        $var = preg_replace('/(D)/', 'd', $var);
        $var = preg_replace('/(F)/', 'f', $var);
        $var = preg_replace('/(G)/', 'g', $var);
        $var = preg_replace('/(H)/', 'h', $var);
        $var = preg_replace('/(J)/', 'j', $var);
        $var = preg_replace('/(K)/', 'k', $var);
        $var = preg_replace('/(L)/', 'l', $var);
        $var = preg_replace('/(M)/', 'm', $var);
        $var = preg_replace('/(N)/', 'n', $var);
        $var = preg_replace('/(P)/', 'p', $var);
        $var = preg_replace('/(Q)/', 'q', $var);
        $var = preg_replace('/(R)/', 'r', $var);
        $var = preg_replace('/(S)/', 's', $var);
        $var = preg_replace('/(T)/', 't', $var);
        $var = preg_replace('/(V)/', 'v', $var);
        $var = preg_replace('/(W)/', 'w', $var);
        $var = preg_replace('/(X)/', 'x', $var);
        $var = preg_replace('/(Z)/', 'z', $var);
        $var = preg_replace('/(Â|Ầ|Ầ|Ấ|Ấ|Ậ|Ậ|A|Ẩ|Ẩ|Ẫ|Ẫ|Ă|Ắ|Ằ|Ằ|Ắ|Ặ|Ặ|Ẳ|Ẳ|Ẵ|Ẵ|À|À|Á|Á|Ạ|Ạ|Ả|Ả|Ã|Ã)/', 'a', $var);
        $var = preg_replace('/(Ẽ|Ẽ|Ê|Ề|E|Ề|Ế|Ế|Ệ|Ệ|Ể|Ể|Ễ|Ễ|È|È|É|É|Ẹ|Ẹ|Ẻ|Ẻ)/', 'e', $var);
        $var = preg_replace('/(Ì|Ì|Í|Í|Ị|Ị|I|Ỉ|Ỉ|Ĩ|Ĩ)/', 'i', $var);
        $var = preg_replace('/(Ô|Ồ|Ồ|Ố|Ố|O|Ộ|Ộ|Ổ|Ổ|Ỗ|Ỗ|Ờ|Ơ|Ờ|Ớ|Ớ|Ợ|Ợ|Ở|Ở|Ỡ|Ỡ|Ò|Ò|Ó|Ó|Ọ|Ọ|Ỏ|Ỏ|Õ|Õ)/', 'o', $var);
        $var = preg_replace('/(Ư|Ừ|Ừ|U|Ứ|Ứ|Ự|Ự|Ử|Ử|Ữ|Ữ|Ù|Ù|Ú|Ú|Ụ|Ụ|Ủ|Ủ|Ũ|Ũ)/', 'u', $var);
        $var = preg_replace('/(Ỳ|Ỳ|Ý|Ý|Ỵ|Y|Ỵ|Ỷ|Ỷ|Ỹ|Ỹ)/', 'y', $var);
        $var = preg_replace('/(́|̀|̉|̃||̣)/', '', $var);
        $var = preg_replace('/(Đ)/', 'd', $var);
        $var = htmlspecialchars_decode($var);
        $var = str_replace(',', '', $var);
        $var = str_replace('_', '', $var);
        $var = str_ireplace(array('&ETH;', '&Eth;', '&eth;'), '-', $var);
        $var = preg_replace('/[\W]+/s', '-', $var);
        $var = preg_replace('/-{2,}/', '-', $var);

        $strlen = mb_strlen($var);
        $check = mb_stripos($var, "-");
        if($check == false)
            $var = mb_substr($var, 1, $strlen);
    
        $strlen = mb_strlen($var);
        $check = mb_strrpos($var, "-");
        if(($strlen - $check) === 1)
            $var = mb_substr($var, 0, $check);

        return $var;
    }

    public function cutText($var, $begin, $end) {
        if (mb_strlen($var) > $end) {
            $var = mb_substr($var, $begin, $end);
            if ($begin > 0)
                $var = mb_substr($var, mb_stripos($var, " "), mb_strrpos($var, " "));
            else
                $var = mb_substr($var, 0, mb_strrpos($var, " "));
            $var = ($begin > 0 ? '... ' : '') . $var . ' ...';
        }
        return $var;
    }

    public function createTags($name, $type = 0){
        $home = $this->config['homeurl'];
        $name = trim($name);
        if(stristr($name, ' ')){
            $explode = explode(' ', $name);
            unset($name);
            $count = count($explode);
            for($i = 0; $i < $count; $i++){
                $var = trim($explode[$i]);
                $getEx = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
                $getD = rawurlencode($getEx);
                $name .= ($type == 1 ? "\n" . '<li class="ul-list"><a href="' . $home . '/forum/search.php?search=' . $getD . '" class="tload m-chip gray"><span class="m-chip__text">' . $getEx . '</span></a></li>' : $getEx.', ');
            }
         } else if ($type == 1) $name = "\n" . '<li class="ul-list"><a href="' . $home . '/forum/search.php?search=' . $name . '" class="tload m-chip gray"><span class="m-chip__text">' . $name . '</span></a></li>';
         return $name;
    }

    public function curl_imgur($tout = 0, $client = '', $value = '')
    {
        $timeout = $tout;
        $client_id = $client;
        $pvars = $value;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $pvars);
        $out = curl_exec($curl);
        curl_close($curl);

        return $out;
    }

    public function formatsize($size) {
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

    public function stringEscape($content, $nullvalue = false){
        if (empty($content) && $nullvalue != false) {
            $content = $nullvalue;
        }
        $content = trim($content);
        $content = htmlspecialchars($content, ENT_QUOTES);
        $content = stripslashes($content);

        return $content;
    }

    /* Fix Orientation */
    public function fixOrientation($path)
    {
        if (file_exists ($path))
        {
            if (strrpos($path, '.'))
            {
                $ext = substr($path, strrpos($path,'.') + 1, strlen($path) - strrpos($path, '.'));

                if (in_array($ext, array('jpeg', 'jpg')))
                {
                    $fxt = true;
                }
            }
        }

        if (! isset($fxt))
        {
            return false;
        }

        $image = imagecreatefromjpeg($path);
        $exif = exif_read_data($path);

        if (!empty($exif['Orientation']))
        {
            switch ($exif['Orientation'])
            {
                case 3:
                    $image = imagerotate($image, 180, 0);
                    break;

                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;

                case 8:
                    $image = imagerotate($image, 90, 0);
                    break;
            }
        }

        imagejpeg($image, $path);
        return true;
    }

    /* Process Images */
    public function processMedia($run, $photo_src, $save_src, $width=0, $height=0, $quality=80){

    if (! is_numeric($quality) || $quality < 0 || $quality > 100)
    {
        $quality = 80;
    }

    if (file_exists ($photo_src))
    {
        if (strrpos($photo_src, '.'))
        {
            $ext = substr($photo_src, strrpos($photo_src,'.') + 1, strlen($photo_src) - strrpos($photo_src, '.'));
            $fxt = (in_array($ext, array('jpeg', 'png', 'gif'))) ? $ext : "jpeg";
        } else
        {
            $ext = $fxt = 0;
        }

        if (preg_match('/(jpg|jpeg|png|gif)/', $ext))
        {
            if ($fxt == "gif")
            {
                copy($photo_src, $save_src);
                return true;
            }

            list($photo_width, $photo_height) = getimagesize($photo_src);
            $create_from = "imagecreatefrom" . $fxt;
            $photo_source = $create_from($photo_src);

            if ($run == "crop")
            {
                if ($width > 0 && $height > 0)
                {
                    $crop_width = $photo_width;
                    $crop_height = $photo_height;
                    $k_w = 1;
                    $k_h = 1;
                    $dst_x = 0;
                    $dst_y = 0;
                    $src_x = 0;
                    $src_y = 0;

                    if ($width == 0 || $width > $photo_width)
                    {
                        $width = $photo_width;
                    }

                    if ($height == 0 || $height > $photo_height)
                    {
                        $height = $photo_height;
                    }

                    $crop_width = $width;
                    $crop_height = $height;

                    if ($crop_width > $photo_width)
                    {
                        $dst_x = ($crop_width - $photo_width) / 2;
                    }

                    if ($crop_height > $photo_height)
                    {
                        $dst_y = ($crop_height - $photo_height) / 2;
                    }

                    if ($crop_width < $photo_width || $crop_height < $photo_height)
                    {
                        $k_w = $crop_width / $photo_width;
                        $k_h = $crop_height / $photo_height;

                        if ($crop_height > $photo_height)
                        {
                            $src_x  = ($photo_width - $crop_width) / 2;
                        } else if ($crop_width > $photo_width)
                        {
                            $src_y  = ($photo_height - $crop_height) / 2;
                        } else
                        {
                            if ($k_h > $k_w)
                            {
                                $src_x = round(($photo_width - ($crop_width / $k_h)) / 2);
                            } else
                            {
                                $src_y = round(($photo_height - ($crop_height / $k_w)) / 2);
                            }
                        }
                    }

                    $crop_image = @imagecreatetruecolor($crop_width, $crop_height);

                    if ($ext == "png")
                    {
                        @imagesavealpha($crop_image, true);
                        @imagefill($crop_image, 0, 0, @imagecolorallocatealpha($crop_image, 0, 0, 0, 127));
                    }

                    @imagecopyresampled($crop_image, $photo_source ,$dst_x, $dst_y, $src_x, $src_y, $crop_width - 2 * $dst_x, $crop_height - 2 * $dst_y, $photo_width - 2 * $src_x, $photo_height - 2 * $src_y);

                    @imageinterlace($crop_image, true);

                    if ($fxt == "jpeg")
                    {
                        @imagejpeg($crop_image, $save_src, $quality);
                    } else if ($fxt == "png")
                    {
                        @imagepng($crop_image, $save_src);
                    } else if ($fxt == "gif")
                    {
                        @imagegif($crop_image, $save_src);
                    }

                    @imagedestroy($crop_image);
                }
            } else if ($run == "resize")
            {
                if ($width == 0 && $height == 0)
                {
                    return false;
                }

                if ($width > 0 && $height == 0)
                {
                    $resize_width = $width;
                    $resize_ratio = $resize_width / $photo_width;
                    $resize_height = floor($photo_height * $resize_ratio);
                } else if ($width == 0 && $height > 0)
                {
                    $resize_height = $height;
                    $resize_ratio = $resize_height / $photo_height;
                    $resize_width = floor($photo_width * $resize_ratio);
                } else if ($width > 0 && $height > 0)
                {
                    $resize_width = $width;
                    $resize_height = $height;
                }

                if ($resize_width > 0 && $resize_height > 0)
                {
                    $resize_image = @imagecreatetruecolor($resize_width, $resize_height);

                    if ($ext == "png")
                    {
                        @imagesavealpha($resize_image, true);
                        @imagefill($resize_image, 0, 0, @imagecolorallocatealpha($resize_image, 0, 0, 0, 127));
                    }

                    @imagecopyresampled($resize_image, $photo_source, 0, 0, 0, 0, $resize_width, $resize_height, $photo_width, $photo_height);
                    @imageinterlace($resize_image, true);

                    if ($fxt == "jpeg")
                    {
                        @imagejpeg($resize_image, $save_src, $quality);
                    } else if ($fxt == "png")
                    {
                        @imagepng($resize_image, $save_src);
                    } else if ($fxt == "gif")
                    {
                        @imagegif($resize_image, $save_src);
                    }

                    @imagedestroy($resize_image);
                }
            } else if ($run == "scale")
            {
                if ($width == 0)
                {
                    $width = 100;
                }

                if ($height == 0)
                {
                    $height = 100;
                }

                $scale_width = $photo_width * ($width / 100);
                $scale_height = $photo_height * ($height / 100);
                $scale_image = @imagecreatetruecolor($scale_width, $scale_height);

                if ($ext == "png")
                {
                    @imagesavealpha($scale_image, true);
                    @imagefill($scale_image, 0, 0, imagecolorallocatealpha($scale_image, 0, 0, 0, 127));
                }

                @imagecopyresampled($scale_image, $photo_source, 0, 0, 0, 0, $scale_width, $scale_height, $photo_width, $photo_height);
                @imageinterlace($scale_image, true);

                if ($fxt == "jpeg")
                {
                    @imagejpeg($scale_image, $save_src, $quality);
                } else if ($fxt == "png")
                {
                    @imagepng($scale_image, $save_src);
                } else if ($fxt == "gif")
                {
                    @imagegif($scale_image, $save_src);
                }
                @imagedestroy($scale_image);
            }
        }
    }
}

    /* Register Media */
    public function registerMedia($upload, $new_id, $folder = '', $type = '', $re_width = 0, $re_height = 0)
    {
        $id = $new_id;
        $resize_width = $re_width;
        $resize_height = $re_height;
        $photo_dir = ROOT_PATH . $folder;
        if (is_uploaded_file($upload['tmp_name']))
        {
            $upload['name'] = $this->stringEscape($upload['name']);
            $name = preg_replace('/([^A-Za-z0-9_\-\.]+)/i', '', $upload['name']);
            $ext = strtolower(substr($upload['name'], strrpos($upload['name'], '.') + 1, strlen($upload['name']) - strrpos($upload['name'], '.')));

            if ($upload['size'] > 1024)
            {
                if (preg_match('/(jpg|jpeg|png|gif)/', $ext))
                {
                    if (getimagesize($upload['tmp_name']))
                    {
                        if($type == 'avatar') {
                            $urExt = $this->db->query("SELECT `avatar_extension` FROM `users` WHERE `id`='$id' LIMIT 1")->fetch();
                            if ($urExt['avatar_extension'] != 'none')
                            {
                                $ext_base = $urExt['avatar_extension'];
                                if (file_exists(($photo_dir . '/' . $id . '.' . $ext_base))) {
                                    @unlink($photo_dir . '/' . $id . '.' . $ext_base);
                                }
                                if (file_exists(($photo_dir . '/' . $id . '_100x100.' . $ext_base))) {
                                    @unlink($photo_dir . '/' . $id . '_100x100.' . $ext_base);
                                }
                                if (file_exists(($photo_dir . '/' . $id . '_100x75.' . $ext_base))) {
                                    @unlink($photo_dir . '/' . $id . '_100x75.' . $ext_base);
                                }
                                if (file_exists(($photo_dir . '/' . $id . '_thumb.' . $ext_base))) {
                                    @unlink($photo_dir . '/' . $id . '_thumb.' . $ext_base);
                                }
                            }
                        } else if ($type == 'thumb') {
                            $urExt = $this->db->query("SELECT * FROM `forum` WHERE `id` = " . $this->db->quote($id) . " ")->fetch();
                            if ($urExt['thumb_extension'] != 'none')
                            {
                                $ext_base = $urExt['thumb_extension'];
                                if (file_exists(($photo_dir . '/' . $id . '.' . $ext_base))) {
                                    @unlink($photo_dir . '/' . $id . '.' . $ext_base);
                                }
                            }
                        }

                        list($width, $height) = getimagesize($upload['tmp_name']);

                        $original_file_name = $photo_dir . '/' . $id;
                        $original_file = $original_file_name . '.' . $ext;

                        if (move_uploaded_file($upload['tmp_name'], $original_file))
                        {

                            $image_mime = image_type_to_mime_type(exif_imagetype($original_file));
                            switch ($image_mime)
                            {
                                case "image/gif":
                                    $base_mime = "gif";
                                break;

                                case "image/jpeg":
                                    $base_mime = "jpg";
                                break;

                                case "image/png":
                                    $base_mime = "png";
                                break;
                            }

                            if ($base_mime != $ext){
                                rename($original_file, $original_file_name . '.' . $base_mime);
                                $original_file = $original_file_name . '.' . $base_mime;
                                $ext = $base_mime;
                            }

                            @$this->fixOrientation($original_file);

                            $min_size = $width;

                            if ($width > $height)
                            {
                                $min_size = $height;
                            }

                            $min_size = floor($min_size);

                            if ($min_size > 920)
                            {
                                $min_size = 920;
                            }
                            if ($type == 'avatar') {
                                $imageSizes = array(
                                    'thumb' => array(
                                        'type' => 'crop',
                                        'width' => 64,
                                        'height' => 64,
                                        'name' => $original_file_name . '_thumb'
                                    ),
                                    '100x100' => array(
                                        'type' => 'crop',
                                        'width' => $min_size,
                                        'height' => $min_size,
                                        'name' => $original_file_name . '_100x100'
                                    ),
                                    '100x75' => array(
                                        'type' => 'crop',
                                        'width' => $min_size,
                                        'height' => floor($min_size * 0.75),
                                        'name' => $original_file_name . '_100x75'
                                    )
                                );

                                foreach ($imageSizes as $ratio => $data)
                                {
                                    $save_file = $data['name'] . '.' . $ext;
                                    $this->processMedia($data['type'], $original_file, $save_file, $data['width'], $data['height']);
                                }

                                $this->processMedia('resize', $original_file, $original_file, $min_size, 0);
                            } else if ($type == 'size_thumb') {
                                $data = array(
                                    'type' => 'crop',
                                    'width' => $resize_width,
                                    'height' => $resize_height,
                                    'name' => $original_file_name
                                );
                                $this->processMedia($data['type'], $original_file, $original_file, $data['width'], $data['height']);
                            } else if ($type == 'thumb') {
                                $data = array(
                                    'type' => 'crop',
                                    'width' => 100,
                                    'height' => 100,
                                    'name' => $original_file_name
                                );
                                $this->processMedia($data['type'], $original_file, $original_file, $data['width'], $data['height']);
                            }
                            $get = array(
                                'id' => '1',
                                'active' => 1,
                                'extension' => $ext,
                                'name' => basename($original_file_name),
                                'url' => $original_file_name
                            );
                            return $get;
                        }
                    } else
                    {
                        $get = false;
                        return $get;
                    }
                }
            }
        }
        $get = false;
        return $get;
    }

        /* Register cover */
    public function registerCoverImage($upload, $pos=0)
    {
        $photo_dir = 'files/users/photo';

        if (is_uploaded_file($upload['tmp_name']))
        {
            $upload['name'] = $this->stringEscape($upload['name']);
            $name = preg_replace('/([^A-Za-z0-9_\-\.]+)/i', '', $upload['name']);
            $ext = strtolower(substr($upload['name'], strrpos($upload['name'], '.') + 1, strlen($upload['name']) - strrpos($upload['name'], '.')));
        
            if ($upload['size'] > 1024)
            {
                if (preg_match('/(jpg|jpeg|png|gif)/', $ext))
                {
                    list($width, $height) = getimagesize($upload['tmp_name']);

                    $original_file_name = $photo_dir . '/' . $this->user->id;
                    $original_file = $original_file_name . '.' . $ext;
                    
                    if (move_uploaded_file($upload['tmp_name'], $original_file))
                    {
                        $image_mime = image_type_to_mime_type(exif_imagetype($original_file));
                        switch ($image_mime) { 
                            case "image/gif": 
                                $base_mime = "gif"; 
                                break; 
                            case "image/jpeg": 
                                $base_mime = "jpg"; 
                                break; 
                            case "image/png": 
                                $base_mime = "png"; 
                                break; 
                        }
                        if ($base_mime != $ext){
                            rename($original_file, $original_file_name . '.' . $base_mime);
                            $original_file = $original_file_name . '.' . $base_mime;
                            $ext = $base_mime;
                        }
                        $this->processMedia('resize', $original_file, $original_file, $width, 0, 100);

                        $img = $original_file;
                        $cover_img_url = $original_file_name . '_cover.' . $ext;
                        $dst_x = 0;
                        $dst_y = 0;
                        $src_x = 0;
                        $src_y = 0;
                        $dst_w = $width;
                        $dst_h = $dst_w * (0.39);
                        $src_w = $width;
                        $src_h = $dst_h;

                        if (! empty($pos) && is_numeric($pos) && $pos < $width) {
                            $pos = $this->stringEscape($pos);
                            $src_y = $width * $pos;
                        }

                        if ($ext == "gif") {
                            if (!extension_loaded('imagick')){
                                $cover_img = imagecreatetruecolor($dst_w, $dst_h);
                                imagealphablending($cover_img, false);
                                imagesavealpha($cover_img, true);
                                $image = imagecreatefromgif($img);
                                imagecopyresampled($cover_img, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
                                imagegif($cover_img, $cover_img_url);
                            } else {
                                $image = new \Imagick($img);
                                $image = $image->coalesceImages();
                                foreach ($image as $frame) {
                                    $frame->cropImage($src_w, $src_h, $src_x, $src_y);
                                    $frame->thumbnailImage($src_w, $src_h);
                                    $frame->setImagePage($src_w, $src_h, 0, 0);
                                }
                                $image = $image->deconstructImages();
                                $image->writeImages($cover_img_url, true);
                            }
                        } else {
                            $cover_img = imagecreatetruecolor($dst_w, $dst_h);
                            if ($ext == "png") {
                                $image = imagecreatefrompng($img);

                                imagealphablending($cover_img, false);
                                imagesavealpha($cover_img, true);
                            } else {
                                $image = imagecreatefromjpeg($img);
                            }
                            imagecopyresampled($cover_img, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
                            if ($ext == "png") {
                                imagepng($cover_img, $cover_img_url);
                            } else {
                                imagejpeg($cover_img, $cover_img_url, 100);
                            }
                        }

                        $this->processMedia('crop', $original_file, $original_file_name.'_thumb.'.$ext, '64', '64');

                        $get = array(
                            'id' => '1',
                            'active' => 1,
                            'extension' => $ext,
                            'name' => $name,
                            'url' => $original_file_name,
                            'cover_url' => $original_file_name . '_cover.' . $ext
                        );

                        return $get;
                    }
                }
            }
        }
    }

    public function createCover($cover_id=0, $pos=0)
    {
        $photo_dir = 'files/users/photo';
        $cover = $this->getUser($cover_id);
        $img = ROOT_PATH . '/' .$photo_dir . '/' . $cover_id . '.' . $cover['cover_extension'];
        $cover_img_url = $photo_dir . '/' . $cover_id . '_cover.' . $cover['cover_extension'];

        list($width, $height) = getimagesize($img);
        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        $dst_w = $width;
        $dst_h = $dst_w * (0.39);
        $src_w = $width;
        $src_h = $dst_h;
        if (!empty($pos) && is_numeric($pos) && $pos < $width) {
            $pos = $this->stringEscape($pos);
            $src_y = $width * $pos;
        }

        if ($cover['cover_extension'] == "gif") {
            if (!extension_loaded('imagick')){
                $cover_img = imagecreatetruecolor($dst_w, $dst_h);
                imagealphablending($cover_img, false);
                imagesavealpha($cover_img, true);
                $image = imagecreatefromgif($img);
                imagecopyresampled($cover_img, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
                imagegif($cover_img, ROOT_PATH . '/' . $cover_img_url);
            } else {
                $image = new \Imagick($img);
                $image = $image->coalesceImages();
                foreach ($image as $frame) {
                    $frame->cropImage($src_w, $src_h, $src_x, $src_y);
                    $frame->thumbnailImage($src_w, $src_h);
                    $frame->setImagePage($src_w, $src_h, 0, 0);
                }
                $image = $image->deconstructImages();
                $image->writeImages(ROOT_PATH . '/' . $cover_img_url, true);
            }
        } else {
            $cover_img = imagecreatetruecolor($dst_w, $dst_h);
            if ($cover['cover_extension'] == "png") {
                $image = imagecreatefrompng($img);
                imagealphablending($cover_img, false);
                imagesavealpha($cover_img, true);
            } else {
                $image = imagecreatefromjpeg($img);
            }

            imagecopyresampled($cover_img, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

            if ($cover['cover_extension'] == "png") {
                imagepng($cover_img, ROOT_PATH . '/' . $cover_img_url);
            } else {
                imagejpeg($cover_img, ROOT_PATH . '/' . $cover_img_url, 100);
            }
        }

        return $cover_img_url;
    }

    /* Like Validate Check */
    public function Like_Check($msg_id, $reactions, $map = 'forum')
    {
        if($this->user->isValid()) {
            // Check reaction type for user
            $q = $this->db->query("SELECT `id` FROM `forum_reactions` WHERE
                    `post_id`=" . $this->db->quote($msg_id) . " AND
                    `user_id`='" . $this->user->id . "' AND
                    `map`=" . $this->db->quote($map) . " AND
                    `reactions`=" . $this->db->quote($reactions) . "
                ")->fetchColumn();
            // Output the result
            if($q != 0) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /* Unlike */
    public function Unlike($msg_id, $map = 'forum')
    {
        if($this->user->isValid()) {
            // Forum
            $q = $this->db->query("SELECT `id` FROM `forum_reactions` WHERE `post_id`=" . $this->db->quote($msg_id) . " AND `user_id`='" . $this->user->id . "' AND `map`=" . $this->db->quote($map) . " ")->fetchColumn();
            if ($q > 0) {
                $this->db->exec("DELETE FROM `forum_reactions` WHERE `post_id`=" . $this->db->quote($msg_id) . " AND `user_id`='" . $this->user->id . "' AND `map`=" . $this->db->quote($map) . " ");
            }
            return $this->Like_CountTotal($msg_id, $map);
            /**
            $total = $this->db->query("SELECT COUNT(*) FROM `forum_reactions` WHERE `post_id`=" . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " ")->fetchColumn();
            if($total){
                $userLike = "";
                $req = $this->db->query("SELECT `user_id` FROM `forum_reactions` WHERE `post_id`=" . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " ORDER BY `time` DESC LIMIT 3");
                while ($res = $req->fetch()){
                    $infoUser = $this->db->query("SELECT `name` FROM `users` WHERE `id` = " . $res['user_id'] . " ")->fetch();
                    $userLike .= $infoUser['name'] . ($total == 1 ? '' : ($total == 2 ? ($i == 1 ? '' : ' và ') : ($total >= 3 ? ($i == 2 ? '' : ',') : ''))) . ' ';
                    $i++;
                }
                if($total <= 3) {
                    return $userLike . 'đã bày tỏ cảm xúc.';
                }else{
                    return $userLike . 'và ' . ($total - 3) . ' người khác';
                }
            }else{
                return false;
            }
            */
        }
        return false;
    }

    /* Like */
    public function Like($msg_id, $reactions, $map = 'forum')
    {
        if($this->user->isValid()) {
            // Select the message id from forum_reactions table
            $row = $this->db->query("SELECT `id` FROM `forum_reactions` WHERE `post_id`=" . $this->db->quote($msg_id) . " AND `user_id`='" . $this->user->id . "' AND `map`=" . $this->db->quote($map) . " ")->fetchColumn();
            // 1 row means there's a Like already, so Unlike() it.
            if($row >= 1) {
                $this->Unlike($msg_id, $map);
            }
            // then insert the like from message like table
            $this->db->prepare('
                INSERT INTO `forum_reactions` SET
                `post_id` = ?,
                `user_id` = ?,
                `map` = ?,
                `time` = ?,
                `reactions` = ?
            ')->execute([
                $msg_id,
                $this->user->id,
                $map,
                time(),
                $reactions,
            ]);

            return $this->Like_CountTotal($msg_id, $map);
            // Prepare the statement
            /**
            $alike = "";
            $totals = $this->db->query("SELECT COUNT(*) AS `reaction_count` FROM `forum_reactions` WHERE `post_id` = " . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " ")->fetch();
            $total = $totals['reaction_count'];
            $itotal = $total - 1;
            if($total){
                $userLike = "";
                $req = $this->db->query("SELECT `user_id` FROM `forum_reactions` WHERE `post_id`=" . $this->db->quote($msg_id) . " AND `user_id` != " . $this->user->id . " AND `map`=" . $this->db->quote($map) . " ORDER BY `time` DESC LIMIT 2");
                while ($res = $req->fetch()){
                    $infoUser = $this->db->query("SELECT `name` FROM `users` WHERE `id` = " . $res['user_id'] . " ")->fetch();
                    $userLike .= $infoUser['name'] . ($itotal == 1 ? '' : ($itotal == 2 ? ($i == 1 ? '' : ' và') : ($itotal >= 3 ? ($i == 1 ? '' : ',') : ''))) . ' ';
                    $i++;
                }
                if($total <= 3) {
                    return 'Bạn' . ($total == 1 ? ' ' : ($total == 2 ? ' và ' : ', ')) . $userLike . 'đã bày tỏ cảm xúc.';
                }else{
                    return 'Bạn, ' .$userLike . 'và ' . ($total - 3) . ' người khác';
                }
            }else{
                return false;
            }
            */
        }
        return false;
    }

    /* Like Count Test */
    public function Like_CountT($msg_id, $reactions, $map = 'forum')
    {
        $row = $this->db->query("SELECT COUNT(*) AS `reaction_count` FROM `forum_reactions` WHERE `post_id` = " . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " AND `reactions` = " . $this->db->quote($reactions) . " ")->fetch();
        if ($row) {
            return $row['reaction_count'];
        } else return 0;
    }

    /* Like Count Test */
    public function Like_CountTotal($msg_id, $map = 'forum')
    {
        $row = $this->db->query("SELECT COUNT(*) AS `reaction_count` FROM `forum_reactions` WHERE `post_id` = " . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " ")->fetch();
        $total = $row['reaction_count'];
        $itotal = $total;
        if($total){
            if ($map == 'status_comment' || $map == 'status_reply') {
                return $total;
            }
        	$userLike = "";
        	if($this->user->isValid()) {
            	$icheck = $this->db->query("SELECT `id` FROM `forum_reactions` WHERE `post_id`=" . $this->db->quote($msg_id) . " AND `user_id`='" . $this->user->id . "' AND `map`=" . $this->db->quote($map) . " ")->fetchColumn();
                if($icheck > 0){
                	$req = $this->db->query("SELECT `user_id` FROM `forum_reactions` WHERE `post_id` = " . $this->db->quote($msg_id) . " AND `user_id` != " . $this->user->id . " AND `map`=" . $this->db->quote($map) . " ORDER BY `time` DESC LIMIT 2");
                    $ss = 1;
                    $itotal = $total - 1;
                }else{
                	$req = $this->db->query("SELECT `user_id` FROM `forum_reactions` WHERE `post_id` = " . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " ORDER BY `time` DESC LIMIT 3");
                    $ss = 2;
                }
                while ($res = $req->fetch()){
                    $infoUser = $this->db->query("SELECT `name` FROM `users` WHERE `id` = " . $res['user_id'] . " ")->fetch();
                    $userLike .= $infoUser['name'] . ($itotal == 1 ? '' : ($itotal == 2 ? ($i == 1 ? '' : ' và') : ($itotal >= 3 ? ($ss == 1 ? ($i == 1 ? '' : ',') : ($i == 2 ? '' : ',')) : ''))) . ' ';
                    $i++;
                }
                if($total <= 3) {
                    return ($ss == 1 ? 'Bạn' . ($total == 1 ? ' ' : ($total == 2 ? ' và ' : ', ')) : '') . $userLike . 'đã bày tỏ cảm xúc.';
                }else{
                    return ($ss == 1 ? 'Bạn' . ($total == 1 ? ' ' : ', ') : '') . $userLike . 'và ' . ($total - 3) . ' người khác';
                }
            }else{
                $req = $this->db->query("SELECT `user_id` FROM `forum_reactions` WHERE `post_id` = " . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " ORDER BY `time` DESC LIMIT 3");
                while ($res = $req->fetch()){
                    $infoUser = $this->db->query("SELECT `name` FROM `users` WHERE `id` = " . $res['user_id'] . " ")->fetch();
                    $userLike .= $infoUser['name'] . ($total == 1 ? '' : ($total == 2 ? ($i == 1 ? '' : ' và ') : ($total >= 3 ? ($i == 2 ? '' : ',') : ''))) . ' ';
                    $i++;
                }
                if($total <= 3) {
                    return $userLike . 'đã bày tỏ cảm xúc.';
                }else{
                    return $userLike . 'và ' . ($total - 3) . ' người khác';
                }
            }
        } else return 0;
    }

    /* Reactions use rel */
    public function Reactions_URel($msg_id, $reactions, $map = 'forum', $json = false)
    {
        $total = $this->db->query("SELECT COUNT(*) FROM `forum_reactions` WHERE `post_id`=" . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " AND `reactions`=" . $this->db->quote($reactions) . "")->fetchColumn();

        if ($total) {
            $ra = null;
            $more = null;
            $no_use = null;
            $check = $this->Like_Check($msg_id, $reactions, $map);
            if($total > 5 && $this->user->isValid()) {
                $no_use = " AND `user_id` != " . $this->user->id . " ";
            }
            $req = $this->db->query("SELECT `user_id` FROM `forum_reactions` WHERE `post_id` = " . $this->db->quote($msg_id) . " AND `map`=" . $this->db->quote($map) . " AND `reactions` = " . $this->db->quote($reactions) . $no_use . "ORDER BY `time` DESC LIMIT 5");

            $list = array();
            $i    = 0;
            while ($res = $req->fetch()){
                $infoUser = $this->db->query("SELECT `name`, `sex` FROM `users` WHERE `id` = " . $res['user_id'] . " ")->fetch();
                $avatar_name = $this->avatar_name($res['user_id']);
                if (file_exists((ROOT_PATH . 'files/users/avatar/' . $avatar_name))) {
                    $avt = '/files/users/avatar/' . $avatar_name;
                } else {
                     $avt = '/images/empty' . ($infoUser['sex'] ? ($infoUser['sex'] == 'm' ? '_m.jpg' : '_w.jpg') : '.png');
                }
                if ($json) {
                    $list[$i] = array(
                        'id'     => $res['user_id'], 
                        'name'   => $infoUser['name'],
                        'avatar' => $avt
                    );
                } else
                    $ra .= ($this->user->isValid() ? '<a href="/profile/?user=' . $res['user_id'] . '">' : '') . '<span class="m-chip m-chip--contact m-chip--deletable"><img class="m-chip__contact" src="' . $avt . '" alt="' . $infoUser['name'] . '" /><span class="nickname">' . $infoUser['name'] . '</span></span>' . ($this->user->isValid() ? '</a>' : '') . ($total > 5 ? ($i == 5 ? '' : '<br />') : ($i == $total ? '' : '<br />'));

                $i++;
            }
            $more = '';
            if($total > 5) {
                if($check){
                    $more = ($total == 6 ? 'và Bạn.' : 'Bạn và '. ($total - 5) . ' người khác.');
                }else{
                    $more = 'và ' . ($total - 5) . ' người khác.';
                }
                if (!$json)
                    $more = '<br />' . ($total == 6 && $check ? '<span class="reaction_total-style">' . $more . '</span>' : '<a href="/forum/?"><span class="reaction_total-style">' . $more . '</span></a>');
            }
            if ($json) {
                $data = array(
                        'data' => $list, 
                        'rest' => $total - 5,
                        'info' => $more
                    );
                return $data;
            } else
                return $ra . $more;
        } else
            return '';
    }
}



