<?php

namespace Core\Classes\DBO\Bin;
use Core\Config\SQL;

class Categories 
{
    protected $t = 'KAS_CATEGORIES';
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
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getLargeDescription () {
    $this->c[] = 'KAS_LARGE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setLargeDescription () {
    $this->c[] = 'KAS_LARGE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getMiddleDescription () {
    $this->c[] = 'KAS_MIDDLE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setMiddleDescription () {
    $this->c[] = 'KAS_MIDDLE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getShortDescription () {
    $this->c[] = 'KAS_SHORT_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setShortDescription () {
    $this->c[] = 'KAS_SHORT_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getImgMiddle () {
    $this->c[] = 'KAS_IMG_MIDDLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setImgMiddle () {
    $this->c[] = 'KAS_IMG_MIDDLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getImgLarge () {
    $this->c[] = 'KAS_IMG_LARGE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setImgLarge () {
    $this->c[] = 'KAS_IMG_LARGE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getImgSmall () {
    $this->c[] = 'KAS_IMG_SMALL';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setImgSmall () {
    $this->c[] = 'KAS_IMG_SMALL';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getImgIcon () {
    $this->c[] = 'KAS_IMG_ICON';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setImgIcon () {
    $this->c[] = 'KAS_IMG_ICON';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getPrice () {
    $this->c[] = 'KAS_PRICE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setPrice () {
    $this->c[] = 'KAS_PRICE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getPid () {
    $this->c[] = 'KAS_PID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setPid () {
    $this->c[] = 'KAS_PID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getGid () {
    $this->c[] = 'KAS_GID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setGid () {
    $this->c[] = 'KAS_GID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getCid () {
    $this->c[] = 'KAS_CID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setCid () {
    $this->c[] = 'KAS_CID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getUrlSet () {
    $this->c[] = 'KAS_URL_SET';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setUrlSet () {
    $this->c[] = 'KAS_URL_SET';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getModel () {
    $this->c[] = 'KAS_MODEL';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setModel () {
    $this->c[] = 'KAS_MODEL';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getUri () {
    $this->c[] = 'KAS_URI';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setUri () {
    $this->c[] = 'KAS_URI';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getCurId () {
    $this->c[] = 'KAS_CUR_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setCurId () {
    $this->c[] = 'KAS_CUR_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getNamespaceId () {
    $this->c[] = 'KAS_NAMESPACE_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setNamespaceId () {
    $this->c[] = 'KAS_NAMESPACE_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function getTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Categories
    */
    public function setTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



}
