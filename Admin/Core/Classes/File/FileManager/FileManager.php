<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 25.06.2017
 * Time: 11:29
 */

namespace Core\Classes\File\FileManager;


class FileManager
{
    const TPL_DIR           = 'dir.tpl';
    const TPL_FILE          = 'file.tpl';
    const TPL_WRAP          = 'wrap.tpl';

    const GET               = 'get';
    const PATH              = 'path';

    protected $path         = '../../';
    protected $html         = '';
    protected $tplDirId     = 23;
    protected $tree         = [];
    protected $tplList      = [];
    protected $post         = [];

    protected function __construct()
    {
        $this->post     = \kas::data()->_post()->asArr();
        $this->tplList  = \kas::load($this->tplDirId);
    }

    protected function getHtml()
    {
        if
        (
            !\kas::arr($this->tree)     ||
            !\kas::arr($this->tplList)
        )
        {
            return false;
        }

        foreach ($this->tree as $path)
        {

            $html            = '';
            $r               = [NAME => basename($path)];

            switch (is_dir($path))
            {
                case true:

                    $html    = $this->tplList[self::TPL_DIR];
                    $r[DATE] = ' ';

                break;

                case false:

                    $html    = $this->tplList[self::TPL_FILE];
                    $r[DATE] = filemtime($path);
                    $r[EXT]  = \kas::data($r[NAME])->explode('.')->last();

                break;

                default:
                    continue;
                    break;
            }

            $this->html .= \kas::tpl($r, $html)->asStr();
        }

        return true;
    }

    protected function getTree()
    {
        \kas::str($this->post[self::PATH]) ?
            $this->path .= $this->post[self::PATH] : false;
        $this->tree = \kas::scan($this->path, 5);
        return true;
    }

    protected function conf()
    {
        if (!\kas::arr($this->post)) {
            return false;
        }

        switch($this->post[ACT])
        {
            case self::GET:

                $this->getTree();
                $this->getHtml();
                return $this->html;

            break;
        }

        return false;
    }

    static public function run()
    {
        $ob = new static();
        return $ob->conf();
    }
}