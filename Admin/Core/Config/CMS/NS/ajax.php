<?php

switch ($_POST[KAS_TYPE])
{
    case CATEGORIES:

        switch($_POST[ACT])
        {
            case SAFE:
                return print \kas::ob()->catalog()->safe();
                break;

            case INS:
                return print \kas::ob()->catalog()->ins();
                break;
            
            case DEL:
                return print \kas::ob()->catalog()->del();
                break;
        }

    break;

    case TAB:
        return print \kas::ob()->table()->mod();
    break;
    
    case TASK:
        return print \Core\Classes\TaskManager\TaskManager::run();
    break;

    case SN:
        return print \Core\Classes\TaskManager\Handlers\SynchronizationHandler::action();
    break;

    case TERMINAL:
        return print \Core\Classes\Terminal\Terminal::run();
    break;
    
    case FM:
        return print \Core\Classes\File\FileManager\FileManager::run();
    break;

}
