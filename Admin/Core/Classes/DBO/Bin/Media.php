<?php

namespace Core\Classes\DBO\Bin;
use Core\Config\SQL;

class Media 
{
    protected $t = 'KAS_MEDIA';
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
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getCid () {
    $this->c[] = 'KAS_CID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setCid () {
    $this->c[] = 'KAS_CID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getLargeDescription () {
    $this->c[] = 'KAS_LARGE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setLargeDescription () {
    $this->c[] = 'KAS_LARGE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getSrc () {
    $this->c[] = 'KAS_SRC';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setSrc () {
    $this->c[] = 'KAS_SRC';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getPath () {
    $this->c[] = 'KAS_PATH';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setPath () {
    $this->c[] = 'KAS_PATH';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getMime () {
    $this->c[] = 'KAS_MIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setMime () {
    $this->c[] = 'KAS_MIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function getTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Media
    */
    public function setTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



}
