<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 30.07.2016
 * Time: 12:27
 */

namespace Core\Classes\Iterator;

/**
 * Данный класс осуществляет обход и обработку
 * массивов любого уровня вложенности.
*/
class MultipleIterator
{
    /**
     * Целевой массив обхода.
    */
    protected $targetArray = [];
    /**
     * Пользовательский обработчик.
    */
    protected $callable;
    /**
     * Аргументы обработчика
    */
    protected $args;

    /**
     * @param array $targetArray
     * Целевой массив для обработки.
     *
     * @param bool|callable $callable
     * Обработчик
     *
     * @param array $argumentsArray
     * Аргументы обработчика
    */
    protected function __construct
    (
        $targetArray        = [],
        $callable           = false,
        $argumentsArray     = []
    )
    {
        $this->targetArray = $targetArray;
        $this->callable = $callable;
        $this->args = $argumentsArray;
    }

    /**
     * Инициализация
    */
    protected function config()
    {
        if
        (
            !\kas::arr($this->targetArray)  ||
            !is_callable($this->callable)
        )
        {
            //\kas::ext('Invalid arguments.');
            return $this->targetArray;
        }

        $this->iterator($this->targetArray);
        return true;
    }

    protected function iterator(&$array = [])
    {
        foreach ($array as $k => $v)
        {
            if (\kas::arr($v))
            {
                $this->iterator($array[$k]);
                continue;
            }

            if (!is_scalar($v) && !is_bool($v)) {
                continue;
            }

            /**
             * Вызов пользовательского обработчика.
            */
            $callable = $this->callable;

            /**
             * Установка значения обработчика.
            */
            $array[$k] = $callable($k, $v, $this->args);

            continue;
        }
    }

    static public function run
    (
        $targetArray        = [],
        $callable           = false,
        $argumentsArray     = []
    )
    {
        $ob = new static
        (
            $targetArray,
            $callable,
            $argumentsArray
        );

        $ob->config();
        return $ob->targetArray;
    }

}