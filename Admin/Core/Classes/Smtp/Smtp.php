<?php
/**
 * Модуль почтовой рассылки - KAS_SMTP
 * Разработчик Киркор Артем Сергеевич.
 * Сайт разработчика: http://kas.by
 * Почта: kirkorartsiom@yandex.ru
 */

namespace Core\Classes\Smtp;
abstract class kas_smtp_ac {

    /**
     * $arr_config      - конфигурационный массив данных отправителя
     * $arr_data        - массив, состоящий из заголовка и тела сообщения,
     *                    которое может содержать html-разметку или простой текст.
     * @param $arr_mailsArr
     * @param $arr_config
     * @param $arr_data
     * @param bool $int_connectionType
    */
    abstract protected function __construct($arr_mailsArr, $arr_config, $arr_data, $int_connectionType=false);

    /**
     * Обработчик исключительных ситуаций
     * @param $msg
     * @param $int_code
     * @return
    */
    abstract protected function write_log($msg, $int_code);
    abstract protected function create_report();

    /**
     * @param $fp
     * @param bool $line
     * @return mixed
    */
    abstract protected function server_response($fp, $line=false);

    /**
     * Метод выполняет запрос или массив запросов на smtp-сервер и
     * возвращает код ответа (модуля).
     * @param $fp
     * @param $arr_cmd
     * @param bool $line
     * @param bool $comment
     * @return
    */
    abstract protected function smtp_cmd($fp, $arr_cmd, $line=false, $comment=false);

    /**
     * Соединяемся с smtp.
     */
    abstract protected function smtp_connect();

    /**
     * Выполнение и анализ запросов на smtp-сервер.
     */
    abstract protected function smtp_requests();
    abstract protected function create_mails_arr($dirtyMailsArr);

    /**
     * Создать заголовок с использованием urlencode...
     * @param $data
     * @return
     */
    abstract protected function get_pattern($data);

    /**
     * Метод осуществляет поиск заголока по его фрагменту
     * в результирующем массиве $this->headers.
     * @param $needle
     * @return
     */
    abstract protected function get_header_key($needle);

    /**
     * Метод создает два типа заголовков с использованием
     * вспомагательных методов, анализируя $k, которая может
     * являться email-адресом...
     * @param $headerName
     * @param $arr_data
     * @return
     */
    abstract protected function create_header($headerName, $arr_data);
    abstract protected function get_headers();

    /**
     * метод устанавливает параметры конфигурационного
     * массива $this->config.
     * @param $configArray
     * @return
     */
    abstract protected function set_config_array($configArray);

    /**
     * метод анализирует содержимое массива $arr_data,
     * определяет тип контента для тела сообщения,
     * проверяет длину заголовка subj.
     * @param $arr_data
     * @return
     */
    abstract protected function config_data_array($arr_data);

    /**
     * Центральный конфигурационный узел модуля.
     */
    abstract protected function config();

}

class Smtp extends kas_smtp_ac {

    /**
     * Протокол с которым будет работать smtp-клиент.
     */
    const TR                        = 'smtp_transport';

    const HOST                      = 'smtp_host';
    const PORT                      = 'smtp_port';
    const TIME                      = 'smtp_timeout';
    const USER                      = 'smtp_username';
    const PASS                      = 'smtp_password';

    /**
     * От кого yourmail@mail.com
     */
    const FROM                      = 'smtp_from';

    /**
     * Указывается Ваше имя.
     */
    const NAME                      = 'smtp_real_name';
    const EML                       = 'smtp_email';
    const LEN                       = 'smtp_header_length';
    const HEADER                    = 'smtp_header_data';

    /**
     * Название модуля.
     */
    protected $moduleName           = 'KAS_smtp_v_1.1(kas.by)';

    /**
     * Дирректория хранеия лог-файла.
     */
    protected $logFileDir           = 'log/';
    protected $logFileName          = 'events.log';

    /**
     * Дирректория хранения журнала.
     */
    protected $reportFileDir        = 'report/';

    /**
     * В файл журнала записывается результирующий отчет доставленных писем,
     * общее количество отправки, количество недоставленых писем, и адреса
     * на которые не удалось отправить сообщение.
     */
    protected $reportFileName       = 'report.log';

    /**
     * Разделитель строк кода в лог-файле, файле журнала и заголовках headers.
     * @var string
     */
    protected $delimiter            = "\r\n";

    /**
     * @var string двойной разделитель заголовков.
     */
    protected $doubleDelimiter      = "\r\n\r\n";

    /**
     * Массив недоставленных писем - записывается в журнал отчетов.
     * @var array
     */
    protected $undeliveredMails     = array();

    /**
     * Массив доставленных писем - записывается в журнал отчетов.
     */
    protected $deliveredMails       = array();

    protected $mailsArr             = array();

    /**
     * Конфигурационный массив - содержит пользовательские настройки.
     */
    protected $config               = array();
    protected $data                 = array();

    /**
     * Домен отправителя.
     */
    protected $domain               = array();

    /**
     * $fp - дескриптор подключения.
     */
    protected $fp                   = '';

    /**
     * Тип отправки сообщения (smtp/mail)
     * 1 || false - mail
     * 2 - smtp
     */
    protected $connectionType       = '';

    /**
     * Максимальное количество байт, которое
     * может содержать тема письма ($data[0])
     */
    protected $subjLen              = 150;

    /**
     * Тип отправляемого контента для тела сообщения:
     * $data - массив передаваемый конструктору класса,
     * который содержит заголовок и тело сообщения, оно
     * может быть двух типов (text/plain | text/html).
     */
    protected $contentType          = '';

    /**
     * Кодировка письма.
     * Модуль будет работать с кодировкой Юникод.
     */
    protected $charset              = 'utf-8';

    /**
     * Content-Transfer-Encoding base64
     */
    protected $ctEnc                = 'base64';

    /**
     * Приоритет письма по умолчанию 3 (Normal)
     * @var array
     */
    protected $priority             = array
    (
       1 => '1 (Highest)',
       2 => '2 (High)',
       3 => '3 (Normal)',
       4 => '4 (Low)',
       5 => '5 (Lowest)'
    );

    /**
     * Все заголовки будут добавлены в результирующий массив.
     */
    protected $headers              = array();

    /**
     * Содержимое письма + заголовки.
     */
    protected $readyContent         = '';


    /**
     * Описание конструктора:
     *
     *      1.  аргумент ($arr_mailsArr) - это массив адресов электронных почт,
     *          котрый может иметь от одного и до бесконечного колличества email-адресов.
     *          Например: array(yourmail@mail.com).
     *
     *      2.  аргумент($arr_config) - конфигурационный массив с настройками отправителя.
     *          Т.к программа расчитана на массовые и на частные рассылки,
     *          некоторые поля могут оставаться незаполнеными.
     *
     *          Образец конфигурационного массива:
     *
     *          array
     *          (
     *              'smtp_transport'    =>  'tcp',
     *              'smtp_host'         =>  'mail.kas.by',
     *              'smtp_port'         =>  false,          - если данный параметр не указывать, соединение будет
     *                                                        осуществляться с использованием 25 порта.
     *              'smtp_username'     =>  'info@kas.by',  - почтовый логин
     *              'smtp_password'     =>  'KAS20111989',  - пароль
     *              'smtp_from'         =>  'info@kas.by'   - адрес отправителя (от кого)
     *              'smtp_real_name'    =>  'Киркор Артем', - имя отправителя, данный параметр не обязателен.
     *          );
     *
     *
     *      3.  аргумент ($arr_data) - это массив состоящий из заголовка $subject и тела $msg.
     *
     *      4.  аргумент ($int_connectionType) - это тип отправки сообщения:
     *          1 - mail;
     *          2 - smtp.
     *          По умолчанию данный параметр имеет значение false, и использует тип отправки mail().
     * @param $arr_mailsArr
     * @param $arr_config
     * @param $arr_data
     * @param bool $int_connectionType
     */

    protected function __construct
                                 (
                                    $arr_mailsArr,
                                    $arr_config,
                                    $arr_data,
                                    $int_connectionType=false
                                 )
    {
        $this->create_mails_arr($arr_mailsArr);
        $this->set_config_array($arr_config);
        $this->config_data_array($arr_data);
        $this->connectionType   = $int_connectionType;
    }

    /**
     * Метод делает запись в лог-файл и возвращает код исключения.
     * Данный метод может быть использован для ригистрации исключительных ситуаций (ошибки, отчеты)
     * Код ответа должен быть в виде трех-значной цифры.
     *
     * Коды ответов и их значения:
     * 1xx - отрицательные ответы и критические ошибки модуля.
     * 2xx - положительные ответы модуля.
     * 3xx - частично-положетельные ответы модуля.
     * 4xx - различные коментарии, заметки и дополнительная информация.
     *
     * $code - подразумевает integer - тип данных.
     * @param $msg
     * @param $int_code
     * @return bool
    */
    protected function write_log($msg, $int_code) {

        if
        (
            !$msg                       ||
            !(int)($int_code)
        )
        {
            return false;
        }
        
        \kas::ext($msg);
        return $int_code;
    }

    /**
     * Метод создает результирующий отчет отправленных писем.
    */
    protected function create_report() {

        $path = $this->reportFileDir . $this->reportFileName;

        if (!file_exists($path)) {
            return $this->write_log(' Wrong report path: ', 101);
        }

        /**
         * Если имеются недоставленные адреса электронных почт,
         * зафиксируем их в журнале.
         */
        switch($this->undeliveredMails) {

            case true:

                $report   = array();
                $report[] = 'Total mails - ' . count($this->mailsArr);
                $report[] = 'Undelivered mails - ' . count($this->undeliveredMails);

                $mails    = @implode($this->delimiter, $this->undeliveredMails);
                $data     = @implode($this->delimiter, $report) . $this->delimiter . $mails;

                if
                (
                    $mails                              &&
                    $data                               &&
                    @file_put_contents($path, $data)
                )
                {
                    return $this->write_log(' The report data was created: ', 201);
                }

                return $this->write_log(' Report writing error: ', 101);

                break;

            case false:

                if (@file_put_contents($path, 'All messages were sent...')) {
                    return $this->write_log(' The report data was created: ', 201);
                }

                return $this->write_log(' Error writing report. ', 102);

                break;
        }

        return $this->write_log(' Script error: ', 105);
    }

    /**
     * Метод делает запрос на сервер и возвращает массив с кодом ответа и
     * строку с подробностями соединения.
     * return array($code, $str);
     * @param $fp
     * @param bool $line
     * @return array|bool|mixed
     */
    protected function server_response($fp, $line=false) {

        if (!is_resource($fp)) {
            $this->write_log(' $fp is non resource.' . $line, 106);
            return false;
        }

        $str  = fgets($fp, 500);

        if (!$str) {

            $this->write_log
                (
                    ' Can not get response from the server - ' . $this->config[self::HOST] .
                    $line,
                    107
                );
            return false;
        }

        /**
         * Первых три символа возвращаемой строки $str - является код ответа сервера
         */
        $code = (int)(@substr($str, 0, 3));

        if ($code == 0) {

            $this->write_log
                (
                    ' Can not get response code from the server - ' . $this->config[$fp[0]][self::HOST] .
                    $line,
                    108
                );
            return false;
        }

        return array($code, $str);

    }

    /**
     * Данный метод выполняет массив запросов к smtp-серверу
     * и анализирует полученный результат, возвращая соответствующий
     * код ответа.
     * @param $fp
     * @param $arr_cmd
     * @param bool $line
     * @param bool $comment
     * @return bool
     */
    protected function smtp_cmd($fp, $arr_cmd, $line=false, $comment=false) {

        if
        (
            !is_resource($fp)        ||
            !is_array($arr_cmd)
        )
            return $this->write_log('invalid $fp || $command - ', 109);

        $responseArr = array();

        /**
         * Проводим итерации по каждой команде $command.
         */
        foreach($arr_cmd as $command) {

            fputs($fp, $command  . $this->delimiter);
            $response = $this->server_response($fp, __LINE__);

            /**
             * $response возвращает массив с кодом ответа и строкой с доп.инф
             * array($code, $str);
             * Далее необходимо проверить наличие положительных кодов ответа сервера.
             * Коды ответов smtp-сервера такие как (1xx, 2xx, 3xx)
             * являются положительными по спецификации.
             * Если получен положительный ответ записываем его в массив.
             */
            switch(preg_match('/^[1-3][0-9]{2}$/', $response[0])) {

                case true:
                    $responseArr[] = $response[0];
                    break;

                case false:

                    /**
                     * Если был добавлен коментарий к данному вызову метода,
                     * включаем его в формирование лога.
                     */
                    $comment? $response[] = $comment : false;
                    !is_array($response) ? $response = array($response) : false;
                    $this->write_log(implode(', ', $response) .' '. $line, 110);
                    break;

                default:

                    /**
                     * На случай системных сбоев и непредвиденного выполнения
                     * сценария создаем лог script-error.
                     */
                    return $this->write_log(' Script error: ', 105);
                    break;
            }

        }

        /**
         * Необходимо проанализировать количество положительных
         * ответов к общему количеству полученных команд.
         */

        if (count($responseArr) === count($arr_cmd)) {
            return $this->write_log('All commands complete: ' .  __LINE__, 202);
        }

        /**
         * Сообщаем о частичном выполнении команд.
         */
        elseif
        (
            count($responseArr) > 0                &&
            count($responseArr) < count($arr_cmd)
        )
        {
            return $this->write_log(' Requested commands have been partially implemented: ', 300);
        }

        /**
         * Запрашиваемые команды были невыполнены в полном объеме.
         */
        else  {
            return $this->write_log(' The requested commands were impracticable in full: ', 111);
        }

    }

    /**
     * Метод осуществляет подключение к smtp-серверу и возвращает
     * дескриптор соединения $fp
     */
    protected function smtp_connect() {

        if
        (
            !is_array($this->config)    ||
            !count($this->config)
        )
        return $this->write_log(' Invalid $this->config array: ', 112);

        /**
         * Протокол smtp-соединения, если в параметрах он не задан
         * будем использовать (tcp)
         */
        $this->config[self::TR] ?
            $tr = $this->config[self::TR] :
            $tr = 'tcp';

        /**
         * Порт соединения по умолчанию 25, если отсутствует альтернативный.
         */
        $this->config[self::PORT] ?
            $port = $this->config[self::PORT] :
            $port = 25;

        /**
         * Тайм-аут, устанавливаем на 10 секунд по умолчанию.
         */
        $this->config[self::TIME] ?
            $time = $this->config[self::TIME] :
            $time = 10;


        $fp = @stream_socket_client
                                  (
                                        $tr . '://' .
                                        $this->config[self::HOST] . ':' .
                                        $port,
                                        $errno,
                                        $errstr,
                                        $time
                                  );

        if (!$fp) {

            return $this->write_log($errno . ' - ' . $errstr, 112);
        }

        /**
         * Фиксируем успешное подключение.
         */
        else {
            $this->write_log($errno . ' - ' . $errstr, 203);
            return $fp;
        }

    }

    /**
     * Метод выполняет запросы на smtp
     * начиная с соединения и заканчивая отправкой сообщения.
     */
    protected function smtp_requests() {

        /**
         * Проверяем чтобы заголовки и контент были установлены,
         * иначе генерируем исключение.
         */
        if
        (
            !count($this->headers)      ||
            !is_array($this->headers)   ||
            !$this->readyContent
        )
        {
            $this->write_log(' Headers error: ', 116);
            return false;
        }

        /**
         * Подключаемся к smtp.
         */
        $this->fp = $this->smtp_connect();

        if (!is_resource($this->fp)) {

            /**
             * Возвращаем код ошибки -117.
             */
            return $this->write_log(' $this->fp is non resource: ', 117);
        }

        /**
         * Метод $this->smtp_cmd выполняет запросы к серверу
         * и анализирует возвращаемые ответы данного сервера.
         * После комплекса полученных ответов на выполняемые запросы
         * метод анализирует общее количество положительных и отрицательных ответов.
         *
         * Если все запросы на сервер были выполнены успешно метод возвращает
         * собственный код ответа [202] (2xx - положительные ответы сервера).
         *
         * Если запросы были выполнены частично, метод возвратит код ответа - [300].
         *
         * Если запросы не были выполнены метод возвратит код ответа [111](1xx) - отрицательные ответы
         * сервера.
         *
         * МЕТОД ВОЗВРАЩАЕТ СОБСТВЕННЫЕ КОДЫ ОТВЕТА, КОТОРЫЕ БЫЛИ ОПРЕДЕЛЕНЫ ПРИ ПРОЕКТИРОВАНИИ МОДУЛЯ,
         * И НЕ ЯВЛЯЮТСЯ КОДАМИ ОТВЕТА СЕРВЕРА!
         * КОДЫ ОТВЕТА СЕРВЕРА ЗАПИСЫВАЮТСЯ В ЛОГ-ФАЙЛ.
         *
         * Такой подход позволяет комлексно анализировать запросы и формировать результирующий
         * вывод о выполнении запросов.
         */
        $cmd = array
        (
            "EHLO " . $this->domain[0],
            "AUTH LOGIN"
        );

        /**
         * $this->smtp_cmd  - код ответа на запрос.
         * Если не 202 - сервер отказал в разрешении на авторизацию.
         */
        if ($this->smtp_cmd($this->fp, $cmd, __LINE__) !== 202) {
            fclose($this->fp);
            return $this->write_log(' Auth start error: ', 118);
        }

        $cmd = array
        (
            base64_encode($this->config[self::USER])
        );

        /**
         * Отправляем логин пользователя
         */
        if ($this->smtp_cmd($this->fp, $cmd, __LINE__) !== 202) {
            fclose($this->fp);
            return $this->write_log(' Auth LOGIN error: ', 119);
        }

        $cmd = array
        (
            base64_encode($this->config[self::PASS])
        );

        /**
         * Отправляем пароль пользователя
         */
        if ($this->smtp_cmd($this->fp, $cmd, __LINE__) !== 202) {
            fclose($this->fp);
            return $this->write_log(' Auth PASSWORD required: ', 120);
        }

        $cmd = array
        (
            "MAIL FROM:<" . $this->config[self::FROM] . "> SIZE=" . strlen($this->readyContent)
        );

        if ($this->smtp_cmd($this->fp, $cmd, __LINE__) !== 202) {
            fclose($this->fp);
            return $this->write_log(' server refused to MAIL FROM command via SMTP: ', 121);
        }

        /**
         * Удаляем $cmd и инициализируем её повторно
         */
        unset($cmd);
        $cmd = array();

        /**
         * Необходимо создать массив $cmd со всеми адресами получателей
         * для формирования дальнейших запросов.
         */
        foreach($this->mailsArr as $k => $v) {
            $cmd[] = "RCPT TO:<".$k.">";
        }

        if ($this->smtp_cmd($this->fp, $cmd, __LINE__) !== 202) {
            fclose($this->fp);
            return $this->write_log(' server refused to RCPT TO command via SMTP: ', 122);
        }

        $cmd = array
        (
            "DATA"
        );

        if ($this->smtp_cmd($this->fp, $cmd, __LINE__) !== 202) {
            fclose($this->fp);
            return $this->write_log(' server refused to DATA command via SMTP: ', 123);
        }

        /**
         * Отправляем наше письмо
         */
        $cmd = array
        (
            $this->readyContent . $this->delimiter . '.'
        );

        if ($this->smtp_cmd($this->fp, $cmd, __LINE__) !== 202) {
            fclose($this->fp);
            return $this->write_log(' server refused to DATA($this->readyContent) command via SMTP: ', 125);
        }

        /**
         * После обработки всех запросов посылаем
         * запрос на выход выход.
         */
        $cmd = array
        (
            "QUIT"
        );

        if ($this->smtp_cmd($this->fp, $cmd, __LINE__)) {
            fclose($this->fp);
        }

        return $this->write_log(' Message send via SMTP: ', 205);
    }

    /**
     * Метод выполняет проверку email, в качестве аргумента
     * принемается массив $dirtyMailsArr "грязные адреса" и
     * формирует массив $this->mailsArr.
     * @param $dirtyMailsArr
     * @return bool
     */
    protected function create_mails_arr($dirtyMailsArr) {

        if (!is_array($dirtyMailsArr)) return false;

        /**
         * @param string $email - электронный адрес получателя
         * @param string $name  - имя отправителя
         */
        foreach($dirtyMailsArr as $email => $name) {

            if (filter_var(trim($email), FILTER_VALIDATE_EMAIL)){
                $name ? $name = trim($name) : false;
                $this->mailsArr[$email] = $name;
            }
        }

        return true;
    }

    /**
     * Метод создает стандартный шаблон заголовка и
     * тем самым избавляет от повторного дублирования кода.
     * @param $data string
     * @return string
     */
    protected function get_pattern($data) {
        return "=?" . $this->charset . "?Q?" .
        str_replace("+", "_", str_replace("%", "=", urlencode(strtr($data, $this->delimiter, "  "))))."?=";
    }

    /**
     * Метод находит ключ массива по регулярному выражению
     * в массиве заголовков $this->headers
     *
     * Данный подход удобен, поскольку если брать фиксированный ключ
     * массива для работы, есть вероятность того что в процессе модификации модуля
     * значения ключей будут изменяться.
     * @param $needle
     * @return bool|mixed
     */
    protected function get_header_key($needle) {

        if
        (
            !is_string($needle)         ||
            !is_array($this->headers)   ||
            !count($this->headers)
        )
        {
            return $this->write_log(' Invalid input arguments: ', 116);
        }

        foreach($this->headers as $v) {

            if (preg_match('/^'. $needle .'/', $v)) {
                return $v;
            }

        }

        return false;
    }

    /**
     * Метод создает заголовки сценария двух типов
     * @param $headerName - название заголовка
     * @param $arr_data - массив с данными для формирования заголовка.
     * @return string
     */
    protected function create_header($headerName, $arr_data) {

        $header = '';

        foreach($arr_data as $k => $data) {

            /**
             * $k - может являться массивом, если аргументу
             * $arr_data были переданы электронные адреса с
             * именами получателей (например).
             */
            switch(filter_var($k, FILTER_VALIDATE_EMAIL)) {

                case true:

                    /**
                     * $data в данном контексте будет являться именем отправителя.
                     * $header будет инициализированна как массив для удобства работы.
                     */
                    if ($data) {
                         $header[] = $this->get_pattern($data) . " <{$k}>";
                    }

                    else $header[] = $k;

                break;

                case false:
                    $header = $this->get_pattern($data);
                break;

            }
        }

        /**
         * Преобразуем массив в строку разделяя его части.
         */
        if (is_array($header)) {
            $header = implode(', ', $header);
        }

        return $headerName . ": ". $header;

    }

    /**
     * Метод устанавливает заголовки сценария,
     * работая со вспомагательными методами.
     */
    protected function get_headers() {

        $this->headers[]     = "Date: " . date("r");
        $this->headers[]     = "Message-ID: <" . rand() . "." . __CLASS__ . date("YmjHis") . "@" . $this->domain[1] . ">";
        $this->headers[]     = $this->create_header('Subject', array($this->data[0]));
        $this->headers[]     = $this->create_header('To', $this->mailsArr);

        /**
         * Устанавливаем заголовок на получение копии письма "СС" в
         * качестве адресата устанавливаем адрес отправителя:
         * $this->config[self::FROM].
         */
        $this->headers[]     = $this->create_header('CC', array($this->config[self::FROM] => ''));


        /**
         * Устанавливаем заголовок From(от кого).
         * Так же здесь мы можем указать своё имя или
         * прочую информацию ($this->config[self::NAME])
         */
        $this->headers[]     = $this->create_header('From', array($this->config[self::FROM] => $this->config[self::NAME]));

        /**
         * Устанавливаем заголовок Reply-To(куда ответить).
         * Аналогичная информация вышестоящей.
         */
        $this->headers[]     = $this->create_header('Reply-To', array($this->config[self::FROM] => ''));

        /**
         * Устанавливаем приоритет X-Priority, по умолчанию 3 (Normal)
         * '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)'
         */
        $this->headers[]     = "X-Priority: " . $this->priority[3];
        $this->headers[]     = "Mime-Version: 1.0";
        $this->headers[]     = "X-Mailer: " . $this->moduleName;

        /**
         * Далее идут заголовки содержимого
         */
        $this->headers[]     = "Content-Type: " . $this->contentType . "; charset=".$this->charset;

        /**
         * Content-Transfer-Encoding - в конце данного заголовка
         * используется двойной разделитель, поэтому ставим в конце
         * разделитель $this->delimiter.
         */
        $this->headers[]     = "Content-Transfer-Encoding: " . $this->ctEnc . $this->delimiter;

        /**
         * Формируем текст сообщения в соответствие с требованиями RFC 2045
         * Данные будут перекодированы в base64 и разбиты пофрагментно
         * функцией chunk_split длиной [int $chunklen = 76].
         */
        $this->headers[]    = chunk_split(base64_encode($this->data[1]));

        /**
         * Соединяем содержимое в результирующий набор
         * $this->readyContent;
         */
        $this->readyContent = implode($this->delimiter, $this->headers);

        return true;
    }

    /**
     * Данный метод устанавливает параметры конфигурационного
     * массива $this->config, для дальнейшей работы приложеня
     * с данным массивом.
     * @param $configArray
     * @return bool
     */
    protected function set_config_array($configArray) {

        if
        (
            !is_array($configArray)     ||
            !count($configArray)
        )
        {
            $this->write_log(' Invalid input argument $configArray: ', 113);
            return false;
        }

        /**
         * Определяем константы класса в ассоциативный массив.
         */
        $obj = new \ReflectionClass(__CLASS__);

        /**
         * На каждой итерации проверяем существование ключа
         * в input [$configArray] и в случае удачи добавляем
         * массив с данным ключом в конфигурационный массив
         * $this->config_array().
         *
         * Преимущество данного метода заключается в автоматизации процесса
         * инициализации параметров конфигурационного массива $this->config_array().
         *
         * Другими словами, если нам потребуется добавить новый пареметр
         * в input [$configArray] для расширения функциональных возможностей данного
         * модуля, все что нам необходимо будет сделать - это добавить новую константу.
         */
        foreach($obj->getConstants() as $constant) {

            if (array_key_exists($constant, $configArray)) {
                $this->config[$constant] = $configArray[$constant];
            }

        }

        return true;
    }

    /**
     * Метод анализирует содержимое массива $arr_data,
     * определяет тип контента и в случае успешной
     * проверки устанавливает для свойств
     * $this->data
     * $this->content_type
     * соответствующие значения.
     * @param $arr_data
     * @return bool
     */
    protected function config_data_array($arr_data) {

        if
        (
            !is_array($arr_data) ||
            !count($arr_data)
        )
        {
            return false;
        }

        /**
         * Проверяем аголовок [subj], если он пустой устанавливается
         * значение untitled
         */
        switch($arr_data[0]) {

            case true:

                /**
                 * Проверяем длинну заголовока [subj], она не должна
                 * превышать 150 байт (strlen - возвращает количество байт).
                 */
                if (strlen($arr_data[0]) > $this->subjLen) {
                    $this->data[] = substr($arr_data[0], 0, $this->subjLen) . '...';
                }

                else $this->data[] = $arr_data[0];
            break;

            case false:
                $this->data[] = 'Untitled';
            break;
        }

        /**
         * Проверяем наличие тегов в теле сообщения и устанавливаем
         * тип контента. text/(plain|html)
         */
        if (preg_match('#<.*>.*<\/.*>#', $arr_data[1])) {
            $this->contentType = 'text/html';
        }

        else {
            $this->contentType = 'text/plain';
        }

        $this->data[] = $arr_data[1];

        return true;
    }

    /**
     * Метод представляет собой центральный конфигурационный узел данного модуля.
     *
     * Он определяет:
     *  - какой тип передачи использовать mail|smtp;
     *  - настраивает модуль в соответствии с выбранным типом;
     *  - осуществляет проверку основных параметров передаваемых через конструктор.
     *
     * Данный метод должен быть синхронизирован с обработчиком ошибок,
     * который будет информировать пользователя о невозможности дальнейшего
     * выполнения сценария.
     */
    protected function config() {


        /**
         * Проверяем mailsArr на наличие адресов
         */
        if
        (
            !is_array($this->mailsArr)      ||
            !count($this->mailsArr)
        )
        {
            /**
             * Если тип данных свойства не является массивом или массив
             * имеет 0 адресов для отправки - генерируется исключение.
             */
            $this->write_log(' Invalid $this->config array: ', 114);

            /**
             * Требуется вывод информации в окно браузера.
             */
            return false;

        }



        /**
         * Проверяем наличие массива data
         */
        if
        (
            !is_array($this->data)      ||
            !count($this->data)
        )
        {
            /**
             * Если тип данных свойства не является массивом или массив
             * имеет 0 адресов для отправки - генерируется исключение.
             */
            $this->write_log(' Invalid $this->data ', 114);

            /**
             * Требуется вывод информации в окно браузера.
             */
            return false;

        }


        /**
         * Проверяем тип передачи с которым будем работать.
         */
        !$this->connectionType ? $this->connectionType = 1 : false;


        switch($this->connectionType) {

            /**
             * mail().
             */
            case 1 :
                print 1;
            break;

            /**
             * smtp server.
             */
            case 2 :

                /**
                 * Проверяем необходимые параметры $this->config для дальнейшей работы.
                 * Проверка имеет разветвленный характер, поскольку для передачи
                 * сообщений с использованием функции mail некоторые параметры
                 * непотребуются.
                 *
                 * Опускается проверка по таким константам как:
                 * TR   - (по умолчанию tcp),
                 * PORT - (по умолчанию 25),
                 * NAME - (может быть не заданно).
                 */

                if (!$this->config[self::HOST]) {
                    $this->write_log(' Hosting data is undefined [self::HOST] : ', 115);
                    return false;
                }
                else if(!$this->config[self::USER]) {
                    $this->write_log(' User data is undefined [self::USER] : ', 115);
                    return false;
                }
                else if(!$this->config[self::PASS]) {
                    $this->write_log(' Password data is undefined [self::PASS] : ', 115);
                    return false;
                }

                /**
                 * self::FROM (от кого), в качестве параметра должен быть указан
                 * email отправителя.
                 */
                else if
                (
                    !$this->config[self::FROM]                                      ||
                    !filter_var($this->config[self::FROM], FILTER_VALIDATE_EMAIL)
                )
                {
                    $this->write_log(' From data is undefined [self::FROM] OR incorrect : ', 115);
                    return false;
                }

                /**
                 * Получаем домен отправителя для smtp-авторизации и
                 * формирования заголовков.
                 */
                if ($tmp = @explode('@', $this->config[self::FROM])){
                    $this->domain = $tmp;
                }
                else return false;

                /**
                 * Формирование заголовков, в результате вызова данного
                 * метода формируется $this->readyContent это результирую-
                 * щий набор заголовки + содержимое сообщения.
                 * Так же заголовки доступны для работы через массив $this->headers.
                 */
                $this->get_headers();

                /**
                 * Метод отвечает за организацию выполнения запросов
                 * на smtp-сервер, и в качестве ответа возвращает,
                 * коды ответа сервера.
                 * Тут необходимо уведомить пользователя
                 * о статусе доставки.
                 */
               switch($this->smtp_requests()) {

                   case 205:
                       return true;
                   break;

                   case 118:
                       /**
                        * Сервер отказал в запросе на авторизацию.
                        */
                       return false;
                   break;

                   case 119:
                       /**
                        * Неверный логин пользователя
                        */
                       return false;
                   break;

                   case 120:
                       /**
                        * Неверный пароль
                        */
                       return false;
                   break;

                   /**
                    * Далее идет обработка ошибок
                    * системного характера.
                    */
                   default:
                       return false;
                   break;

               }

            break;
        }

        return true;

    }

    static public function run_send
                                  (
                                        $arr_mailsArr,
                                        $arr_config,
                                        $arr_data,
                                        $int_connectionType=false
                                  )
    {
        $obj = new static
                        (
                            $arr_mailsArr,
                            $arr_config,
                            $arr_data,
                            $int_connectionType
                        );

        return $obj->config();
    }

} 