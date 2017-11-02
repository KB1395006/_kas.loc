<?php

namespace Core\Classes\DB;

class Tables
{

    const IPK               = 'INTEGER(11) PRIMARY KEY AUTO_INCREMENT NOT NULL';
    const CT                = 'CREATE TABLE IF NOT EXISTS';
    const VC255             = 'varchar(255) NOT NULL';
    const FL10              = 'float(10) NOT NULL';
    const INT               = 'INTEGER NOT NULL';
    const DATE              = 'DATE';
    const TIME              = 'TIME';
    const COL               = '%COLUMNS%';
    const TXT               = 'TEXT';
    const LTXT              = 'LONGTEXT';

    protected $sqlCmd       = '';
    protected $currentCmd   = '';
    protected $columns      = [];

    protected $_types       = [];
    protected $types        = [];

    protected function __construct()    {
        $this->setTypes();
    }

    protected function setTypesList()
    {
        $this->_types[self::IPK]     = [ID];
        $this->_types[self::INT]     = [PID, CID, FID, GID, CUST_ID, CUR_ID, NS_ID];
        $this->_types[self::FL10]    = [MKP, PRC, CUR_V];

        $this->_types[self::VC255]   = [NAME, TITLE, TYPE, IMG_G,
            IMG_I, IMG_L, IMG_S, IMG_M, CODE, VC, SRC, PATH, MIME, MODEL, STATUS, URI, C_NAME];

        $this->_types[self::TXT]     = [DESC_S, URL_SET];
        $this->_types[self::LTXT]    = [DESC_M, DESC_L];
        $this->_types[self::DATE]    = [DATE];
        $this->_types[self::TIME]    = [TIME];

        return true;
    }

    /**
     * Метод устанавливает типы полей SQL по умолчанию,
     * если типы полей не определены.
    */
    protected function setTypes()
    {
        $this->setTypesList();
        foreach ($this->_types as $k => $v)
        {
            foreach ($v as $column) {
                $this->types[$column] = $k;
            }
        }
    }

    protected function clear()
    {
        $this->columns = [];
        return true;
    }

    /**
     * @param string $tName
     * @param array $columns
     * @param array $dataTypes
     * @return Tables
     *
     * Метод создает таблицу $tName с набором полей $columns
     * и значениями данных полей $dataTypes.
    */
    public function create($tName = '', $columns = [], $dataTypes = [])
    {
        if
        (
            !\kas::str($tName)                      ||
            !\kas::arr($columns)
        )
        {
            return false;
        }

        $this->clear();

        \kas::arr($dataTypes) ?:
            $dataTypes = [];

        $cmd = implode(' ', [self::CT, "`{$tName}` (\r\n", self::COL, ');']);

        foreach ($columns as $k => $column)
        {
            if(!\kas::str($column)) {
                \kas::ext('column type error');
                return false;
            }

            $dt = $dataTypes[$k];
            $dt ?: $dt = $this->types[$column];

            $this->columns[] = "`{$column}` {$dt}";
        }

        $col                 = implode(',' . "\r\n", $this->columns);
        $this->currentCmd    = str_replace(self::COL, $col, $cmd);
        $this->sqlCmd       .= "\r\n\r\n" . $this->currentCmd;

        return $this;
    }

    /**
     * Возвращает сборку таблиц sql
    */
    public function asString() {
        return trim($this->sqlCmd);
    }

    /**
     * Выполнить список команд SQL
    */
    public function exec()
    {
        return \kas::sql()->exec($this->asString()) ?
            true : false;
    }

    static public function run()
    {
        $ob = new static();
        return $ob;
    }
}