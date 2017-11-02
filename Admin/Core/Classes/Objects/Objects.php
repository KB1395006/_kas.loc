<?php

namespace Core\Classes\Objects;

class Objects
{
    protected function __construct()
    {

    }

    protected function conf()
    {
        return $this;
    }

    /**
     *
     * @param int $tplId
     * @return \Core\Classes\Categories\Categories
     */
    public function catalog($tplId = 0)
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        return \Core\Classes\Categories\Categories::run($tplId);
    }

    /**
     * @param string $t
     * Show sql data as a table
     *
     * @param array $c
     * @param array $tplId - идентификаторы шаблонов (5.6.7 - по умолчанию)
     * @return \Core\Classes\Tables\Tables
    */
    public function table($t = '', $c = [], $tplId = [])
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        return \Core\Classes\Tables\Tables::run($t, $c, $tplId);
    }

    /**
     * @param int $id
     * @param int $tplId
     * @return bool|string
    */
    public function pageNav($id = 0, $tplId = 0) {
        return \Core\Classes\PageNavigation\PageNavigation::run($id, $tplId)->html();
    }

    static public function run()
    {
        $ob = new static();
        return $ob->conf();
    }
}