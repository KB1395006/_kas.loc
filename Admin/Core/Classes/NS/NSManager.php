<?php
namespace Core\Classes\NS;
use Core\Classes\Generator\Generator;

class NSManager
{
    const NS_DIR            = 'NS/';
    const NS_CMS            = '.admin.';
    const MAIN              = 'index';
    const EXT               = '.php';

    protected $routingData  = [];
    protected $routingPath  = '';
    protected $currentRoute = '';

    protected $envDir       = '';
    protected $uri          = '';
    protected $ns           = '';
    protected $nsDefault    = '';
    protected $path         = '';

    protected function __construct($uri = false)
    {
        $this->uri = $uri;
        $this->setEnvDir();
        $this->setRoutingPath();
    }

    protected function setRoutingPath() {
        $this->routingPath = \ENV::_()->M_PATH . KAS_CONFIG_PATH . $this->envDir .  KAS_ROUTING_FILE;
        return true;
    }

    protected function setEnvDir()
    {
        \kas::isCMS() ?
            $this->envDir = KAS_CMS . '/':
            $this->envDir = KAS_APP . '/';
    }

    // Устанавливает путь для текущего окружения
    protected function setPath()
    {
        $this->path  = \ENV::_()->M_PATH . KAS_CONFIG_PATH . $this->envDir . self::NS_DIR;
        return is_dir($this->path) ? true :
            false;

    }

    protected function setNS()
    {
        if (!\kas::str($this->uri)) {
            return false;
        }

        // Преобразовать данные типа: first/second/ в first.second.php
        $this->ns  = \kas::data($this->uri)->r('/', '.')
            ->r(self::NS_CMS, '')->asStr() ?: self::MAIN;

        $this->ns .= self::EXT;
        $this->ns  = \kas::data($this->ns)->r('..', '.')->r('/\.[0-9]+\./', '.id.')->asStr();

        $this->ns  == self::EXT ?
            $this->ns = 'index' . $this->ns : false;

        $this->ns  = $this->path . $this->ns;
        $this->ns  = \kas::data($this->ns)->r('/.', '/')->asStr();

        // Создать альтернативное пространство имен.
        $this->nsDefault = $this->path . self::MAIN . self::EXT;

        return true;
    }

    protected function getNsData()
    {
        if
        (
            !file_exists($this->nsDefault) &&
            !file_exists($this->ns)
        )
        {
            \kas::ext("Namespace {$this->ns} not found.");
            return false;
        }

        if (\kas::isCMS())
        {
            file_exists($this->ns) ?
                $ns = $this->ns : $ns = $this->nsDefault;

            /** @noinspection PhpIncludeInspection */
            return require_once $ns;
        }

        if (!$this->routeExists())
        {
            // 404
            \kas::ext(404, false);
            return false;
        }

        file_exists($this->ns) ?:
            file_put_contents($this->ns, Generator::routingPageGenerator());

        /** @noinspection PhpIncludeInspection */
        return require_once $this->ns;
    }

    protected function routeExists()
    {
        $this->routingData = \kas::load($this->routingPath);

        if (!$this->routingData) {
            \kas::ext('Routing list not found');
        }

        $this->routingData = explode("\r\n", $this->routingData);

        if (!\kas::arr($this->routingData)) {
            \kas::ext('Routing error');
            return false;
        }

        $uri = preg_replace('/\/[0-9]+\//', '/%ID%/', preg_quote($this->uri));
        if (!\kas::str($uri)) {
            \kas::ext('Routing error');
            return false;
        }

        foreach ($this->routingData as $route)
        {
            if ($uri == $route) {
                $this->currentRoute = $route;
                return true;
            }
        }

        return false;
    }

    protected function config()
    {
        if
        (
            !$this->setPath()       ||
            !$this->setNS()
        )
        {
            return false;
        }

        return $this->getNsData();
    }

    static public function run($uri = false)
    {
        $ob = new static($uri);
        return $ob->config();
    }
}