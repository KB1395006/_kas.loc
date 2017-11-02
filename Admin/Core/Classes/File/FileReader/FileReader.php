<?php

namespace Core\Classes\File\FileReader;

/**
 * Данный класс осуществляет чтение локальных и удаленных
 * файлов различного размера.
*/
class FileReader
{
    /**
     * В качестве пути к читаемому файлу
     * был передан URL.
    */
    const TYPE_URL          = 1;
    /**
     * В качестве пути используется локальный
     * путь.
    */
    const TYPE_LOC          = 2;
    /**
     * Возвратить содержимое всего файла.
    */
    const READ_ALL          = 1;
    /**
     * Построчное чтение файла.
    */
    const READ_STR          = 2;
    /**
     * Если читаемый поток является буферизованным и не
     * представляет собой обычный файл, то за один раз
     * максимум читается количество байт, равное размеру
     * одной порции данных (обычно это 8192), однако, в зависимости
     * от ранее буферизованных данных размер возвращаемых данных
     * может быть больше размера одной порции данных.
    */
    const READ_URL          = 8192;
    /**
     * Локальный путь либо url указывающий
     * на расположение файла.
    */
    protected $src          = '';
    /**
     * Файловый ресурс.
     * @param resource
    */
    protected $file;
    /**
     * Взависимости от размера файла устанавливаются
     * следующие режимы чтения:
     * READ_ALL|READ_STR|READ_URL.
     *
     * Так же можно установить свой размер.
     * Если размер файла неизвестен или слишком большой
     * рекомендуется использовать модификатор - READ_STR.
    */
    protected $rLength      = self::READ_STR;
    /**
     * Максимальный размер прочитанных данных в байтах.
     * Не более 10 мегабайт для локальных файлов.
    */
    protected $locLenLim    = 10485760;
    /**
     * Максимальный размер прочитанных данных в байтах.
     * Не более 1 мегабайта для удаленных файлов.
    */
    protected $urlLim       = 1048576;
    /**
    */
    protected $data         = false;
    /**
     * Текущий статус работы класса.
     * Если значение = 0 (ошибка выполнения).
     * Если значение = 1 (OK).
    */
    protected $state        = 0;
    /**
     * Параметр указывает на тип атрибута
     * $src.
     * Локальный путь либо url указывающий
     * на расположение файла.
    */
    protected $pathType     = 0;
    /**
     * Кодировка читаемого файла.
    */
    protected $fileEnc      = '';

    public function __construct ( $src = false, $locLnLim = false, $urlLnLim = false )
    {
        $this->src              = trim($src);

        !is_int ($locLnLim) ?:
            $this->locLenLim    = $locLnLim;

        !is_int ($urlLnLim) ?:
            $this->urlLim       = $urlLnLim;

        if
        (
            !$this->fileExists()            ||
            !$this->setReadingLength()      ||
            !$this->open()
        )
        {
            return false;
        }

        /**
         * Свойство указывает на состояние
         * работы класса.
         *
         * 1 - OK.
        */
        $this->state = 1;

        return true;
    }

    /**
     * Данный метод проверяет существования
     * файла или url.
     * @return bool
    */
    protected function fileExists()
    {
        if ( !is_string($this->src) ) {
            return false;
        }

        switch ( preg_match('/(https?|ftp)\:/', $this->src) )
        {
            /**
             * Проверять как url.
            */
            case true:

                $h = @get_headers($this->src);

                /**
                 * Произошла ошибка.
                */
                if (!is_array($h)) {
                    return false;
                }

                /**
                 * В качестве пути используется url.
                */
                $this->pathType = self::TYPE_URL;

                /**
                 * Получить ответ от сервера.
                */
                if (preg_match('/404 Not Found/', $h[0])) {
                    \kas::ext("File doesn't exist {$this->src}");
                    return false;
                }

                return true;

            break;

            /**
             * Проверять как локальный файл.
            */
            case false:

                /**
                 * В качестве пути используется
                 * локальный путь.
                */
                $this->pathType = self::TYPE_LOC;

                if (!file_exists($this->src)) {
                    \kas::ext("File doesn't exist {$this->src}");
                    return false;
                }

                return true;

            break;
        }

        return false;
    }

    /**
     * Метод получает файловый ресурс.
    */
    protected function open()
    {
        if ($this->rLength == self::READ_ALL)
        {
            $this->data = @file_get_contents($this->src);

            return $this->data
                ?: false;
        }

        $this->file = @fopen($this->src, 'rb');

        /**
         * Если файловый ресурс не был получен.
        */
        if (!is_resource($this->file)) {
            \kas::ext("Can't get file resource.");
            return false;
        }

        return true;
    }

    /**
     * Метод устанавливает длину чтения файла (в байтах)
    */
    protected function setReadingLength()
    {
        /**
         * Определить размер загружаемых файлов, а
         * так же их тип (для url) для определения
         * оптимального режима чтения.
        */
        switch ($this->pathType)
        {
            case self::TYPE_URL :

                $h = @get_headers($this->src);

                if (!is_array($h)) {
                    \kas::ext("Headers error.");
                    return false;
                }

                foreach ($h as $v)
                {
                    switch (@preg_match('/Content\-Type\: text\/html;/', $v))
                    {
                        case true :
                            $this->rLength = self::READ_URL;
                            return true;
                        break;

                        /**
                         * Читать файл построчно.
                        */
                        case false :

                            /**
                             * Определить размер запрашиваемого файла.
                            */
                            if
                            (
                                !@preg_match
                                (
                                    '/Content-Length: ([1-9]+)/',
                                    $v,
                                    $m
                                )
                            )
                            {
                                continue;
                            }

                            $ln = (int) $m[1];

                            $ln < $this->urlLim ?
                                $this->rLength = self::READ_ALL :
                                $this->rLength = self::READ_STR;

                            return true;

                        break;
                    }
                }

            break;

            case self::TYPE_LOC :

                $fsize = @filesize($this->src);

                if (!$fsize) {
                    //\kas::ext("Local error.");
                    return false;
                }

                $fsize > $this->locLenLim ?
                    $this->rLength = self::READ_STR :
                    $this->rLength = self::READ_ALL;

                return true;

            break;
        }

        return true;
    }

    /**
     * Определить кодировку строк читаемого файла.
     *
     * @param string $ln
     * @return string
     */
    protected function defineFileEnc($ln = '')
    {
        if
        (
            $this->fileEnc                                      ||
            preg_match("/encoding=\"WINDOWS-1251\"/i", $ln)
        )
        {
            $ln = iconv("CP1251", "UTF-8", $ln);
            $this->fileEnc = "CP1251";
        }

        return $ln;
    }

    /**
     *
    */
    protected function getContents()
    {

        switch ($this->rLength)
        {
            case self::READ_STR :

                $fn = 'fgets';
                $ln = 4096;
                break;

            case self::READ_URL :

                $fn = 'fread';
                $ln = self::READ_URL;
                break;

            default :

                $fn = 'fread';
                $ln = $this->rLength;
                break;
        }

        while ($line = @$fn($this->file, $ln))
        {
            /**
             * Определить кодировку файла.
            */
            yield $this->defineFileEnc($line);
        }

    }

    /**
     * Данный метод закрывает файловый дескриптор.
    */
    protected function close()
    {
        return @fclose($this->file) ?
            true : false;
    }

    /**
     * @param string $src
     * @param bool|int $locLnLim
     * @param bool|int $urlLnLim
     * @return \Generator
    */
    static public function content($src = '',  $locLnLim = false, $urlLnLim = false )
    {
        $ob = new static ( $src,  $locLnLim, $urlLnLim );

        /**
         * Выбросить ошибку.
        */
        if ( !$ob->state ) {
            yield false;
        }

        /**
         * Выбросить содержимое файла(ов).
        */
        if ($ob->rLength == self::READ_ALL) {
            yield $ob->data;
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        foreach ($ob->getContents() as $line) {
            yield $line;
        }

        /**
         * Закрыть файловый дескриптор.
        */
        $ob->close();
    }
} 