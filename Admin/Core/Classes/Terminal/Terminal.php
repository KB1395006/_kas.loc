<?php

namespace Core\Classes\Terminal;
use \Core\Classes\App;
use \Core\Classes\DownloadManager;
use \Core\Classes\Terminal\Handlers;
use \Core\Classes\Spider;

class Terminal
{

    const H             = 'handler';
    const K             = 'keys';
    const PATH          = 'path';
    const HTML          = 'html';
    const CONT          = 'cont';
    const SQL_PARAMS    = 'sqlParams';
    const CB            = 'codeBase';
    const CNT           = 'count';

    protected $post     = [];
    protected $ob       = [];
    protected $ss       = [];

    protected $term     =
    [
        '-dm'       => [self::H => 'dmHandler'],
        '-up'       => [self::H => 'upHandler'],
        '-lc'       => [self::H => 'lcHandler'],
        '-cu'       => [self::H => 'cuHandler'],
        '-inst'     => [self::H => 'instHandler'],
        '-smp'      => [self::H => 'sitemapHandler'],
        '-cat'      => [self::H => 'catalogHandler'],
        '-rt'       => [self::H => 'routeHandler'],

    ];

    protected $cmd;

    protected function __construct()
    {
        $this->post = \kas::data()->_post()->asArr();
        $this->setSs();
    }

    protected function out($strData = '', $continue = false, $timeout = 0)
    {
        $strData ?: $strData = \kas::st(1, true);

        $this->ob[TERMINAL] = (string) $strData;
        $continue ? $this->ob[self::CONT] = 1 : false;
        (int) $timeout ? sleep($timeout) : false;

        return true;
    }

    protected function setSs()
    {
        !\kas::arr($_SESSION[__CLASS__]) ?
            $_SESSION[__CLASS__] = [] :
            false;

        $this->ss = &$_SESSION[__CLASS__];
    }

    protected function catalogHandler() {
        return $this->out(Handlers\CatalogHandler::run($this->cmd));
    }

    protected function routeHandler() {
        return $this->out(Handlers\RouteHandler::run($this->cmd));
    }

    protected function sitemapHandler ()
    {
        $resp = Spider\Spider::run($this->cmd);
        $this->out($resp[0], $resp[1]);
        return true;
    }

    protected function cuHandler()
    {
        $cu  = array_flip([USD, EUR, RUR]);
        $sql = \kas::sql()->simple()->upd(OFFERS, [CUR_V], [0]) . ID . ' > ?';

        $this->cmd[2] ? $cv = (float) $this->cmd[2] :
            $cv = false;
        
        if 
        (
            !\kas::str($this->cmd[1])       ||
            !is_int($cu[$this->cmd[1]])
        )
        {
            $this->ob[TERMINAL] = \kas::st(30, true);
            return false;
        }

        if (!$cv) {
            $this->ob[TERMINAL] = \kas::st(31, true);
            return false;
        }

        $res = \kas::sql()->exec($sql, [$cv, 0]);

        $res ? $this->ob[TERMINAL] = \kas::st(32, true) :
            $this->ob[TERMINAL] = \kas::st(1, true);
        
        return $res;
    }

    protected function upHandler()
    {
        /************************* Инициализации *************************/

        /**
         * Создать сессию для данного метода.
        */
        \kas::arr($this->ss[__METHOD__]) ?:
            $this->ss[__METHOD__] = [];
        /**
         * Создать ссылку.
        */
        $m = &$this->ss[__METHOD__];

        /**
         * Параметры команды update
        */
        \kas::arr($m[self::SQL_PARAMS]) ?:
            $m[self::SQL_PARAMS] = false;

        /**
         * Коды товаров по прайсу
        */
        \kas::arr($m[self::CB]) ?:
            $m[self::CB] = false;

        // Кэшировать время выполнения,
        // если запуск осуществляеться впервые.
        !\kas::arr($m[self::SQL_PARAMS]) ?
            $m[DATE] = date('h:i:s') : false;


        $report = function($method)
        {
            // Создать ссылку.
            $m = &$this->ss[$method];

            // Отчет формируется, когда все SQL
            // команды были выполнены.
            if (\kas::arr($m[self::SQL_PARAMS])) {
                return false;
            }

            // Отчет о выполнении.
            $sqlRep = \kas::sql()->simple()->sel(OFFERS, [CODE], [0], [TIME]);

            // Сформировать отчет:
            $rprt = \kas::sql()->exec($sqlRep, [$m[DATE]]);

            // Данные, которые были обновлены убираем.
            foreach ($rprt as $v) {
                unset($m[self::CB][$v[CODE]]);
                continue;
            }

            // Все данные были обновлены.
            if (!\kas::arr($m[self::CB])) {
                $this->ob[TERMINAL] = \kas::st(12, true);
                return true;
            }

            // Сформировать отчет о позициях, которые не были обновлены.
            $msg                 = \kas::st(13, true);
            $this->ob[TERMINAL] .= "\r\n" . \kas::st(16, true) . "\r\n\r\n";

            // Добавить позиции в отчет
            foreach ($m[self::CB] as $code => $v) {
                $this->ob[TERMINAL] .= $code . ' - ' . $msg . "\r\n";
            }

            return true;
        };

        $exec = function($method)
        {
            $sql       = \kas::sql()->simple()->upd(OFFERS,
                [PRC, TIME], [0,0], [CODE, VC]);

            // Определить начальное время запуска.
            $current   = time();
            // Максимальное время работы
            $lim       = 5;

            // Создать ссылку.
            $m = &$this->ss[$method];

            if (!\kas::arr($m[self::SQL_PARAMS]))
            {
                $this->ob[TERMINAL]   = \kas::st(17, true);
                $this->ob[self::CONT] = 1;
                return false;
            }

            foreach ($m[self::SQL_PARAMS] as $k => $params)
            {
                if (time() - $current > $lim)
                {
                    $prc = count($m[self::CB]) / count($m[self::SQL_PARAMS]);
                    $prc = 100 - round(100 / $prc);

                    $this->ob[TERMINAL]     = \kas::st(18, true) . ' ' . $prc . '% '
                        . count($m[self::SQL_PARAMS]);

                    $this->ob[self::CONT]   = 1;
                    return true;
                }

                // var_dump($sql, $params);

                // Выполнить запрос.
                \kas::sql()->exec($sql, $params);
                // Убрать выполненный запрос.
                unset($m[self::SQL_PARAMS][$k]);
            }

            $this->ob[TERMINAL] = '> 100%' . "\r\n";

            // Свидетельствует о завершении.
            return 1;
        };

        /************************* Запуск *************************/

        switch(is_array($m[self::SQL_PARAMS]))
        {
            // Первый запуск
            case false:

                $path = DownloadManager\dm::UPL_DIR . 'files/';
                $cmd  = implode(' ', $this->cmd);

                $vSQL = \kas::sql()->simple()->sel(OFFERS, [ID], [0], [VC])
                    . ' LIMIT 1';

                // Имя файла отсутствует
                if (!\kas::str($this->cmd[1]))
                {
                    $this->ob[TERMINAL] = \kas::st(10, true) . ' ' . $cmd;
                    unset($this->ss[__METHOD__]);
                    return false;
                }

                // Укажите код поставщика
                if (!\kas::str($this->cmd[2]))
                {
                    $this->ob[TERMINAL] = \kas::st(15, true);
                    unset($this->ss[__METHOD__]);
                    return false;
                }

                // Неверно указан поставщик
                if
                (
                    !\kas::str($this->cmd[2])                       ||
                    !preg_match('/^[a-z0-9_-]+$/i', $this->cmd[2])
                )
                {
                    $this->ob[TERMINAL] = \kas::data(\kas::st(14,true))
                        ->r('%V%', $this->cmd[2])->asStr();
                    
                    unset($this->ss[__METHOD__]);
                    return false;
                }

                // Проверить наличие поставщика в БД
                if (!\kas::arr(\kas::sql()->exec($vSQL, [$this->cmd[2]])))
                {
                    $this->ob[TERMINAL] = \kas::data(\kas::st(19,true))
                        ->r('%V%', $this->cmd[2])->asStr();

                    unset($this->ss[__METHOD__]);
                    return false;
                }


                // Загрузка
                $data = \kas::load($path . $this->cmd[1]);

                if (!\kas::str($data))
                {
                    $this->ob[TERMINAL] = \kas::data(\kas::st(11,true))
                        ->r('%F%', $this->cmd[1])->asStr();

                    unset($this->ss[__METHOD__]);
                    return false;
                }

                $data       = \kas::data($data)->explode("\r\n")->asArr();
                $cBase      = [];

                foreach ($data[0] as $k => $row)
                {
                    if (!\kas::str($row)) {
                        continue;
                    }

                    $row = \kas::data($row)->explode(';')->asArr();

                    if (!preg_match(VC_REG_EXP, $row[0][0])) {
                        continue;
                    }

                    $code           = $row[0][0];
                    $cBase[$code]   = 1;

                    /**
                     * Добавляем в кеш.
                    */
                    $m[self::SQL_PARAMS][] = [(float) $row[0][1], $m[DATE],
                        $code, $this->cmd[2]];

                    $m[self::CB][$code]    = 1;
                }

            break;
        }

        $a = $exec(__METHOD__);
        $report(__METHOD__);

        // Удалить текущую сессию.
        if (is_int($a)) {
            unset($this->ss[__METHOD__]);
        }

        return true;
    }

    // Менеджер загрузки поставщика
    protected function lcHandler()
    {

        $data = Handlers\LcHandler::run($this->cmd);

        $data ? $this->ob = $data :
            $this->ob[TERMINAL] = \kas::st(1, true);

        return true;
    }

    // Менеджер загрузки файлов.
    protected function dmHandler()
    {
        $_SESSION[UPL_DIR] = $this->cmd[1] ?: false;
        $data = DownloadManager\DownloadManager::html();

        $data ? $this->ob[self::HTML] = $data :
            $this->ob[TERMINAL] = \kas::st(1, true);

        return true;
    }

    // Менеджер установки шаблонов
    protected function instHandler() {
        return $this->out(App\AppInstaller::run());
    }

    protected function execCmd()
    {
        // Команда не найдена.
        if
        (
            !\kas::arr($this->term[$this->cmd[0]])                      ||
            !method_exists($this, $this->term[$this->cmd[0]][self::H])
        )
        {
            $this->ob[TERMINAL] = \kas::st(9, true);
            return false;
        }

        // Передать управление
        return $this->{$this->term[$this->cmd[0]][self::H]}();
    }

    protected function parseCmd()
    {
        if (!\kas::str($this->post[CMD])) {
            return false;
        }

        // Выбрать последнюю команду.
        $_tmp = \kas::data($this->post[CMD])->explode("\n")->last();

        // Разделить на аргументы.
        $_tmp = explode(' ', $_tmp);

        foreach ($_tmp as $frag)
        {
            if (!\kas::str($frag)) {
                continue;
            }

            $this->cmd[] = $frag;
        }

        return true;
    }

    protected function conf()
    {
        $this->parseCmd();
        $this->execCmd();

        return json_encode($this->ob);
    }

    static public function run()
    {
        $ob = new static();
        return $ob->conf();
    }

    static public function getResponse($msgId = 1, $continue = false) {
        return [\kas::st((int) ($msgId) ?: 1, true), (bool) ($continue)];
    }
    
    static public function getResponseError($line, $continue = false) {
        return [\kas::st(37, true) . ' ' . (int) ($line), (bool) ($continue)];
    }
}