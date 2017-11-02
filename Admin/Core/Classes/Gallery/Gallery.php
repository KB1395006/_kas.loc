<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 17.10.17
 * Time: 18:25
 */

namespace Core\Classes\Gallery;
use Core\Classes\DBO\Bin;


class Gallery
{
    const ATTR             = [ ATTR[0], ATTR[1] ];
    const COMPRESSION_DIR  = COMPRESSION_DIR;

    protected $queryLimit  = 30;
    protected $html        = '';
    protected $data        = [];

    public function __construct(){
        $this->setData();
    }

    /**
     * @return bool
    */
    protected function setData()
    {
        $DBO = new Bin\Media();
        $r = $DBO->getId()->getSrc()->getGroup(0,0,10);
        $r && $this->data = $r;

        return \kas::arr($this->data);
    }

    /**
     * @param string $src
     * @return bool|string
    */
    protected function setSrc($src = '')
    {
        if (!\kas::str($src) || !\kas::isImg($src)) {
            return false;
        }

        $filename = basename($src);
        $cSrc = implode
        (
            '/',
            [
                explode(DS . $filename, $src)[0],
                self::COMPRESSION_DIR[0],
                self::COMPRESSION_DIR[2],
                $filename
            ]
        );

        if (!file_exists($cSrc)) {
            return false;
        }

        return $cSrc;
    }

    /**
     * @return bool
    */
    protected function setIA()
    {
        foreach ($this->data as $k => $row)
        {
            $cSrc = $this->setSrc($row[SRC]);
            if (!$cSrc) {
                unset($this->data[$k]);
                continue;
            }

            $this->data[$k][IMG_M] = $cSrc;
            $this->data[$k][IMG_L] = $row[SRC];

            // Set attributes
            $size = getimagesize($cSrc);
            $this->data[$k][self::ATTR[0]] = $size[0];
            $this->data[$k][self::ATTR[1]] = $size[1];
            continue;
        }

        return true;
    }

    protected function setHtml() {
        $this->html = \kas::tpl($this->data, 24)->asStr();
    }

    /**
     * return bool
    */
    protected function conf()
    {
        if (!$this->setData()) {
            return false;
        }

        $this->setIA();
        $this->setHtml();
        return true;
    }

    /**
     * @return bool | string
    */
    public function getHtml()
    {
        if (!$this->conf()) {
            return false;
        }

        return $this->html;
    }

}