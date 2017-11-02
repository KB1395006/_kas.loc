<?php

namespace Core\Classes\DBO\Bin;
use Core\Config\SQL;

class Publications 
{
    protected $t = 'KAS_PUBLICATIONS';
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
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setId () {
    $this->c[] = 'KAS_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setName () {
    $this->c[] = 'KAS_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getCategoryName () {
    $this->c[] = 'KAS_CATEGORY_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setCategoryName () {
    $this->c[] = 'KAS_CATEGORY_NAME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setTitle () {
    $this->c[] = 'KAS_TITLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getLargeDescription () {
    $this->c[] = 'KAS_LARGE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setLargeDescription () {
    $this->c[] = 'KAS_LARGE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getMiddleDescription () {
    $this->c[] = 'KAS_MIDDLE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setMiddleDescription () {
    $this->c[] = 'KAS_MIDDLE_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getShortDescription () {
    $this->c[] = 'KAS_SHORT_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setShortDescription () {
    $this->c[] = 'KAS_SHORT_DESCRIPTION';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getImgMiddle () {
    $this->c[] = 'KAS_IMG_MIDDLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setImgMiddle () {
    $this->c[] = 'KAS_IMG_MIDDLE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getImgLarge () {
    $this->c[] = 'KAS_IMG_LARGE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setImgLarge () {
    $this->c[] = 'KAS_IMG_LARGE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getImgSmall () {
    $this->c[] = 'KAS_IMG_SMALL';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setImgSmall () {
    $this->c[] = 'KAS_IMG_SMALL';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getImgIcon () {
    $this->c[] = 'KAS_IMG_ICON';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setImgIcon () {
    $this->c[] = 'KAS_IMG_ICON';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setType () {
    $this->c[] = 'KAS_TYPE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getPrice () {
    $this->c[] = 'KAS_PRICE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setPrice () {
    $this->c[] = 'KAS_PRICE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getFid () {
    $this->c[] = 'KAS_FID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setFid () {
    $this->c[] = 'KAS_FID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getGid () {
    $this->c[] = 'KAS_GID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setGid () {
    $this->c[] = 'KAS_GID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getCid () {
    $this->c[] = 'KAS_CID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setCid () {
    $this->c[] = 'KAS_CID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getUrlSet () {
    $this->c[] = 'KAS_URL_SET';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setUrlSet () {
    $this->c[] = 'KAS_URL_SET';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getModel () {
    $this->c[] = 'KAS_MODEL';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setModel () {
    $this->c[] = 'KAS_MODEL';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getCode () {
    $this->c[] = 'KAS_CODE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setCode () {
    $this->c[] = 'KAS_CODE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getMarkup () {
    $this->c[] = 'KAS_MARKUP';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setMarkup () {
    $this->c[] = 'KAS_MARKUP';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getVendorCode () {
    $this->c[] = 'KAS_VENDOR_CODE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setVendorCode () {
    $this->c[] = 'KAS_VENDOR_CODE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setStatus () {
    $this->c[] = 'KAS_STATUS';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getUri () {
    $this->c[] = 'KAS_URI';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setUri () {
    $this->c[] = 'KAS_URI';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getCurId () {
    $this->c[] = 'KAS_CUR_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setCurId () {
    $this->c[] = 'KAS_CUR_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getCurValue () {
    $this->c[] = 'KAS_CUR_VALUE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setCurValue () {
    $this->c[] = 'KAS_CUR_VALUE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getNamespaceId () {
    $this->c[] = 'KAS_NAMESPACE_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setNamespaceId () {
    $this->c[] = 'KAS_NAMESPACE_ID';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setDate () {
    $this->c[] = 'KAS_DATE';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function getTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



    /**    
    * @return \Core\Classes\DBO\Bin\Publications
    */
    public function setTime () {
    $this->c[] = 'KAS_TIME';
    return $this;
    }



}
