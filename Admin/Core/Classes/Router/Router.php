<?php

namespace Core\Classes\Router;


class Router
{
    protected $uri = '';

    protected function __construct()
    {
        $this->uri = $_SERVER['REQUEST_URI'];
    }

    protected function config()
    {
        var_dump($this->uri);
    }

    static public function run()
    {
        $ob = new static();
        return $ob->config();
    }
} 