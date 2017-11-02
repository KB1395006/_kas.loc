<?php

/**
 * Created by KAS CMS
 */
use Core\Classes\DBO\Bin;

// Document
$doc        = \kas::doc();
$content    = false;

$ob = new Bin\Offers();
$data = $ob->getName()->getId()->getOne(5);
var_dump($data);

$doc->content($content);
print $doc->html();