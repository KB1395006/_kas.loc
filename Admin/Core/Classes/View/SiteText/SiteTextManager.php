<?php
/**
 * Менеджер текстовых фрагментов.
 * Данный менеджер возвращает текстовый фрагмент по его
 * идентификатору.
*/
namespace Core\Classes\View\SiteText;


class SiteTextManager
{
    const DELIM             = KAS_LINE_DELIM;
    const CONF_PATH         = KAS_CONFIG_PATH;
    const FILE              = KAS_SITE_TEXT_FILE;
    const EXT_FILE          = KAS_EXT_TEXT_FILE;
    const GET_DATA_BY_ID    = 1;
    const GET_DATA_BY_TPL   = 2;

    /**
     * Название файла загрузки.
     * sitetext.txt|extensions.txt
    */
    protected $FILE;
    /**
     * Формат фрагментов в шаблонах.
     * Пример: %ST25%
    */
    protected $fragTpl = '/%ST([0-9]+)%/';
    /**
     * Формат фрагментов исключений в шаблонах.
     * Пример: %EXT25%
    */
    protected $extTpl  = '/%EXT([0-9]+)%/';
    /**
     * Регулярные выражения и разделители обработки фрагментов.
    */
    protected $fragArr = ['#', '/^[\d]+\^/', '/^([\d]+)\^/'];
    /**
     * Массив обработанных фрагментов.
    */
    protected $fragDataArr = [];
    /**
     * Путь к файлу фрагментов.
    */
    protected $path;
    /**
     * @var string
     * Содержимое файла фрагментов.
    */
    protected $data;
    /**
     * Идентификатор фрагмента.
    */
    protected $id;
    /**
     * Шаблон или идентификатор элемента.
    */
    protected $mixed;
    /**
     * Тип обработки.
    */
    protected $type;
    /**
     * Обработка исключений.
     * Если параметр имеет значение true, то
     * в качестве целевого файла подгрузки будет использован
     * файл исключений.
    */
    protected $isExtension = false;
    /**
     * True, если необходима обработка текстовых
     * фрагментов приложения.
    */
    protected $isApp       = false;

    /**
     * SiteTextManager constructor.
     * @param mixed $mixed
     *
     * Обработка исключений отключена по умолчанию.
     *
     * @param bool $isExtension
     * @param bool $isApp
     */
    protected function __construct($mixed = false, $isExtension = false, $isApp = false)
    {

        $isExtension ?
            $this->isExtension = true : false;

        $this->isApp = (bool) ($isApp);

        switch ($this->isExtension)
        {
            case true:
                $this->FILE     = self::EXT_FILE;
                $this->fragTpl  = $this->extTpl;
            break;

            case false:
                $this->FILE = self::FILE;
            break;
        }

        /**
         * Поиск фрагментов в шаблонах поддерживает работу с массивами.
        */
        switch (\kas::arr($mixed))
        {
            /**
             * Группа шаблонов.
            */
            case true:

                $this->tpl  = $mixed;
                $this->type = self::GET_DATA_BY_TPL;
                return true;

            break;

            /**
             * Строка или идентификатор.
            */
            case false:

                if (is_integer($mixed))
                {
                    $this->id = $mixed;
                    $this->type = self::GET_DATA_BY_ID;
                    return true;
                }

                if (\kas::str($mixed)) {
                    $this->tpl = [$mixed];
                    $this->type = self::GET_DATA_BY_TPL;
                    return true;
                }

            break;
        }

        return false;
    }

    /**
     * Устанавливает путь к файлу текстовых
     * фрагментов.
     * @return bool
    */
    protected function setPath()
    {
        $this->isApp   ? $e = KAS_APP : $e = KAS_CMS;
        \kas::isProj() ? $e = KAS_APP : false;

        /**
         *  Если файл отсутствует, делаем запись в лог ошибок.
        */
        $_tmp = \ENV::_()->M_PATH . self::CONF_PATH . $e
            . '/' . $this->FILE;

        if
        (
            !file_exists($_tmp) ||
            !is_readable($_tmp)
        )
        {
            \kas::ext('File is missing or not readable.');
            return false;
        }

        $this->path = $_tmp;
        return true;
    }

    /**
     * Метод получает содержимое файла фрагментвов.
    */
    protected function getFileData()
    {
        $_tmp = \kas::load($this->path);

        if (!$_tmp) {
            \kas::ext('Can\'t get file data.');
            return false;
        }
        
        $this->data = $_tmp;
        return true;
    }

    /**
     * Метод выполняет разделение фрагментов по
     * идентификаторам.
     *
     * По завершению работы будет создан разряженный массив fragDataArr,
     * который разрешает наличие тегов в текстах фрагментов.
    */
    protected function parseData()
    {
        /**
         * Пофрагментное разделение содержимого.
        */
        $_tmp = @explode($this->fragArr[0], $this->data);

        /**
         * Убрать нулевой идентификатор.
        */
        unset($_tmp[0]);

        /**
         * Выполнить обработку фрагментов.
        */
        foreach ($_tmp as $fr)
        {
            $fr = trim($fr);

            if
            (
                !$fr                                    ||
                !preg_match($this->fragArr[1], $fr)
            )
            {
                continue;
            }

            /**
             * Получить ключ и разделитель фрагмента.
            */
            @preg_match($this->fragArr[2], $fr, $frKeys);

            /**
             * Если ключи отсутствуют или < 2
             * (разделитель фрагмента и идентификатор фрагмента) пропускаем.
            */
            if
            (
                !\kas::arr($frKeys)         ||
                count($frKeys) < 2          ||
                (int) $frKeys[1] == 0
            )
            {
                continue;
            }

            $fragData = @explode($frKeys[0], $fr);

            /**
             * Проверить фрагмент после раздеелния.
            */
            if
            (
                !\kas::arr($fragData)   ||
                count($fragData) < 2
            )
            {
                continue;
            }

            /**
             * Допускается наличие тегов.
            */
            $this->fragDataArr[$frKeys[1]] = htmlentities($fragData[1]);
        }

        if (!\kas::arr($this->fragDataArr)) {
            return false;
        }

        return true;
    }

    /**
     * Метод возвращает фрагмент данных по идентификатору.
     * @param array $idArr
     * @return bool
    */
    protected function getDataById($idArr = [])
    {
        if (!\kas::arr($idArr)) {
            return $this->fragDataArr[$this->id] ?: false;
        }

        $data = [];

        /**
         * Получить значения по массиву идентификаторов.
        */
        foreach ($idArr as $id)
        {
            if ((int) $id == 0) {
                return [];
            }

            $data[] = $this->fragDataArr[(int) $id];
        }

        return $data;
    }

    /**
     * Поиск фрагментов в шаблонах.
     * Метод возвращает html-фрагмент, если был передан
     * один шаблон и массив html-фрагментов, если было
     * передано несколько шаблонов.
    */
    protected function getDataByTpl()
    {

        if (!\kas::arr($this->tpl)) {
            \kas::ext('Param must be an array.');
            return false;
        }

        /**
         * Выполнить обход шаблонов.
        */
        foreach ($this->tpl as $k => $tpl)
        {
            if (!\kas::str($tpl)) {
                continue;
            }

            @preg_match_all($this->fragTpl, $tpl, $matches);

            if
            (
                !\kas::arr($matches)    ||
                (int) $matches[1][0] == 0
            )
            {
                continue;
            }

            $repl = $this->getDataById($matches[1]);
            $ptrn = $matches[0];

            /**
             * Выполнить подмену.
            */
            $this->tpl[$k] = str_replace($ptrn, $repl, $tpl);
        }

        return count($this->tpl) == 1 ?
            $this->tpl[0] : $this->tpl;
    }


    protected function config()
    {

        if
        (
            !$this->setPath()       ||
            !$this->getFileData()   ||
            !$this->parseData()
        )
        {
            return false;
        }

        switch ($this->type)
        {
            case self::GET_DATA_BY_ID:
                return $this->getDataById();
                break;

            case self::GET_DATA_BY_TPL:
                return $this->getDataByTpl();
                break;
        }

        return false;
    }

    /**
     * @param mixed $mixed
     * @param bool $isExtension
     * @return
     */
    static public function run($mixed = false, $isExtension = false)
    {
        $ob = new static ($mixed, $isExtension);
        return $ob->config();
    }

    /**
     * @param bool $isApp
     * @return mixed
    */
    static public function getAll($isApp = false)
    {
        $ob = new static(0,0, $isApp);
        $ob->config();

        /**Добавить теги*/
        $data = [];

        foreach ($ob->fragDataArr as $k => $frag) {
            $data[$k] = $frag;
        }

        return $data;
    }

    static public function getPath($isApp = false)
    {
        $ob = new static(0,0, $isApp);
        $ob->setPath();
        return $ob->path;
    }

    /**
     * Возвращает тексты веб интерфейса в html-разметке.
     * @param int $tplId
     * @param bool $isApp
     * @return bool|string
    */
    static public function html($tplId = 0, $isApp = false)
    {
        $_data = self::getAll($isApp);
        $data  = [];

        if (!\kas::arr($_data)) {
            return false;
        }

        foreach ($_data as $k => $v) {
            $data[] = [ID => $k, NAME => str_replace(['<', '>'], ['[[', ']]'], html_entity_decode($v))];
        }

        return \kas::tpl($data, $tplId)->asStr();
    }

    /**
     * @param bool $act
     * @param array $data
     * @return bool
    */
    static public function mod($act = false, $data = [])
    {

        if
        (
            !\kas::arr($data) ||
            $data['TABLE'] !== KAS_SITE_TEXT_FILE
        )
        {
            return false;
        }

        switch ($act)
        {
            case 1:

                $path   = self::getPath(true);
                $stData = '';
                $_data  = self::getAll(true);

                if
                (
                    !\kas::str($data[ID])           ||
                    !\kas::str($_data[$data[ID]])
                )
                {
                    return false;
                }

                $_data[$data[ID]] = $data[DATA];

                if (!\kas::str($path)) {
                    return false;
                }

                foreach ($_data as $k => $v)
                {
                    \kas::str($v) ?
                        $v = str_replace(['[[', ']]'], ['<', '>'], html_entity_decode($v)) : false;
                    
                    $stData .= "#{$k}^{$v}" . "\r\n";
                }

                $r = @file_put_contents($path, $stData);
                $r ?: \kas::ext('Writing error.');

                return $r ? true : false;

            break;

            default:
                return false;
            break;
        }
    }
} 