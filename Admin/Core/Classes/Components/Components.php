<?php
namespace Core\Classes\Components;
use Core\Classes\Gallery\Gallery;

class Components
{
    protected function __construct()
    {

    }

    public function gallery()
    {
        $ob = new Gallery();
        return $ob->getHtml();
    }

    /**
     * @return $this
    */
    static public function run()
    {
        $ob = new static();
        return $ob;
    }
}
