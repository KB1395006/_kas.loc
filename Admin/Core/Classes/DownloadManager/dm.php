<?php
/**
 * @class  download_file_manager v 1.0
 * @author Created by Kirkor Artsiom
 * @date: 23.01.2015
 * @addr info@kas.by
 */
namespace Core\Classes\DownloadManager;
use Core\Classes\Image as Image;

class dm {

    const UPL_DIR              = UPL_PATH;
    const REQUEST              = 'REQUEST_METHOD';
    const POST                 = 'POST';
    const NAME                 = 'name';
    const TYPE                 = 'type';
    const SIZE                 = 'size';
    const ACCESS               = 'access';
    const T_NAME               = 'tmp_name';

    const PUB                  = 'public';
    const IT                   = 'items';
    const CATALOG              = 'catalog';
    const DS                   = DS;

    const IMG_DIR              = 'img/';
    const FL_DIR               = 'files/';

    /**
     * При передаче данного праметра методом POST
     * будет осуществлятся проверка работы класса
     * finfo на возможность возникновения ошибки
     * 500 (Internal server error)
    */
    const F_INFO               = 'finfo';

    /**
     * Упрощенная конфигурация работы класса.
     * При передаче данного праметра методом POST
     * класс перестраивается на упрощенный режим работы.
     *
     * Работа класса finfo будет проигнорирована;
     * Проверка соответствия типов MIME так же будет проигнорирована.
    */
    const SIMPLE_CONF          = 'simple_config';

    /**
     * Серверные конфигурации:
     *
     * PHP_INI_PERDIR - значение может быть установлено в php.ini, .htaccess
     * или httpd.conf (С версии PHP 5.3).
    */
    protected $maxFileSize    = ''; //PHP_INI_PERDIR
    protected $postMaxSize    = ''; //PHP_INI_PERDIR
    protected $memoryLimit    = '';

    /**Сообщение в случае отсутствия альтернативного сообщения лога*/
    protected $eventMsg       = 'event on line';

    /**Формат записи даты в log-файле*/
    protected $dateForm       = 'd-m-y, h:i:s';

    /**Символ-разделитель между частями сообщения текущего лога*/
    protected $imp            = ' ';

    /**Отсутствие данных*/
    protected $noDate         = 'INVALID_DATE';
    protected $noLine         = 'UNKNOWN_LINE';

    /**Прочие конфигурации*/

    protected $delim          = "\r\n";

    /**
     * Разделитель параметра запроса (id).
    */
    protected $q_delim        = '=';

    /** 1024 * 1024 = 1MB */
    protected $mb             = 5242880;

    /** Права доступа к дирректории загрузки файлов */
    protected $mode           = 0777;

    /**Ругулерное выражение проверки допустимого имени файла*/
    protected $rExp           = '/^[0-9A-Z-_\.]+$/i';

    /**
     * Определять путь для сохранения файлов в зависимости от положения
     * модуля.
     * @param bool
    */
    protected $setUploadPath  = true;

    /**
     * Mime-тип файла, который был установлен методом access-control.
    */
    protected $defineType     = '';

    /**
     * Информация о текущем файле
    */
    protected $fileData       = [];

    /**
     * Целевая стр. перехода => Таблица
    */
    protected $targetTables   = array
    (
        self::IT  => self::CATALOG,
        self::PUB => self::PUB
    );

    /**Список запрещенных расширений*/
    protected $disabled       = ['.exe', '.js', '.php', '.cc', '.cpp'];
    /**Изображения*/
    protected $imgExt         = ['.jpg', '.jpeg', '.png', '.bmp', '.gif'];
    /**Устанавливать кодировку utf-8 файлам с данным типом расширения*/
    protected $utf8Files      = ['.txt', '.csv', '.doc', '.docx'];
    /**Текущее расширение*/
    protected $currentExt     = '';

    // Undefined as default
    protected $filetype       = 0;


    /**
     * Общий доступ.
    */
    public $uploadDir         = self::UPL_DIR;
    public $dstDir            = ['img/', 'files/'];

    /**
     * @param bool|string $msg
     * @return bool
    */
    protected function log($msg = false) {
        \kas::ext($msg);
        return true;
    }

    protected function request_control()
    {
        if ($_SERVER[self::REQUEST] !== self::POST) return false;
        return true;
    }

    protected function setUploadPath()
    {
        \kas::str($_SESSION[UPL_DIR]) ?
            $this->uploadDir .= $_SESSION[UPL_DIR] : false;

        is_dir($this->uploadDir) ?:
            mkdir($this->uploadDir, 0777, true);

        return $this->uploadDir;
    }

    /**
     * Работа осуществляется только с одним файлом,
     * это значит что js-сценарий передаёт каждый файл по очериди.
     *
     * @return bool | array
    */
    protected function getFiles()
    {
        if (!is_array($_FILES)) {
            return false;
        }

        /**
         * Поскольку данный массив имеет двухмерную структуру
         * 'inputFileName' => array('tmp_name' => '', 'size' => ''...)
         * его необходимо преобразовать до одномерного, чтобы соответствовать
         * структуре массива $_POST.
        */
        return @current($_FILES);
    }

    /**
     * @param $filename string
     * @return bool|string
    */
    protected function getFilename($filename)
    {
        if
        (
            !$filename              ||
            !is_string($filename)
        )
        {
            $this->log('Filename argument error.');
            return false;
        }

        if (!preg_match($this->rExp, $filename))
        {
            $ext = @pathinfo($filename, PATHINFO_EXTENSION);

            if (!$ext)
            {
                log('Extension error');
                return false;
            }

            $filename  = hash('GOST', $filename);
            $filename .= '.' . $ext;
        }

        return $filename;
    }

    /**
     * @param $file array
     * @return bool
    */
    protected function moveFile(array $file)
    {
        /**
         * Устанавливаем дирректорию для загрузки файлов.
        */
        $this->uploadDir = $this->setUploadPath();

        if (!is_dir($this->uploadDir))
        {
            $this->log('Upload dir is not exists.');
            return false;
        }

        $basename = $this->getFilename($file[self::NAME]);
        $fO = \kas::data($basename)->getFileOb();

        if (!$basename || !$fO)
        {
            $this->log('Error filename.');
            return false;
        }

        // Set filename
        $filename  = $this->uploadDir;

        // Check is img
        $isImage = $this->isImg($file[self::T_NAME]);
        $isImage ?
            $filename .= self::IMG_DIR :
            $filename .= self::FL_DIR;

        // Check exists
        is_dir($filename . $fO->dir) ?:
            mkdir($filename . $fO->dir, 0777, true);

        // Join additional path and convert string to lower case
        $filename = \kas::data($filename . $fO->path)->strLow()->asStr();

        if (!move_uploaded_file($file[self::T_NAME], $filename))
        {
            $this->log('Can\'t move uploaded file ' . $filename);
            return false;
        }

        // Convert to UTF-8
        if (is_int($this->utf8Files[$this->currentExt]))
        {
            $_tmp = \kas::load($filename);

            if (!\kas::str($_tmp)) {
                $this->log('Can\'t change encoding');
                return false;
            }

            $_tmp = mb_convert_encoding($_tmp, ENCODING, 'windows-1251');

            if (!file_put_contents($filename, $_tmp)) {
                $this->log('Writing error');
                return false;
            }
        }

        // Complete compression if this file is image
        $isImage ? Image\ImageCompression::run($filename) : false;

        // Check exists same file in database
        $exists = \kas::sql()->exec(\kas::sql()->simple()->sel(MEDIA, [ID]) . SRC . ' = ?', [$filename]);

        // If is array
        switch (\kas::arr($exists))
        {
            case true:
                return true;
            break;

            case false:

                // Database
                $q = \kas::sql()->simple()->ins(MEDIA, [NAME, TITLE, SRC, TYPE, MIME, DATE]);
                $r = \kas::sql()->exec
                (
                    $q,
                    [
                        basename($filename),
                        $this->fileData['name'],
                        $filename,
                        $this->filetype,
                        $this->fileData['type'],
                        date(KAS_DATE_FORMAT)
                    ]
                );

                if (!$r) {
                    $this->log('File [' . $filename . '] is not inserted in database.');
                    return false;
                }

            break;
        }

        $this->log('File ' . $filename . ' success upload on the server.');
        return true;
    }

    protected function finfo() {
        return new \finfo(FILEINFO_MIME);
    }

    protected function checkFinfo()
    {
        if (is_object($this->finfo())) {
            return true;
        }

        $this->log('Class finfo error');
        return false;
    }

    /**
     * Метод устанавливает упрощенный режим работы класса
    */
    protected function simpleConfig()
    {
        $this->log('Transform to simple config mod.');
        $_SESSION[__CLASS__] = array(self::SIMPLE_CONF => true);

        if (!$_SESSION[__CLASS__]) {
            $this->log('Session error.');
            return false;
        }

        return true;
    }

    protected function isImg($filename = '')
    {
        if (!\kas::str($filename)) {
            return false;
        }

        $resp = (bool) @getimagesize($filename);

        // Set current filetype
        $resp ? $this->filetype = (int) $resp : false;
        return $resp;
    }

    /**
     * @param string $filename
     * @return int
    */
    protected function getFileInfo($filename)
    {
        if
        (
            !is_string($filename)       ||
            !file_exists($filename)
        )
        {
            $this->log('Invalid argument');
            return 0;
        }

        if (!is_uploaded_file($filename))
        {
            $this->log('!is_uploaded_file');
            return false;
        }

        /**
         * Работа класса осуществляется в упрощенном режиме.
         * return true - проверка завершена успешно.
        */
        if ($_SESSION[__CLASS__]) {
            return 1;
        }

        $obj    = $this->finfo();
        $fType  = trim( explode( ';', $obj->file($filename) )[0] );

        return \kas::str($fType) ? 
            1 : 0;
    }

    protected function config()
    {
        $this->fileData = $this->getFiles();

        if
        (
            !$this->accessControl(true)                             ||
            !$this->fileData                                        ||
            !$this->getFileInfo($this->fileData[self::T_NAME])
        )
        {
            return 0;
        }

        if (!$this->moveFile($this->fileData)) return 0;
        return true;
    }

    public function __construct()
    {
        @session_start();
        $this->maxFileSize = ini_get("upload_max_filesize");
        $this->postMaxSize = ini_get("post_max_size");
        $this->memoryLimit = ini_get("memory_limit");

        // Инвертировать расширения
        $this->disabled    = array_flip($this->disabled);
        $this->imgExt      = array_flip($this->imgExt);
        $this->utf8Files   = array_flip($this->utf8Files);
    }

    protected function getExt($n = '')
    {
        if (!\kas::str($n)) {
            return 0;
        }

        /*Определить текущее расширение*/
        $ext = \kas::data($n)->explode('.')->last();

        \kas::str($ext) ?
            $this->currentExt = '.' . $ext :
            $this->currentExt = false;

        /**Текущее расширение в нижнем регистре*/
        $this->currentExt = \kas::data($this->currentExt)->strLow()->asStr();

        return $this->currentExt ? 1 : 0;
    }

    /**
     * Предварительная проверка файлов до начала загрузки.
     *
     * @param bool $mod
     * Модификатор определяет контекст вызова данного метода.
     * Вызов либо внешний (до начала загрузки файлов на сервер)
     * либо локальный (внутри класса).
     *
     * @return bool
    */
    public function accessControl($mod = false)
    {

        /**Глобальный массив по умолчанию*/
        $req = $this->getFiles();

        /**
         * Предварительная проверка данных до начала загрузки
         * передается методом $_POST (модификатор false по умолчанию)
        */
        $mod ?: $req = $_POST;

        if
        (
            !$this->request_control()           ||
            !is_array($req)                     ||
            empty($req)                         ||
            !$this->getExt($req[self::NAME])
        )
        {
            $this->log('error request');
            return 0;
        }

        /**Данный тип файла запрещен!*/
        if (is_int($this->disabled[$this->currentExt]))
        {
            $this->log(implode(', ', $req) . '. Data is unsupported.');
            return 0;
        }

        return 1;
    }

    public function upload()
    {
        /**
         * Определяем доступность класса finfo.
        */
        if ($_POST[self::F_INFO]){
            return $this->checkFinfo();
        }

        /**
         * Определяем режим работы класса
        */
        if ($_POST[self::SIMPLE_CONF]) {
            if (!$this->simpleConfig()) return 0;
            return 1;
        }

        if (!\kas::arr($_FILES)) {
            return $this->accessControl();
        }

        return $this->config();
    }
    
    static public function run() 
    {
        $ob = new static();
        return $ob->upload();
    }

    static public function get_ini() {
        $obj = new static();
        return (int)($obj->maxFileSize);
    }

} 