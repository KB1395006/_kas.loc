<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 10.03.2017
 * Time: 19:44
 */

namespace Core\Classes\TaskManager\Handlers;
use Core\Classes\TaskManager\TaskManager as TM;
use Core\Classes\Tables\Tables as TAB;

/**
 * Класс выполняет обработку процессов синхронизации.
*/
class SynchronizationHandler
{
    const NO_SN     = 6;
    const REM       = 'remove';

    protected $post = [];
    protected $data = [];
    protected $ss   = [];

    // Текущий процесс
    protected $task = [];
    
    // Идентификатор процесса
    protected $tId  = '';

    // Location
    protected $loc  = '';

    // Текущее уведомление на вывод
    protected $msg;

    // Родительский ключ
    protected $pId  = 0;

    protected function __construct()
    {
        $this->post = \kas::data()->_post()->asArr();
        $this->data = $this->post[DATA];

        $this->getSs();
        $this->tId  = $this->post[DATA][TM::TASK_ID];
        $this->loc  = $this->post[DATA][TM::LOC];
    }

    protected function getSs()
    {
        \kas::arr($_SESSION[TM::TM]) ?
            $this->ss = &$_SESSION[TM::TM][TM::SN] : false;

        if (!\kas::arr($this->ss)) {
            return false;
        }

        return true;
    }

    /**
     * Создает системные уведомления на вывод.
     * @param int $t
     * @param int $d
     *
     * @return booL
     */
    protected function msg($t = 0, $d = 0)
    {
        if
        (
            !is_int($t)     ||
            !is_int($d)
        )
        {
            \kas::ext('Invalid params');
            return false;
        }

        $t ?: $t = 2;
        $ob = new \stdClass();

        $ob->t = \kas::st($t, true);
        $ob->d = \kas::st($d, true);

        $this->msg = json_encode($ob);
        return true;
    }

    // Определить текущую задачу
    protected function setTask()    {
        $this->task = &$this->ss[$this->tId];
        return true;
    }

    // Удаляет задачу с идентификатором $taskId
    protected function removeTask($taskId = 0)
    {
        !$taskId ?
            $taskId = $this->tId :
            false;

        unset($this->ss[$taskId]);
        return true;
    }

    protected function sqlIn($array = [])
    {
        if (!\kas::arr($array)) {
            return false;
        }

        $in     = array_fill(0, count($array), '?');
        $in     = ID . ' IN ( ' . implode(', ', $in) . ' )';

        return $in;
    }

    // Выполнить текущую задачу
    protected function execTask()
    {
        // SQL
        $sSql   = \kas::sql()->simple()->sel($this->task[TM::P_TAB],
            [NAME], [0], [ID]);

        $sql    = \kas::sql()->simple()->upd($this->task[TM::C_TAB],
            [CID, C_NAME], [$this->task[TM::PID]]);

        $sql    = \kas::data($sql)->r(C_NAME . ' = ?', C_NAME . " = ({$sSql}) ")->asStr();
        $sql   .= $this->sqlIn($this->task[TM::CID]);

        // Params
        $params = $this->task[TM::CID];

        array_unshift($params, $this->task[TM::PID],
            $this->task[TM::PID]);

        // var_dump($sql, $params);
        
        if (!\kas::sql()->exec($sql, $params)) {
            $this->msg(0, 1);
            return false;
        }

        // Удалить процесс
        $this->removeTask();

        $this->msg(0, 4);
        return true;
    }
    
    protected function getId() 
    {
        $this->pId = \kas::getId($this->loc);

        if (!$this->pId) {
            return false;
        }

        return true;
    }

    protected function push()
    {
        // Добавить родительский ID (для синхронизации)
        $this->task[TM::PID] = $this->pId;

        return true;
    }

    /**
     * Проверяет параметры свойства $this->loc
    */
    protected function locControl()
    {
        /**
         * Сформировать уведомление об ошибке.
        */
        $this->msg(0,3);

        switch ($this->getId())
        {
            case true:

                // Добавить родительский ID (для синхронизации)
                $this->task[TM::PID] = $this->pId;

                // Определить Location
                switch (\kas::loc())
                {
                    case L1:

                        $this->push();
                        return true;

                    break;

                    /**
                     * Была выбрана локация в которой так же был найден идентификатор
                    */
                    default:

                        /**
                         * Поскольку происхождение данного ID системе незнакомо, она
                         * сформирует уведомление об отсутствующим ID
                        */
                        if (!$this->task[TM::PID]) {
                            return $this->msg;
                        }

                        return true;

                    break;
                }

            break;

            case false:

                // Отсутствует родительский ID
                if (!$this->task[TM::PID]) {
                    return $this->msg;
                }

                // Есть ID
                return true;

            break;
        }

        return $this->msg;
    }

    protected function conf()
    {
        if
        (
            !\kas::arr($this->ss)               ||
            !\kas::arr($this->post)             ||
            !\kas::str($this->tId)              ||
            !\kas::arr($this->ss[$this->tId])   ||
            !$this->setTask()
        ) 
        {
            $this->msg(0,1);
            return $this->msg;
        }

        if (!is_bool($this->locControl())) {
            return $this->msg;
        }

        $this->execTask();
        return $this->msg;
    }

    // Удаляет синхронизацию
    protected function snRemove()
    {
        $sql    = \kas::sql()->simple()->upd($this->data[TM::C_TAB],
            $this->data[TAB::GROUP_COL], [1]);

        // Нет синхронизации
        $nsn    = \kas::st(self::NO_SN, true);

        /**
         * группа или идентификаторы
        */
        switch($this->data[TM::CID])
        {
            case true:
                $sql     .= $this->sqlIn($this->data[TM::CID]);
                $params   = $this->data[TM::CID];
                array_unshift($params, 0, $nsn);
            break;

            case false:
                $sql     .= $this->data[TAB::GROUP_COL][0] . ' = ? ';
                $params   = [0, $nsn, $this->data[TM::PID]];
            break;

            default:
                $this->msg(0,1);
                return $this->msg;
            break;
        }

        // Ошибка при выполнении запроса
        if (!\kas::sql()->exec($sql, $params)) 
        {
            $this->msg(0,1);
            return false;
        }

        // OK
        $this->msg(0,5);
        return true;
    }

    protected function acConfig()
    {
        if
        (
            !\kas::arr($this->post)                 ||
            !\kas::arr($this->data)                 ||
            !\kas::str($this->data[TAB::GROUP_COL])
        )
        {

            $this->msg(0,1);
            return $this->msg;
        }

        /**
         * Разбить на параметры.
        */
        $this->data[TAB::GROUP_COL] = explode(' ',
            $this->data[TAB::GROUP_COL]);

        switch ($this->post[ACT])
        {
            // Убрать синхронизацию
            case self::REM:
                $this->snRemove();
                break;

            default:
                break;
        }

        return $this->msg;

    }

    static public function run()
    {
        $ob = new static();
        return $ob->conf();
    }

    /**
     * Выполнить какое-либо действие (взависимости от параметров)
    */
    static public function action()
    {
        $ob = new static();
        return $ob->acConfig();
    }
}