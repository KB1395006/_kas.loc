<?php

namespace Core\Classes\Tables;

use Core\Classes\DB\BaseSQL as BS;
use \Core\Config\SQL;
use \Core\Classes\View\SiteText;
use \Core\Classes\Generator;


/**
 * Представляет результирующий набор mysql в виде таблицы
 * html
 */
class Tables
{
    // Const
    const L                     = 50;
    const C                     = '%CONTENT%';
    const K                     = '%KEY%';
    const T                     = 'TABLE';
    const GG                    = 'GET_GROUP';
    const UPD                   = 'UPDATE';
    const DEL                   = 'DELETE';
    const INS                   = 'INSERT';
    const UPL                   = 'UPLOAD';
    const COL                   = 'column';
    const CMD                   = 'COMMAND';
    const TERM                  = 'TERMINAL';

    /**
     * Параметр используеться при группировке элементов согласно
     * родительскому идентификатору.
     * $this->post[self::GROUP_COL] содержит группу параметров,
     * которые разделены пробелом.
     *
     * $this->post[self::GROUP_COL][0] - идентификатор родителя
     * $this->post[self::GROUP_COL][1] - название
    */
    const GROUP_COL             = 'GROUP_COLUMN';

    // String
    protected $t                = '';
    protected $html             = '';
    protected $cSql             = '';
    protected $cTpl             = '';

    // Array
    protected $c                = [];
    protected $sqlData          = [];
    protected $tplId            = [];
    protected $tplIdAsDefault   = [5,6,7,8];

    // Идентификатор группы (используеться для вывода
    // элементов принадлежащих $cId)
    protected $cId             = '';


    // $_POST
    protected $post             = [];

    // Эквиваленты наименований таблиц
    protected $equals           = [];

    protected $intGroup         = [ID, CODE];

    /**
     * @param string $t название таблицы
     * @param array $c колонки
     * @param array $tplId идентификаторы шаблонов (по умолчанию 5.6.7)
    */
    protected function __construct($t = '', $c = [], $tplId = [])
    {
        $this->t        = $t;
        $this->c        = $c;
        $this->post     = \kas::data()->_post()->asArr();
        $this->tplId    = $tplId;
    }

    /**
     * Установить идентификатор категории,
     * если он был передан.
    */
    protected function setCid() {
        $this->cId = (int) $this->post[CID];
        return true;
    }

    protected function conf()
    {
        \kas::str($this->t) ?:
            $this->t = $this->post[self::T];

        if (!\kas::str($this->t)) {
            return $this;
        }

        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        \kas::arr($this->c) ?:
            $this->c = \Core\Config\SQL::tables($this->t);

        if (!\kas::arr($this->c) && $this->t !== KAS_SITE_TEXT_FILE)
        {
            \kas::ext('Columns not found');
            return false;
        }

        return $this;
    }

    /**
     * Возвращает идентификатор шаблоны сборки.
     * 0,1,2 - ключи массива.
     * @param int $id
     * @return mixed
    */
    protected function getTpl($id = 0)
    {
        $id = (int) ($id);

        return is_int($this->tplId[$id]) ?
            $this->tplId[$id] : $this->tplIdAsDefault[$id];
    }

    /**
     * Собирает список заголовков thead.
    */
    protected function getHead()
    {
        // Преобразовать до уровня шаблонов.
        $c = \kas::iterator($this->c, function($k, $v){
            return [NAME => $v, ID => $k];
        });

        $this->html = \kas::tpl($c, $this->getTpl(0), false, false)->asStr();
        return $this->html ? true : false;
    }

    /**
     *
     * @param string $sql SQL-запрос к БД
     * @param array $params параметры запроса.
     *
     * @return bool
    */
    protected function getSql($sql = '', $params = [])
    {

        \kas::str($this->post[ID]) ?
            $id = (int) $this->post[ID] : $id = 0;

        $id ? $sm = ' < ' : $sm = ' > ';

        // Стандартый запрос
        $this->cSql     = \kas::sql()->simple()->sel($this->t, $this->c, $this->c);
        $this->cSql     ? $this->cSql .=  ID . $sm . ' ? AND ' . CID . ' = ? OR ' . CID . ' != ? ORDER BY ' . ID .
            ' DESC LIMIT ' . self::L : false;

        // Параметры стандартного запроса
        $p = [$id, $this->cId, ''];

        switch (\kas::str($sql))
        {
            case true:

                if (\kas::arr($params))
                {
                    $this->cSql = $sql;
                    $p = $params;
                }

                false;
        }

        if (!$this->cSql) {
            return false;
        }

        // var_dump($this->cSql, $p, $this->post[ID]);
        $this->sqlData  = \kas::sql()->exec($this->cSql, $p);

        // Вернуть true, если запрос был сформирован.
        return \kas::arr($this->sqlData)
            ? true : false;
    }

    protected function cellEncode($data = '')
    {
        if (!\kas::str($data)) {
            return $data;
        }

        return str_replace(['<', '>'], ['[[', ']]'], $data);
    }

    protected function cellDecode($data = '')
    {
        if (!\kas::str($data)) {
            return $data;
        }

        return str_replace(['[[', ']]'], ['<', '>'], $data);
    }

    /**
     * Получить шаблон ячейки
     * @return bool|string
    */
    protected function getCellTpl()
    {
        $this->cTpl = \kas::tpl([CONTENT, self::C],
            $this->getTpl(3), false, false)->asStr();

        return \kas::str($this->cTpl) ? true : false;
    }

    /**
     * Собрать данные таблицы.
    */
    protected function getBody()
    {
        if (!\kas::arr($this->sqlData)) {
            return false;
        }

        // Выполнить сборку.
        foreach ($this->sqlData as $r)
        {
            $tr = '';

            foreach ($r as $k => $c)
            {
                $tr .= str_replace([self::K, self::C], [$k, $this->cellEncode($c) ?:
                    '&nbsp;'], $this->cTpl);
            }

            $this->html .= \kas::tpl([CONTENT => $tr, ID => $r[ID]],
                $this->getTpl(1), false, false)->asStr();
        }

        return true;
    }

    /**
     * Оборачивает сборку.
    */
    protected function wrapContent()
    {
        $this->html = str_replace('%CONTENT%', $this->html,
            \kas::tpl([self::T => $this->t], $this->getTpl(2))->asStr());
        
        return true;
    }


    /**
     * Группирует элементы таблицы по параметру $g
     *
     * @param string $g
     * Group column name, GROUP BY <PID>
     * SELECT <$c> FROM <$t> GROUP BY <$g>
     *
     * @return bool|string
    */
    public function grHtml($g = '')
    {
        if
        (
            !\kas::str($g)          ||
            !\kas::str($this->t)    ||
            !\kas::arr($this->c)
        )
        {
            return false;
        }

        $sql = \kas::sql()->simple()
            ->sel($this->t, $this->c, [0], [CID]);

        if (!\kas::str($sql)) {
            return false;
        }

        // Получить первую часть запроса
        $sql  = @explode(BS::WH, $sql)[0];

        if (!\kas::str($sql)) {
            return false;
        }

        $sql .= BS::GRB . ' ' . $g . ' ORDER BY ' . NAME;
        $r    = \kas::sql()->exec($sql, [1]);

        if (!\kas::arr($r)) {
            return false;
        }

        return \kas::tpl($r, $this->tplId, false, false)->asStr();
    }

    /**
     * Возвращает html-разметку таблицы
     * @return bool|string
    */
    public function html()
    {
        if
        (
            !$this->getCellTpl()    ||
            !$this->getHead()       ||
            !$this->getSql()
        )
        {
            return false;
        }

        $this->getBody();
        $this->wrapContent();


        // Вернуть сборку
        return $this->html;
    }

    /**
     * Возвращает элементы принадлежащие передаваемому
     * идентификатору группы
    */
    protected function getElementsByGid()
    {

        if
        (
            !$this->getCellTpl()            ||
            !$this->post[self::GROUP_COL]
        )
        {
           return false;
        }


        $this->post[self::GROUP_COL] = explode(' ',
            $this->post[self::GROUP_COL]);

        $sql  = \kas::sql()->simple()->sel($this->t, $this->c, $this->c);
        $sql .= $this->post[self::GROUP_COL][0] . ' = ? ORDER BY ' . ID . ' DESC LIMIT ' . self::L;

        if
        (
            !$sql                                            ||
            !$this->getSql($sql, [(int) $this->post[ID]])    ||
            !$this->getBody()
        )
        {
            return false;
        }

        return true;
    }

    protected function terminal()
    {
        if
        (
            !\kas::str($this->t)                 ||
            !\kas::str($this->post[self::CMD])
        )
        {
            return false;
        }

        $sql = '';
        $lp  = '';
        $prm = [];




        // Команда наценки, либо уценки товара
        if
        (
            preg_match('/^(\+|\-)([0-9]+)$/', $this->post[self::CMD], $m)   &&
            $this->post[ID]
        )
        {
            // Установить значение по умолчанию.
            (int) $m[2] == 0 ?
                $mk = 1 : $mk = false;

            switch ($m[1])
            {
                // Наценка
                case '+':

                    $m[2] >= 1   ? $lp = '1.0'   : false;
                    $m[2] >= 10  ? $lp = '1.'    : false;
                    $m[2] >= 100 ? $lp = ''      : false;

                    $mk ?: $mk = (float) ($lp . $m[2]);

                break;

                case '-':
                    
                    $m[2] >= 1    ? $lp = '0.0'   : false;
                    $m[2] >= 10   ? $lp = '0.'    : false;
                    $m[2] >= 100  ? $lp = ''      : false;

                    $mk ?:
                    $mk = (float) ($lp . $m[2]);
                    $mk = 1 - $mk;

                break;
            }


            $sql = \kas::sql()->simple()->upd($this->t, [MKP], [0], [CID]);

            // В параметре HTML будет сохранено уведомление.
            \kas::sql()->exec($sql, [$mk, $this->post[ID]]) ?
                $a = [2, 7] :
                $a = [2, 1];

            $this->html = json_encode([
                't' => \kas::st($a[0], true),
                'd' => \kas::st($a[1], true)]);

            return true;
        }

        // Поиск по коду, ID ...
        if ((int) $this->post[self::CMD] > 0)
        {
            $cmd  = (int) $this->post[self::CMD];

            // Создать SQL
            $sql  = \kas::sql()->simple()->sel($this->t, $this->c, [0]);
            $sql .= implode(' = ? OR ', $this->intGroup) . ' = ? ';

            // Переопределить параметры
            $prm  = [$cmd, $cmd];
        }

        // Переопределяем
        if
        (
            !$this->getCellTpl()        ||
            !$this->getSql($sql, $prm)  ||
            !$this->getBody()
        )
        {
            return false;
        }

        return true;
    }

    protected function in($array = [])
    {
        // Проверить идентификатор
        if (!\kas::arr($array)) {
            return false;
        }

        $rng = @array_fill(0, count($array), '?');

        \kas::arr($rng) ?
            $rng = ' IN (' . implode(', ', $rng) . ')' : $rng = false;

        if (!$rng) {
            return false;
        }

        return $rng;
    }

    // Добавляет новый элемент в таблицу
    protected function tIns()
    {

        switch ((int) $this->post[ID])
        {
            // Создаст дубликат на основе существующего ID
            case true:

                \kas::arr($this->c) ?:
                    $this->c = SQL::tables($this->t);

                unset($this->c[0]);

                $sql = \kas::sql()->simple()->sel($this->t, $this->c, 0, [ID]);                
                $res = \kas::sql()->exec($sql, [$this->post[ID]]);

                if (!\kas::arr($res)) {
                    \kas::ext('Select error');
                    return false;
                }
                
                $sql = \kas::sql()->simple()->ins($this->t, array_keys($res[0]));
                $res = \kas::sql()->exec($sql, array_values($res[0]));

            break;

            case false:

                $rows = [NAME, TITLE, CID, C_NAME];
                $prms = [\kas::st(29, true), \kas::st(29, true), 0, \kas::st(6, true)];
                $sql  = \kas::sql()->simple()->ins($this->t, $rows);
                $res  = \kas::sql()->exec($sql, $prms);

            break;

            default:
                $res = false;
                break;
        }

        $res ? $this->html = \kas::st(28, true) :
            $this->html = \kas::st(1, true);

        $this->html = json_encode(['t' => \kas::st(2, true),
            'd' => $this->html]);

        return $res;

    }

    /**
     * Изменить запрашиваемую таблицу (добавить|обновить|удалить)
    */
    public function mod()
    {
        switch ($this->post[ACT])
        {
            case self::UPD:

                /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                $r = SiteText\SiteTextManager::mod(1, $this->post);

                // Тексты веб приложения успешно обновлены.
                if ($r) {
                    return true;
                }

                // Проверить идентификатор
                if (!\kas::str($this->post[ID])) {
                    return false;
                }

                Generator\Generator::createTpl($this->post);

                // Сформировать запрос
                $q = \kas::sql()->simple()->upd($this->t,
                    [$this->post[self::COL]], [1], [ID]);

                // Выполнить
                return \kas::sql()->exec($q,
                    [$this->cellDecode($this->post[DATA]), $this->post[ID]]);

            break;

            case self::DEL:

                Generator\Generator::removeTpl($this->post);
                
                $rng = $this->in($this->post[ID]);

                if (!$rng) {
                    return false;
                }

                $q = \kas::sql()->simple()->del($this->t) . ID . $rng;

                // Удалить данные
                $r = \kas::sql()->exec($q, $this->post[ID]);

                // \kas::ob()->table($this->t)->html()
                return (int) $r;

            break;

            case self::UPL:

                if
                (
                    !$this->setCid()        ||
                    !(int) $this->post[ID]  ||
                    !$this->t               ||
                    !$this->getCellTpl()    ||
                    !$this->getSql()        ||
                    !$this->getBody()
                )
                {
                    return '';
                }

                return $this->html;

            break;

            // Добавляет новый элемент в таблицу.
            case self::INS:
                $this->tIns();
                return $this->html;
            break;

            case self::GG:
                $this->getElementsByGid();
                return $this->html;
            break;

            // Запускаем терминал..
            case self::TERM:
                $this->terminal();
                return $this->html;
            break;

        }

        return false;
    }

    static public function run($t = '', $c = [], $tplId = []) {
        $ob = new static($t, $c, $tplId);
        return $ob->conf();
    }
}