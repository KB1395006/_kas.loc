<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 18.05.2017
 * Time: 9:21
 */

namespace Core\Classes\Generator;


class DefaultRowDBGenerator
{
    protected $defaultTextId  = 5;
    protected $defaultText    = '';

    protected $defaultColumns = [TITLE, NAME];
    protected $sqlStack       = [];

    protected function __construct() {
        $this->setDefaultText();
    }

    protected function setDefaultText() {
        $this->defaultText = \kas::st($this->defaultTextId);
        return true;
    }

    /**
     * @param string $t
     * @return DefaultRowDBGenerator
     */
    public function create($t = '')
    {
        if (!\kas::str($t))
        {
            \kas::ext('Invalid arguments');
            return false;
        }

        $this->sqlStack[] = [
            $t,
            \kas::sql()->simple()->ins($t, $this->defaultColumns),
            array_fill(0, count($this->defaultColumns), $this->defaultText)
        ];
        
        return $this;
    }

    public function exec()
    {
        if (!\kas::arr($this->sqlStack)) {
            return false;
        }

        foreach ($this->sqlStack as $k => $sqlData)
        {
            if (!\kas::arr($sqlData)) {
                continue;
            }

            $tSql = \kas::sql()->simple()->sel($sqlData[0], [ID]) . ID . ' > ? LIMIT 1';
            if (\kas::arr(\kas::sql()->exec($tSql, [0]))) {
                continue;
            }

            \kas::sql()->exec($sqlData[1], $sqlData[2]);
        }

        return true;
    }

    /**
     * @return DefaultRowDBGenerator
    */
    static public function run()
    {
        $ob = new static();
        return $ob;
    }
}