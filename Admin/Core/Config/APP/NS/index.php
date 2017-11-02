<?php

// Document
$doc        = \kas::doc();
$content    = false;

$doc->content($content);
print $doc->html();