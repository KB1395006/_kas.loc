<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 02.07.2016
 * Time: 16:06
 */

namespace Core\Classes\DB;


/**
 * DATABASE QUERY LIST
 * Класс выполняет построение sql-запросов по идентификатору.
*/
class SimpleSQL
{
    // SQL CMD
    const SEL               = 'SELECT';
    const DEL               = 'DELETE';
    const INS               = 'INSERT INTO';
    const UPD               = 'UPDATE';
    const SET               = 'SET';
    const FR                = 'FROM';
    const WH                = 'WHERE';
    const VAL               = 'VALUES';

    protected function __construct() {}

    protected function argsControl($t = '', $c = [], $v = [])
    {
        if
        (
            !\kas::str($t)              ||
            !\kas::arr($c)              ||
            !\kas::arr($v)
        )
        {
            return false;
        }

        return true;
    }

    // Конструктор WHERE
    protected function w($w)
    {
        if (!\kas::arr($w)) {
            return $w;
        }

        for ($i = 0; $i < count($w) - 1; $i++) {
            $w[$i] .= ' = ? AND ';
        }

        $w[] = ' = ?';
        $w = implode('', $w);

        return $w;
    }

    /**
     * @param string $t
     * Название таблицы
     *
     * @param array $c
     * Колонки (ID, NAME...)
     *
     * @param array $v
     * Значения (необходимы для проверки правильности запроса)
     *
     * @param string|array $w
     * Условия поиска, имеет 2 типа:
     * 
     * String: ID > ?, PID >= ?...
     * Array: [ID, PID]
     * 
     * Во втором случае метод будет интерпритировать 
     * выражение [ID, PID], как ID = ?, PID = ?
     * 
     * @return bool|string
    */
    public function sel($t = '', $c = [], $v = [], $w = '')
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $unused = $v;

        if (!$this->argsControl($t, $c, $c))
        {
            \kas::ext('Arguments error');
            return false;
        }

        $w      = $this->w($w);
        $sql    = implode(' ', [self::SEL, implode(', ', $c), self::FR, $t, self::WH, $w]);
        return $sql;
    }

    public function ins($t = '', $c = [])
    {
        if
        (
            !$this->argsControl($t, $c, $c)
        )
        {
            \kas::ext('Arguments error');
            return false;
        }

        $cStr = '(' . @implode(', ', $c) . ')';

        if (!$cStr) {
            \kas::ext('Argument type error');
            return false;
        }

        $vStr = implode(', ', array_fill(0, count($c), '?'));
        $vStr = '(' . $vStr . ')';


        $cmd = implode(' ', [self::INS, $t, $cStr, self::VAL, $vStr]);
        return $cmd;
    }

    public function upd($t = '', $c = [], $v = [], $w = '')
    {
        $cnt    = count($c);
        $cStr   = '';
        $mod    = [' = ?, ', ' = ?'];

        if ( !$this->argsControl($t, $c, $v) )
        {
            \kas::ext('Arguments error');
            return false;
        }

        foreach ($c as $k => $col)
        {
            $k == $cnt - 1 ?
                $cStr .= $col . $mod[1] :
                $cStr .= $col . $mod[0];

            continue;
        }

        $w      = $this->w($w);
        $sql    = implode(' ', [self::UPD, $t, self::SET, $cStr, self::WH, $w]);
        return $sql;
    }


    public function del($t = '')
    {
        if (!\kas::str($t)) {
            return false;
        }

        $sql = @implode(' ', [self::DEL, self::FR, $t, self::WH, '']);
        return $sql;
    }


    static public function run()
    {
        $ob = new static();
        return $ob;
    }
}