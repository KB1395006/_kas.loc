<?php

namespace Core\Classes\Document;

use Core\Classes\CMD\CMD;
use Controllers;

class Document
{
    const DOC_TPL_DIR           = '/blocks/';
    const INDEX_KEY             = 'index.tpl';
    const PATH                  = 'path';
    const DATA                  = 'data';
    const KEY                   = 'key';

    protected $html             = '';
    protected $frags            = [HTML_I, HTML_M, HTML_C, HTML_F, HTML_H, HTML_T];
    protected $doc              = [];

    protected $appDocPath    = '/www';
    protected $cmsDocPath    = '/Admin/_public/Tpl/CMS/1/Document';
    protected $httpPath      = '';
    protected $docPath       = '';
    protected $uploadPath    = UPL_PATH;
    protected $host          = '';

    /**
     * Значение 0, свидетельствует о невозможности продолжения
     * дальнейшей работы.
    */
    protected $state    = 0;

    protected function __construct() {
    }
    
    protected function conf()
    {
        $_tmp = \kas::scan(1);

        if (!\kas::arr($_tmp)) {
            \kas::ext('Document dir not found!');
            return false;
        }

        $this->doc = \kas::scan(current($_tmp) . self::DOC_TPL_DIR);

        if (!\kas::arr($this->doc))
        {
            $this->state = 0;
            return false;
        }

        $this->state = 1;
        $this->prepareDoc();
        $this->host();
        $this->setDocPath();
        $this->setHttpPath();

        return true;
    }

    protected function prepareDoc()
    {
        foreach ($this->frags as $frag) {
            $this->getTpl($frag);
        }
    }

    protected function setHttpPath()
    {
        \kas::isCMS() ?
            $this->httpPath = $this->host . DS . ADMIN :
            $this->httpPath = $this->host . DS;
    }

    protected function setDocPath()
    {
        \kas::isCMS() ?
            $this->docPath = $this->host . $this->cmsDocPath :
            $this->docPath = $this->host . $this->appDocPath;
    }

    protected function host()
    {
        $this->host = Controllers\HostController::getHost();
        return true;
    }

    protected function getTpl($p = '', $d = [], $i = 0, $c = false)
    {
        if (!\kas::str($p))
        {
            \kas::ext('Html part undefined');
            return false;
        }

        // Передана строка с разметкой
        is_string($d) ?
            $_tpl = $d : $_tpl = false;
        
        // Передано замыкание
        if (is_callable($d))
        {
            $string = $d();
            
            \kas::str($string) ?
                $_tpl = $string : false;
        }

        // Передан идентификатор шаблона
        is_int($d) ?
            $_tpl = \kas::load($d) : false;

        // Переданы параметры сборки
        \kas::str($_tpl) ?:
            $_tpl = \kas::tpl($d, $i, $c);

        \kas::arr($_tpl) ?
            $_tpl = current($_tpl) : false;

        !is_object($_tpl) ?:
            $_tpl = $_tpl->asStr();

        $tplKey = \kas::data($p)->strLow()->asStr();

        if (!\kas::str($tplKey))
        {
            \kas::ext('$tplKey not found');
            return false;
        }

        $tplKey .= TPL_EXT;

        !\kas::str($_tpl) ?
            $_tpl = \kas::load(\kas::str($this->doc[$tplKey]) ?
            $this->doc[$tplKey] : $this->doc[$tplKey][self::PATH]) : false;

        $this->doc[$tplKey] = [
            self::KEY  => $p,
            self::PATH => \kas::str($this->doc[$tplKey]) ?
                $this->doc[$tplKey] : $this->doc[$tplKey][self::PATH],
            self::DATA => $_tpl
        ];

        return \kas::str($this->doc[$tplKey][self::DATA]) ?
            true : false;
    }
    
    public function meta($d = [], $i = 0, $c = false)
    {
        $this->getTpl(HTML_M, $d, $i, $c);
        return $this;
    }
    
    public function header($d = [], $i = 0, $c = false)
    {
        $this->getTpl(HTML_H, $d, $i, $c);
        return $this;
    }

    public function footer($d = [], $i = 0, $c = false)
    {
        $this->getTpl(HTML_F, $d, $i, $c);
        return $this;
    }

    public function content($d = [], $i = 0, $c = false)
    {
        $this->getTpl(HTML_C, $d, $i, $c);
        return $this;
    }

    public function title($d = [], $i = 0, $c = false)
    {
        $this->getTpl(HTML_T, $d, $i, $c);
        return $this;
    }

    /**
     * Собирает компоненты в единый шаблон.
     * @param bool $globalsConvert
     * @return array|bool|string
     */
    public function html($globalsConvert = true)
    {
        $main = $this->doc[self::INDEX_KEY][self::DATA];

        if (!\kas::str($main)) {
            \kas::ext('Document data not found');
            return false;
        }

        unset($this->doc[self::INDEX_KEY]);

        foreach ($this->doc as $k => $frag)
        {
            if 
            (
                !\kas::arr($frag)               ||
                !\kas::str($frag[self::KEY])    ||
                !\kas::str($frag[self::DATA])
            ) 
            {
                continue;
            }

            // Подключить DocPath
            $fd = \kas::data($frag[self::DATA])->r('%' . DOC_PATH . '%',
                $this->docPath)->asStr();

            // Подключить HTTP PATH
            $fd = \kas::data($fd)->r('%' . HTTP_PATH . '%',
                $this->httpPath)->asStr();

            // Удалить служебные маркеры
            $fd = \kas::data($fd)->noMasc()->asStr();

            if
            (
                $frag[self::KEY] == CONTENT &&
                !$globalsConvert
            )
            {
                $fd = $frag[self::DATA];
            }
            
            $main = str_replace("%{$frag[self::KEY]}%", 
                $fd, $main);
        }

        // Подключить тексты интерфейса.
        $main = \kas::st($main);
        
        // Подключить интерпритатор.
        $main = CMD::exec($main);

        $this->html = $main;
        return $this->html;
    }

    static public function getHttpPath()
    {
        $ob = new static();
        $ob->host();
        $ob->setHttpPath();
        
        return $ob->httpPath;
    }

    static public function getDocPath()
    {
        $ob = new static();
        $ob->host();
        $ob->setDocPath();

        return $ob->docPath;
    }

    static public function getUploadPath() {
        return UPL_PATH;
    }

    // Устанвливает системные пути для
    // передаваемого контента.
    static public function pathes($data = '')
    {
        if (!\kas::str($data)) {
            return $data;
        }

        // Подключить HTTP PATH
        $data = \kas::data($data)->r('%' . HTTP_PATH . '%',
            self::getHttpPath())->asStr();

        $data = \kas::data($data)->r('%' . DOC_PATH . '%',
            self::getDocPath())->asStr();

        return $data;
    }
    
    static public function run() 
    {
        $ob = new static();
        $ob->conf();
        return $ob;
    }
}