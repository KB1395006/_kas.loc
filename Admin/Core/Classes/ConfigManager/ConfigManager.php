<?php
/**
 * Менеджер конфигурации платформы.
*/
namespace Core\Classes\ConfigManager;

class ConfigManager
{
    /**
     * Имя файла конфигурации.
    */
    protected $confFilename     = 'config.ini';
    /**
     * Дирректория файла конфигурации
    */
    protected $confDir          = 'Config/';
    /**
     * Путь к файлу конфигурации.
    */
    protected $confPath         = '';

    /**
     * Входящий параметр, например array(ID = 2)
     * Данный параметр должен иметь ключ и значение по умолчанию.
    */
    protected $option       = [];

    /**
     * Данное свойство информирует класс о необходимости обработать
     * значение $this->option как массив.
     *
     * Для обработки данного аргумента как массива необходимо соблюдать
     * следующий формат представления: 'param_1, param_2, param_3'
    */
    protected $asArray      = '';

    protected $iniData      = '';
    //Массив с параметрами файла конфигурации.

    protected $iniDataArr   = [];

    //Разделитель параметров
    protected $delim        = "\r\n";

    /**
     * Булев тип данных
    */
    protected $boolTrue     = 'true';
    protected $boolFalse    = 'false';

    /**
     * Синтаксис файла конфигураций
    */

    //Раздел файла конфигураций.
    protected $iniSection   = '/\[[^]]+\]/';

    //Комментарии файла конфигураций.
    protected $iniComment   = '/^\;/';

    //Разделитель параметров ( ключ = значение )
    protected $iniDelim     = '/\=/';

    //Разделитель параметров (explode)
    protected $iniDlm       = ' = ';

    //Разделитель формата и шаблон поиска по РВ значения аргумента,
    //который должен быть преобразован в одноуровневый массив.
    protected $asArrDlm     = ',';
    protected $asArrPtrn    = '/,/';

    /**
     * Значение обрабатываемой константы.
    */
    protected $current      = '';

    protected function __construct($option)
    {
        $this->setConfigPath();
        $this->option = $option;
    }

    /**
     * Метод устанавливает путь к файлу конфигурации.
    */
    protected function setConfigPath()
    {
        /**
         * Установить путь к файлу конфигурации.
        */
        $_tmp = \ENV::_()->M_PATH .
            $this->confDir . \ENV::_()->DIR . $this->confFilename;

        if (!file_exists($_tmp))
        {
            /**
             * Сообщить об ошибке.
            */
            \kas::ext("401 Invalid config path {$_tmp}");
            return false;
        }

        $this->confPath = $_tmp;
        return true;
    }

    protected function getIni()
    {
        /**
         * Получить содержимое файла конфигурации.
        */
        $this->iniData = \kas::load($this->confPath);

        if (!$this->iniData) {
            return false;
        }

        $tmp = explode($this->delim, $this->iniData);

        if
        (
            !is_array($tmp) ||
            empty($tmp)
        )
        {
            return false;
        }



        foreach($tmp as $v)
        {
            if
            (
                preg_match($this->iniSection, $v)   ||
                preg_match($this->iniComment, $v)
            )
            {
                continue;
            }

            $v_tmp = \kas::ai(explode($this->iniDlm, $v));

            if
            (
                $v_tmp->count() == 2    &&
                !empty($v_tmp[0])
            )
            {
                $this->iniDataArr[ trim($v_tmp[0]) ] = trim($v_tmp[1]);
                continue;
            }

            continue;
        }

        return $this->iniDataArr;
    }

    /**
     * Фильтр преобразовывает данные типа string в
     * булев тип.
    */
    protected function filterValidateBool()
    {
        /**
         * Только для строковых типов данных.
        */
        if (!\kas::str($this->current)) {
            return null;
        }

        if ($this->current === $this->boolTrue)
        {
            $this->current = true;
            return true;
        }

        if ($this->current === $this->boolFalse)
        {
            $this->current = false;
            return false;
        }

        return null;
    }

    protected function filterValidateInt()
    {
        /**
         * Только для строковых типов данных.
        */
        if
        (
            is_bool($this->current)                     ||
            !\kas::str($this->current)                  ||
            !preg_match('/^[0-9]+$/', $this->current)
        )
        {
            return false;
        }

        $this->current = (int) $this->current;
        return true;
    }

    protected function filterValidateArray()
    {
        /**
         * Если текущий параметр является массивом.
        */
        if (is_array($this->current))
        {
            $this->current = serialize($this->current);
            return true;
        }

        if
        (
            !$this->current                                   ||
            !is_string($this->current)                        ||
            !preg_match($this->asArrPtrn, $this->current)
        )
        {
            return false;
        }

        /**
         * Данные, которые были преобразованы в массив.
        */
        $newPrmArr = @explode($this->asArrDlm, $this->current);

        if (!is_array($newPrmArr)) {
            return false;
        }

        /**
         * Очистка от пробелов
        */
        foreach($newPrmArr as $k => $v)
        {
            if (is_null($v)) continue;
            $newPrmArr[$k] = @trim($v);
        }

        /**
         * Поскольку значения констант могут включать только
         * скалярные типы данных будет использована сериализация.
        */
        $this->current = serialize($newPrmArr);
        return true;
    }

    /**
     * Метод возвращает значение запрашиваемого
     * параметра константы.
    */
    protected function getOpt()
    {
        /**
         * Если не был передан запрашиваемый
         * параметр константы.
        */
        if (!\kas::arr($this->option)) {
            return $this->iniDataArr;
        }

        /**
         * Получить объект константы.
        */
        $cOb    = \kas::ai($this->option);

        /**
         * Получить имя и значение константы.
        */
        $cName  = $cOb->key();

        /**
         * Данное значение является значением по умолчанию и
         * используется в случае отсутствия данного значения в
         * файле конфигурации.
        */
        $this->current = $cOb->current();

        if
        (
            empty($cName)             ||
            empty($this->current)     &&
            !is_bool($this->current)
        )
        {
            return $this->iniDataArr;
        }

        /**
         * Если параметр отсутствует или не верный (вместо ID по ошибке указал IP),
         * вернуть значение по умолчанию.
        */
        if
        (
            is_null($this->iniDataArr[$cName])  ||
            empty($this->iniDataArr[$cName])
        )
        {

            $this->filterValidateBool();
            $this->filterValidateInt();
            $this->filterValidateArray();

            /**
             * Вернуть значение параметра.
            */
            return $this->current;
        }

        /**
         * Перезаписать значение параметра.
        */
        $this->current = $this->iniDataArr[$cName];

        /**
         * Пропустить значение через фильтры.
        */
        $this->filterValidateBool();
        $this->filterValidateInt();
        $this->filterValidateArray();

        return $this->current;
    }

    protected function config()
    {
        if
        (
            !$this->confPath    ||
            !$this->getIni()
        )
        {
            return false;
        }

        $this->getOpt();

        /**
         * Вернуть значение параметра.
        */
        return $this->current;
    }

    static public function run($option = [])
    {
        $ob = new static($option);
        return $ob->config();
    }

    /**
     * Метод устанавливает параметры конфигурации платформы
     * используя для этого класс конфигурации ConfigManager.
     *
     * В качестве аргументов класс принимает параметры
     * $optName, $optValue
     *
     * В качестве значения $optName передается название константы.
     * В качестве значения $optValue можно передавать все типы данных, кроме
     * объектов и ресурсов.
     *
     * Если в качестве значения $optValue был передан массив, метод вернет
     * его строковое представление после сериализации.
     *
     * Примеры работы:
     *
     * \kas::ini('CONSTANT_NAME', 'Some data');
     * \kas::ini('CONSTANT_NAME', [1,2,3]');
     *
     * @param string $optName
     * @param mixed $optValue
     * @return mixed
    */
    static public function set($optName = '', $optValue = false)
    {
        if (!\kas::str($optName)) {
            return false;
        }

        return self::run([$optName => $optValue]);
    }
}