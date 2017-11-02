<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 03.06.2017
 * Time: 13:15
 */

namespace Core\Classes\App;



class AppInstaller
{

    protected $wwwPath      = WWW_PATH;
    protected $dumpPath     = DUMP_PATH;
    protected $cssPath      = 'css/';
    protected $jsPath       = 'js/';
    protected $fontsPath    = 'fonts/';
    protected $imgPath      = 'img/';
    protected $indexPath    = './Tpl/APP/1/Document/blocks/index.tpl';

    protected $rExp         = ['/(href|src)=\"([^"]+\.[^"]+)\"/', '/href=\"([^"]+\.[^"]+)\"/'];
    protected $blocks       = ['%DOC_TITLE%', '%META%', '%HEADER%', '%CONTENT%', '%FOOTER%'];
    protected $dumpFiles    = [];
    protected $extList      = [];

    protected $msg          = '';
    protected $indexContent = '';

    protected function __construct() {
        $this->setPath();
        $this->setExtList();
        $this->dumpFiles = \kas::scan($this->dumpPath, 2);
    }

    protected function setExtList()
    {
        $this->extList = [
            $this->cssPath      => ['css'],
            $this->jsPath       => ['js'],
            $this->fontsPath    => ['eot', 'ttf'],
            $this->imgPath      => ['jpg', 'jpeg', 'gif', 'png']
        ];

        return true;
    }

    protected function setPath()
    {
        $this->cssPath      = $this->wwwPath . $this->cssPath;
        $this->jsPath       = $this->wwwPath . $this->jsPath;
        $this->fontsPath    = $this->wwwPath . $this->fontsPath;
        $this->imgPath      = $this->wwwPath . $this->imgPath;
        return true;
    }
    
    protected function getExt($path = '')
    {
        if (!\kas::str($path)) {
            return false;
        }

        $ext = explode('.', $path);
        return $ext[count($ext) - 1];
    }

    protected function getPathByFilename($filename = '')
    {
        if (!\kas::str($filename)) {
            return false;
        }

        $p = '%DOCUMENT_PATH%';
        $e = $this->getExt($filename);

        foreach ($this->extList as $path => $extensions)
        {
            $ex = array_flip($extensions);

            if (is_null($ex[$e])) {
                continue;
            }

            $p .= '/' . basename($path) . '/';
            break;
        }

        $p .= $filename;
        return $p;
    }

    protected function copy($dumpPath = '', $e = '')
    {
        if
        (
            !\kas::str($dumpPath)   ||
            !\kas::str($e)
        )
        {
            return false;
        }

        foreach ($this->extList as $path => $extensions)
        {
            $ex = array_flip($extensions);

            if (is_null($ex[$e])) {
                continue;
            }

            return copy($dumpPath, $path . basename($dumpPath)) ?
                true : false;
        }

        return true;
    }

    protected function groupByExt()
    {
        foreach ($this->dumpFiles as $path)
        {
            $e = $this->getExt($path);

            if (!$e) {
                continue;
            }

            $this->copy($path, $e);
        }

        return true;
    }

    protected function getUrlsFromContent($content = '', $hrefOnly = false)
    {
        if (!\kas::str($content)) {
            return false;
        }

        preg_match_all($this->rExp[(int) $hrefOnly], $content, $m);

        if (!\kas::arr($m[0])) {
            return false;
        }

        return $m;
    }

    protected function pathConfig()
    {
        $m = $this->getUrlsFromContent($this->indexContent);
        
        if (!\kas::arr($m[2])) {
            return false;
        }

        foreach ($m[2] as $k => $filename)
        {
            $p = $this->getPathByFilename(basename($filename));

            if (!$p) {
                continue;
            }

            count(explode('href', $m[0][$k])) > 1 ?
                $p = "href=\"{$p}\"" : $p = "src=\"{$p}\"";

            $this->indexContent = str_replace($m[0][$k], $p, $this->indexContent);
            continue;
        }
        return true;
    }

    /**
     * @return bool
     */
    protected function setIndex()
    {        
        $this->indexContent   = \kas::load($this->dumpFiles['index.htm']) ?: '';
        $this->indexContent   ?: $this->indexContent = \kas::load($this->dumpFiles['index.html']);

        if (!\kas::str($this->indexContent)) {
            $this->msg = \kas::st(34, true);
            return false;
        }

        $this->pathConfig();

        $this->indexContent .= implode("\r\n", $this->blocks);
        file_put_contents($this->indexPath, $this->indexContent);
        return true;
    }

    protected function conf()
    {
        if
        (
            !\kas::arr($this->dumpFiles)    ||
            !$this->groupByExt()            ||
            !$this->setIndex()
        )
        {
            return $this->msg;
        }

        return \kas::st(33, true);
    }

    static public function run() {
        $ob = new static();
        return $ob->conf();
    }
    
    static public function _getUrlsFromContent($content = '', $hrefOnly = false) {
        $ob = new static();
        return $ob->getUrlsFromContent($content, $hrefOnly);
    }
}