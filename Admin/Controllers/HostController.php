<?php


namespace Controllers;


class HostController
{
    protected $uri      = '';
    protected $host     = '';
    protected $protocol = '';
    protected $appHost  = '';

    protected function __construct()
    {
        $this->protocol = PROTOCOL . '://';
        $this->uri      = $_SERVER['REQUEST_URI'];
        $this->host     = $_SERVER['HTTP_HOST'];
    }

    protected function isLocalhost()
    {
        if
        (
            $this->host == 'localhost' ||
            $this->host == '127.0.0.1'
        )
        {
            return true;
        }

        return false;
    }

    protected function _getUri()
    {
        if (!$this->isLocalhost()) {
            return $this->uri;
        }

        $this->uri = explode('/', $this->uri);

        $this->appHost = $this->uri[1];
        unset($this->uri[1]);

        $this->uri = implode('/', $this->uri);
        return $this->uri;
    }

    protected function _getHost()
    {
        $this->_getUri();           
        $this->isLocalhost() ?
            $h = $this->protocol . $this->host . '/' . $this->appHost :
            $h = $this->protocol . $this->host;

        return \kas::data($h)->strLow()->asStr();
    }

    static public function getUri()
    {
        $ob = new static();
        return $ob->_getUri();
    }

    static public function getHost()
    {
        $ob = new static();
        return $ob->_getHost();
    }
}