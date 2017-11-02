<?php

/**
 * Драйвер Базы данных.
*/
namespace Core\Classes\DB;

class DB
{
    /**
     * Константы указывают на выбор параметров подключения.
    */
    const DB_CUSTOM_SETTINGS    = 1;
    const DB_DEFAULT_SETTINGS   = 2;
    /**
     * Тип подключения.
    */
    const DSN_DB                = 1;
    const DSN_MYSQL             = 2;
    /**
     * Кодировка
    */
    const ENC                   = KAS_ENCODING;

    protected $dbHost           = KAS_DB_HOST;
    protected $dbName           = KAS_DB_NAME;
    protected $dbUser           = KAS_DB_USER;
    protected $dbPass           = KAS_DB_PASS;
    protected $dbDefaultUser    = KAS_DB_DEFAULT_USER;
    protected $dbDefaultPass    = KAS_DB_DEFAULT_PASS;

    /**
     * DSN
    */
    protected $dsn;
    /**
     * @var $dbh \PDO
    */
    protected $dbh;
    /**
     * Параметры подключения к БД.
    */
    protected $DBSettings = [];
    /**
     * Содержит информацию о последней ошибке.
    */
    protected $lastError;
    /**
     * Тип используемых параметров (пользовательские или по умолчанию).
    */
    protected $connectionType;

    protected function __construct()
    {
        $this->setDSN();
        $this->setDBSettings();
    }

    /**
     * Метод устанавливает имя источника данных или DSN,
     * содержащее информацию, необходимую для подключения
     * к базе данных.
     * @param int $connectionType
     * @return bool
     */
    protected function setDSN($connectionType = 1)
    {
        switch ($connectionType)
        {
            case self::DSN_DB:
                $this->dsn = "mysql:host={$this->dbHost}";
                break;
            case self::DSN_MYSQL:
                $this->dsn = "mysql:host={$this->dbHost};dbname={$this->dbName}";
                break;
        }

        return true;
    }

    /**
     * Метод устанавливает параметры подключения к БД.
    */
    protected function setDBSettings()
    {
        //Пользовательские параметры.
        $this->DBSettings[self::DB_CUSTOM_SETTINGS] = [
            $this->dsn,
            $this->dbUser,
            $this->dbPass,
            self::DB_CUSTOM_SETTINGS
        ];

        //Параметры по умолчанию.
        $this->DBSettings[self::DB_DEFAULT_SETTINGS] = [
            $this->dsn,
            $this->dbDefaultUser,
            (string) $this->dbDefaultPass,
            self::DB_DEFAULT_SETTINGS
        ];

        return true;
    }
    /**
     * Метод получает дескриптор подключения к БД.
     * @return bool;
    */
    protected function getDBH()
    {
        return $this->MySQLConnect($this->DBSettings[self::DB_CUSTOM_SETTINGS]) ?:
            $this->MySQLConnect($this->DBSettings[self::DB_DEFAULT_SETTINGS]);
    }

    /**
     * Метод устанавивает соединение с MySql.
     * @param array $settings
     * @return bool
    */
    protected function MySQLConnect($settings = [])
    {
        /**
         * Дескриптор уже существует.
        */
        if ($this->DBHExists()) {
            return true;
        }

        if (!\kas::arr($settings))
        {
            \kas::ext('401 Empty DBSettings');
            return false;
        }

        try {
            $dbh = new \PDO($settings[0], $settings[1], $settings[2]);
        }

        catch (\PDOException $e)
        {
            $this->lastError = $e->getMessage();
            return false;
        }

        /**
         * Сохранить дескриптор.
        */
        $this->dbh = $dbh;
        /**
         * Установить тип подключения.
        */
        $this->connectionType = $settings[3];
        /**
         * Установить атрибуты подключения.
        */
        $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->dbh->setAttribute(\PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'UTF8'");

        return true;
    }

    /**
     * Метод устанавливает соединение с БД.
     * @return bool | object \PDO
    */
    protected function DBConnect()
    {
        $this->setDSN(self::DSN_MYSQL);

        try
        {
            $dbh = new \PDO
            (
                $this->dsn,
                $this->dbUser,
                $this->dbPass,
                [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'']
            );

            $dbh->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
        }

        catch (\PDOException $e)
        {
            $this->lastError = $e->getMessage();
            return false;
        }

        $this->dbh = $dbh;
        return true;
    }

    /**
     * Метод выполняет проверку дескриптора.
    */
    protected function DBHExists()
    {
        return $this->dbh && $this->dbh instanceof \PDO ?
            true: false;
    }

    /**
     * Метод создает нового пользователя БД.
     * Новый пользователь будет создан со всеми привелегиями.
     *
     * @param bool $login
     * @param bool $psw
     * @param bool $dbName
     * @return bool
    */
    protected function createDBUser
    (
        $login = false,
        $psw = false,
        $dbName = false
    )
    {

        if (!$this->DBHExists()) {
            return false;
        }

        /**
         * Использовать аргументы или параметры по
         * умолчанию.
        */
        \kas::str($login)       ?:
            $login = $this->dbUser;

        \kas::str($psw)    ?:
            $psw = $this->dbPass;

        \kas::str($dbName)      ?:
            $dbName = $this->dbName;

        try
        {
            $this->dbh->exec("GRANT ALL ON `{$this->dbName}`.* TO '{$login}'@'$this->dbHost' 
                IDENTIFIED BY '{$psw}' WITH GRANT OPTION");
        }

        catch (\PDOException $e)
        {
            /**
             * Неудалось создать пользователя БД.
            */
            $this->lastError = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Метод создает БД по заданному дескриптору
     * подключения.
     *
     * @param string $dbName
     * Имя БД может быть передано в качестве аргумента либо
     * быть определено параметрами платформы.
     *
     * @return bool
    */
    protected function createDB($dbName = '')
    {
        if (!$this->DBHExists()) {
            return false;
        }

        (string) $dbName ?:
            $dbName = $this->dbName;

        /**
         * Переводить в нижний регистр.
        */
        $dbName = mb_strtolower($dbName, self::ENC);

        try {
            $this->dbh->exec("CREATE DATABASE IF NOT EXISTS  `{$dbName}`");
        }

        catch (\PDOException $e)
        {
            $this->lastError = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Метод останавливает выполнение ПО, делает запись в журнале ошибок и
     * выводит информацию об ошибке клиенту.
     * @param int $code
     * @return string.
    */
    protected function showError($code = 401) {
        return \kas::ext(implode(' ', [$code, $this->lastError]));
    }

    /**
     * Конфигурации класса
     * @return bool| \PDO
    */
    protected function config()
    {
        /**
         * Попытка соединения с БД.
        */
        if ($this->DBConnect()) {
            return $this->dbh;
        }

        /**
         * Получить дескриптор PDO.
        */
        if 
        (
            !$this->getDBH()
        )
        {
            $this->showError(402);
            return false;
        }

        /**
         * Проверить тип соединения.
        */
        switch ($this->connectionType)
        {
            /**
             * Создать пользователя и БД, если подключение было
             * выполнено с использованием пользовательских параметров.
            */
            case self::DB_DEFAULT_SETTINGS:

                /**
                 * Создать БД и пользователя.
                */
                if
                (
                    !$this->createDB()      ||
                    !$this->createDBUser()
                )
                {
                    $this->showError(402);
                    return false;
                }
                
                break;

            case self::DB_CUSTOM_SETTINGS:
                break;
        }

        /**
         * Ошибка подключения к БД.
        */
        if (!$this->DBConnect())
        {
            $this->showError(402);
            return false;
        }

        return $this->dbh;
    }

    protected function sqlByConst() {}

    /**
     * Метод возвращает дескриптор подключения к БД.
    */
    static public function dbh()
    {
        $ob = new static();
        return $ob->config();
    }

    /**
     * Метод выполняет sql-запрос по константе KAS_SQL_QX
     * @param int $sqlConstNum
     * @return mixed
    */
    static public function sql($sqlConstNum = 0) {
        return $sqlConstNum;
    }
} 