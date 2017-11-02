<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 18.05.2017
 * Time: 11:15
 */

namespace Core\Classes\Generator;


class TplGenerator
{
    const T                     = 'TABLE';
    protected $t                = TPL;
    protected $tplExt           = TPL;
    protected $perms            = 0777;
    protected $tplLimit         = 999;
    protected $parentHtml       = '';
    protected $childHtml        = '';

    protected $tplPath          = [];
    protected $tplId            = [];
    protected $data             = [];
    protected $locData          = [DESC_M => P_TPL, DESC_L => C_TPL];

    protected function __construct($tplData = []) {
        $this->data = $tplData;
    }

    protected function setTplPath()
    {
        foreach ($this->tplId as $id) {
            $this->tplPath[$id] = \CMS::V_PATH . APP_TPL . $id . '/';
        }

        return true;
    }

    protected function setTplId()
    {
        \kas::str($this->data[ID]) ?
            $this->tplId = [$this->data[ID]] :
            $this->tplId = $this->data[ID];

        if (!\kas::arr($this->tplId)) {
            return false;
        }

        return true;
    }

    protected function setDir()
    {
        foreach ($this->tplId as $id)
        {
            is_dir($this->tplPath[$id]) ?:
                mkdir($this->tplPath[$id], $this->perms, true);
        }

        return true;
    }

    protected function setContent()
    {
        $isComplete = true;

        foreach ($this->locData as $c => $f)
        {
            \kas::str($this->data[COL]) && $this->data[COL]  == $c ?
                $isComplete = @file_put_contents(current($this->tplPath)
                    . $f, html_entity_decode($this->data[DATA])) : false;

            if (!$isComplete) {
                \kas::ext('Writing error');
                return false;
            }
        }

        return true;
    }

    protected function _create()
    {
        $this->setDir();

        if (!$this->setContent()) {
            return false;
        }

        return true;
    }
    
    protected function _removeTpl() 
    {
        foreach ($this->tplId as $id)
        {
            if
            (
                !is_dir($this->tplPath[$id])
            )
            {
                continue;
            }

            $data = \kas::scan($this->tplPath[$id], KAS_SCAN_FILE);

            if (!\kas::arr($data)) {
                continue;
            }
            
            foreach ($data as $file)
            {
                if (!unlink($file)) {
                    \kas::ext('Removing error');
                    return false;
                }
            }

            continue;
        }

        return true;
    }

    protected function conf()
    {       
        if
        (
            !\kas::arr($this->data)             ||
            !$this->data[ID]                    ||
            $this->data[self::T] !== $this->t   ||
            !$this->setTplId()                  ||
            !$this->setTplPath()
        )
        {
            return false;
        }

        return true;
    }

    static public function create($tplData = [])
    {
        $ob = new static($tplData);

        if (!$ob->conf()) {
            return false;
        }

        return $ob->_create();
    }

    static public function removeTpl($tplData = [])
    {
        $ob = new static($tplData);

        if (!$ob->conf()) {
            return false;
        }

        return $ob->_removeTpl();
    }
}