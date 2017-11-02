<?php

namespace Core\Classes\DownloadManager;

class DownloadManager
{
    const TPL_ID    = 16;
    const EXT       = 'tpl';
    
    protected $html = '';
    protected $ob   = [];

    protected function __construct() {
        $this->html = \kas::doc()->pathes(\kas::load(self::TPL_ID, self::EXT));
    }

    static public function html($path = '') {
        $ob = new static($path);
        return $ob->html;
    }
}