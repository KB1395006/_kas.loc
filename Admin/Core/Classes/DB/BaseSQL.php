<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 03.08.2016
 * Time: 10:46
 */

namespace Core\Classes\DB;


class BaseSQL
{
    const CMS = 0;
    const APP = 1;

    /**
     * Session
    */
    const SQL               = 'SQL';
    /**
     * CMD
    */
    const SEL               = 'SELECT';
    const DEL               = 'DELETE';
    const INS               = 'INSERT';
    const UPD               = 'UPDATE';
    const SET               = 'SET';
    const FR                = 'FROM';
    const WH                = 'WHERE';
    const VAL               = 'VALUES';
    const GRB               = 'GROUP BY';
    const DS                = 'DISTINCT';

    /**
     * Ключи массива запросов
    */
    const TABLE             = 'table';
    const COLUMNS           = 'columns';
    const CONDITION         = 'condition';
    const CONDITION_ARR     = 'conditionArr';
    const QUERY             = 'query';

    /**
     * Объект SQL-запросов.
    */
    protected $sqlOb;

    /**
     * Значения внешних аргументов.
    */
    protected $sqlId;
    protected $env;
    protected $sqlCmd;

    
    protected $dbh;

    /**
     * Флаг указывает на тип выполнения запроса.
    */
    protected $multiple = false;

    /**
     *  Параметры выполнения запроса
    */
    protected $params   = [];

    protected function __construct($sqlIdOrCmd  = false, $env = false, $params = [], $multiple = false)
    {
        $this->env      = $env;
        $this->multiple = $multiple;
        $this->params   = $params;

        $this->env();
    }

    /**
     * @param $dbh \PDOStatement
     * @return mixed
    */
    protected function execCmd($dbh)
    {
        if (!$dbh->execute($this->params))
        {
            // Записать исключение в журнал.
            \kas::ext($dbh->errorInfo());
            return false;
        }

        switch($this->multiple)
        {
            case true:
                return $this->dbh;
                break;

            case false:
                return $dbh->fetchAll(\PDO::FETCH_OBJ);
                break;
        }

        return false;
    }

    protected function getSql()
    {
        $sql = $this->get();

        if
        (
            !$sql
        )
        {
            return false;
        }

        \kas::arr($sql)     ?:
            $sql = [$sql];

        \kas::arr($sql[1])  ?
            $this->params = $sql[1] : false;

        if (!\kas::str($sql[0])) {
            return false;
        }

        return $sql;
    }

    /**
     * Метод возвращает объект PDOStatement для текущего
     * sql-идентификатора.
     *
     * @param mixed $sqlId
     * @return bool | object
    */
    public function dbh($sqlId = false)
    {
        !$sqlId ?:
            $this->sqlId = $sqlId;

        $sql = $this->getSql();

        if (!$sql) {
            return false;
        }

        /**@var $dbh \PDO*/
        $dbh        = \kas::dbh();
        $this->dbh  = $dbh->prepare($sql[0]);

        return $this->dbh;
    }

    /**
     * Выполняет команду SQL.
     *
     * @param array $params
     * @return mixed
    */
    public function execute($params = [])
    {
        !\kas::arr($params) ?:
            $this->params = $params;

        switch (is_object($this->dbh))
        {
            case true:

                \kas::arr($params) ?
                    $this->params = $params : false;
                
                return $this->execCmd($this->dbh);

            break;

            case false:

                if (!$this->dbh()) {
                    return false;
                }

                // Выполнить запрос к БД.
                return $this->execCmd($this->dbh);

            break;
        }

        return false;
    }

    /**
     * @param string $sqlCmd
     * @param array $params
     * @return mixed
     *
     * Выполнить произвольную SQL команду.
    */
    public function exec($sqlCmd = '', $params = [])
    {
        if
        (
            !\kas::str($sqlCmd)     ||
            !is_array($params)
        )
        {
            return false;
        }

        /**@var $dbh \PDO*/
        $dbh = \kas::dbh();

        if (!is_object($dbh)) {
            \kas::ext('PDO error', false);
        }

        $sth = $dbh->prepare($sqlCmd);
        
        try {
            $sth->execute($params);
        }
        
        catch (\PDOException $e) {
            \kas::ext($e->getMessage());
            return false;
        }
        
        return preg_match('/^SELECT/', $sqlCmd) ?
            $sth->fetchAll(\PDO::FETCH_ASSOC) : true;
    }

    protected function conf() {
        return $this;
    }

    protected function env()
    {
        $this->env = (int) $this->env;

        if
        (
            $this->env == self::APP     ||
            $this->env == self::CMS
        )
        {
            return true;
        }

        // Установить принудительно
        \kas::isCMS() ?
            $this->env = self::CMS : false;
        \kas::isProj() ?
            $this->env = self::APP : false;

        return true;
    }

    /**
     * Метод используется для множественного
     * выполнения одного sql-запроса.
     * @param array $params
     * @return bool
     */
    public function params($params = [])
    {
        \kas::str($params) ?
            $params = [$params] : false;

        !\kas::arr($params) ?:
            $params = [];

        if (!is_object($this->dbh)) {
            return false;
        }

        return true;
    }

    public function get($sqlId = '', $env = false)
    {
        $this->sqlId ?:
            $this->sqlId = $sqlId;

        $this->env ?:
            $this->env = $env;

        $this->env();

        if
        (
            !\kas::str($this->sqlId)                                    ||
            !\kas::arr($_SESSION[SQL_DATA])                             ||
            !\kas::arr($_SESSION[SQL_DATA][$this->env])                 ||
            !\kas::arr($_SESSION[SQL_DATA][$this->env][$this->sqlId])
        )
        {
            return false;
        }

        return $_SESSION[SQL_DATA][$this->env][$this->sqlId];
    }

    public function set($sqlId  = false, $env = false,
                        $sqlCmd = false)
    {

        $this->sqlId    = $sqlId;
        $this->env      = $env;
        $this->sqlCmd   = $sqlCmd;

        \kas::arr($this->sqlCmd) ?:
            $this->sqlCmd = [$this->sqlCmd];

        $this->env();

        if
        (
            !\kas::str($this->sqlId)    ||
            !\kas::arr($this->sqlCmd)
        )
        {
            return false;
        }

        !\kas::arr($_SESSION[SQL_DATA]) ?
            $_SESSION[SQL_DATA] = [] : false;

        !\kas::arr($_SESSION[SQL_DATA][$this->env]) ?
            $_SESSION[SQL_DATA][$this->env] = [] : false;

        !\kas::arr($_SESSION[SQL_DATA][$this->env][$this->sqlId]) ?
            $_SESSION[SQL_DATA][$this->env][$this->sqlId] = [] : false;

        // Установить sql-запрос
        $_SESSION[SQL_DATA][$this->env][$this->sqlId] = $this->sqlCmd;

        return true;
    }

    /**
     * Упрощенная генерация SQL-запросов.
     * @return \Core\Classes\DB\SimpleSQL
    */
    public function simple() {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        return \Core\Classes\DB\SimpleSQL::run();
    }

    static public function run($sqlIdOrCmd = false, $env = false, $params = [])
    {
        $ob = new static($sqlIdOrCmd, $env, $params);
        return $ob->conf();
    }

}