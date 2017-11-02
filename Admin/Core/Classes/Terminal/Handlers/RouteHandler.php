<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 04.10.17
 * Time: 6:20
 */

namespace Core\Classes\Terminal\Handlers;


class RouteHandler
{
    const N = 'new';
    const R = 'remove';
    const L = 'list';

    protected $msg       = '';
    protected $path      = '';
    protected $type      = '';
    protected $filename  = 'APP/routing.txt';
    protected $routesArr = ['/'];
    protected $args      = [];

    protected function __construct($args = []) {
        $this->setMsg();
        $this->args = $args;
    }

    protected function setMsg($id = 1) {
        $this->msg = \kas::st($id, true);
        return true;
    }

    protected function setType()
    {
        if (!\kas::str($this->args[1])) {
            $this->setMsg(54);
            return false;
        }
        $this->type = $this->args[1];
        return true;
    }

    protected function setPath() {
        $this->path = \ENV::_()->M_PATH . KAS_CONFIG_PATH . $this->filename;
        return file_exists($this->path);
    }

    protected function setRoutesArr() {
        $data = (string) \kas::load($this->path);
        $data ? $this->routesArr = array_flip(explode("\r\n", $data)) : false;
        return true;
    }

    protected function setRoutesList() {
        $this->msg = implode("\r\n", array_keys($this->routesArr));
        return true;
    }

    protected function prepareRoute($route = '') {
        $route = str_replace('//', '/',"/{$route}/");
        return $route;
    }

    protected function addNewRoutes()
    {
        unset($this->args[0]);

        foreach ($this->args as $route) {
            $this->routesArr[$this->prepareRoute($route)] = true;
        }

        return true;
    }

    protected function removeRequestedRoutes()
    {
        unset($this->args[1]);
        if (!\kas::arr($this->args)) {
            return false;
        }
        foreach ($this->args as $route) {
            if (!$route) {
                return false;
            }
            unset($this->routesArr[$this->prepareRoute($route)]);
        }
        return true;
    }

    protected function safeRequestedRoutes() {
        return @file_put_contents($this->path,
            implode("\r\n", array_keys($this->routesArr)));
    }

    /**
     * @return string
    */
    protected function conf()
    {
        if
        (
            !\kas::arr($this->args)     ||
            !$this->setType()           ||
            !$this->setPath()
        )
        {
            \kas::ext('Invalid arguments');
            return $this->msg;
        }

        $this->setRoutesArr();
        $this->addNewRoutes();

        switch ($this->type)
        {
            case self::N:
                // OK
                $this->setMsg(55);
                break;
            case self::R:
                $this->removeRequestedRoutes() ?
                    $this->setMsg(56) : false;
                break;
            case self::L:
                $this->setRoutesList();
                break;
            default:
                $this->setMsg(54);
                break;
        }

        $this->safeRequestedRoutes();
        return $this->msg;
    }

    /**
     * @param array $args
     * @return string
    */
    static public function run($args = []) {
        $ob = new static($args);
        return $ob->conf();
    }
}