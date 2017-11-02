<?php

namespace Core\Classes\TaskManager;

/**
 * Менеджер выполнения задач.
*/
class TaskManager
{
    const TM            = __CLASS__;
    const SN            = 'synchronization';
    const CID           = 'childId';
    const PID           = 'parentId';
    const LOC           = 'loc';
    const HTML          = 'html';
    const EXEC          = 'execute';
    const PUSH          = 'push';
    const TPL_ID        = 10;
    const CANCEL        = 'cancel';
    const TASK_ID       = 'taskId';
    const HANDLER       = 'Handler';

    const P_TAB         = 'parentTable';
    const C_TAB         = 'childTable';

    // $_SESSION
    protected $ss       = [];
    // $_POST
    protected $post     = [];

    // Тип Задачи
    protected $tType    = '';

    // Идентификатор задачи
    protected $tId      = '';

    // HTML содержимое
    protected $html     = '';

    protected function __construct()
    {
        $this->post     = \kas::data()->_post()->asArr();
        $this->tType    = $this->post[DATA][KAS_TYPE];
        $this->setSS();
    }

    protected function setSS()
    {
        \kas::arr($_SESSION[self::TM]) ?:
            $_SESSION[self::TM] = [];

        $this->ss = &$_SESSION[self::TM];

        return true;
    }

    // Добавляет новую либо обновляет старую задачу.
    protected function pushTask()
    {
        $tk     = &$this->ss[$this->tType];
        // Передаваемый идентификатор

        $tId    = $this->post[DATA][self::TASK_ID];
        // Идентификатор по умолчанию
        $taskId = time();

        // Создать новый буффер задач
        \kas::arr($tk) ?:
            $tk = [];

        // Если нет идентификатора, присвоить по умолчанию.
        \kas::str($tId) ?:
            $tId = $taskId;

        $tk[$tId] = $this->post[DATA];
        $tk[$tId][self::TASK_ID] = $tId;

        return true;
    }

    /**
     * Формирует данные о синхронизации
     * @param $task array
     * @return string
    */
    protected function snGetInfo($task = [])
    {
        if (!\kas::arr($task)) {
            return '';
        }

        $data       = [];
        $data[ID]   = $task[self::TASK_ID];
        $data[TYPE] = $task[TYPE];

        $data[NAME] = \kas::data(\kas::st(2))
            ->r('%C%', count($task[self::CID]))->asStr();

        return [$data];
    }

    protected function getHtml()
    {
        // $this->ss = [];

        if (!\kas::arr($this->ss)) {
            return false;
        }

        foreach ($this->ss as $tType => $tasks)
        {
            // $task - содержит массив задач типа $tType, например
            // $tType = synchronization
            // Каждый элемент $tasks имеет свой ID и параметры обработки.
            if (!\kas::arr($tasks)) {
                continue;
            }

            foreach ($tasks as $tId => $task)
            {
                // Установить тип события
                $task[TYPE]  = $tType;

                switch($tType)
                {
                    // Выполнить синхронизацию
                    case self::SN:

                        $this->html .= \kas::tpl($this->snGetInfo($task),
                            self::TPL_ID)->asStr();

                    break;
                }
            }
        }

        return $this->html;
    }

    /**
     * Обработчики процессов
    */
    protected function synchronizationHandler() {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        return \Core\Classes\TaskManager\Handlers\SynchronizationHandler::run();
    }

    protected function exec()
    {
        // Текущий метод-обработчик процесса
        $method = $this->post[DATA][KAS_TYPE] . self::HANDLER;

        // Обработчик отсутствует
        return method_exists($this, $method) ?
            $this->{$method}() : false;
    }

    protected function conf()
    {
        if
        (
            !\kas::arr($this->post)     ||
            !\kas::str($this->tType)
        )
        {
            return false;
        }

        switch ($this->post[ACT])
        {
            case self::EXEC:
                return $this->exec();
            break;

            case self::PUSH:

                return $this->pushTask() ?
                    true : false;

            break;

            case self::CANCEL:

            break;

            case self::HTML:
                return $this->getHtml();
            break;
        }

        return $this;
    }

    /**
     * @param bool|string $taskType
     * В качестве входящего аргумента принимает тип задачи,
     * например - synchronization
     *
     * @return mixed
    */
    static public function getSs($taskType = false)
    {
        $ob = new static();
        $ob->setSS();

        $ssType = &$ob->ss[$taskType];
        
        return \kas::str($taskType) ?
            \kas::arr($ob->ss[$taskType]) ? $ssType : false :
            $ob->ss;
    }

    static public function run()
    {
        $ob = new static();
        return $ob->conf();
    }
}