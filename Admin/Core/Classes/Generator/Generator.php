<?php

namespace Core\Classes\Generator;

class Generator
{
    protected function __construct()
    {
        
    }

    /**
     * @return RoutingPageGenerator
    */
    static public function routingPageGenerator() {
        return RoutingPageGenerator::getCode();
    }

    /**
     * @return DefaultRowDBGenerator
     */
    static public function defaultRowDBGenerator() {
        return DefaultRowDBGenerator::run();
    }

    /**
     * @param array $tplData
     * @return tplGenerator
     */
    static public function createTpl($tplData = []) {
        return TplGenerator::create($tplData);
    }

    /**
     * @param array $tplData
     * @return tplGenerator
     */
    static public function removeTpl($tplData = []) {
        return TplGenerator::removeTpl($tplData);
    }

    static public function DBOGenerator() {
        return DBOGenerator::create();
    }    
}