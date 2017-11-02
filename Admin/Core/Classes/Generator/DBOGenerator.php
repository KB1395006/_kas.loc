<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 21.05.2017
 * Time: 18:53
 */

namespace Core\Classes\Generator;
use Core\Config;
use Core\Classes\DBO;


class DBOGenerator
{
    const CLASS_NAME     = 0;
    const CLASS_METHODS  = 1;
    const METHOD_NAME    = 0;
    const COLUMN_NAME    = 1;

    protected $t         = [];
    protected $current   = [];

    protected $classTpl  = '';
    protected $methodTpl = '';
    protected $DBOPath   = '';

    protected $classData = [
        self::CLASS_NAME    => [],
        self::CLASS_METHODS => []
    ];

    protected function __construct()
    {
        $this->DBOPath = DBO\DBO::getDir();
        $this->classTpl = DBO\DBO::getClassTpl();
        $this->methodTpl = DBO\DBO::getMethodTpl();
        $this->t = Config\SQL::tables();
    }

    protected function clear()
    {
        $this->current   = [];
        $this->classData = [
            self::CLASS_NAME    => [],
            self::CLASS_METHODS => []
        ];

        return true;
    }

    protected function setClassName()
    {
        if
        (
            !\kas::arr($this->current[0])       ||
            !\kas::str($this->current[0][1])
        )
        {
            \kas::ext('Arguments error');
            return false;
        }

        $this->classData[self::CLASS_NAME] = [$this->current[0][0],
            $this->current[0][1]];
        return true;
    }

    protected function setGettersAndSetters()
    {
        foreach ($this->current[1] as $mName)
        {
            if
            (
                !\kas::arr($mName)      ||
                !\kas::str($mName[1])
            )
            {
                \kas::ext('Arguments error');
                return false;
            }

            $this->classData[self::CLASS_METHODS][] = [
                self::METHOD_NAME => "get{$mName[1]}",
                self::COLUMN_NAME => $mName[0]
            ];

            $this->classData[self::CLASS_METHODS][] = [
                self::METHOD_NAME => "set{$mName[1]}",
                self::COLUMN_NAME => $mName[0]
            ];
        }

        return true;
    }

    protected function convertStrToMethodName($str = '')
    {
        if (!\kas::str($str)) {
            return $str;
        }

        $arr = \kas::data($str)
            ->r('KAS_', '')
            ->strLow()
            ->explode('_')
            ->asArr();

        $arr = $arr[0];
        $m   = '';

        foreach ($arr as $k => $p) {
            $p[0] = \kas::data($p[0])->strUp()->asStr();
            $m .= $p;
        }

        return $m;
    }

    /**
     * @param string $mName
     * @return string
    */
    protected function convertMethodNameToColumnName($mName = '')
    {
        if (!\kas::str($mName)) {
            return $mName;
        }

        return 'KAS_' . mb_strtoupper($mName, ENCODING);
    }

    protected function setMethodNames()
    {
        if
        (
            !\kas::arr($this->current)      ||
            !\kas::str($this->current[0])   ||
            !\kas::arr($this->current[1])
        )
        {
            \kas::ext('Arguments error');
            return false;
        }

        $this->current[0] = [$this->current[0],
            $this->convertStrToMethodName($this->current[0])];

        foreach ($this->current[1] as $k => $c)
        {
            if (!\kas::str($c)) {
                \kas::ext('Arguments error');
                return false;
            }

            $this->current[1][$k] = [$c, $this->convertStrToMethodName($c)];
        }

        return true;
    }

    protected function createDBOClass()
    {
        $path = \kas::slash($this->DBOPath . '\\Bin\\' . $this->classData[self::CLASS_NAME][1] . '.php');

        if (!is_dir($this->DBOPath)) {
            \kas::ext('Invalid DBO path ' . $this->DBOPath);
            return false;
        }

        if (file_exists($path)) {
            return true;
        }

        $methodsTpl = '';
        foreach ($this->classData[self::CLASS_METHODS] as $mData)
        {
            $methodsTpl .= \kas::data($this->methodTpl)
                ->r('%METHOD%', $mData[0])
                ->r('%COLUMN%', $mData[1])
                ->r('%CLASS%', $this->classData[self::CLASS_NAME][1])
                ->asStr() . "\r\n\r\n";
        }

        $classTpl = \kas::data($this->classTpl)
            ->r('%CLASS%', $this->classData[self::CLASS_NAME][1])
            ->r('%TABLE%', $this->classData[self::CLASS_NAME][0])
            ->r('%METHODS%', $methodsTpl)
            ->r('/[ ]{8}/', '')
            ->asStr();

        if (!\kas::str($classTpl)) {
            \kas::ext('DBO Error');
            return false;
        }

        if (!file_put_contents($path, $classTpl)) {
            \kas::ext('Writing error ' . $path);
            return false;
        }

        chmod($path, 0777);
        $this->clear();

        return true;
    }

    protected function _create()
    {
        foreach ($this->t as $t => $c)
        {
            $this->current = [$t, $c];

            if
            (
                !$this->setMethodNames() ||
                !$this->setClassName()
            )
            {
                return false;
            }

            $this->setGettersAndSetters();
            $this->createDBOClass();
        }

        return true;
    }

    protected function conf()
    {

        if
        (
            !\kas::arr($this->t)    ||
            !$this->_create()
        )
        {
            \kas::ext('Tables is not defined');
            return false;
        }

        return true;
    }

    static public function create() {
        $ob = new static();
        return $ob->conf();
    }
    
    /**
     * @param string $str string
     * @return string
    */
    static public function getMethodName($str = '')  
    {
        $ob = new static();
        $method     = $ob->convertStrToMethodName($str);
        $method[0]  = mb_strtolower($method[0], ENCODING);
        return $method;
    }

    /**
     * @param string $str string
     * @return string
     */
    static public function methodToColumnName($str = '') 
    {
        $ob = new static();
        return $ob->convertMethodNameToColumnName($str);
    }
}