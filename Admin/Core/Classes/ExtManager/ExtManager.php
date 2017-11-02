<?php

/**
 * Обработчик исключений плтформы Extension Manager.
*/
namespace Core\Classes\ExtManager;
use \Core\Classes\CMD;

/**
 * Менеджер исключений платформы.
 * Коды ответа (по принципу кодов ответа HTTP):
 * 
 * 1xx (Info)
 * 2xx (Success)
 * 3xx (Redirection)
 * 
 * 4xx (ClientError) ошибки, которые связаны с 
 * передаваемыми данными клиента. Данные авторизации, настройки, данные 
 * имеющие неверный формат... 
 * 
 * 5xx (ServerError) ошибки, которые связаны с работой ПО.
*/
class ExtManager
{
    /**
     * Использование внешних констант запрещено!
    */
    const KAS_TPL_EXT       = '.tpl';

    /**
     * debug_backtrace
    */
    const FUNC              = 'function';
    const E                 = 'ext';

    const F                 = 'file';
    const L                 = 'line';

    const CMS               = 'CMS';
    const PROJ              = 'PRJ';

    /**
     * Получить путь к шаблону исключения.
    */
    const GET_EXT_PATH      = 1;

    /**
     * Объект окружающей среды.
    */
    protected $ENV;

    /**
     * Контекст вызова: CMS|PROJ
    */
    protected $context      = '';

    protected $eCode        = '';
    protected $eData        = '';
    protected $eMsg         = '';
    protected $eLine        = '';
    protected $eFile        = '';

    protected $delim        = "\r\n";
    protected $eTplPath     = 'Tpl/Extensions/';
    protected $eLogPath     = 'Logs/Extensions.log';

    /**
     * Модификатор, который сообщает классу о
     * необходимости завершения работы всего приложения.
     *
     * false - показать уведомление и остановить работу при наличии
     *         кода исключения.
     * true  - показать уведомление и передать управление
     *         приложению.
    */
    protected $continue = false;

    protected function __construct($eData = '', $continue = true)
    {
        $this->ENV      = \ENV::_();
        $this->eData    = $eData;
        $this->exit   = (bool)($continue);
        $this->context  = $this->ENV->DIR;
    }

    /**
     * Метод разбивает содержимое свойства $this->eData
     * на параметры.
    */
    protected function parseExtData()
    {
        /**
         * Передан идентификатор шаблона.
        */
        if (is_int($this->eData))
        {
            $this->eCode = $this->eData;
            $this->eMsg  = '';
            return true;
        }

        if (!\kas::str($this->eData)) {
            return false;
        }

        /**
         * Исключение может быть передано и
         * без кода исключения.
        */
        switch(preg_match('/\d+/', $this->eData, $matches))
        {
            /**
             * Определить код исключения.
            */
            case true:

                (int)($matches[0]) == 0 ?:
                    $this->eCode = $matches[0];

                /**
                 * Код исключения имеет неверный формат.
                 * Длина кода исключения долна быть равна 3.
                */
                if
                (
                    $this->eCode                   &&
                    strlen($this->eCode)    !== 3
                )
                {
                    return false;
                }

                /**
                 * Получить описание исключения.
                */
                $_tmp = explode($this->eCode ?: ' ', $this->eData);


                if
                (
                    !\kas::arr($_tmp)       ||
                    !\kas::str($_tmp[1])
                )
                {
                    return false;
                }

                $this->eMsg = trim($_tmp[1]);

            break;

            /**
             * Если код исключения небыл передан
            */
            case false:
                $this->eMsg = trim($this->eData);
            break;
        }

        return true;
    }

    /**
     * Метод предоставляет дополнительную информацию о
     * том, где было возбуждено исключение (сценарий, линия).
    */
    protected function getInf()
    {
        /**
         * Обойти стек вызовов и выбрать ту функцию у которой
         * значение ключа args является массивом.
        */
        foreach(debug_backtrace() as $data)
        {
            if ($data[self::FUNC] !== self::E) {
                continue;
            }

            /**
             * Нет данных для обработки.
            */
            if
            (
                !$data[self::L]  ||
                !$data[self::F]
            )
            {
                return false;
            }

            $this->eLine = $data[self::L];
            $this->eFile = $data[self::F];
        }

        return true;
    }

    /**
     * Метод осуществляет запись исключения в журнал.
     * @return bool
    */
    protected function logExt()
    {
        $this->context  = 'ENV:'  . $this->context;
        $this->eMsg     = 'MSG:'  . $this->eMsg;
        $this->eLine    = 'LINE:' . $this->eLine;
        $this->eFile    = 'FILE:' . $this->eFile;

        $eInf =
            [
                $this->context,
                date(KAS_DATE_FORMAT),
                (int) $this->eCode,
                $this->eMsg,
                $this->eLine,
                $this->eFile
            ];

        $eInf = implode(', ', $eInf) . $this->delim;

        return @file_put_contents
        (
            $this->eLogPath,
            $eInf,
            FILE_APPEND | LOCK_EX
        )
            ? true : false;

    }

    /**
     * Метод возвращает путь к шаблону исключения.
     * @return bool|string
    */
    protected function getTplPath()
    {
        if
        (
            !$this->eCode               ||
            !is_dir($this->eTplPath)
        )
        {
            return false;
        }

        /**
         * Проверка и запуск шаблона
        */
        $tpl = $this->eTplPath . $this->eCode . self::KAS_TPL_EXT;

        if (!file_exists($tpl)) {
            return false;
        }

        return $tpl;
    }

    /**
     * Данный метод выводит уведомление пользователю
     * в соответствии с заданным кодом исключения.
     * @return bool|string
    */
    protected function showExt()
    {
        $tpl = $this->getTplPath();

        if (!$tpl) {
            return false;
        }      

        /**
         * Тексты интерфейса.
        */
        $content = \kas::st(\kas::st(\kas::load($tpl)), true);

        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $_tmp = \Core\Classes\CMD\CMD::exec($content);

        /**
         * Проверка.
        */
        $_tmp ? $content = $_tmp : false;
        return $content ?: false;
    }

    /**
     * Медод устанавливает пути в соответствии
     * со средой выполнения ENV.
     *
     * @return bool
    */
    protected function setPathes()
    {
        $this->eTplPath = $this->ENV->V_PATH . $this->eTplPath;
        $this->eLogPath = $this->ENV->M_PATH . $this->eLogPath;
        return true;
    }

    protected function config()
    {
        if
        (
            !$this->setPathes()     ||
            !$this->parseExtData()  ||
            !$this->getInf()
        )
        {
            return false;
        }

        /**
         * Запись текущего исключения в журнал.
        */
        $this->logExt();

        /**
         * Вывод контента возможен только при наличии
         * кода шаблона.
        */
        if (!$this->eCode) {
            return true;
        }

        /**
         * Вывод уведомления (если был установлен код исключения).
        */
        print $this->showExt();

        /**
         * Передать управление.
        */
        if (!$this->exit) {
            return true;
        }

        /**
         * Остановить работу приложения.
        */
        exit();
    }

    static public function run($eData = '', $continue = true)
    {        
        $ob = new static($eData, $continue);
        return $ob->config();
    }

    /**
     * Получить путь к шаблону исключения относительно
     * программной среды выполнения.
     * @param string $eData
     * @return bool|string
    */
    static public function getExtensionPath($eData = '')
    {
        $ob = new static($eData, false);

        if
        (
            !$ob->setPathes()     ||
            !$ob->parseExtData()
        )
        {
            return false;
        }

        $tpl = $ob->getTplPath();

        if (!$tpl) {
            return false;
        }

        return $tpl;
    }
} 