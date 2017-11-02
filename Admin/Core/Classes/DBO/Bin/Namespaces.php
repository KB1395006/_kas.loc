<?php

namespace Core\Classes\DBO\Bin;
use Core\Config\SQL;

class Namespaces 
{
    protected $t = 'KAS_NAMESPACES';
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
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getUrlSet () {
    $this->c[] = 'KAS_URL_SET';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setUrlSet () {
    $this->c[] = 'KAS_URL_SET';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getUri () {
    $this->c[] = 'KAS_URI';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setUri () {
    $this->c[] = 'KAS_URI';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function getTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Namespaces
    */
    public function setTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



}
