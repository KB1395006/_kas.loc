<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 08.10.2016
 * Time: 21:40
 */

namespace Core\Config;
use \Core\Classes\Generator;

/**
 * Класс просто запускает стэк SQL-запросов и
 * служит как юзабилити.
*/
class SQL
{
    const OFFERS = '';
    
    static public function tables($t = '') 
    {
        $tables = 
        [
            OFFERS      => [
                ID, NAME, C_NAME, TITLE, DESC_L, DESC_M, DESC_S, IMG_M, IMG_L, IMG_S, IMG_I,
                TYPE, PRC, FID, GID, CID, URL_SET, MODEL, CODE, MKP, VC, STATUS, URI, CUR_ID, CUR_V, NS_ID, DATE, TIME
            ],

            PUB         => [
                ID, NAME, C_NAME, TITLE, DESC_L, DESC_M, DESC_S, IMG_M, IMG_L, IMG_S, IMG_I,
                TYPE, PRC, FID, GID, CID, URL_SET, MODEL, CODE, MKP, VC, STATUS, URI, CUR_ID, CUR_V, NS_ID, DATE, TIME
            ],

            CATEGORIES  => [ID, NAME, TITLE, DESC_L, DESC_M, DESC_S, IMG_M, IMG_L, IMG_S, IMG_I,
                TYPE, PRC, PID, GID, CID, URL_SET, MODEL, STATUS, URI, CUR_ID, NS_ID, DATE, TIME
            ],

            MEDIA       => [ID, CID, NAME, TITLE, DESC_L, TYPE, SRC, PATH, MIME, STATUS, DATE, TIME],
            TPL         => [ID, CID, NAME, TITLE, DESC_M, DESC_L, TYPE, SRC, PATH, MIME, STATUS, DATE, TIME],

            NAMESPACES  => [ID, NAME, TITLE, TYPE, URL_SET, STATUS, URI, DATE, TIME]
        ];
        
        if 
        (
            !\kas::str($t)              ||
            !\kas::arr($tables[$t])
        ) 
        {
            return $tables;
        }
        
        return $tables[$t];
    }
    
    static public function run()
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \Core\Classes\DB\Tables::run()
            ->create(OFFERS,     self::tables(OFFERS))
            ->create(PUB,     self::tables(PUB))
            ->create(CATEGORIES, self::tables(CATEGORIES))
            ->create(MEDIA, self::tables(MEDIA))
            ->create(NAMESPACES, self::tables(NAMESPACES))
            ->create(TPL, self::tables(TPL))
            ->exec();
        
        Generator\Generator::defaultRowDBGenerator()
            ->create(OFFERS)
            ->create(PUB)
            ->create(TPL)
            ->exec();

        Generator\Generator::DBOGenerator();
    }
}

