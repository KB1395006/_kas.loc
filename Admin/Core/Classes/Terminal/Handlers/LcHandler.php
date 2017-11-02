<?php

namespace Core\Classes\Terminal\Handlers;
/**
 * Класс осуществляет загрузку товаров поставщика, используя для этого
 * файл формата .csv
*/
class LcHandler
{
    const EXP_PATH              = 'exportPath';
    const REP_PATH              = 'reportPath';
    const LAST_KEY              = 'lastKey';
    const CONT                  = 'cont';
    const REP                   = 'report';
    const CALL                  = 'call';

    const SET                   = 1;
    const GET                   = 2;

    protected $csvReport        = '';
    protected $rExt             = '-report.csv';
    protected $path             = '';
    protected $exportPath       = '';
    protected $data;

    /**
     * Данные после обработки csv-документа
    */
    protected $export       = [];
    protected $ss           = [];
    protected $cmd          = [];
    protected $ob           = [];
    protected $columns      = [];
    protected $colRange     = [];

    /**
     * Максимальное время непрерывной работы класса, секунд.
    */
    protected $lim          = 5;

    /**
     * Параметры запуска:
     * -lc <file.csv> <A;B;C;D> <CustomerName>
     *
     * @param array $cmd
    */
    protected function __construct($cmd)
    {
        $this->cmd = $cmd;

        $this->setSs();
        $this->setPath();
        $this->setColRange();
    }

    protected function setPath()
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $this->path         = \Core\Classes\DownloadManager\dm::UPL_DIR . 'files/' . $this->cmd[1];
        $this->exportPath   = $this->path . '.json';

        clearstatcache($this->path);
        clearstatcache($this->exportPath);

        return true;
    }

    /**
     * Создать сессию для данного класса
    */
    protected function setSs()
    {
        !\kas::arr($_SESSION[__CLASS__]) ?
            $_SESSION[__CLASS__] = [] :
            false;

        $this->ss = &$_SESSION[__CLASS__];
    }

    protected function getData()
    {
        // Имя файла отсутствует
        if (!\kas::str($this->cmd[1]))
        {
            $this->ob[TERMINAL] = \kas::st(10, true);
            return false;
        }

        // Загрузка
        $this->data = \kas::load($this->path);

        // Не удалось загрузить
        if (!\kas::str($this->data))
        {
            $this->ob[TERMINAL] = \kas::data(\kas::st(11,true))
                ->r('%F%', $this->cmd[1])->asStr();

            return false;
        }

        return true;
    }

    protected function setColRange()
    {
        foreach (range('A', 'Z') as $k => $v) {
            $this->colRange[$v] = $k;
        }

        return true;
    }

    /**
     * Извлекает адреса колонок.
     * @return bool
    */
    protected function parseColumns()
    {
        if (!\kas::str($this->cmd[2]))
        {
            $this->ob[TERMINAL] = \kas::st(20, true);
            return false;
        }

        $this->columns = explode(';', $this->cmd[2]);

        if
        (
            !\kas::arr($this->columns)  ||
            count($this->columns) < 2
        )
        {
            $this->ob[TERMINAL] = \kas::st(21, true);
            return false;
        }

        foreach ($this->columns as $k => $col)
        {
            if (!is_int($this->colRange[$col]))
            {
                $this->ob[TERMINAL] = \kas::data(\kas::st(22, true))
                    ->r('%C%', $col)->asStr();

                return false;
                break;
            }

            continue;
        }

        return true;
    }

    // Возвращает код поставщика
    protected function getCode($code = '')
    {
        if
        (
            !\kas::str($code)                   ||
            !preg_match(VC_REG_EXP, $code)
        )
        {
            return false;
        }

        return trim($code);
    }

    protected function getPrc($prc = '')
    {
        if (!\kas::str($prc)) {
            return false;
        }

        $prc = \kas::data($prc)->trim()->r(',', '.')->r(' ', '')->asStr();
        return (float) $prc;
    }

    protected function getName($name = '')
    {
        if (!\kas::str($name)) {
            return false;
        }

        return trim($name);
    }

    /**
     * Наличие товара (возвращает true или false)
     *
     * Наличие определяется по метке товара, которого нет в наличии,
     * например -lc 4.csv A;B;C;D;E <в_резерве> VENDOR_NAME,
     * в_резерве - пробел заменяется нижним подчеркиванием.
     *
     * Если метка товара = 1, значит все товары есть в наличии
     * @param string $itemStatus
     * @return bool
     */
    protected function availability($itemStatus = '')
    {
        $itemStatus = trim($itemStatus);

        // Все товары в наличии
        if ($this->cmd[3] == 1) {
            return true;
        }



        $av = trim(str_replace('_', ' ', $this->cmd[3]));

        // Товара нет в наличии
        if
        (
            preg_match('/^0$/', $av)    ||
            $av == $itemStatus
        )
        {
            return false;
        }

        return true;
    }

    /**
     * Извлекает идентификатор программного
     * обеспечения или оборудования.
     * @param string $str
     * @return bool|string
    */
    protected function getHardwareId($str = '')
    {
        if (!\kas::str($str)) {
            return false;
        }

        $rExp1 = '/([\s]+(\[|\(|<)[^ А-Яа-я]{10,}(\]|\)|>)([\s]+)?|[\s]+(\[|\(|<)[^ А-Яа-я]{6,}(\]|\)|>)([\s]+)?|
        [\s]+(\[|\(|<)[^ А-Яа-я]{3,}(\]|\)|>)([\s]+)?)/i';
        $rExp2 = '/[\s]+[A-Z0-9\.\-\/]{6,}([\s]+)?/';

        preg_match($rExp1, $str, $m1);
        preg_match($rExp2, $str, $m2);

        \kas::str($m1[0]) ?
            $id = $m1[0] :
            $id = false;

        \kas::str($id) ?:
            $id = $m2[0];

        if (!preg_match('/[0-9]+/', $id)) {
            return false;
        }

        $id = str_replace(['(', ')', '[', ']', '<', '>'], '', trim($id));
        return \kas::str($id) ? $id : false;
    }

    // Сохраняет данные обработки в файл экспорта.
    protected function safeExportData()
    {

        // Сохраняем данные в файл .json
        if (!file_put_contents($this->exportPath, json_encode($this->export))){
            return false;
        }

        // Указать общее количество элементов.
        $this->ss[COUNT] ?: $this->ss[COUNT]  = count($this->export);
        // Сохраняем путь к файлу экспорта в сессию
        $this->ss[self::EXP_PATH]   = $this->exportPath;
        return true;
    }

    /**
     * @param int $type
     * Кодирует/декодирует $this->export в base64
     * @return bool
    */
    protected function base64Export($type = 0)
    {
        if
        (
            !$type                      ||
            !\kas::arr($this->export)
        )
        {
            return false;
        }

        foreach ($this->export as $k => $row)
        {
            $row = (array) $row;

            if (!\kas::arr($row)) {
                continue;
            }

            foreach ($row as $param => $str)
            {
                switch ($type)
                {
                    case self::SET:

                        \kas::str($str) ?
                            $this->export[$k][$param] = base64_encode($str) :
                            false;
                    break;

                    case self::GET:

                            is_object($this->export[$k]) ?
                                $this->export[$k]->{$param} = base64_decode($str) :
                                false;

                            \kas::arr($this->export[$k]) ?
                                $this->export[$k][$param] = base64_decode($str) :
                                false;
                            false;

                    break;
                }
            }
        }

        return true;
    }

    protected function parseData()
    {
        $this->data = \kas::data($this->data)->parseCSV()->asArr();

        foreach ($this->data as $k => $row)
        {
            if (!\kas::arr($row)) {
                unset($this->data[$k]);
            }

            $code = $this->getCode($row[$this->colRange[$this->columns[0]]]);
            $name = $this->getName($row[$this->colRange[$this->columns[1]]]);

            if
            (
                !$code  ||
                !$name
            )
            {
                continue;
            }

            $desc = $this->getName($row[$this->colRange[$this->columns[2]]]);
            $prc  = $this->getPrc($row[$this->colRange[$this->columns[3]]]);
            $av   = $this->availability($row[$this->colRange[$this->columns[4]]]);
            $hId  = $this->getHardwareId($name);

            $this->export[] =
                [
                    CODE    => $code,
                    NAME    => $name,
                    DESC_S  => $desc,
                    PRC     => $prc,
                    STATUS  => $av,
                    MODEL   => $hId
                ];
        }

        if (!\kas::arr($this->export)) {
            \kas::ext('Hardware ID export error');
            return false;
        }

        $this->base64Export(self::SET);

        // var_dump($this->export);
        // exit();

        return $this->safeExportData() ? true : false;
    }

    // Устанавливает код поставщика для
    // текущего файла экспорта.
    protected function setVendorCode()
    {
        if (!\kas::str($this->cmd[4])) {
            $this->ob[TERMINAL] = \kas::st(23,true);
            return false;
        }
        
        if
        (
            !\kas::str($this->cmd[4])                       ||
            !preg_match('/^[a-z0-9_-]+$/i', $this->cmd[4])
        )
        {
            $this->ob[TERMINAL] = \kas::data(\kas::st(14,true))
                ->r('%V%', $this->cmd[4])->asStr();
            return false;
        }

        // Сохраняем код поставщика.
        $this->ss[VC] = $this->cmd[4];
        return true;
    }

    protected function export()
    {
        \kas::arr($this->ss[self::REP]) ?:
            $this->ss[self::REP] = [0,0,0];

        // Загрузить файл экспорта
        $data = \kas::load($this->ss[self::EXP_PATH]);

        if (!\kas::str($data)) {
            return false;
        }

        $this->export = (array) json_decode($data);

        if
        (
            !\kas::arr($this->export)       ||
            !$this->base64Export(self::GET)
        )
        {
            return false;
        }

        $current = time();

        foreach ($this->export as $k => $itm)
        {
            !$this->ss[self::LAST_KEY] ?
                $this->ss[self::LAST_KEY] = $k : false;


            if ($this->ss[self::LAST_KEY] > $k) {
                continue;
            }

            // Если было превышено время работы.
            if (time() - $current > $this->lim)
            {
                $this->ss[self::CALL] ?
                    $this->ss[self::CALL]++ :
                    $this->ss[self::CALL] = 1;

                // Сохранить место разъединения.
                $this->ss[self::LAST_KEY] = $k;

                // Подсчитать общий % выполнения
                $prc = count($this->export) / $this->ss[self::LAST_KEY];
                $prc = round(100 / $prc);

                // Отправить в терминал
                $this->ob[TERMINAL] = \kas::st(24, true) . ' ' . $prc . '% '
                    . $this->ss[self::LAST_KEY];

                $this->ob[self::CONT] = 1;
                return true;
            }

            $itm        = (array) $itm;
            $itmExists  = $this->sqlCheckItm($itm);

            // Товар уже добавлен
            if
            (
                is_int($itmExists)  ||
                $itmExists
            )
            {

                 $this->ss[self::REP][0]++;
                 continue;
            }

            // Поиск товара по фрагменту.
            $r = $this->sqlSearchItmByFrag($itm[MODEL]);

            // Добавить товар в раздел (нет синхронизации)
            if (!\kas::arr($r))
            {
                $this->ss[self::REP][1]++;
                $this->sqlInsItm($itm);
                continue;
            }

            // Товар найден!
            $this->ss[self::REP][2]++;
            $this->sqlUpdateItm($itm, $r);
            continue;
        }

        // Сформировать отчет.
        $this->report();

        // Освободить ресурсы.
        $this->clear();
        return true;
    }

    protected function clear()
    {
        unset($_SESSION[__CLASS__]);
        return true;
    }

    /**
     * @param array $itm
     * @param array $dbItm
     *
     * @param bool $noCheck
     * Если, необходимо провести обновление без проверки.
     *
     * @return bool|mixed
    */
    protected function sqlUpdateItm($itm = [], $dbItm = [], $noCheck = false)
    {
        if
        (
            !\kas::arr($itm)    ||
            !\kas::arr($dbItm)
        )
        {
            return false;
        }

        $sqlUpd = \kas::sql()->simple()->upd(OFFERS, [CODE, VC, PRC, STATUS], [0, 0, 0, 0], [ID]);


        // Обновить, если нет поставщика либо цена не
        // определена или больше текущей
        if
        (
            !\kas::str($dbItm[0][VC])   ||
            $dbItm[0][PRC] == 0         ||
            $dbItm[0][PRC] > $itm[PRC]  ||
            $noCheck
        )
        {
            $s = \kas::sql()->exec($sqlUpd, [$itm[CODE], $this->ss[VC],
                $itm[PRC], (int) $itm[STATUS] ? 1 : -1, $dbItm[0][ID]]);

            // Сформировать отчет.
            $msg = implode(';', ['Обновлена цена, статус, наличие', $itm[CODE], $this->ss[VC],
                $itm[PRC], (int) $itm[STATUS] ? 1 : -1, $dbItm[0][ID]]);

            $this->csvReport($msg  . "\r\n");
            return $s;
        }

        return false;
    }

    // Проверить существование товара в БД
    protected function sqlCheckItm($itm)
    {
        $sqlCheck  = \kas::sql()->simple()->sel(OFFERS, [ID, NAME, CODE, VC, PRC], [0, 0, 0, 0, 0]);
        $sqlCheck .= VC . ' = ? AND ' . CODE . ' = ? ';

        // Отсутствует код товара
        if
        (
            !\kas::arr($itm)        ||
            !\kas::str($itm[CODE])
        )
        {
            // Сформировать отчет.
            $msg = implode(';', ['Код товара некорректен', $itm[NAME]]);
            $this->csvReport($msg  . "\r\n");

            return 0;
        }

        $r = \kas::sql()->exec($sqlCheck, [$this->ss[VC], $itm[CODE]]);

        /**
         * Совпадений не найдено
        */
        if (!\kas::arr($r)) {
            return false;
        }

        /**
         * Если данный товар существует,
         * то обновляем его цену и наличие.
        */
        $this->sqlUpdateItm($itm, $r, true);
        return \kas::arr($r) ? true : false;
    }

    // Поиск товара по фрагменту
    protected function sqlSearchItmByFrag($frag = '')
    {
        $sqlItm    = \kas::sql()->simple()->sel(OFFERS, [ID, NAME, CODE, VC, PRC, DESC_L], [0]);
        $sqlItm   .= NAME . ' LIKE ? AND ' . DESC_L . ' != ? ';

        if (!\kas::str($frag)) {
            return false;
        }

        $r = \kas::sql()->exec($sqlItm, ["%{$frag}%", '']);
        return \kas::arr($r) ? $r : false;
    }

    protected function sqlInsItm($itm = [])
    {
        if (!\kas::arr($itm)) {
            return false;
        }

        // Добавление товара
        $sqlIns    = \kas::sql()->simple()->ins(OFFERS, [CODE, VC, NAME, TITLE,
            DESC_S, PRC, CID, STATUS, C_NAME]);

        $params    = [
            $itm[CODE],
            $this->ss[VC],
            $itm[NAME],
            $itm[NAME],
            $itm[DESC_S],
            $itm[PRC],
            0,
            (int) $itm[STATUS] ? 1 : -1,
            \kas::st(3)
        ];

        // Сформировать отчет.
        $msg = implode(';', ['Добавлено в раздел "Нет синхронизации"', $itm[CODE],
        $itm[NAME], $itm[PRC]]);

        $this->csvReport($msg  . "\r\n");

        $s = \kas::sql()->exec($sqlIns, $params);
        return $s;
    }

    // 0 - уже добавлены
    // 1 - нет синхронизации
    // 2 - Определены в разделы
    protected function report()
    {
        $this->ob[TERMINAL] .= '> 100%' . "\r\n" . \kas::st(16, true) . "\r\n\r\n";
        $this->ob[TERMINAL] .= \kas::st(27, true) . ' ' . $this->ss[self::REP][0] . "\r\n";
        $this->ob[TERMINAL] .= \kas::st(25, true) . ' ' . $this->ss[self::REP][1] . "\r\n";
        $this->ob[TERMINAL] .= \kas::st(26, true) . ' ' . $this->ss[self::REP][2] . "\r\n";
    }

    protected function setReportPath()
    {
        if
        (
            !\kas::str($this->path)     ||
            !file_exists($this->path)
        )
        {
            return false;
        }

        if
        (
            file_exists($this->path)                &&
            file_exists($this->path . $this->rExt)
        )
        {
            // Удалить старый файл отчета...
            unlink($this->path . $this->rExt);
        }

        // Создать путь к файлу отчета.
        $this->ss[self::REP_PATH] = $this->path . $this->rExt;
        return true;
    }

    // Записывает данные в файл отчета.
    protected function csvReport($msg = '')
    {
        if
        (
            !$this->ss[self::REP_PATH]  ||
            !\kas::str($msg)
        )
        {
            return false;
        }

        $r = @file_put_contents
        (
            $this->ss[self::REP_PATH],
            mb_convert_encoding($msg, 'windows-1251', ENCODING),
            FILE_APPEND|LOCK_EX
        );

        return $r;
    }

    protected function conf()
    {

        // Продолжение работы с файлом экспорта.
        if ($this->ss[self::EXP_PATH])
        {
            $this->export();
            return $this->ob;
        }

        // Первый запуск
        if
        (
            !\kas::arr($this->cmd)      ||
            !$this->getData()           ||
            !$this->parseColumns()      ||
            !$this->setVendorCode()     ||
            !$this->parseData()         ||
            !$this->setReportPath()
        )
        {
            return $this->ob;
        }

        $this->export();
        return $this->ob;
    }

    static public function run($cmd)
    {
        $ob = new static($cmd);
        return $ob->conf();
    }
}