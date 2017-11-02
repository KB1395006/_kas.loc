<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 28.06.2017
 * Time: 12:37
 */

namespace Core\Classes\Spider;
use Core\Classes\Deadline;
use Core\Classes\Terminal;
use Core\Classes\App;


class Spider
{
    const URL       = 1;
    const KEY       = 0;
    const PROTOCOL  = 2;
    const IN_URL    = 3;
    const PR_PATH   = 4;

    /** Allowed data types*/
    const TYPE_HTML = 'text/html';
    const TYPE_CSS  = 'text/css';


    protected $key          = 0;

    protected $url          = '';
    protected $protocol     = '';
    protected $inUrl        = '';
    protected $contentType  = '';
    protected $projPath     = '/proj/';

    protected $todoLst      = 'todo.lst';
    protected $completeLst  = 'complete.lst';
    protected $content      = '';

    protected $headers      = [];
    protected $term         = [];
    protected $opt          = [];
    protected $response     = [];
    protected $list         = [];

    // Extracted urls from content
    protected $urls         = [];

    /**
     * @param object Deadline\Deadline;
    */
    protected $dd;

    protected function __construct($term = [])
    {
        $this->dd   = new Deadline\Deadline(__CLASS__);
        $this->opt  = $this->dd->getOpt();
        $this->term = $term;
    }

    /**
     * @param int $id
     * @param bool $continue
     * @return array
     */
    protected function setResponse($id = 0, $continue = false)
    {
        (int) $id ? $this->response = Terminal\Terminal::getResponse($id, $continue) :
            $this->response = Terminal\Terminal::getResponseError((int) (@current(debug_backtrace())['line']),
                $continue);

        return $this->response;
    }

    /**
     * @param string $url
     * @return bool|string
    */
    protected function getContent($url = '')
    {
        \kas::str($url) ?
            $url = $this->prepareUrl($url) :
            $url = $this->url;

        $ch  = curl_init();
        $opt = array
        (
            CURLOPT_URL             => $url,
            CURLOPT_HEADER          => false,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_USERAGENT       => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_CONNECTTIMEOUT  => 120,
            CURLOPT_ENCODING		=> ENCODING
        );

        curl_setopt_array($ch, $opt);
        $content = curl_exec($ch);
        curl_close($ch);

        if (!\kas::str($content))
        {
            $this->setResponse();
            return false;
        }

        $this->content = html_entity_decode($content);
        return $this->content;
    }

    /**
     * @param $fn bool|callable
     * @return bool
    */
    public function onContentLoaded($fn = false)
    {
        if (!is_callable($fn)) {
            return false;
        }

        // Set onLoad object
        $ob             = new \stdClass();
        $ob->url        = $this->url;
        $ob->content    = $this->content;
        $ob->terminal   = $this->term;
        $ob->response   = $this->response;
        $ob->options    = $this->opt;

        $fn($ob);
        return true;
    }

    /**
     * @param string $url
     * @return bool
    */
    protected function externalUrl($url)
    {
        if
        (
            filter_var($url, FILTER_VALIDATE_URL)               &&
            count(explode(basename($this->term[1]), $url)) == 1
        )
        {
            return true;
        }

        return false;
    }


    protected function urlsInit()
    {
        if (!\kas::arr($this->urls[1])) {
            return false;
        }

        foreach ($this->urls[1] as $url)
        {
            if ($this->externalUrl($url)) {
                continue;
            }

        }

        return true;
    }

    /**
     * @return bool|array
    */
    protected function getHeaders()
    {
        $this->headers = @get_headers($this->url) ?: false;

        if (!\kas::arr($this->headers)) {
            return false;
        }

        foreach ($this->headers as $header)
        {
            if (count(explode($this->contentType, $header)) > 1) {
                return true;
                break;
            }

            continue;
        }

        return false;
    }

    protected function next() {

    }

    protected function getListPath($type = 0)
    {
        $list = [$this->projPath . $this->todoLst,
            $this->projPath . $this->completeLst];

        return $list[(int)$type];
    }

    /**
     * If $getAll = 0, method return first line with 0 key
     * @param int $type
     * @param bool $getAll
     * @return mixed
    */
    protected function getList($type = 0, $getAll = false)
    {
        if (\kas::arr($this->list[$type])) {
            return \kas::arr($this->list[$type]);
        }

        $this->list[$type] = explode("\r\n", \kas::load($this->getListPath($type)));

        if (!\kas::arr($this->list[$type])) {
            return false;
        }

        return $getAll ? $this->list[$type] : $this->list[$type][0];
    }

    protected function setList($type = 0, $data = '')
    {
        \kas::str($data) ?: $data = $this->url;
        \kas::arr($data) ?  $data = implode("\r\n", $data) : false;

        $data .= "\r\n";
        $resp = @file_put_contents($this->getListPath($type), $data);
        return (bool) $resp;
    }

    protected function listInit()
    {
        switch (file_exists($this->getListPath()))
        {
            case true:
                
                if (!$this->setUrl($this->getList())) {
                    $this->setResponse();
                    return false;
                }

                break;

            // Create if not
            case false:

                if
                (
                    !$this->setUrl()    ||
                    !$this->setList()
                )
                {
                    $this->setResponse();
                    return false;
                }

                break;
        }

        return true;
    }

    protected function parseUrls() {
        $this->urls = App\AppInstaller::_getUrlsFromContent($this->content, true);
        return $this->urls;
    }

    /**
     * @return bool|string
    */
    protected function getUrls()
    {
        if (!$this->listInit()){
            $this->setResponse();
        }

        // Here...
        switch ($this->getHeaders())
        {
            case true:

                if (!$this->getContent()) {
                    // next
                }

                $this->parseUrls();
                $this->urlsInit();

            break;

            case false:

                // next

            break;
        }

        return false;
    }

    protected function prepareUrl($url = '')
    {
        if (!\kas::str($url)) {
            // No response (multiple execution)
            return false;
        }

        $url = $this->protocol . '://' . basename($url);
        return $url;
    }

    protected function setProjPath()
    {
        if
        (
            \kas::str($this->opt[self::PR_PATH]) &&
            is_dir($this->opt[self::PR_PATH])
        )
        {
            $this->projPath = $this->opt[self::PR_PATH];
            return true;
        }

        $this->projPath = __DIR__ . $this->projPath .
            preg_replace('/[^a-zA-Z0-9_-]+/', '', $this->term[1]) . '/';
        
        $this->opt[self::PR_PATH] = $this->projPath;
        
        if (is_dir($this->projPath)) {
            return true;
        }
        
        if (!@mkdir($this->projPath)) {
            $this->setResponse(35);
            return false;
        }
        
        return true;
    }

    protected function notInUrl($url)
    {
        if
        (
            !$this->inUrl                           ||
            count(explode($this->inUrl, $url)) == 1
        )
        {
            return false;
        }

        return true;
    }

    protected function setInUrl()
    {
        \kas::str($this->opt[self::IN_URL]) ?:
            $this->opt[self::IN_URL] = (string) $this->term[2] ?: false;

        $this->inUrl = $this->opt[self::IN_URL];
        return true;
    }

    protected function setProtocol()
    {
        switch ($this->opt[self::PROTOCOL])
        {
            case true:
                $this->protocol = $this->opt[self::PROTOCOL];
                break;

            case false:
                $this->protocol = parse_url($this->term[1])['scheme'] ?: 'http';
                $this->opt[self::PROTOCOL] = $this->protocol;
                break;
        }
        
        return true;
    }

    protected function setUrl($url = '')
    {
        $url ? $this->url = $this->prepareUrl($url) :
            $this->url = $this->prepareUrl($this->term[1]);

        $this->url ?
            $this->opt[self::URL] = $this->url : false;

        return (bool) $this->url;
    }

    protected function setKey($key = 0)
    {
        $this->key = (int) $key;
        $this->opt[self::KEY] = $this->key;
        return true;
    }

    protected function setContentType() {
        $this->contentType = self::TYPE_HTML;
        return true;
    }

    protected function setUrlStack() {}

    /**
     * @return bool
    */
    protected function conf()
    {
        if
        (
            !\kas::str($this->term[1])                          ||
            !$this->setProtocol()                               ||
            !$this->setContentType()                            ||
            !$this->setInUrl()
        )
        {
            return $this->setResponse();
        }

        if
        (
            !$this->setProjPath()   ||
            !$this->getUrls()
        )
        {
            return $this->response;
        }

        return true;
    }

    static public function run($terminal = [])
    {
        $ob = new static($terminal);
        return $ob->conf();
    }
}