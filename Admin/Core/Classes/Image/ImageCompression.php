<?php

namespace Core\Classes\Image;
/**
 * @author  Kirkor Artsiom <info@kas.by>
 * @licence KAS-PLATFORM
 *
 * Даныый класс осуществляет сжатие оригиналов изображений,
 * делая несколько локальных копий с различным коэфициентом сжатия.
 * Сжатие осуществляется для изображений формата .jpg, jpeg, .JPG
*/

class ImageCompression
{
    /**
     * Подняться на уровень выше.
    */
    const B               = '../';
    const DS              = DIRECTORY_SEPARATOR;

    /**
     * Ключ к данным аналитики класса.
    */
    const AN              = 'analytics';
    /**
     * Константа аналитики - является ключом массива
     * содержащего ошибки работы класса.
    */
    const AN_ERR           = 'errors';
    /**
     * Константа аналитики - является ключом массива
     * содержащего общее число сжимаемых изображений.
    */
    const AN_IMG_COUNT     = 'count';
    /**
     * Константа аналитики - является ключом массива
     * содержащего общий % выполнения сжатия.
    */
    const AN_PROG          = 'progress';

    /**
     * Константы для работы генератора.
    */
    const FILE            = 'file';
    const DIR             = 'dir';

    /**
     * Дирректория в которую будут сохраняться
     * сжатые файлы.
    */
    const COMPRESSION_DIR = COMPRESSION_DIR[0];

    /**
     * Дочерние дирректории сжатия
    */
    const IMG_L           = COMPRESSION_DIR[1];
    const IMG_M           = COMPRESSION_DIR[2];
    const IMG_S           = COMPRESSION_DIR[3];

    /**
     * Параметры качества сжимаемых изображений
    */

    /**
     * Значения в диапазоне от 0 (низкое качество, маленький размер файла)
     * до 100 (высокое качество, большой размер файла).
    */
    const IMG_JPG_QUAL    = 75;

    /**
     * Максимально-допустимые пределы по разрешению
    */
    protected $imgL_res   = CC_IMG_LRG_RES;
    protected $imgM_res   = CC_IMG_MID_RES;
    protected $imgS_res   = CC_IMG_ICO_RES;

    /**
     * Установить ограничение времени выполнения скрипта.
     * Параметр устанавливается в секундах (0 - неограничено).
    */
    protected $timeLimit  = 0;
    /**
     * Регулярное выражение проверки имени файла.
    */
    protected $rExp       = '/^[0-9A-Za-z_\.-]+$/';
    /**
     * Список поддерживаемых форматов исходных изображений.
    */
    protected $extList    = [1 => 'jpg', 'jpeg', 'JPG', 'JPEG'];
    /**
     * Поддерживаемые типы MIME.
     * Подробная информация для дальнейшего расширения:
     * @link <http://php.net/manual/ru/function.exif-imagetype.php>
    */
    protected $mime      = [1 => IMAGETYPE_JPEG];

    /**
     * 0|1, параметр указывает на общий результат выполнения сжатия.
     * Если, сжатие хотя бы одного элемента было выполнено успешно
     * значение параметра будет установлено на 1.
    */
    protected $state     = 0;

    /**
     * Параметр указывает на доступность
     * сохранения данных аналитики в сессию.
     *
     * 0 - сессия недоступна.
     * 1 - сессия доступна для работы.
    */
    protected $sesState  = 1;

    /**
     * Список оригиналов изображений с которых будут выполняться
     * сжатые снимки.
    */
    protected $imgList    = [];

    /**
     * Список дирректорий подлежащих сканированию.
    */
    protected $dirList    = [];

    /**
     * Данный массив содержит пути к сканируемым дирректориям
    */
    protected $pathArr    = [];

    /**
     * Запись данных в log-файл
    */
    protected $logDir     = __DIR__;
    protected $logName    = '_analytics.log';
    protected $delim      = "\r\n";

    /**
     * Формат даты, которая заноситься в log-файл.
    */
    protected $dateFormat = 'h:i:s';

    /**
     * Массив констант статистики (используется при очистке журналов)
    */
    protected $anArr      = [self::AN_ERR, self::AN_PROG, self::AN_IMG_COUNT];

    /**
     * При первом запуске метод $this->log устанавливает
     * путь к файлу журнала.
    */
    protected $logPath    = '';


    protected function __construct($path = [])
    {
        $this->pathArr = $path;

        // Set path config
        $this->setPathArr();

        /**
         * Класс сохраняет данные аналитики в сессию.
        */
        isset($_SESSION) ?: @session_start() ?: $this->sesState = 0;

        /**
         * Установить ограничение времени выполнения скрипта.
        */
        set_time_limit($this->timeLimit);

        /**
         * Распаковать и установить максимальные пределы разрешения.
        */
        $this->setResolutionLimit();

        /**
         * Меняем местамми ключи и значения массива, который
         * содержит список поддерживаемых расширений.
        */
        $this->extList = array_flip($this->extList);

        /**
         * Меняем местамми ключи и значения массива, который
         * содержит список MIME типов.
        */
        $this->mime = array_flip($this->mime);

    }

    protected function setPathArr() {
        \kas::str($this->pathArr) ? $this->pathArr  = [$this->pathArr] : false;
        \kas::arr($this->pathArr) ?: $this->pathArr = [];

        $this->pathArr = array_map(function($v){
            return pathinfo($v)['dirname'];
        }, $this->pathArr);
        return true;
    }

    /**
     * Данный метод выполняет ДЕсериализацию свойств в которые записанны
     * максимальные пределы по разрешению преобразовывая
     * данные свойства в массив.
    */
    protected function setResolutionLimit()
    {
        $resArr = array
        (
            'imgL_res' => $this->imgL_res,
            'imgM_res' => $this->imgM_res,
            'imgS_res' => $this->imgS_res
        );

        /**
         * Каждое значение данного массива связанно с константой платформы,
         * которая содержит одномерный массив прошедший сериализацию.
        */
        foreach($resArr as $k => $v)
        {
            if (!\kas::str($v)) return false;
            $v = @unserialize($v);
            if (!\kas::arr($v)) return false;

            $this->$k = $v;
        }

        return true;
    }

    /**
     * Медод выполняет очистку данных сессии для обновления данных
     * и очистку log-файлов.
    */
    protected function clearLog()
    {
        unset($_SESSION[self::AN]);
        $this->log();
    }

    /**
     * Метод записывает данные аналитики в log-файл.
     * Если данный метод вызвать без аргументов все записи будут удалены.
     *
     * @param bool|string $const
     * @param bool|string $data
     * @param bool $mod - если true метод будет дописывать данные.
     * @return bool
     */
    protected function log($const = false, $data = false,  $mod = false)
    {

        $this->dirAccess($this->logPath) ?:
            $this->logPath = $this->logDir . self::DS;

        /**
         * Удаление старых журналов.
        */
        if
        (
            !$const     &&
            !$data      &&
            !$mod
        )
        {
            foreach($this->anArr as $log)
            {
                $path = $this->logPath . $log . $this->logName;

                !file_exists($path) ?:
                    @unlink($path);
            }

            return true;
        }

        if
        (
            !$data              ||
            !\kas::str($const)
        )
        {
            return false;
        }

        /**
         * Зафиксировать дату.
        */
        $date = date($this->dateFormat) . ': ';

        /**
         * Режим дозаписи данных
        */
        !$mod ?:
            $mod = FILE_APPEND;

        if
        (
            file_put_contents
            (
                $this->logPath . $const . $this->logName,
                $date . $data . $this->delim,
                $mod
            )
        )
        {
            return true;
        }

        return false;
    }

    /**
     * Метод осуществляет запись и сбор статистических данных о работе
     * класса.
     *
     * Данные статистики включают:
     * - общее количество файлов;
     * - общее количество ошибок и краткое описание;
     * - общий процент выполнения сжатия.
     *
     * @param $const bool|string
     * @param $data bool|string
     * @return bool
    */
    protected function analytics($const = false, $data = false)
    {
        if
        (
            !$this->sesState    ||
            !$const             ||
            !$data              ||
            !\kas::str($data)
        )
        {
            return false;
        }

        /**
         * Запись расположения (только для AN_ERR).
        */
        $const !== self::AN_ERR ?:
            $data = __LINE__ . ': ' . $data;

        /**
         * Подсчет % выполнения
        */
        if ($const == self::AN_PROG)
        {

            $prog = round((100/count($this->imgList)*(int)($data)));
            $data = $prog;

            /**
             * Запись данных в log-файл.
            */
            $this->log($const, $data);
        }



        switch(is_object($_SESSION[self::AN]))
        {
            case true:

                $constArr = $_SESSION[self::AN]->$const;

                \kas::arr($constArr) ?
                    $constArr[] = $data :
                    $constArr = array($data);

                $_SESSION[self::AN]->$const = $constArr;

            break;

            case false:

                $obj = (object)('');
                $obj->$const = [$data];
                $_SESSION[self::AN] = $obj;

            break;
        }

        return is_object($_SESSION[self::AN])
            ? true: false;
    }

    /**
     * Проверка имени файла по Регулярному выражению.
     *
     * @param bool|string $fName
     * @return bool
    */
    protected function checkFileName($fName = false)
    {
        if (!\kas::str($fName)) return false;

        return preg_match($this->rExp, $fName) ?
            true : false;
    }

    /**
     * Проверка Mime-типа файла.
     *
     * @param bool|string $path
     * @return bool
    */
    protected function checkMime($path = false)
    {
        if (!\kas::str($path)) return false;

        /**
         * Функция возвращает номер Imagetype константы.
         *
         * @var integer|false $ex
        */
        $ex = (int)(@exif_imagetype($path));

        return (bool)($this->mime[$ex])
            ? $ex : false;
    }

    /**
     * Данный метод отсеивает файлы лежащие в дирректории сжатия.
     *
     * @param bool|string $path
     * @return bool
    */
    protected function isCompressionDir($path = false)
    {
        if (!$path) return false;
        return (bool) (preg_match('/'.self::COMPRESSION_DIR.'/', $path));
    }

    /**
     * @param \DirectoryIterator| bool $e
     * @return bool
    */
    protected function imgFilter($e = false)
    {
        if ( !($e instanceof \DirectoryIterator) ) {
            return false;
        }

        /**
         * Путь к текущему файлу.
        */
        $path = $e->getPath() . self::DS . $e->getFilename();

        /**
         * Получить получить расширение файла (без точки).
        */
        $ext  = $e->getExtension();

        /**
         * Получить размер файла в КБ.
        */
        $size = $e->getSize()/1024;

        /**
         * Получить тип file|dir.
        */
        $type = $e->getType() == self::FILE ?: false;

        /**
         * ДАЛЕЕ ИДУТ ПРОВЕРКИ ФАЙЛА ПО РАЗЛИЧНЫМ КРИТЕРИЯМ,
         * КОТОРЫЕ БУДУТ РАЗДЕЛЕНЫ.
        */

        if
        (
            !$ext                   ||
            !$this->extList[$ext]   ||
            !$size                  ||
            !$type
        )
        {
            return false;
        }

        /**
         * Проверка имени файла
        */
        if (!$this->checkFileName($e->getFilename())) {
            return false;
        }

        /**
         * Проверка MIME
        */
        if (!$this->checkMime($path)) return false;

        return true;
    }

    /**
     * Метод осуществляет проверку корректности передаваемого
     * аргумента который должен содержать путь к сканируемой дирректории.
     *
     * @param bool|string $path
     * @return bool
     */
    protected function dirAccess($path = false)
    {
        if
        (
            !\kas::str($path)    ||
            !is_dir($path)      ||
            !is_readable($path)
        )
        {
            return false;
        }

        return true;
    }

    /**
     * Метод осуществляет конфигурацию дирректорий $this->pathArr
     * связывая их с дирректориями проекта.
     *
     * @return bool
    */
    protected function configProjDir()
    {
        foreach($this->pathArr as $k => $path)
        {
            if (!\kas::str($path)){
                return false;
            }

            if (!$this->dirAccess($path)) {
                return false;
            }

            $this->pathArr[$k] = new \DirectoryIterator($path);
        }

        return true;
    }

    /**
     * Данный метод осуществляет сканирование объектов дирректорий,
     * которые были определены свойством $this->pathArr
     *
     * В качестве аргумента принемает массив $arr_path, который содержит
     * объекты класса DirectoryIterator.
     *
     * @param bool|array|object $arr_pathObj
     * @return mixed
    */
    protected function scanDir($arr_pathObj = false)
    {
        /**
         * Только массивы или объекты
        */
        if
        (
            !is_object($arr_pathObj)    &&
            !is_array($arr_pathObj)
        )
        {
            return false;
        }

        is_object($arr_pathObj) ?
            $arr_pathObj = array($arr_pathObj) : false;

        /**
         * Аргумент должен быть массивом
        */
        if (!\kas::arr($arr_pathObj)) return false;

        /**
         * @var  \DirectoryIterator $pathObj
        */
        foreach($arr_pathObj as $pathObj)
        {

            if (!is_object($pathObj)) {
                return false;
            }

            /**
             * @var \DirectoryIterator $e объект (файл либо дирректория)
            */
            foreach($pathObj as $e)
            {
                switch
                (
                    $e->isDir()         &&
                    $e->isReadable()    &&
                    !$e->isDot()
                )
                {
                    /**
                     * Список дирректорий доступных для чтения.
                    */
                    case true:

                        $dirObj = new \DirectoryIterator
                        (
                            $e->getPath() . self::DS . $e->getBasename()
                        );

                        $this->scanDir($dirObj);

                    break;

                    /**
                     * Список файлов.
                    */
                    case false:

                        if
                        (
                            !$e->getExtension()                     ||
                            !$e->getBasename()                      ||
                            !$this->imgFilter($e)                   ||

                            /**
                             * Файлы, которые находяться в дирректории
                             * сжатия.
                            */
                            $this->isCompressionDir($e->getPath)
                        )
                        {
                            continue;
                        }

                        $this->imgList[] = $e->getPath() . self::DS . $e->getFilename();

                    break;
                }
            }

        }

        return \kas::arr($this->imgList) ? true : false;
    }


    /**
     * Метод создает дирректорию <compression> в
     * пределах передаваемой дирректории.
     *
     * @param bool|string $imgDir
     * @return bool|array
    */
    protected function setCompressionDir($imgDir = false)
    {
        if (!$imgDir) return false;

        /**
         * Добавить к передаваемой дирректории дирректорию сжатия
        */
        $compDir    = $imgDir  . self::DS . self::COMPRESSION_DIR;

        /**
         * Создать дочерние дирректории.
        */
        $compDirL   = $compDir . self::DS . self::IMG_L;
        $compDirM   = $compDir . self::DS . self::IMG_M;
        $compDirS   = $compDir . self::DS . self::IMG_S;

        $cArr = array
        (
            self::COMPRESSION_DIR   => $compDir  . self::DS,
            self::IMG_L             => $compDirL . self::DS,
            self::IMG_M             => $compDirM . self::DS,
            self::IMG_S             => $compDirS . self::DS
        );

        foreach($cArr as $path)
        {
            if
            (
                !$this->dirAccess($path)    &&
                is_readable($imgDir)
            )
            {
                if (!mkdir($path)) {
                    \kas::ext('Can\'t create compression dir - ' . $path);
                    return false;
                }
            }

            continue;
        }

        return $cArr;
    }

    /**
     * Расчитать разрешение для текущего изображения.
     *
     * @param bool|string $img - путь к изображению.
     * @return bool|array
    */
    protected function calculateResolution($img = false)
    {
        if (!\kas::str($img)) {
            return false;
        }

        /**
         * Получить разрешение
        */
        $imSize = @getimagesize($img);

        if (!\kas::arr($imSize)) {
            return false;
        }

        /**
         * @var array $wh
         * Переменная содержит параметры разрешения для
         * разных типов сжатия.
        */
        $wh     = [];

        /**
         * Определяем ширину и высоту элемента.
        */
        $imW    = $imSize[0];
        $imH    = $imSize[1];

        $rArr   = array
        (
            self::IMG_L => $this->imgL_res,
            self::IMG_M => $this->imgM_res,
            self::IMG_S => $this->imgS_res
        );

        /**
         * @var integer $w - максимальный предел
         * разрешения по ширине.
        */
        foreach($rArr as $type => $w)
        {
            $imW > $w[0] ?
                $wh[$type] = [$w, round($imH/($imW/$w))] : //round() - возвр. данные типа float
                $wh[$type] = [$imW, $imH];
        }

        /**
         * Добавляем параметры исходного изображения
        */
        $wh[self::COMPRESSION_DIR] = [$imW,$imH];
        return $wh;
    }

    /**
     * Создать пустое изображение по заданным параметрам разрешения.
     * @param  bool|array $rArr
     * @return bool|array
     */
    protected function createClearImg($rArr = false)
    {
        if (!\kas::arr($rArr)) return false;

        foreach($rArr as $type => $wh)
        {
            if
            (
                !\kas::str($type)                ||
                $type == self::COMPRESSION_DIR  ||
                !\kas::arr($wh)
            )
            {
                continue;
            }

            /**
             * Возвращает идентификатор изображения, представляющий
             * черное изображение заданного размера.
            */
            $im = @imagecreatetruecolor($wh[0], $wh[1]);
            if (!$im) continue;

            $imArr[$type] = $im;
        }

        /** @var array $imArr */
        return $imArr ?: false;
    }

    /**
     * Метод создает изображения формата JPG с заданным коэффициентом
     * сжатия делая копию исходного изображения.
     *
     * @param bool|string $img  - путь к исходному изображению (images/img.jpg).
     * @param bool|array  $cArr - пути к дирректориям сжатия IMG_L, IMG_M, IMG_S.
     * @param bool|array  $rArr - разрешения исходного изображения
     * и IMG_L, IMG_M, IMG_S.
     *
     * @return bool
     */
    protected function createImgJpg($img = false, $cArr = false, $rArr = false)
    {
        if
        (
            !\kas::str($img)    ||
            !\kas::arr($cArr)   ||
            !\kas::arr($rArr)
        )
        {
            return false;
        }

        $dst = $this->createClearImg($rArr);

        /**
         * @var string $type IMG_L, IMG_M, IMG_S
        */
        foreach($dst as $type => $dst_img)
        {
            $src = @imagecreatefromjpeg($img);

            if
            (
                !is_resource($dst[$type])   ||
                !is_resource($src)
            )
            {
                return false;
            }

            /**
             * @var bool $resampled
             * В случае успешного завершения вернет true.
             * На $dst[$type] будет наложен слой исходного изображения.
            */
            $resampled = @imagecopyresampled
            (
                $dst[$type],
                $src,
                0,0,0,0,
                $rArr[$type][0],                 //$dstW
                $rArr[$type][1],                 //$dstH
                $rArr[self::COMPRESSION_DIR][0], //$srcW
                $rArr[self::COMPRESSION_DIR][1]  //$srcH
            );

            if (!$resampled)
            {
                $this->log
                (
                    self::AN_ERR,
                    'Imagecopyresampled Error - ' . $img,
                    true
                );
                return false;
            }

            if
            (
                @imagejpeg
                (
                    $dst[$type],
                    $cArr[$type]  . basename($img),
                    self::IMG_JPG_QUAL
                )
            )
            {
                /**
                 * Если, сжатие хотя бы одного элемента было выполнено успешно
                 * значение параметра $this->state будет установлено на 1.
                 * @return bool $this
                */
                $this->state = 1;
                /**
                 * Освободить память, если сжатие было
                 * успешно выполнено.
                */
                @imagedestroy($dst[$type]);
                /**
                 * Если память не будет освобождена возвращаемое
                 * значение всё равно будет true т.к сжатие завершено успешно.
                */
                continue;
            }

            /**
             * Если изображение не было создано
            */
            $this->log
            (
                self::AN_ERR,
                'Imagejpeg Error - ' . $img,
                true
            );

            return false;
        }

        return true;
    }

    /**
     * Метод выполняет сжатие изображений в соответствии с типом MIME
    */
    protected function imgCompression()
    {
        foreach($this->imgList as $k => $img)
        {
            /**
             * Пропускать дирректории в которых неудалось
             * создать папку compression.
             *
             * @param array $cArr содержит массив состоящий из
             * 4-х путей к дирректориям:
             * COMPRESSION_DIR/, IMG_L/, IMG_M/, IMG_S/
            */
            $cArr = $this->setCompressionDir(dirname($img));

            if (!\kas::arr($cArr)) continue;

            /**
             * Расчитать разрешение текущего изображения для
             * всех видов сжатия (IMG_L, IMG_M, IMG_S)
             *
             * @var bool|array $rArr
            */
            $rArr = $this->calculateResolution($img);

            if (!\kas::arr($rArr)) continue;

            /**
             * Метод возвращает номер Imagetype константы,
             * что позволяет для каждого типа изображения определять
             * свой набор методов обработки.
            */
            switch($this->checkMime($img))
            {
                /**
                 * Создать изображения формата .jpg
                */
                case 2:

                    !$this->createImgJpg($img, $cArr, $rArr) ?:
                        $this->analytics(self::AN_PROG, $k);

                break;
            }
        }

        return $this->state ? true : false;
    }

    /**
     * Метод возвращает данные о работе класса в формате строки.
    */
    protected function getStatus()
    {
        $path = $this->logDir . self::DS. self::AN_PROG . $this->logName;
        $pref = '%';

        if (!file_exists($path)) {
            return 0 . $pref;
        }

        $data = explode(' ', file_get_contents($path));
        if (!\kas::arr($data)) return 0 . $pref;

        return trim(end($data)) . $pref;
    }

    protected function config()
    {

        /**
         * Удалить старую информацию
        */
        $this->clearLog();
        /**
         * Конфигурация параметров сканируемых дирректорий
        */
        if (!$this->configProjDir()) return 0;
        if (!$this->scanDir($this->pathArr)) return 0;

        /**
         * Общее число изображений
        */
        $this->analytics(self::AN_IMG_COUNT, count($this->imgList));
        $this->log(self::AN_IMG_COUNT, count($this->imgList));

        /**
         * Сжатие изображений.
        */
        $this->imgCompression();
        return $this->state;
    }

    /**
     * @param $path mixed
     * It will be path to files or filename.
     * For example: /dir/images/ or /dir/images/img.jpg
     *
     * @return mixed
    */
    static public function run($path = [])
    {
        $obj = new static($path);
        return $_POST[STATUS] ? $obj->getStatus() : $obj->config();
    }
} 