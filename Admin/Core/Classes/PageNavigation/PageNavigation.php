<?php

namespace Core\Classes\PageNavigation;
/**
 * Класс создает постраничную навигацию для выбранного раздела.
*/
class PageNavigation
{
    protected $pageLimit    = 20;
    protected $id           = 0;
    protected $tplId        = 0;

    // Общее количество элементов
    protected $count        = 0;
    // Количество страниц
    protected $pages        = 0;
    protected $html         = '';
    
    protected function __construct($id = 0, $tplId = 0) {
        $this->id = \kas::str($id) ? $id : \kas::getId();
        $this->tplId = (int) $tplId;
    }

    protected function sqlGetCount()
    {
        // Постраничная навигация.
        $pSql      = \kas::sql()->simple()->sel(OFFERS, ['COUNT(*)'], [0], [CID]);
        $pSql     .= ' AND ' . CODE . ' != ? ';
        $r        = \kas::sql()->exec($pSql, [$this->id, '']);

        if (!\kas::arr($r)) {
            return false;
        }

        $this->count = current($r[0]);
        return true;
    }

    protected function conf()
    {
        if
        (
            !\kas::str($this->id)       ||
            !\kas::str($this->tplId)    ||
            !$this->sqlGetCount()
        )
        {
            return '';
        }

        return $this;
    }

    public function html()
    {
        if (!\kas::str($this->count)) {
            return false;
        }

        $this->pages = $this->count / $this->pageLimit;

        // Количество товаром меньше заданного лимита
        if ($this->pages <= 1) {
            return false;
        }

        is_int($this->pages) ?:
            $this->pages = ceil($this->pages);

        $data = [];

        // Преобразовать для работы с шаблонизатором
        for ($i = 1; $i <= $this->pages; $i++) {
            $data[] = [ID => $i];
        }

        $this->html = \kas::tpl($data, $this->tplId)->asStr();
        return $this->html;
    }

    /**
     * @param int $id
     * @param int $tplId
     * @return PageNavigation
     */
    static public function run($id = 0, $tplId = 0)
    {
        $ob = new static($id, $tplId);
        return $ob->conf();
    }
}