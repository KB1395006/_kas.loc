<?php

namespace Core\Classes\Categories;


class Categories
{
    /**
     * Параметры сборки каталога.
    */
    const HTML_BUILD_ALL        = 0;
    const HTML_BUILD_PARENT     = 1;
    const HTML_BUILD_CHILD      = 2;
    const HTML_BUILD_ALL_CHILD  = 3;

    const HTML              = 'html';
    const NAME              = 'name';
    const LOC               = 'loc';

    const CAT_M             = 'cat-main';
    const CAT_S             = 'cat-sub';

    public $id              = 0;
    protected $tplId        = 2;

    protected $buildMode    = 0;

    // Шаблон замены
    public $html            = '';
    protected $loc          = '';
    protected $sql          = '';

    // Запрашивай узел
    protected $node         = [];

    protected $sqlData      = [];
    protected $group        = [];

    // Безопастный $_POST
    protected $post         = [];

    // Передаваемый список категорий после парсинга.
    protected $catList      = [];

    // Навигация
    protected $nav          = [];

    protected function __construct($tplId = 0)   {
        $this->tplId = (int) $tplId;
        $this->post = \kas::data()->_post()->asArr();
    }

    protected function setLoc()
    {
        $this->loc = \kas::uri();

        return $this->loc ?
            true : false;
    }

    /**
     * Получить идентификатор категории
     * @param string $loc
     * @return bool
    */
    protected function getId($loc = '')
    {
        if (!is_string($loc)) {
            return false;
        }
        
        \kas::str($loc) ?
            $this->loc = $loc : false;

        if (!preg_match('/\/?([0-9]+)\/?/', $this->loc, $m)) {
            return false;
        }

        $this->id = (int) $m[1];
        return true;
    }

    protected function conf()
    {
        $this->setLoc();
        $this->getId();

        return $this;
    }

    // Возвращает идентификатор группы в которой находиться
    // требуемый html-шаблон.
    protected function findPidByPid($pid = 0)
    {
        if (!$pid) {
            return 0;
        }

        foreach ($this->sqlData as $row)
        {
            if ($row[ID] != $pid) {
                continue;
            }

            return $row[PID];
        }

        return false;
    }

    // Освобождение памяти и удаление маркеров.
    protected function clear()
    {
        // $this->sqlData  = [];
        $this->group    = [];

        return true;
    }

    protected function join(&$array)
    {

        foreach ($array as $pid => $gr)
        {

            $parentId   = (int) $this->findPidByPid($pid);
            $parentHtml = $array[$parentId][self::HTML];

            if (!$parentHtml) {
                return false;
            }

            // Спец маркер вложенного элемента.
            $array[$parentId][self::HTML] = str_replace
            (
                "<!--CHILD_{$pid}-->",
                $array[$pid][self::HTML],
                $parentHtml
            );

        }


        $this->html = $this->group[0][self::HTML];
        return true;
    }

    protected function groupHtml()
    {

        foreach ($this->group as $pid => $gr)
        {
            $tpl = \kas::tpl($gr, $this->tplId)->asStr();

            if (!$tpl) {
                return false;
            }

            $this->group[$pid][self::HTML] = $tpl;
        }

        return true;
    }

    protected function group()
    {
        if (!\kas::arr($this->sqlData)) {
            return false;
        }

        foreach ($this->sqlData as $row)
        {
            !\kas::arr($this->group[$row[PID]]) ?
                $this->group[$row[PID]] = [] : false;

            $this->group[$row[PID]][] = $row;
        }

        $this->group = array_reverse($this->group, true);
        return true;
    }

    /**
     * Выполнить построение с использованием лишь
     * дочерних подкатегорий.
    */
    protected function buildOnlyChild()
    {
        if
        (
            !\kas::arr($this->sqlData)                      ||
            $this->buildMode !== self::HTML_BUILD_CHILD     &&
            $this->buildMode !== self::HTML_BUILD_ALL_CHILD
        )
        {
            return false;
        }

        foreach ($this->sqlData as $k => $row)
        {
            if ((int) $row[PID] !== 0) {
                continue;
            }

            foreach ($this->sqlData as $key => $r)
            {
                if ($this->sqlData[$key][PID] !== $row[ID]) {
                    continue;
                }

                // Тип данных string!
                $this->sqlData[$key][PID] = (string) 0;
            }

            unset($this->sqlData[$k]);
        }

        return true;
    }

    protected function sqlGet()
    {
        $this->sql  = \kas::sql()->simple()->sel(CATEGORIES,
            [ID, NAME, PID, DESC_L], [0], [PID]);

        // Подзапросы, если есть идентификатор.
        $sSql       = \kas::sql()->simple()->sel(CATEGORIES, [ID, NAME, PID, DESC_L],
            [$this->id], [CID]);
        $sSqlX2     = \kas::sql()->simple()->sel(CATEGORIES,
            [CID], [$this->id], [ID]);
        $sSql       = \kas::data($sSql)->r('?', "({$sSqlX2})")->asStr();

        switch ($this->buildMode)
        {
            // Только PID = 0
            case self::HTML_BUILD_PARENT :
                $params = [0];
            break;

            // Только дочерние категории
            case self::HTML_BUILD_CHILD :

                $this->sql  = \kas::sql()->simple()->sel(CATEGORIES,
                    [ID, NAME, PID, DESC_L], [0], [PID]) . ' UNION ';
                $this->sql .= \kas::sql()->simple()->sel(CATEGORIES, [ID, NAME, PID, DESC_L], [0]);
                $this->sql .= PID . ' IN (' . \kas::sql()->simple()->sel(CATEGORIES,
                        [ID], [0], [PID]) . ')';

                $params = [0, 0];

            break;

            case self::HTML_BUILD_ALL_CHILD :
                $this->sql  = \kas::sql()->simple()->sel(CATEGORIES,
                    [ID, NAME, PID, DESC_L], [0]) . ID . ' > ?';
                $params = [0];
            break;

            // И родительские и дочерние
            default:
                $params = [0];
            break;

        }

        if ($this->id)
        {
            $this->sql .= ' UNION ' . $sSql;
            $params[]   = $this->id;
        }

        $this->sqlData = \kas::sql()->exec($this->sql, $params);

        $this->buildOnlyChild();
        return true;
    }

    protected function sqlUpdate($data = [])
    {
        if
        (
            !\kas::arr($data)                   ||
            !\kas::str($data[self::NAME])       ||
            !\kas::str($data[self::LOC])        ||
            !$this->getId($data[self::LOC])
        )
        {
            return false;
        }

        $params = [
            $data[self::NAME],
            $data[self::NAME],
            $this->id,
        ];

        $sql = \kas::sql()->simple()->upd(CATEGORIES,
            [NAME, TITLE], $params, [ID]);

        if (!\kas::sql()->exec($sql, $params)) {
            return false;
        }

        return true;
    }

    protected function sqlInsert()
    {
        $this->sql = \kas::sql()->simple()->ins(CATEGORIES,
            [NAME, TITLE, PID, DATE, CID]);

        $d = date(KAS_DATE_FORMAT);

        switch ($this->post[AC_TYPE])
        {
            case self::CAT_M:

                $pid  = 0;
                $sSql = \kas::sql()->simple()->sel(CATEGORIES, ['MAX(' . CID  . ')'], [0]);
                $sSql = \kas::data($sSql)->r('WHERE ', '')->trim()->asStr();

                // Проверить запрос
                $r   = \kas::sql()->exec($sSql)[0];

                if (!$r) {
                    return false;
                }

                $cid = (int)current($r);

            break;

            case self::CAT_S:

                $pid  = (int)\kas::data($this->post[PID])->r('/[^0-9]/', '')->asStr();
                $sSql = \kas::sql()->simple()->sel(CATEGORIES, [CID], [1], [ID]);

                // Проверить запрос
                $r    = \kas::sql()->exec($sSql, [$pid])[0];

                if (!$r) {
                    return false;
                }

                $cid = (int)current($r);

                break;

            default:
                return false;
                break;
        }

        foreach ($this->catList as $n) {
            \kas::sql()->exec($this->sql, [$n, $n, $pid, $d, $cid]);
        }

        return true;
    }

    // Формирует список дочерних элементов принадлежащих
    // запрашиваемому идентификатору
    protected function getNode($id = 0, &$data = [])
    {
        if
        (
            !$id                ||
            !\kas::arr($data)
        )
        {
            return false;
        }

        foreach ($data as $k => $row)
        {
            if ( $row[PID] != $id ) {
               continue;
            }

            // Сохранить данные
            $this->node[] = $row[ID];

            // Удалить найденный элемент
            unset($data[$k]);

            // Переключиться на следующую ветвь.
            $this->getNode($row[ID], $data);
        }

        return true;
    }

    protected function sqlNode()
    {
        $gSql = \kas::sql()->simple()->sel(CATEGORIES, [ID, PID], [1, 1], [CID]);
        $sSql = \kas::sql()->simple()->sel(CATEGORIES, [CID], [1], [ID]);
        $gSql = \kas::data($gSql)->r('?', '(' . $sSql . ')')->asStr();

        $this->sqlData  = \kas::sql()->exec($gSql, [$this->id]);

        return \kas::arr($this->sqlData) ?
            true : false;
    }


    protected function sqlDelete()
    {
        if
        (
            !$this->id          ||
            !$this->sqlNode()
        )
        {
            return false;
        }

        $this->getNode($this->id, $this->sqlData);

        // Добавить родительский идентификатор.
        $this->node[] = $this->id;

        // Сформировать запрос.
        $in   = @implode( ', ', array_fill(0, count($this->node), '?') );
        $sql  = \kas::sql()->simple()->del(CATEGORIES);
        $sql .= ID . ' IN (' . $in . ')';

        // Выполнить запрос.
        return \kas::sql()->exec( $sql, $this->node ) ?
            true : false;
    }

    // Сохраняет изменения в категория, которые были переданы
    // путем $_POST
    protected function safeData()
    {
        foreach ($this->post as $data)
        {
            if
            (
                !\kas::arr($data)           ||
                !$this->sqlUpdate($data)
            )
            {
                continue;
            }
        }

        return true;
    }

    // Сформировать список категорий
    protected function parseData()
    {
        if (!\kas::str($this->post[DATA])) {
            return false;
        }

        preg_match("/\n/", $this->post[DATA]) ?
            $this->catList = \kas::data($this->post[DATA])->clear()->explode("\n")->trim() :
            $this->catList = \kas::data($this->post[DATA])->clear()->explode(',')->trim();

        // Преобразовать в массив
        $this->catList = $this->catList->asArr();

        \kas::arr($this->catList) ?
            $this->catList = $this->catList[0] :
            false;

        return true;
    }

    protected function insData()
    {
        if
        (
            !$this->parseData()     ||
            !$this->sqlInsert()
        )
        {
            return false;
        }

        return true;
    }

    protected function delData()
    {
        if
        (
            !$this->getId($this->post[ID])  ||
            !$this->sqlDelete()
        )
        {
            return false;
        }


        return true;
    }


    protected function getNav($id = 0)
    {
        if
        (
            !\kas::arr($this->sqlData)  ||
            !\kas::str($id)
        )
        {
            return false;
        }

        foreach ($this->sqlData as $row)
        {
            if ($id == $row[ID])
            {
                $this->nav[] = $row;
                return $this->getNav($row[PID]);
                break;
            }
        }

        return false;
    }

    // Выполняет построение навигации по
    // идентификатору элемента.
    public function nav($tplId = 0, $id = 0)
    {
        if
        (
            !\kas::str($tplId)      ||
            !$this->sqlGet()
        )
        {
            return false;
        }

        !\kas::str((int) $id) ?:
            $this->id = $id;

        foreach ($this->sqlData as $row)
        {
            if ($this->id == (int) $row[ID]) {
                $this->nav[] = $row;
                break;
            }
        }

        if (!\kas::arr($this->nav)) {
            return false;
        }

        $this->getNav($this->nav[0][PID]);
        $this->nav = array_reverse($this->nav);

        // Построить навигацию.
        return \kas::tpl($this->nav, $tplId)->asStr();
    }

    public function current($colName = '')
    {
        if
        (
            !\kas::arr($this->sqlData)  ||
            !\kas::str($colName)        ||
            !\kas::str($this->id)
        )
        {
            return false;
        }

        foreach ($this->sqlData as $row)
        {
            if ($row[ID] == $this->id) {
                return $row[$colName] ?: false;
            }

            continue;
        }

        return false;
    }

    // Возвращает html-разметку каталога.
    public function html($buildMode = 0, $returnSelf = false)
    {
        /**
         * Определяет параметры сборки каталога.
        */
        $this->buildMode = (int) ($buildMode);

        if
        (
            !$this->sqlGet()            ||
            !$this->group()             ||
            !$this->groupHtml()         ||
            !$this->join($this->group)
        )
        {
            return false;
        }


        // Освободить память, удалить маркеры
        $this->clear();

        return $returnSelf ?
            $this : $this->html;
    }

    // Сохранить изменения в БД
    public function safe()
    {
        $this->safeData();

        return \kas::ob()->catalog(2)
            ->html();
    }

    // Добавить категорию (группу категорий)..
    public function ins()
    {
        $ob = new static();
        $ob->insData();
        
        return \kas::ob()->catalog(2)
            ->html();
    }

    // Удалить категорию (группу категорий)
    public function del()
    {
        $ob = new static();
        $ob->delData();

        return \kas::ob()->catalog(2)
            ->html();
    }

    static public function getIdFromLoc($loc = '')
    {
        $ob = new static();
        $ob->getId($loc);

        return $ob->id;
    }

    static public function run($tplId = 0)
    {
        $ob = new static($tplId);
        return $ob->conf();
    }


}