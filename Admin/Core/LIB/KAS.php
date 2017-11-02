<?php
/**
 * Библиотека CMS KAS.
*/
class kas
{
    /**
     * Хранилище метода cls (closure).
    */
    static protected $callStack = [];

    /**
     * Closure
     * Метод расширяет область видимости программного окружения.
     * Данный метод особенно полезен при работе с замыканиями т.к
     * все параметры доступны на любом уровне вложенности.
     *
     * Название и значение параметра.
     * @param string $prm
     * @param mixed $data
     * @return mixed
    */
    static public function cls($prm = '', $data = false)
    {

        if
        (
            !\kas::str($prm)                            ||
            !$data && is_null(self::$callStack[$prm])
        )
        {
            return false;
        }

        /**
         * Если значение данного параметра == false
         * Метод интерпритирует это как вызов для возврата
         * значения по параметру.
        */
        if (!$data) {
            return self::$callStack[$prm];
        }

        /**
         * Сохранить параметр.
        */
        self::$callStack[$prm] = $data;
        return true;
    }

    /**
     * Метод вернет true, если его инициализация
     * была выполнена в рабочей среде CMS.
    */
    static public function isCMS() {
        return \Core\Classes\Loader\Loader::_isCMS();
    }

    /**
     * Метод вернет true, если его инициализация
     * была выполнена в рабочей среде Проекта.
    */
    static public function isProj(){
        return \Core\Classes\Loader\Loader::_isPROJ();
    }

    /**
     * Метод определяет является ли переменная скалярным
     * значением за исключением значения false.
     *
     * @param $data bool|string|int|float
     * @return bool
    */
    static public function str($data = false)
    {
        if
        (
            !$data              ||
            is_array($data)     ||
            is_object($data)    ||
            is_resource($data)
        )
        {
            return false;
        }

        return true;
    }

    /**
     * @var $data array
     * @return bool
    */
    static public function arr($data = [])
    {
        if
        (
            empty($data)            ||
            !is_array($data)
        )
        {
            return false;
        }

        return true;
    }

    /**
     * Обработчик исключений платформы.
     * 
     * @var $msg string|array
     * @var $continue bool
     * true  - передать управление платформе.
     * false - остановить выполнение.
     * @return bool|string
     *
     * Данный инструмент осуществляет управление исключениями
     * платформы.
    */
    static public function ext($msg = [], $continue = true)
    {  
        \kas::arr($msg) ?
            $msg = @implode(' ', $msg) : 
            false;
        
        if (!\kas::str($msg)) {
            return false;
        }
        
        /**
         * Передать управление классу.
        */
        return \Core\Classes\ExtManager\ExtManager::run($msg, (bool) $continue);
    }

    /**
     * Метод возвращает путь к шаблону исключения.
     * @param bool $tplId
     * @return bool|string
    */
    static public function getExtensionPath($tplId = false) 
    {
        if (!\kas::str($tplId)) {
            return false;
        }
        
        return Core\Classes\ExtManager\ExtManager::getExtensionPath($tplId);
    }

    /**
     * Метод-оболочка для класса ArrayIterator.
     * Осуществляет итерации по массивам и объектам.
     *
     * @param bool|array $arr
     * @return bool|object
    */
    static public function ai($arr = [])
    {
        if
        (
            !\kas::arr($arr) &&
            !is_object($arr)
        )
        {
            return false;
        }

        return new ArrayIterator($arr);
    }

    /**
     * Преобразование обратных слешей.
     * @param string $data.
     * @return string|bool
    */
    static public function slash($data = '')
    {
        if (!\kas::str($data)) {
            return false;
        }

        return str_replace('\\', '/', $data) ?: $data;
    }

    /**
     * Метод осуществляет поиск файлов и каталогов по
     * заданному пути.
     *
     * @param string $path
     * Путь к содержимому.
     * 
     * @param int $type
     * Тип данных. Поддерживаются следующие константы:
     *
     * KAS_SCAN_DIR
     * KAS_SCAN_FILE
     * KAS_SCAN_IMG
     * KAS_SCAN_DOC
     * KAS_SCAN_ALL
     * KAS_SCAN_TPL
     * KAS_SCAN_EXT
     *
     * Директории, файлы, изображения, документы, все типы*,
     * файлы шаблонов, файлы php.
     *
     * Так же поддерживается произвольный набор расширений, например:
     * php|JPG, pdf|gif|png.., doc, JPEG,...
     * Все расширения должны быть указанны без точки.
     * Набор расширений должен иметь разделитель: |.
     *
     * @return bool|array
     * В качестве возвращаемого значения метод передает ассоциативный массив
     * типа: [" Название файла " => " Путь к файлу / название файла "]
    */
    static public function scan($path = '', $type = 0)
    {
        /*Получить шаблон по идентификатору*/
        is_int($path) ?
            $path = \kas::getTplPathById($path) : false;
        
        if
        (
            !\kas::str($path)   ||
            !is_dir($path)
        )
        {
            return false;
        }

        /**
         * Выбрать все типы файлов.
        */
        !$type ? $type = KAS_SCAN_ALL : false;
        
        return \Core\Classes\Iterator\ScanDir::run($path, $type);
    }

    /**
     * Метод возвращает путь к
     * переданному идентификатору шаблона.
     *
     * @param int $id
     * @return bool|string
     */
    static public function getTplPathById($id = 0)
    {
        if (!$id || !is_int($id)) {
            return false;
        }

        $p = \ENV::_()->V_PATH . 'Tpl' . DS;

        \kas::isCMS() ?
            $p .= KAS_CMS : $p .= KAS_APP;

        $p .= DS . (int) ($id) . DS;

        return  is_dir($p) ?
            $p : false;
    }

    /**
     * Метод осуществляет загрузку содержимого по
     * заданному пути $path.
     *
     * @param $path - путь к каталогу либо к отдельному файлу.
     * Если был передан путь к каталогу, метод выполнит загрузку
     * содержимого всего каталога с поправкой на параметр $type.
     *
     * @param $type - см. метод ::scan.
     * @return mixed
    */
    static public function load($path = '', $type = 0)
    {
        /*Получить шаблон по идентификатору*/
        is_int($path) ? 
            $path = \kas::getTplPathById($path) : false;

        if
        (
            !\kas::str($path)                   ||
            (
                !is_dir($path)                  &&
                !is_file($path)
            )
        )
        {
            return false;
        }

        is_dir($path) ?
            $path = \kas::scan($path, $type) :
            $path = [$path];

        if (!\kas::arr($path)) {
            return false;
        }

        /**
         * Массив с загруженным содержимым.
        */
        $dataArr = [];

        foreach ($path as $fileName => $p)
        {
            //Только файлы.
            if (!is_file($p)) {
                continue;
            }

            //Объект генератора.
            $gOb = \Core\Classes\File\FileReader\FileReader::content($p);

            if (!is_object($gOb)) {
                continue;
            }

            //Собрать содержимое пофрагментно.
            foreach($gOb as $string) {
                $dataArr[$fileName] .= $string;
            }
        }

        if (!\kas::arr($dataArr)) {
            return false;
        }

        //Если массив содержит только один элемент,
        //метод вернет строку.
        return count($dataArr) == 1 ?
            current($dataArr) : $dataArr;
    }

    /**
     * Тексты веб-интерфейса.
     *
     * @param $mixed mixed.
     * В качестве аргумента метод принимает:
     * ID фрагмента, один или несколько шаблонов.
     *
     * @param bool $isExtension
     * Параметр включает|отключает интерпритацию исключений.
     * Если true - работа метода будет перестроена в режим обработки
     * маркеров исключений %EXT..% в шаблонах.
     *
     * @return array|string
    */
    static public function st($mixed = false, $isExtension = false) {
        $mixed = \Core\Classes\View\SiteText\SiteTextManager::run($mixed, $isExtension);
        return html_entity_decode($mixed);
    }


    /**
     * Данный метод осуществляет конфигурацию html-шаблонов
     *
     * Примеры инициализации:
     *
     * 1. Быстрый запуск без обработчиков.
     *
     * Первый аргумент - ассоциативный массив | константа sql-запроса.
     * Второй аргумент - путь или идентификатор шаблона.     *
     * print \kas::tpl($dataArrOrConst, $tplPathOrTplId);
     *
     * 2. Запуск с обработчиком:
     *
     * Аргументы как в п.1
     * print \kas::tpl($dataArrOrConst, $tplPathOrTplId)
     *  ->KAS_TITLE(function($ob, $e){return $e})->KAS_COUNT(function($ob, $e){});
     *
     * $ob - объект класс TplManager
     * $e - запрашиваемый параметр.
     *
     * 3. Передача обработчика, как аргумента.
     * print \kas::tpl($dataArrOrConst, $tplPathOrTplId, [KAS_TITLE, function($ob,$e){return $e}]);
     * В качестве последнего аргумента, метод принимает массив, первым элементом которого
     * является один или несколько параметров к которым будет применен обработчик.
     *
     * 4. Применение обработчика к группе параметров.
     * print \kas::tpl($dataArrOrConst, $tplPathOrTplId, array([KAS_TITLE, KAS_COUNT], function($ob,$e){return $e}));
     *
     * 5. Запуск метода с группой параметров и группой обработчиков.
     * print \kas::tpl($dataArrOrConst, $tplPathOrTplId, array([KAS_TITLE, function($ob,$e){return $e}], [KAS_COUNT,     * function($ob,$e){return $e}]));
     *
     * 6. Применение обработчика к группе параметров + запуск метода с группой параметров и группой обработчиков.
     * print \kas::tpl($dataArrOrConst, $tplPathOrTplId, array( [KAS_TITLE, KAS_COUNT], function($ob,$e){return          * $e}], [KAS_COUNT, function($ob,$e){return $e}]));
     *
     *
     * @param mixed $tplData
     * Массив или константа sql-запроса.
     *
     * @param mixed $pathData
     * Путь к шаблону или идентификатор шаблона.
     *
     * @param bool|array $callbackArr
     * Обработчики.
     * Один обработчик может быть использован одновременно для
     * нескольких полей, например:
     * [[KAS_TITLE, KAS_DESCRIPTION], function($e) {}]
     *
     *
     * @param bool $globalsConvert
     * @return bool|\Core\Classes\Tpl\TplManager|string
     */
    static public function tpl
    (
        $tplData        = false,
        $pathData       = false,
        $callbackArr    = false,
        $globalsConvert = true
    )
    {
        /**
         * Базовая проверка.
        */
        if
        (
            !\kas::str($tplData)     &&
            !\kas::arr($tplData)     ||
            !\kas::str($pathData)
        )
        {
            return false;
        }

        if (\kas::str($tplData))
        {
            /**
             * Константа неопределена
            */
            if (!defined($tplData)) {
                return false;
            }

            /**
             * Выполнение запроса по константе.
            */
        }        

        /**
         * Инициализация.
        */
        $ob = \Core\Classes\Tpl\TplManager::run($tplData, $pathData, $globalsConvert);

        /**
         * Если было передано значение без обработчика.
        */
        if (!\kas::arr($callbackArr)) {
            return $ob;
        }

        /**
         * Если массив содержит один или группу элементов
         * с одним обработчиком.
        */
        is_callable($callbackArr[1]) ?
            $callbackArr = [$callbackArr] : false;

        /**
         * Был передан обработчик.
         * Каждый элемент данного массива представляет собой
         * двухуровневый массив первый элемент которого представляет собой
         * название одного или группы параметров к которым должен быть применен
         * данный обработчик.
        */
        foreach ($callbackArr as $v)
        {
            /**
             * Если обработчик не был передан завершить работу.
            */
            if (!is_callable($v[1]))
            {
                \kas::ext('Argument value must be a callable');
                return false;
                break;
            }

            !\kas::arr($v[0]) ?
                $v[0] = [$v[0]] : false;

            foreach ($v[0] as $method)
            {

                /**
                 * Зарегистрировать ошибку.
                */
                if (!is_string($method))
                {
                    \kas::ext("Method name must be a string.");
                    return false;
                    break;
                }

                /**
                 * Расширяем область видимости и добавляем
                 * параметры замыкания.
                */
                \kas::cls(KAS_TMP_CLOSURE_DATA, $v[1]);

                $ob-> $method
                (
                    function($ob, $e)
                    {
                        /**
                         * Получить обработчик.
                        */
                        $fn = \kas::cls(KAS_TMP_CLOSURE_DATA);
                        /**
                         * Проверить параметры.
                        */
                        if (!is_callable($fn)) {
                            return $e;
                        }
                        /**
                         * Передача параметров обработчику.
                        */
                        return $fn($ob, $e);
                    }
                );

                continue;                
            }
            
        }

        return $ob;
    }

    /**
     * Метод возвращает дескриптор подключения к БД.
     * @return \PDO
    */
    static public function dbh() {
        return \Core\Classes\DB\DB::dbh();
    }

    /**
     * Метод осуществляет управление SQL-запросами к БД.
     * Метод является драйвером класса SQL и работает с объектом, который
     * данный класс возвращает.
     *
     * Примеры работы:
     *
     * \kas::sql($sqlId, $env) - выполняет запрос к БД согласно
     * идентификатору запроса. \kas::sql(11)
     *
     * \kas::sql()->get($sqlId, $env) - предварительный просмотр sql-запроса
     * по его идентификатору.
     *
     * \kas::sql()->set($sqlId, $env, $sqlCmd) - установить sql-запрос и
     * присвоить ему идентификатор.
     *
     * Аргументы:
     *
     * @param mixed $sqlId
     * Идентификатор sql-запроса.
     *
     * @param bool|int $env
     * Переменная окружения
     * 
     * @param $params array $params
     * Параметры для динамического выполнения запроса.
     * 
     * @return \Core\Classes\DB\BaseSQL
    */
    static public function sql($sqlId = false, $env = false, $params = []) {
        return \Core\Classes\DB\BaseSQL::run($sqlId, $env, $params);
    }

    /**
     * Обработка данных.
     * @param mixed $data
     * @return \Core\Classes\Data\Data
    */
    static public function data($data = '')
    {
        $ob = new \Core\Classes\Data\Data($data);
        return $ob;
    }

    /**
     * Обход и изменение многомерных массивов.
     * Метод является оберткой класса MultipleIterator.
     *
     * @param array $targetArray
     * Целевой массив для обработки.
     *
     * @param bool|callable $callable
     * Обработчик
     *
     * @param array $argumentsArray
     * Аргументы обработчика.
    */
    static public function iterator
    (
        $targetArray        = [],
        $callable           = false,
        $argumentsArray     = []
    )
    {
        $data = \Core\Classes\Iterator\MultipleIterator::run
        (
            $targetArray,
            $callable,
            $argumentsArray
        );
        
        return $data;
    }
    
    static public function uri() {
        return \Controllers\HostController::getUri();
    }
    
    static public function loc() 
    {
        $uri = str_replace(ADMIN . DS, '',
            mb_strtolower(\kas::uri(), ENCODING)) . DS;
        
        $uri = explode(DS, $uri);
        $loc = DS;
        
        foreach ($uri as $v) 
        {
            if
            (
                !\kas::str($v)          ||
                preg_match('/(\.|[\d]+)/', $v)
            )
            {
                continue;
            }
            
            $loc .= $v . DS;
        }

        return $loc;
    }

    /**
     * Подключает запрашиваемое простаранство имен, если оно существует,
     * либо пространство имен по умолчанию.
     * 
     * @param string $uri 
     * @return mixed
    */
    static public function ns($uri = '') {
        return \Core\Classes\NS\NSManager::run($uri);
    }

    /**
     * Метод осуществляет упраление содержимым
     * выводимого документа.
     *
     * @return \Core\Classes\Document\Document
    */
    static public function doc() {
        return \Core\Classes\Document\Document::run();
    }

    /**
     * @return \Core\Classes\Components\Components
    */
    static public function components() {
        return \Core\Classes\Components\Components::run();
    }

    /**
     * @param string $src
     * @return bool
     */
    static public function isImg($src = '')
    {
        if (file_exists($src) && getimagesize($src)) {
            return true;
        }

        return false;
    }

    /**
     * @return \Core\Classes\Objects\Objects
    */
    static public function ob()
    {
        return \Core\Classes\Objects\Objects::run();
    }

    /**
     * Метод контролирует интервал подачи запросов
     * на сервер методом $_POST
    */
    static public function requestControl()
    {
        if (!\kas::arr($_POST)) {
            return false;
        }
        
        \kas::arr($_SESSION[session_id()]) ?:
            $_SESSION[session_id()] = [];

        $t = time();

        // Текущее соединение
        $c = &$_SESSION[session_id()];

        /**
         * Разрешить запрос, если промежуток между последним и текущим
         * соединениями был больше регламентированного
         * интервала.
        */
        if ($t - (int) $c[AJAX_LAST_REQUEST] > AJAX_REQUEST_TIMEOUT / 1000)
        {
            $c[AJAX_LAST_REQUEST] = $t;
            return true;
        }

        // Обновить время последнего соединения.
        $c[AJAX_LAST_REQUEST] = $t;
        
        // Интервал подачи запросов меньше регламентированного.
        return false;
    }
    
    /**
     * Извлекает идентификатор из строки запроса
     * @param string $loc 
    */
    static public function getId($loc = '') {
        return \Core\Classes\Categories\Categories::getIdFromLoc($loc);
    }


    /**
     * @param array $t
     * @param string $m
     * @param string $s
     * @param array $f
     * @param array $c
     * @return bool
    */
    static public function mail($t = [], $m = '', $s = '', $f = [], $c = [])
    {
        // Интервал отправки писем.
        $intv = SMTP_SEND_TIMEOUT;
        // Параметры подключения к SMTP
        $sc = [
            SMTP_TR         => SMTP_TR_VAL,
            SMTP_FROM       => SMTP_FROM_VAL,
            SMTP_HOST       => SMTP_HOST_VAL,
            SMTP_PORT       => SMTP_PORT_VAL,
            SMTP_USR        => SMTP_USR_VAL,
            SMTP_PSW        => SMTP_PSW_VAL,
            SMTP_REAL_NAME  => SMTP_REAL_NAME_VAL,
        ];
        // Результат отправки.
        $r = false;

        // To
        !\kas::str($t) ?: $t = [$t];
        // Msg
        !\kas::str($m) ?  $m = '' : false;
        // Subject
        \kas::str($s) ?: $s = \kas::st(4);
        // Config
        \kas::arr($c) ?: $c = [];
        // From
        \kas::str($f) ? $f = [$f] : $f = [];

        if (!\kas::arr($t)) {
            return false;
        }

        // From
        foreach ($f as $v)
        {
            if (!\kas::str($v)) {
                continue;
            }

            preg_match('/@/', $v) ?
                $sc[SMTP_FROM]      = $v :
                $sc[SMTP_REAL_NAME] = $v;
        }

        // Config
        foreach ($c as $k => $v)
        {
            if
            (
                !\kas::str($v)              ||
                is_null($sc[$k])
            )
            {
                continue;
            }

            $sc[$k] = $v;
        }

        foreach ($t as $e)
        {
            if (!\kas::str($e)) {
                continue;
            }

            $r = \Core\Classes\Smtp\Smtp::run_send([
                $e => $sc[SMTP_REAL_NAME]], $sc, [$s, $m], 2);

            sleep($intv);
            
            continue;
        }

        return $r;
    }
} 