<?php

namespace Core\Classes\DBO\Bin;
use Core\Config\SQL;

class Tpl 
{
    protected $t = 'KAS_TPL';
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
    
    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getCid () {
    $this->c[] = 'KAS_CID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setCid () {
    $this->c[] = 'KAS_CID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getMiddleDescription () {
    $this->c[] = 'KAS_MIDDLE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setMiddleDescription () {
    $this->c[] = 'KAS_MIDDLE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getLargeDescription () {
    $this->c[] = 'KAS_LARGE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setLargeDescription () {
    $this->c[] = 'KAS_LARGE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getSrc () {
    $this->c[] = 'KAS_SRC';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setSrc () {
    $this->c[] = 'KAS_SRC';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getPath () {
    $this->c[] = 'KAS_PATH';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setPath () {
    $this->c[] = 'KAS_PATH';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getMime () {
    $this->c[] = 'KAS_MIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setMime () {
    $this->c[] = 'KAS_MIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function getTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Tpl
    */
    public function setTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



}
