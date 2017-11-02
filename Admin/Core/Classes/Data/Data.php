<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 28.07.2016
 * Time: 20:31
 */

namespace Core\Classes\Data;

/**
 * Данный класс выполняет обработку данных.
*/
class Data
{
    /**
     * Исходные данные.
    */
    public $data = [];
    /**
     * Копия исходных данных.
    */
    protected $target = [];
    /**
     * Массив транслитерации.
    */
    protected $trData = [
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'i',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    ];

    protected function str($data = '')
    {
        return \kas::str($data) || !empty($data) ?
            true : false;
    }

    /**
     * Data constructor.
     * @param mixed $data
    */
    public function __construct($data = [])
    {
        \kas::str($data) ?
            $this->data = [$data] :
            $this->data = $data;

        \kas::arr($this->data) ?:
            $this->data = false;
    }

    /**
     * Транслитерация текста
    */
    public function tr()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v)
        {
            if (!$this->str($v)) {
                return $v;
            }

            $str = mb_strtolower(strtr($v, $this->trData));

            if (!$this->str($str)) {
                return $v;
            }
            
            // Заменить все оставшиеся символы 
            $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
            
            // Удаляем начальные и конечные '-'
            $str = trim($str, "-");

            return $str;
        });

        return $this;
    }

    public function getFileOb()
    {
        if (!\kas::str($this->data[0])) {
            return false;
        }

        $fO             = new \stdClass();
        $fO->ext        = pathinfo($this->data[0], PATHINFO_EXTENSION);
        $fO->filename   = md5($this->data[0]) . '.' . $fO->ext;
        $fO->dirArr     = [substr($fO->filename, 0, 2), substr($fO->filename, 2, 2)];        
        $fO->dir        = implode('/', $fO->dirArr) . '/';
        $fO->path       = $fO->dir . $fO->filename;

        return $fO;
    }
    
    /**
     * Переводит данные в нижний регистр.
    */
    public function strLow()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v){
            return mb_strtolower($v, ENCODING);
        });

        return $this;
    }

    /**
     * Переводит данные в верхний регистр.
    */
    public function strUp()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v){
            return mb_strtoupper($v, ENCODING);
        });

        return $this;
    }

    /**
     * Обрезает один или группу элементов от пробельных символов.
    */
    public function trim()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v){
            return trim($v);
        });

        return $this;
    }

    /**
     * Метод выполняет замену по РВ
     * @param string $pattern
     * @param string $replacement
     * @return object
    */
    public function r($pattern = '', $replacement = '')
    {
        if
        (
            !\kas::str($pattern)                                ||
            !is_string($replacement)                            &&
            !is_int($replacement)                               &&
            !is_float($replacement)                             // Если заменяем на ''
        )
        {
            return $this;
        }

        /**
         * Если не РВ
        */
        preg_match('/^\/.*\/$/', $pattern) ?
            $isRexp = true : $isRexp = false;

        $arguments = [$pattern, $replacement, $isRexp];

        /** @noinspection PhpUnusedParameterInspection
         *  @param $k
         *  @param $v
         *  @param $args
         *  @return bool
        */
        $fn = function($k, $v, $args)
        {
            /**
             * Выбираем технологию взависимости от аргумента $args[2].
            */
            switch ($args[2])
            {
                case true:
                    $v = @preg_replace($args[0], $args[1], $v);
                    return $v;
                    break;

                case false:
                    $v = str_replace($args[0], $args[1], $v);
                    return $v;
                    break;
            }

            return true;
        };

        $this->data = \kas::iterator($this->data, $fn, $arguments);
        return $this;
    }

    /**
     * Удаляет нулевой символ.
    */
    public function noBom()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v){
            return str_replace("\0", '', $v);
        });

        return $this;
    }

    public function clear()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v){
            return html_entity_decode(strip_tags(htmlspecialchars(stripslashes(trim($v)))));
        });

        return $this;
    }

    /**
     * Удаление служебных маркеров платформы
    */
    public function noMasc()
    {
        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v){
            return preg_replace('/%[0-9A-Za-z_-]+%/', '', $v);
        });

        return $this;
    }

    public function _post()
    {
        $this->data = $_POST;
        $this->clear();
        return $this;
    }

    public function _get()
    {
        $this->data = $_GET;
        $this->clear();
        return $this;
    }

    /**
     * Метод разбивает строку по параметру разделения.
     * @param string $separator
     * @return $this
    */
    public function explode($separator = '')
    {
        if (!\kas::str($separator)) {
            return $this;
        }

        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v, $args){
            return explode($args[0], $v);
        }, [$separator]);

        return $this;
    }

    /**
     * Возвращает первый элемент массива $this->data
     * @param bool $noEmpty
     * @return \Core\Classes\Data\Data
    */
    public function first($noEmpty = true)
    {
        if (!$noEmpty) {
            $this->data = $this->data[0];
            return $this;
        }

        foreach ($this->data[0] as $v)
        {
            if (!$v) {
                continue;
            }

            $this->data = $v;
            break;
        }
        
        return $this;
    }

    /**
     * Возвращает послединий элемент $this->data
    */
    public function last()
    {
        count($this->data) == 1 && \kas::arr($this->data[0]) ?
            $this->data = $this->data[0] : false;
        
        return \kas::arr($this->data) ?
            $this->data[count($this->data) - 1] :
            $this->data;
    }

    /**
     * Осуществляет преобразование данных формата csv (разделитель ;)
     * в массив
     *
     * data csv
     *
     * @param bool|callable $fn
     * @return \Core\Classes\Data\Data
    */
    public function parseCSV($fn = false)
    {
        if (!\kas::arr($this->data)) {
            return $this;
        }

        $this->explode("\r\n")->last();

        /** @noinspection PhpUnusedParameterInspection */
        $this->data = \kas::iterator($this->data, function($k, $v, $fn)
        {
            // Разбить строку на элементы.
            $row = explode(';', $v);

            foreach ($row as $c)
            {
                if (\kas::str($c)) {
                    return is_callable($fn) ?
                        $fn($row) : $row;
                }

                continue;
            }

            return false;

        }, [$fn]);

        return $this;
    }

    /**
     * Метод выбирает дочерние элементы массива $this->data.
     * @param int $index
     * @return $this
    */
    public function child($index = 0)
    {
        \kas::arr($this->target) ?: $this->target = $this->data;
        $this->data = $this->data[(int) $index];

        return $this;
    }

    /**
     * Метод возвращает массив $this->data
    */
    public function asArr()
    {
        return \kas::arr($this->data) ?
            $this->data : [];
    }

    /**
     * Возвращает строковое представление $this->data
    */
    public function asStr()
    {
        if (\kas::str($this->data)) {
            return $this->data;
        }

        if (!\kas::arr($this->data)) {
            return false;
        }

        $_data = '';

        foreach ($this->data as $data)
        {
            if (!\kas::str($data)) {
                continue;
            }

            $_data .= $data;
        }

        return $_data;
    }
}