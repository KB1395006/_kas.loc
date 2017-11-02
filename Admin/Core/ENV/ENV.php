<?php
/**
 * Управляющий класс-обёртка классов APP и CMS.
 * Класс выбирает ресурсы одного из классов взависимости от
 * среды выполнения.
*/
class ENV
{
    /**
     * Ключи окружения.
    */
    const M_PATH  = 'M_PATH';
    const V_PATH  = 'V_PATH';
    const C_PATH  = 'C_PATH';

    /**
     * Стэк окружения.
    */
    protected $EnvArr = array
    (
        self::M_PATH,
        self::V_PATH,
        self::C_PATH,
    );

    /**
     * Текущая среда выполнения.
    */
    protected $ENV = '';

    /**
     * Значение данных свойств устанавливается в
     * соответствии со средой выполнения.
    */
    public $M_PATH = '';
    public $V_PATH = '';
    public $C_PATH = '';

    /**
     * Текущая директория окружения. APP/ или CMS/
    */
    public $DIR    = '';

    protected function __construct()
    {
        /**
         * Выбираем методы окружения.
        */
        switch(\kas::isCMS())
        {
            case true:
                $this->ENV = '\CMS';
                break;

            case false:
                $this->ENV = '\APP';
                break;
        }

        $this->setEnv();

        return false;
    }

    /**
     * Определить набор свойств и методов окружения.
    */
    protected function setEnv()
    {
        /**
         * Установить окружение.
        */
        $ENV  = $this->ENV;
        /**
         * Установить текущую директорию окружения. APP/ или CMS/
        */
        $this->DIR = @str_replace('\\', '', $this->ENV) . '/';

        foreach($this->EnvArr as $const)
        {
            /**
             * Установить параметры окружения.
            */
            $constData = @constant("$ENV::$const");

            /**
             * Если константа отсутствует
            */
            if (is_null($constData)) {
                continue;
            }

            $this->{$const} = $constData;
        }
    }

    /**
     * @return $this
    */
    static public function _()
    {
        $ob = new static();
        return $ob;
    }
} 