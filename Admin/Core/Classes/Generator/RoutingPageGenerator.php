<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 16.05.2017
 * Time: 13:03
 */

namespace Core\Classes\Generator;


class RoutingPageGenerator
{
    protected $routingCodeTpl  = 21;
    protected $routingCodePath = '';

    protected function __construct()
    {
        $this->routingCodePath = \ENV::_()->V_PATH . KAS_TPL_DIR . KAS_CMS . '/' . 
            $this->routingCodeTpl . '/code.tpl';
    }

    protected function getContent() {
        return \kas::load($this->routingCodePath);
    }

    static public function getCode()
    {
        $ob = new static();
        return $ob->getContent();
    }
}