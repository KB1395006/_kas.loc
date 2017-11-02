<?php
/**
 * Класс осуществляет загрузку файлов из
 * источника src в директорию назначения dst.
*/

class FileLoader
{
    /**
     * @param string
     * Источник загрузки.
    */
    protected $src      = '';
    /**
     * @param string
     * Целевой источник.
    */
    protected $dst      = '';
    /**
     * Объект загружаемого ресурса.
    */
    protected $srcOb;
    /**
     * Буффер загрузки файла.
    */
    protected $buffer   = '';
    /**
     * Максимальный размер буффера (байт).
     * После переполнения буффера идет очистка.
     * Оптимальный размер буффера 5КБ.
    */
    protected $bufferLn = 5120;
    /**
     * @param int $rTime
     *
     * Временной промежуток (дней) до следующей
     * перезагрузки файла $this->src.
    */
    protected $rTime    = 0;
    protected $day      = 86400;

    /**
     * @param string $src
     * @param string $dst
     * @param int $reloadTime
    */
    protected function __construct($src = '', $dst = '', $reloadTime = 0)
    {
        $this->src      = trim($src);
        $this->dst      = trim($dst);
        $this->rTime    = (int)($reloadTime);
    }

    protected function getSrc()
    {
        $this->srcOb = \FileReader::content($this->src);
        return is_object($this->srcOb);
    }

    protected function checkDst()
    {

        $_tmp = @explode('/', $this->dst);

        if (!is_array($_tmp))
        {
            Event::log(__CLASS__, __LINE__, "Creating path error.");
            return false;
        }


        switch (count($_tmp))
        {
            case 1:
                $_tmp = './';
            break;

            default:

                /**
                 * Удалить название файла.
                */
                unset ($_tmp[count($_tmp) - 1]);
                /**
                 * Собрать директории.
                */
                implode('/', $_tmp);

            break;
        }

        /**
         * Проверить существование.
        */
        if (!is_dir($_tmp))
        {
            Event::log(__CLASS__, __LINE__, "Local error.");
            return false;
        }

        /**
         * Если файла не существует необходимо
         * загрузить.
        */
        if (!file_exists($this->dst)) {
            return true;
        }

        /**
         * Если перезагрузка файлу не нужна, работа
         * будет завершена.
        */
        if ( !$this->rTime() )
        {
            Event::log(__CLASS__, __LINE__, "Recovering time limit.");
            return false;
        }

        /**
         * Удалить старый файл перед загрузкой.
        */
        unlink($this->dst);
        return true;
    }

    protected function progress()
    {
        return true;
    }

    protected function fileExists($filename = false)
    {
        return $filename && is_string($filename) && file_exists(trim($filename)) ?
            $filename : false;
    }

    /**
     * Данный метод осуществляет проверку необходимости
     * обновления запрашиваемого файла согласно заданному
     * времени обновления.
     *
     * Метод возвращает true, если файл требует
     * перезагрузки.
     *
     * Список аргументов для динамического
     * обновления.
     *
     * @param bool|string $filename
     * @param bool|string $rTime
     * @return bool
    */
    protected function rTime($filename = false, $rTime = false)
    {

        /**
         * Проверяем существование аргумента.
        */
        $this->fileExists($filename) ?
            $this->dst = $filename : false;

        !is_int($rTime) ?:
            $this->rTime = $rTime;

        /**
         * Перезагрузить целевой файл.
        */
        if (!$this->rTime) {
            return true;
        }

        $_tmp = @filectime($this->dst);

        /**
         * Произошла ошибка.
        */
        if (!$_tmp) {
            return false;
        }

        $rTime =  time() - ($_tmp + $this->day * $this->rTime);

        /**
         * Если $rTime < 0, значит еще
         * осталось некоторое количество дней до
         * следующей перезагрузки.
        */
        return $rTime < 0 ?
            false : true;
    }

    /**
     * Запись содержимого буффера.
     * @param $data
     * @param $flags
     * @return bool
    */
    protected function write($data, $flags)
    {
        /**
         * Отгрузка содержимого.
        */
        $success = @file_put_contents
        (
            $this->dst,
            $data,
            $flags
        );

        return $success ?
            true : false;
    }

    /**
     *
    */
    protected function load()
    {

        foreach ($this->srcOb as $k => $data)
        {
            /**
             * Если буффер переполнен.
            */
            if (mb_strlen($this->buffer, 'UTF-8') > $this->bufferLn)
            {
                $success = $this->write
                (
                    $this->buffer,
                    FILE_APPEND|LOCK_EX
                );

                /**
                 * Очистить буффер.
                */
                $this->buffer = '';

                /**
                 * Ошибка загрузки
                */
                if (!$success)
                {
                    Event::log(__CLASS__, __LINE__, "Loading error.");
                    return false;
                }
            }

            /**
             * Идет заполнение буффера.
            */
            $this->buffer .= $data;
        }

        /**
         * Запись поледней итерации.
        */
        $success = $this->write
        (
            $this->buffer,
            FILE_APPEND|LOCK_EX
        );

        /**
         * Очистить буффер.
        */
        $this->buffer = '';

        return $success ?
            true : false;
    }

    protected function config()
    {
        if
        (
            !$this->getSrc()    ||
            !$this->checkDst()
        )
        {
            Event::log(__CLASS__, __LINE__, "Arguments error.");
            return false;
        }

        return $this->load() ?
            true : false;

    }

    static public function run($src = '', $dst = '', $reloadTime = 0)
    {
        $ob = new static($src, $dst, $reloadTime);
        return $ob->config();
    }

    /**
     * Метод определяет необходимость перезагрузки
     * фрагмента согласно параметру $this->rTime.
     * Если передаваемое имя файла не существует,
     * метод интерпритирует это как необходимость
     * создать новый фрагмент.
     *
     * @param bool|string $dst
     * @param int $rTime
     * @return bool
    */
    static public function mustReload($dst = false, $rTime = 0)
    {
        $ob = new static(false, $dst);

        return file_exists($ob->dst) ?
            $ob->rTime($ob->dst, $rTime) : true;
    }


} 