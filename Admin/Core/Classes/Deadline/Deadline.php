<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 28.06.2017
 * Time: 12:43
 */

namespace Core\Classes\Deadline;


class Deadline
{
    const DL_START          = 'start';
    const DL_END            = 'end';
    const DL_LIMIT          = 'limit';
    const DL_PARAMS         = 'params';

    // Max deadline time. 5 second max.
    protected $limit        = 5;
    protected $processId    = '';
    protected $params       = [];
    protected $ss           = [];

    protected $state        = true;

    protected function conf()
    {
        if (!\kas::str($this->processId)) {
            $this->state = false;
            return false;
        }

        $this->ss = &$_SESSION[$this->processId];
        \kas::arr($this->ss) ?: $this->ss = [];

        $this->state = true;
        return true;
    }

    public function __construct($processId = '') {
        $this->conf();
    }

    public function start($limit = 0)
    {
        (int) $limit == 0 ?: $this->limit = $limit;

        // Set opt
        $this->ss[self::DL_START] = time();
        $this->ss[self::DL_LIMIT] = $this->limit;
        $this->ss[self::DL_END]   = $this->ss[self::DL_START] + $this->limit;

        return true;
    }

    public function end() {
        return time() > $this->ss[self::DL_END] ?
            true : false;
    }

    public function setOpt($params = [])
    {
        if
        (
            !\kas::arr($this->ss)   ||
            !$this->state
        )
        {
            return false;
        }

        $this->ss[self::DL_PARAMS] = $params;
        return true;
    }

    public function getOpt() {
        return $this->ss[self::DL_PARAMS] ?: false;
    }
}