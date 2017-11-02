<?php
namespace Controllers;

class FrontController
{
    protected $uri = '';

    protected function __construct() {
        $this->uri = \kas::uri();
    }

    
    protected function incNS() {
        return \kas::ns($this->uri);
    }

    protected function config() {
        return $this->incNS();
    }

    static public function run()
    {
        $ob = new static();
        return $ob->config();
    }
} 