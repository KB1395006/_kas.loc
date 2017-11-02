<?php
/**
 * Created by PhpStorm.
 * User: diego
 * Date: 30.09.17
 * Time: 12:11
 */

namespace Core\Classes\Terminal\Handlers;


class CatalogHandler
{
    const ARG_NEW = 'new';

    protected $catName = 'Category - ';
    protected $msg     = '';
    protected $cA      = [0];
    protected $args    = [];
    /**
     * @param object \PDO
    */
    protected $dbh;

    protected function __construct($args = []) {
        $this->dbh  = \kas::dbh();
        $this->args = $args;
    }

    /**
     * @param int $count
     * @return bool
     */
    protected function createCategories($count = 0) {

        if (!(int) $count) {
            return false;
        }

        $cA = $this->cA;
        $this->cA = [];

        $sth = $this->dbh->prepare(\kas::sql()->simple()->ins(CATEGORIES, [NAME, TITLE, PID]));

        foreach ($cA as $pid)
        {

            for ($i = 0; $i < (int) $count; $i++)
            {
                $state = $sth->execute([$this->catName . $i, $this->catName . $i, (int) $pid]);

                if (!$state)
                {
                    //var_dump($sth->errorInfo());
                    $this->msg = \kas::st(53, true);
                    return false;
                }

                $this->cA[] = $this->dbh->lastInsertId();
            }
        }

        return \kas::arr($this->cA) ? true : false;
    }

    protected function catGenerator()
    {
        $cc = array_slice($this->args, 2);

        if (!\kas::arr($cc)) {
            return \kas::st(51, true);
        }

        foreach ($cc as $count) {
            if (!$this->createCategories($count)) {
                return false;
            }
        }

        $this->msg = \kas::st(52, true);
        return true;
    }

    /**
     * @return string
    */
    protected function conf()
    {
        if (!\kas::arr($this->args) || !\kas::str($this->args[1])) {
            \kas::ext('Invalid arguments');
            return \kas::st(1, true);
        }

        switch($this->args[1])
        {
            case self::ARG_NEW:
                $this->catGenerator();
                return $this->msg;
            break;
        }


        return \kas::st(1, true);
    }

    /**
     * @param array $args
     * @return string
    */
    static public function run($args = []) {
        $ob = new static($args);
        return $ob->conf();
    }
}