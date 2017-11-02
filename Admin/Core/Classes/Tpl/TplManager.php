<?php
/**
 * Шаблонизатор KAS-PLATFORM
 * Осуществляет поиск и подмену данных по маркеру шаблона.
 *
 * Класс поддерживает следующие маркеры:
 * <!--%NAME_1%-->, <!--%NAME_1 NAME_2%-->
 *
 * Все названия маркеров должны быть заданны в верхнем регистре.
 * Поддерживается следующий формат маркеров: 0-9A-Z_
*/
namespace Core\Classes\Tpl;
use \Core\Classes\CMD\CMD;


/**
 * Запуск класса с дополнительными методами обработки
 *
 * Предположим, что параметр $arg_1 содержит выборку из БД,
 * которая включает в себя поле KAS_TITLE. Чтобы мы могли
 * динамически модифицировать данный параметр необходимо
 * выполнить следующее:
 *
 * \Core\Classes\Tpl\TplManager::run($arg_1, $arg_2)
 *  ->KAS_TITLE(function($v){
        return $v[KAS_TITLE] . '-changed';
 * });
 *
 * В качестве возвращаемого значения, должна быть
 * отправлена строка с соответствующими изменениями.
 *
 * Цепочка вызовов:
 *
 * \Core\Classes\Tpl\TplManager::run($arg_1, $arg_2)
 *  ->KAS_TITLE(function($v){
 *       return $v[KAS_TITLE] . '-changed';
 * })->KAS_ID(function($v){
 *       return $v[KAS_ID] . '-changed';
 * });
 *
 *
*/
class TplManager
{
    /**
     * Родительский и дочерний шаблоны.
    */
    const CHILD             = 'CHILD';
    const PARENT            = 'PARENT';
    const TPL_EXT           = '.tpl';

    /**
     * Маркер дочернего шаблона.
     * Маркер должен быть вложен в родительский шаблон.
    */
    const M_CHILD           = '%CHILD%';
    /**
     * Директория шаблонов.
    */
    const TPL_DIR           = KAS_TPL_DIR;
    /**
     * Формат маркера шаблона.
    */
    protected $rExp         = KAS_TPL_MASK;
    /**
     * @param mixed
    */
    public $data            = [];
    /**
     * @param mixed
    */
    protected $tpl          = '';
    /**
     * Путь к запрашиваемому шаблону.
    */
    protected $tplPath      = '';
    /**
     * True, если метод config был запущен и
     * все необходимые инициализации были выполнены.
    */
    protected $state = false;
    /**
     * Html-шаблон.
     * @param string
    */
    protected $tplData      = [];
    /**
     * Уровень вложенности обрабатываемого массива.
    */
    protected $arrLvl       = 0;
    /**
     * Стек вызовов пользовательских методов.
    */
    protected $callStack    = [];

    /**
     * Объект среды окружения.
    */
    protected $ENV;

    /**
     * Уведомляет класс о вызове метода __call
    */
    protected $isCall = false;

    /**
     * Результирующий html-фрагмент после сборки шаблонов.
    */
    protected $content = '';

    /**
     * Найденные маркеры дочернего и родительского шаблонов.
    */
    protected $tplParentMasc    = [];
    protected $tplChildMasc     = [];

    /**
     * Интерпритировать переменные среды
    */
    protected $globalsConvert   = true;


    /**
     * @param $data mixed
     * @param $tpl mixed
     * @param $globalsConvert bool
     *
     * $data - контент интерпритируемого шаблона.
     * Поддерживаются следующие структуры данных:
     *
     * 1. array( 'NAME_1' => 'VALUE' )
     * 2. array( 0 => ['NAME_1' => 'VALUE'] )
     * 3. Константа SQL-запроса.
     *
     * $tpl - html-шаблон.
     * Поддерживаются следующие структуры данных:
     *
     * 1. Идентификатор шаблона.
     * 2. Путь к заданному шаблону.
     * 3. Содержимое шаблона.
    */
    protected function __construct($data = false, $tpl = false, $globalsConvert = true)
    {
        $this->data = $data;
        $this->tpl  = $tpl;

        $this->globalsConvert = (bool) $globalsConvert;

        /**
         * Установить среду окружения.
        */
        $this->ENV  = \ENV::_();
        /**
         * Установить путь к шаблонам.
        */
        $this->tplPath = self::TPL_DIR . $this->ENV->DIR;
    }

    public function __call($methodName, $args)
    {
        /**
         * Уведомить класс.
        */
        $this->isCall = true;

        /**
         * Проверка параметров.
        */
        if (!$this->config()) {
            return false;
        }

        if
        (
            !is_callable($args[0])                  ||
            !$this->isUsrMethodValid($methodName)
        )
        {
            return $this;
        }

        /**
         * Зарегистрировать пользовательский
         * метод-обработчик.
        */
        $this->callStack[$methodName] = $args[0];

        return $this;
    }

    /**
     * Метод возвращает готовый html-фрагмент,
     * если класс был вызван без дополнительных методов (модификации)
     * значений массива $this->data.
     *
     * @return string
    */
    public function __toString()
    {

        /**
         * Проверить вызов метода __call
        */
        switch ($this->isCall)
        {
            case true:

                break;

            case false:

                if (!$this->config()) {
                    return '';
                }

                break;
        }

        /**
         * Произвести обработку $this->data
         * после вызова метода $this->config...
        */
        if(!$this->exec()) {
            return '';
        }

        /**
         * Выполнить сборку.
        */
        if (!$this->content()) {
            return '';
        }

        // Подключить тексты интерфейса.
        $_tmp = \kas::st($this->content);

        switch ($this->globalsConvert)
        {
            case true:

                // Подключить системные пути.
                $_tmp = \kas::doc()->pathes($_tmp);

                break;

            case false:
                break;
        }

        // Подключить интерпритатор.
        $_tmp = CMD::exec($_tmp);
        
        // Вернуть собранный html-фрагмент.
        return $_tmp ? $_tmp : $this->content;
    }

    public function asStr() {
        return $this->__toString();
    }

    /**
     * Метод осуществляет обход и обработку результирующего массива
     * $this->data. Если был определен стек пользовательских
     * вызовов $this->callStack метедод применяет его содержимое к
     * результирующему массиву $this->data.
     *
     * @return bool|string
    */
    public function exec()
    {
        if (!\kas::arr($this->data)) {
            return false;
        }

        foreach ($this->data as $k => $elt)
        {
            if (!\kas::arr($elt))
            {
                \kas::ext('Invalid param $elt. Argument must be an array.');
                return false;
            }

            foreach ($elt as $key => $v)
            {
                switch
                (
                    \kas::arr($this->callStack)             &&
                    is_callable($this->callStack[$key])
                )
                {
                    /**
                     * Если были определены пользовательские
                     * методы-обработчики.
                    */
                    case true:

                        /**
                         * @param string
                         * Принимаем новое значение от пользовательского
                         * обработчика.
                        */
                        $usrData = $this->callStack[$key]
                        (
                            [$key => $v],
                            $elt,
                            $this
                        );

                        \kas::arr($usrData) ?
                            $usrData = current($usrData) : false;

                        /**
                         * Передаем обработчику запрашиваемые параметры
                         * и перезаписываем результирующий массив $this->data.
                        */
                        $this->data[$k][$key] = $usrData;

                        continue;

                    break;

                    case false:
                        break;

                }
            }
        }

        /**
         * Результирующий массив $this->data был обработан
         * переход к конфигурации с шаблонами.
        */
        return true;
    }

    /**
     * Метод осуществляет проверку ключей
     * пользовательских методов-обработчиков.
     * @param $methodName bool|string
     * @return bool
    */
    protected function isUsrMethodValid($methodName = false)
    {
        if
        (
            !is_string($methodName)                         ||
            !preg_match('/[0-9a-zA-Z_-]+/', $methodName)
        )
        {
            \kas::ext('Method name must be a string.');
            return false;
        }

        return true;
    }

    /**
     * Метод возвращает true, если значения массива $data
     * не являются массивами.
     *
     * @param $data array
     * @return bool|mixed
    */
    protected function ifNotHasArray($data = [])
    {
        if (!\kas::arr($data))
        {
            \kas::ext('Invalid argument $data, must be an array.');
            return false;
        }

        foreach($data as $v)
        {
            if (\kas::arr($v)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Метод получает шаблон по его идентификатору $tplId.
     * В случае ошибки метод вернет bool false.
     * @param int
     * @return bool|string
    */
    protected function getTplById($tplId = 0)
    {
        /**
         * Если, аргумент $tplId не был передан
         * используем $this->tpl по умолчанию.
        */
        $tplId ?: $tplId = $this->tpl;

        if (!$tplId)
        {
            \kas::ext('Invalid param $tplId, must be an integer.');
            return false;
        }

        $_tmp = $this->ENV->V_PATH .
            $this->tplPath . $this->tpl .  '/';

        if ( !is_dir($_tmp) )
        {
            \kas::ext('Invalid path');
            return false;
        }

        $this->tplPath = $_tmp;
        return true;
    }

    protected function getTplByPath($tplPath = false)
    {
        $this->tplPath = $tplPath;
        return true;
    }

    /**
     * Данный метод проверяет и преобразовывает
     * передаваемый аргумент $this->data к формату
     * двухуровнего массива.
    */
    protected function defineArr()
    {
        foreach($this->data as $k => $mixed)
        {
            /**
             * Если ключи массива не являются числами
             * метод оборачивает массив $this->data в
             * массив-обертку и перезапускает метод.
            */
            if (!is_integer($k))
            {
                $this->data = [$this->data];
                return $this->defineArr();
            }

            switch(\kas::arr($mixed))
            {
                case true:

                    $this->arrLvl !== 0
                        ?: $this->arrLvl = 2;

                    if
                    (
                        $this->arrLvl == 1              ||
                        !$this->ifNotHasArray($mixed)
                    )
                    {
                        \kas::ext('Invalid array structure.');
                        return false;
                    }

                    /**
                     * Проверяем каждый элемент массива
                    */
                    $lk = count($this->data) - 1;

                    if ($lk !== $k) {
                        continue;
                    }

                    return true;

                break;

                /**
                 * Одноуровневый массив.
                */
                case false:

                    $this->arrLvl !== 0
                        ?: $this->arrLvl = 1;

                    /**
                     * Тип аргумента $data должен быть скалярным.
                    */
                    if (!\kas::str($mixed))
                    {
                        \kas::ext('Invalid argument type.');
                        return false;
                    }

                    /**
                     * Предыдущий элемент массива должен быть
                     * скалярного типа. В этом случае значение свойства
                     * $this->arrLvl == 1.
                    */
                    if
                    (
                        $k              !== 0   &&
                        $this->arrLvl   !== 1
                    )
                    {
                        \kas::ext('Invalid array structure.');
                        return false;
                    }

                    $lk = count($this->data) - 1;

                    if ($lk !== $k) {
                        continue;
                    }

                    /**
                     * Если текущая итерация является последней
                    */
                    $this->data = [$this->data];

                    return true;

                break;
            }
        }

        return false;
    }

    /**
     * Метод проверяет содержимое аргумента $this->data
     * на соответствие всем необходимым требованиям класса.
    */
    protected function defineArgData()
    {
        /**
         * Проверка аргумента data
         * Данный аргумент должен быть приведен к формату
         * двумерного массива.
        */
        if (!$this->data) {
            return false;
        }

        /**
         * Если $this->data является константой sql-запроса.
        */
        if
        (
            \kas::str($this->data)  &&
            defined($this->data)
        )
        {
            // Поддержка заданного формата в разработке.
            // По завершению обработки $this->data должен быть массивом!
            return false;
        }

        if (!\kas::arr($this->data)){
            return false;
        }

        return $this->defineArr() ?
            true : false;
    }

    /**
     * Метод проверяет содержимое аргумента $this->tpl
     * на соответствие всем необходимым требованиям класса.
     *
     * @return bool
    */
    protected function defineArgTpl()
    {
        if
        (
            !\kas::str($this->tpl)  &&
            !\kas::arr($this->tpl)
        )
        {
            return false;
        }

        /**
         * Класс поддерживает, как один так и группу шаблонов.
        */
        switch (\kas::str($this->tpl)) {

            /**
             * Одиночный шаблон.
            */
            case true:

                /**
                 * Интерпритируем аргумент как идентификатор.
                */
                if (preg_match('/^\d+$/', $this->tpl)) {
                    return $this->getTplById((int) $this->tpl)
                        ? true : false;
                }

                /**
                 * Интерпритируем аргумент как путь к шаблону.
                */
                if
                (
                    is_dir($this->tpl)      ||
                    is_file($this->tpl)
                )
                {
                    return $this->getTplByPath( $this->tpl )
                        ?: false;
                }

                /**
                 * Если был передан уже готовый шаблон.
                */
                $this->tplData = [self::PARENT => $this->tpl];
                return true;

            break;

            /**
             * Группа шаблонов.
            */
            case false:

                /**
                 * Наличие родительского или дочернего
                 * шаблона обязатально.
                */
                if
                (
                    !$this->tpl[self::PARENT]  &&
                    !$this->tpl[self::CHILD]
                )
                {
                    \kas::ext('Invalid templates format.');
                    return false;
                }

                !$this->tpl[self::PARENT] ?:
                    $this->tplData[self::PARENT]    = $this->tpl[self::PARENT];
                !$this->tpl[self::CHILD]  ?:
                    $this->tplData[self::CHILD]     = $this->tpl[self::CHILD];

                return true;

            break;

        }

        return false;

    }

    /**
     * @param mixed $data
     *
     * Метод преобразует ключи типа
     * parent.tpl -> PARENT
     * child.tpl -> CHILD
     *
     * @return mixed
    */
    protected function keysControl($data = false)
    {
        if (!\kas::arr($data)) {
            return $data;
        }
        
        $_data = [];
        
        foreach ($data as $k => $v) 
        {
            $k = \kas::data($k)->r(self::TPL_EXT, '')
                ->strUp()->asStr();

            $_data[$k] = $v;
        }
        
        return $_data;
    }

    /**
     * Метод загружает требуемые шаблоны для
     * дальнейшей обработки.
     *
     * Аргумент $this->tplPath должен содержать путь к директории
     * или путь к загружаемому файлу шаблона.
     *
     * Если параметр $this->tplPath пуст, метод проверяет
     * параметр $this->tplData, который содержит содержимое шаблонов.
    */
    protected function loadTpl()
    {

        if
        (
            !$this->tplPath &&
            !$this->tplData
        )
        {
            \kas::ext('Undefined argument $this->tplPath');
        }

        /**
         * Содержимое шаблонов уже загружено.
        */
        if ($this->tplData) {
            return true;
        }

        $data = \kas::load($this->tplPath, KAS_SCAN_TPL);
        $data = $this->keysControl($data);

        if (!$data) {
            \kas::ext('Content can not be loaded.');
            return false;
        }

        if (\kas::str($data)) {
            $this->tplData[self::PARENT] = $data;
            return true;
        }

        if (!\kas::arr($data)) {
            \kas::ext('Invalid param $data');
            return false;
        }

        /**
         * Массив должен включать хотя-бы один из
         * требуемых шаблонов.
        */
        if
        (
            !\kas::str($data[self::PARENT]) &&
            !\kas::str($data[self::CHILD])
        )
        {
            \kas::ext('Invalid templates format.');
            return false;
        }

        $this->tplData = $data;
        return true;
    }

    /**
     * Контроль содержимого шаблонов.
    */
    protected function tplControl()
    {
        /**
         * Если, после проверки родительский шаблон
         * был удален, а дочерний шаблон остался, делаем
         * дочерний шаблон родительским.
        */
        !is_null($this->tplData[self::PARENT]) ?:
            $this->tplData[self::PARENT] = $this->tplData[self::CHILD];

        if (is_null($this->tplData[self::PARENT])) {
            \kas::ext('Templates data was empty');
            return false;
        }

        return true;
    }

    /**
     * Метод осуществляет проверку значений подмены.
     * @param $data null|string
     * @param $key string
     * @return string
    */
    protected function checkReplacement($data, $key)
    {
        if (!\kas::str($data)) {
            return "%{$key}%";
        }

        return $data;
    }


    /**
     * @param array $v
     * @param string $type
     * @return bool
    */
    protected function joinData($v = [], $type = '')
    {
        /**
         * Маркры шаблона.
        */
        $tplMasc        = [];
        /**
         * Значения маркеров шаблона.
        */
        $replacement    = [];

        switch ($type)
        {
            case self::PARENT:

                \kas::arr($this->tplParentMasc)         ?
                    $tplMasc = $this->tplParentMasc     :
                    preg_match_all
                    (
                        $this->rExp,
                        $this->tplData[$type],
                        $tplMasc
                    );

                if (\kas::arr($tplMasc[0]))
                {
                    $tplMasc = $tplMasc[0];
                    $this->tplParentMasc = $tplMasc;
                }

            break;

            case self::CHILD:

                \kas::arr($this->tplChildMasc)      ?
                    $tplMasc = $this->tplChildMasc  :
                    preg_match_all
                    (
                        $this->rExp,
                        $this->tplData[$type],
                        $tplMasc
                    );

                if (\kas::arr($tplMasc[0]))
                {
                    $tplMasc = $tplMasc[0];
                    $this->tplChildMasc = $tplMasc;
                }

            break;
        }

        /**
         * Если маркеры для подмены отсутствуют, возвращаем
         * исходник.
        */
        if (!\kas::arr($tplMasc)) {
            return $this->tplData[$type];
        }

        /**
         * Если, маркеры были найдены, необходимо подобрать
         * соответствующие значения и произвести подмену.
        */
        foreach ($tplMasc as $masc)
        {
            /**
             * Название маркера в шаблоне соответствует названию
             * ключа ассоциативного массива элемента, например:
             *
             * %ID% -> ID
             * %TITLE% -> TITLE
             * ...
            */
            $key = trim(str_replace('%', '', $masc ?: ''));

            /**
             * Если для запрашиваемого маркера значение отсутствует,
             * например:
             *
             * %UNDEFINED% -> ?? -> '' (все отсутствующие значения
             * будут проигнорированны, т.е маркер будет оставлен в шаблоне).
            */
            $replacement[] = $this->checkReplacement($v[$key], $key);
        }

        /**
         * Произвести замену.
        */
        $content = str_replace
        (
            $tplMasc,
            $replacement,
            $this->tplData[$type]
        );

        return $content;
    }

    /**
     * Метод соединяет данные и шаблоны в
     * единый html-фрагмент.
    */
    protected function content()
    {
        /**
         * Если исполльзуется одиночный шаблон.
        */
        count ($this->tplData) == 1 ?
            $type = self::PARENT :
            $type = self::CHILD;

        /**
         * Выполнить обход всех элементов
         * массива $this->data.
        */
        foreach ($this->data as $v)
        {
            $_tmp = $this->joinData($v, $type);

            if (!$_tmp) {
                return false;
            }

            $this->content .= $_tmp;
        }

        if ($type == self::PARENT) {
            return true;
        }

        /**
         * Сброс маркеров.
        */
        $this->tplParentMasc = [];

        /**
         * Обернуть в родительский шаблон.
        */
        $_tmp = $this->joinData
        (
            array(self::CHILD => $this->content),
            self::PARENT
        );

        if (!$_tmp) {
            \kas::ext('Parent template error.');
            return false;
        }

        /**
         * Сохранить данные.
        */
        $this->content = $_tmp;
        return true;
    }

    /**
     * @return bool
    */
    protected function config()
    {
        /**
         * Все инициализации были
         * выполнены.
        */
        if ($this->state) {
            return true;
        }

        /**
         * Определяем содержимое и тип переменных.
         * Проверяем и устанавливаем путь
         * к запрашиваемому шаблону.
        */
        if
        (
            !$this->defineArgData()     ||
            !$this->defineArgTpl()      ||
            !$this->loadTpl()           ||
            !$this->tplControl()
        )
        {
            /**
             * Очистить $this->data
            */
            $this->data = null;
            return false;
        }

        /**
         * Класс был успешно запущен, все необходимые
         * инициализации были выполнены.
        */
        $this->state = true;
        return true;
    }


    /**
     * @param $data mixed
     * @param $tpl mixed
     * @param bool $globalsConvert
     * @return object
     */
    static public function run($data = false, $tpl = false, $globalsConvert = true)
    {
        $ob = new static($data, $tpl, $globalsConvert);
        return $ob;
    }
} 