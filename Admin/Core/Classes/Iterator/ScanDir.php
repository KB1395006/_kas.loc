<?php
/**
 * Данный класс осуществляет сканирование дирректорий
 * по заданному расширению.
*/
namespace Core\Classes\Iterator;

class ScanDir
{
    /**
     * Модификатор - только директории.
    */
    const DIR  = 1;
    /**
     * Модификатор - только файлы
    */
    const FILE = 2;
    /**
     * Модификатор - только изображения.
    */
    const IMG  = 3;
    /**
     * Модификатор - текстовые файлы и
     * документы.
    */
    const DOC  = 4;
    /**
     * Модификатор - все типы файлов
     * и директории.
    */
    const ALL  = 5;
    /**
     * Файлы шаблонов.
    */
    const TPL  = 6;
    /**
     * Файлы php.
    */
    const EXT  = 7;

    /**
     * Модификатор - указывается в качестве
     * параметра аргумента $return. Данный модификатор
     * указывает на тип данных в котором необходимо возвратить
     * результат.
     *
     * AS_ARR - вернуть данные как массив.
    */
    const RETURN_AS_ARR = 0;
    /**
     * AS_OBJ - вернуть данные как объект.
     * Объект предоставляет более подробный
     * перечень информации нежели массив.
    */
    const RETURN_AS_OBJ = 1;

    /**
     * Путь для сканирования.
    */
    protected $scanPath = '';

    /**
     *
     * @param $return
     * Возвращаемый тип данных: RETURN_AS_ARR|RETURN_AS_OBJ.
     *
    */

    /**
     * @param int $type
     * Фильтрация по маркерам : ALL|DIR|FILE|IMG|DOC.
    */
    protected $type = 0;

    /**
     * @var \DirectoryIterator
    */
    protected $ITR;

    /**
     * Содержимое сканируемой директории.
     * Ассоциативный массив состоящий из имени файла и
     * относительного пути к содержимому.
    */
    protected $scanDir = [];

    /**
     * Библиотека расширений указывающих на
     * различные типы файлов.
    */
    protected $typeFilter = array
    (
        self::IMG => ['jpg', 'jpeg', 'gif', 'png'],
        self::DOC => ['doc', 'docx', 'xls', 'xlsx', 'pdf'],
        self::TPL => ['tpl', 'htm', 'html'],
        self::EXT => ['php']
    );

    protected function __construct($path = '', $type = 0, $return = false)
    {
        \kas::str($path) && is_dir($path) ?
            $this->ITR = new \DirectoryIterator($path) :
            $this->ITR = false;

        $this->type = $type;

        !\kas::str($this->type) ?
            $this->type = self::ALL : false;
    }

    /**
     * Метод возвращает true, если передаваемый тип
     * файла соответствует содержимому.
     * @param int $type
     * @param \DirectoryIterator $fInfo
     * @return bool
    */
    protected function confirmType($type = 0, $fInfo)
    {
        if
        (
            !is_int($type)                          ||
            !is_object($fInfo)                      ||
            !\kas::arr($this->typeFilter[$type])
        )
        {
            return false;
        }

        $lib = array_flip($this->typeFilter[$type]);

        if
        (
            is_null($lib[$fInfo->getExtension()])               &&
            is_null($lib[strtoupper($fInfo->getExtension())])
        )
        {
            return false;
        }

        return true;
    }

    /**
     * Файл является изображением.
     * @param $fInfo \DirectoryIterator
     * @return bool
    */
    protected function isImg($fInfo) {
        return $this->confirmType(self::IMG, $fInfo);
    }

    /**
     * Файл является документом.
     * @param $fInfo \DirectoryIterator
     * @return bool
    */
    protected function isDoc($fInfo) {
        return $this->confirmType(self::DOC, $fInfo);
    }

    /**
     * Файл является шаблоном.
     * @param $fInfo \DirectoryIterator
     * @return bool
    */
    protected function isTpl($fInfo) {
        return $this->confirmType(self::TPL, $fInfo);
    }

    /**
     * Файл php.
     * @param $fInfo \DirectoryIterator
     * @return bool
    */
    protected function isPhpExt($fInfo) {
        return $this->confirmType(self::EXT, $fInfo);
    }

    /**
     * Метод сканирует содержимое
     * директории.
    */
    protected function scan()
    {

        /**
         * Обход содержимого
        */
        foreach ($this->ITR as $k => $fInfo)
        {
            /**
             * Пропустить символические ссылки,
             * текущую и корневую дирректории.
            */
            if
            (
                $fInfo->getBasename() == '.'    ||
                $fInfo->getBasename() == '..'   ||
                $fInfo->isLink()
            )
            {
                continue;
            }

            /**
             * Пропустить файлы без имени.
            */
            if (trim($fInfo->getBasename($fInfo->getExtension())) == '.') {
                continue;
            }

            $_tmp = [$fInfo->getBasename(), \kas::slash($fInfo->getPathname())];

            /**
             * Фильтрация элементов по типам.
            */
            switch ($this->type)
            {
                /**
                 * Файлы и директории.
                */
                case self::ALL:
                    $this->scanDir[$_tmp[0]] = $_tmp[1];
                break;

                /**
                 * Директории.
                */
                case self::DIR:

                    if (!$fInfo->isDir()) {
                        continue;
                    }

                    $this->scanDir[$_tmp[0]] = $_tmp[1];

                break;

                /**
                 * Файлы.
                */
                case self::FILE:

                    if (!$fInfo->isFile()) {
                        continue;
                    }

                    $this->scanDir[$_tmp[0]] = $_tmp[1];

                break;

                /**
                 * Изображения.
                */
                case self::IMG:

                    if (!$this->isImg($fInfo)) {
                        continue;
                    }

                    $this->scanDir[$_tmp[0]] = $_tmp[1];

                break;

                /**
                 * Документы.
                */
                case self::DOC:

                    if (!$this->isDoc($fInfo)) {
                        continue;
                    }

                    $this->scanDir[$_tmp[0]] = $_tmp[1];

                break;

                /**
                 * Шаблоны.
                */
                case self::TPL:

                    if (!$this->isTpl($fInfo)) {
                        continue;
                    }

                    $this->scanDir[$_tmp[0]] = $_tmp[1];

                break;

                /**
                 * Файлы php
                */
                case self::EXT:

                    if (!$this->isPhpExt($fInfo)) {
                        continue;
                    }

                    $this->scanDir[$_tmp[0]] = $_tmp[1];

                break;

                /**
                 * Расширение или набор расширений.
                */
                default :

                    preg_match('/\|/', $this->type) ?
                        $extArr = explode('|', $this->type) :
                        $extArr = [$this->type];

                    foreach ($extArr as $ext)
                    {

                        if
                        (
                            !\kas::str(trim($ext))                  ||
                            $fInfo->getExtension() != trim($ext)
                        )
                        {
                            continue;
                        }

                        $this->scanDir[$_tmp[0]] = $_tmp[1];
                    }

                break;
            }
        }

        return $this->scanDir;
    }

    protected function config()
    {
        if (!is_object($this->ITR)) {
            \kas::ext('Property must be an object.');
        }

        return $this->scan();
    }

    static public function run($path = '', $type = 0)
    {
        $ob = new static($path, $type);
        return $ob->config();
    }
} 