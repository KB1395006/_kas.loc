<?php

namespace Core\Classes\CMD;
use Core\Classes\CMD as C;

/**
 * Командный интерпритатор платформы.
 *
 * Примеры запуска в программной среде:
 * \Core\Classes\CMD\CMD::exec('%CMD:TPL_PATH%');
 *
 * Запуск внутри html шаблона:
 * %CMD:EXT_PATH%
 *
*/
class CMD
{
    /**
     * Набор команд интерпритатора.
    */
    const V_PATH    = 'PUBLIC_PATH';
    const TPL_PATH  = 'TPL_PATH';
    const EXT_PATH  = 'EXT_PATH';

    /**
     * Служебные константы класса.
    */
    const CMD       = KAS_CMD;
    const COMMAND   = 1;
    const DATA      = 2;

    /**
     * Список внешних команд.
    */
    protected $cmdList = [
        self::V_PATH    => '_cmdPublicPath',
        self::TPL_PATH  => '_cmdTplPath',
        self::EXT_PATH  => '_cmdTplExtPath',
    ];

    /**
     * Внешняя команда.
    */
    protected $cmd;
    /**
     * Содержимое подлежащее интерпритации.
    */
    protected $data;
    /**
     * Разделитель команд.
    */
    protected $delim = "\n";
    /**
     * Шаблон интерпритации группы команд.
    */
    protected $rExpMultiTpl = '/\%CMD\:[A-Z0-9_]+\%/';
    /**
     * Тип данных.
    */
    protected $type;


    /**
     * @param bool|string $mixed
    */
    protected function __construct($mixed = false)
    {
        /**
         * Исходное содержимое.
        */
        $this->mixed = $mixed;
        /**
         * Конфигурация списка внешних команд.
        */
        $this->cmdList();
        /**
         * Запустить интерпритатор.
        */
        $this->interpreter();
    }

    /**
     * Метод осуществляет интерпритацию внешних команд.
    */
    protected function interpreter()
    {
        /**
         * Только строковый тип данных.
        */
        if (!\kas::str($this->mixed)) {
            return false;
        }

        /**
         * Определить тип данных.
        */
        preg_match('/^' . self::CMD . '/', $this->mixed) ?
            $this->cmd  = trim($this->mixed) :
            $this->data = $this->mixed;

        $this->cmd ?
            $this->type = self::COMMAND :
            $this->type = self::DATA;

        return true;
    }

    protected function isCmd($cmd)
    {
        if (!\kas::str($cmd)) {
            return false;
        }

        return $this->cmdList[$cmd] ?
            true : false;
    }

    /**
     * Метод проверяет существование метода отвечающего за
     * реализацию внешней команды.
     *
     * @param string $cmd
     * @return bool
    */
    protected function prepareCmd($cmd)
    {
        if (!$cmd) {
            return false;
        }

        return method_exists(__CLASS__, $this->cmdList[$cmd]) ?
            true : false;
    }

    /**
     * @param $cmd
     * @return mixed
    */
    protected function getCmd($cmd) {
        return $this->{$this->cmdList[$cmd]}();
    }

    /**
     * Метод подготавливает список внешних команд.
    */
    protected function cmdList()
    {
        foreach ($this->cmdList as $k => $v)
        {
            unset($this->cmdList[$k]);
            $this->cmdList[self::CMD . $k] = $v;
        }

        return true;
    }

    /**
     * Метод осуществляет проверку и выполнение команды.
     * @param bool|string $cmd
     * @return mixed
    */
    protected function execCmd($cmd = false)
    {
        $cmd ?: $cmd = $this->cmd;

        if ($this->isCmd($cmd) && $this->prepareCmd($cmd)) {
            return $this->getCmd($cmd);
        }

        /**
         * Команда неопределена.
        */
        return false;
    }

    /**
     * Метод интерпритирует группу команд.
     * @return bool|string
    */
    protected function multiExecCmd()
    {
        // Проверить, есть ли команды для интерпритации
        if
        (
            !$this->data                                    ||
            count(explode(self::CMD, $this->data, 2)) < 2
        )
        {
            return $this->data;
        }

        /**
         * Поиск внешних команд.
        */
        preg_match_all($this->rExpMultiTpl, $this->data, $cmdList);

        \kas::arr($cmdList) ?
            $cmdList = current($cmdList) : false;

        /**
         * Неудалось интерпритировать команды.
        */
        if (!\kas::arr($cmdList))  {
            return false;
        }

        $cmdKeyList = [];
        $cmdValList = [];

        foreach ($cmdList as $k => $cmd)
        {
            /**
             * Удалить маркеры.
            */
            $execCmd = str_replace('%', '', $cmd);
            /**
             * Выполнить команду.
            */
            $data = $this->execCmd($execCmd);

            if (!$data) {
                continue;
            }

            $cmdKeyList[] = $cmd;
            $cmdValList[] = $data;
        }

        /**
         * Выполнить преобразование команд.
        */
        $this->data = str_replace($cmdKeyList, $cmdValList, $this->data);
        return $this->data;
    }

    protected function config()
    {
        switch ($this->type)
        {
            case self::COMMAND :
                return $this->execCmd();
                break;

            case self::DATA :
                return $this->multiExecCmd();
                break;
        }

        return false;
    }

    /**
     * Методы интерпритатора.
    */

    protected function _cmdPublicPath() {
        return \ENV::_()->V_PATH . '_public/';
    }

    /**
     * Метод возвращает директорию шаблонов.
    */
    protected function _cmdTplPath() {
        return $this->_cmdPublicPath() . 'Tpl/';
    }

    /**
     * Метод возвращает директорию шаблонов исключений.
    */
    protected function _cmdTplExtPath() {
        return $this->_cmdTplPath() . 'Extensions/';
    }
    
    /**
     * @param mixed $mixed
     * @return bool
    */
    static public function exec($mixed = false)
    {
        $ob = new static($mixed);
        return $ob->config();
    }
}