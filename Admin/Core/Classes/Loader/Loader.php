<?php

namespace Core\Classes\Loader;

class Loader
{
    /**
     * Данная константа используется, если класс
     * запрашивает проект а не платформа.
    */
    const _CMS          = '../';
    const PRJ           = '../Admin/';

    /**
     * Название библиотекти CMS KAS.
    */
    const KAS           = 'kas';
    const KAS_PATH      = 'Core/LIB/';

    /**
     * Главный управляющий класс приложения.
    */
    const APP           = 'APP';
    const APP_PATH      = 'Core/ENV/APP/';

    /**
     * Управляющий класс платформы.
    */
    const CMS           = 'CMS';
    const CMS_PATH      = 'Core/ENV/CMS/';

    /**
     * Управляющий класс срыды выполнения.
     * Является оберткой для (APP & CMS)
    */
    const ENV           = 'ENV';
    const ENV_PATH      = 'Core/ENV/';

    /**
     * Рабочая среда CMS
    */
    const KAS_CMS_PATH      = '/Admin/_public/index.php';
    /**
     * Рабочая среда проекта
    */
    const PROJ_PATH     = '/www/index.php';

    /**
     * Сокр. $_SERVER['REQUEST_URI']
    */
    protected $uri      = '';
    /**
     * Сокр. $_SERVER['HTTP_HOST']
    */
    protected $host     = '';
    /**
     * Сокр. $_SERVER['PHP_SELF']
    */
    protected $self     = '';
    /**
     * Имя класса передаваемое вместе с
     * пространством имен.
    */
    protected $class    = '';
    /**
     * Путь к файлу класса.
    */
    protected $path     = '';
    protected $ext      = '.php';

    /**
     * Среда запуска класса.
     * CMS или проект.
    */
    protected $rEnv     = '';

    /**
     * Пути подключения
    */
    protected $pathTypes = [
        self::_CMS => self::KAS_CMS_PATH,
        self::PRJ => self::PROJ_PATH
    ];

    /**
     * @param string $class
    */
    protected function __construct($class = '')
    {
        /**
         * Название подключаемого класса (вместе с namespace).
        */
        $this->class    = $class;
        /**
         * Параметры сервера.
        */
        $this->uri      = $_SERVER['REQUEST_URI'];
        $this->host     = $_SERVER['HTTP_HOST'];
        $this->self     = $_SERVER['PHP_SELF'];
        /**
         * Определить среду выполнения.
        */
        $this->setRequireEnv();
    }

    protected function slash($str = '')
    {
        if (!is_string($str)) {
            return $str;
        }

        return str_replace('/', '\/', $str);
    }

    /**
     * Метод определяет среду подключения класса.
     * Классы могут быть запущены относительно двух рабочих сред.
     * Рабочая среда проекта и рабочая среда CMS.
     *
     * @return bool;
    */
    protected function setRequireEnv()
    {
        /**
         * Установить среду подключения класса.
        */
        foreach ($this->pathTypes as $type => $path)
        {
            $path = $this->slash($path);

            if (preg_match('/' . $path . '/', $this->self)) {
                $this->rEnv = $type;
                return true;
            }
        }

        return false;
    }

    /**
     * Метод возвращает true, если запуск осуществляется
     * из контекста CMS KAS.
     *
     * @return bool
    */
    protected function isCMS() {
        return $this->rEnv == self::_CMS ?
            true : false;
    }

    /**
     * Метод возвращает true, если запуск осуществляется
     * из контекста проекта.
     *
     * @return bool
    */
    protected function isPROJ()
    {
        return $this->rEnv == self::PRJ ?
            true : false;
    }

    protected function defineClass()
    {
        $_tmp = explode('\\', $this->class);

        /**
         * Неудалось подключить запрашиваемый класс.
        */
        if (!$_tmp) {
            return false;
        }

        /**
         * Преобразовать в путь к файлу
         * запрашиваемого класса.
        */
        $path = implode('/', $_tmp);

        switch($path)
        {
            case self::KAS:

                /**
                 * Название класса в верхнем регистре.
                */
                $cName = strtoupper($path);
                /**
                 * Библиотека доступна в глобальном
                 * пространстве имен.
                */
                $path = implode
                (
                    [
                        $this->rEnv,
                        self::KAS_PATH,
                        $cName,
                        $this->ext
                    ]
                );

            break;

            case self::APP:

                /**
                 * Класс доступен в глобальном
                 * пространстве имен.
                */
                $path = implode
                (
                    [
                        $this->rEnv,
                        self::APP_PATH,
                        $path,
                        $this->ext
                    ]
                );

            break;

            case self::CMS:

                /**
                 * Класс доступен в глобальном
                 * пространстве имен.
                 */
                $path = implode
                (
                    [
                        $this->rEnv,
                        self::CMS_PATH,
                        $path,
                        $this->ext
                    ]
                );

            break;

            case self::ENV:


                /**
                 * Класс доступен в глобальном
                 * пространстве имен.
                 */
                $path = implode
                (
                    [
                        $this->rEnv,
                        self::ENV_PATH,
                        $path,
                        $this->ext
                    ]
                );

                break;

            default:

                /**
                 * Соединить путь с расширением и
                 * средой выполнения класса.
                */
                $path = implode([$this->rEnv, $path, $this->ext]);

            break;
        }

        if ( !file_exists($path) ) 
        {
            //Возбудить исключение.
            return false;
        }

        $this->path = $path;
        return true;
    }

    protected function config()
    {
        if
        (
            !is_string($this->class)    ||
            !$this->defineClass()
        )
        {
            return false;
        }


        /** @noinspection PhpIncludeInspection */
        require_once $this->path;
        return true;
    }

    /**
     * @param string $class
    */
    static public function run($class = '')
    {
        $ob = new static($class);
        return $ob->config();
    }
    
    static public function _isCMS() 
    {
        $ob = new static();
        $ob->setRequireEnv();
        return $ob->isCMS();
    }
    
    static public function _isPROJ()
    {
        $ob = new static();
        $ob->setRequireEnv();
        return $ob->isPROJ();
    }
} 