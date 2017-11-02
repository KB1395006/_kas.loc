<?php
/**
 * Created by PhpStorm.
 * User: KAS
 * Date: 22.05.2017
 * Time: 8:06
 */

namespace Core\Classes\DBO;


class DBO
{
    static public function getDir() {
        return __DIR__;
    }

    static public function getClassTpl() {
        $t = '<?php
        
        namespace Core\Classes\DBO\Bin;
        use Core\Config\SQL;
        
        class %CLASS% 
        {
            protected $t = \'%TABLE%\';
            protected $c = [];
            public function __construct() {}
            
            public function getGroup($pid = 0, $id = 1, $limit = 100) 
            {
                array_flip(SQL::tables($this->t))[PID] ?
                    $column = PID : $column = CID;
        
            return \kas::sql()->exec(
        \kas::sql()->simple()->sel($this->t, $this->c) . $column . " = ? AND " . ID . " >= ? LIMIT {$limit}", [(int)($pid), (int) ($id) ]);
            }
            
            /**
            * @param int $id
            * @return bool|array
            */
            public function getOne($id = 0) 
            {
                    return \kas::sql()->exec(
                        \kas::sql()->simple()->sel($this->t, $this->c) . ID . " = ?", [(int)($id) ?: \kas::getId(\kas::uri())]);
            }
            
            // DBO GENERATOR
            %METHODS%
        }
        ';

        return $t;
    }

    static public function getMethodTpl()
    {
        $t = '
            /**            
            * @return \Core\Classes\DBO\Bin\%CLASS%
            */
            public function %METHOD% () {
                    $this->c[] = \'%COLUMN%\';
                    return $this;
            }
        ';

        return $t;
    }
}