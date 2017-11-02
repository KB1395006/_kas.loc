<?php

// Document
$doc        = \kas::doc();
$content    = false;


// Namespace
switch (\kas::loc())
{
    case '/':

        break;

    case L1:

        $content = \kas::tpl(
            [CONTENT => \kas::tpl([CONTENT => \kas::ob()->catalog(2)
                ->html()], 3)->asStr()], 4)->asStr();

        break;

    case L2:

        $content  = \kas::tpl(
            [CONTENT => \kas::ob()->table(OFFERS)->html()], 9)->asStr();

        $content .= \kas::ob()->table(OFFERS, [ID, CID, C_NAME], 11)
            ->grHtml(CID);

    break;

    case L3:

        // $content  = \kas::tpl(
        //    [CONTENT => \kas::ob()->table(MEDIA, [], [5,6,14,8])->html()], 12)->asStr();

        $content = \kas::tpl([CONTENT => \kas::components()->gallery()], 25)->asStr();

        // $content = \kas::tpl(
        //    [CONTENT => \kas::tpl($DBO->getId()->getSrc()
        //        ->getGroup(0, 0, 2), 24)->{SRC}(function($current, $row){
        //
        //            // here
        //            var_dump($row);
        //            $name  = basename($current[SRC]);
        //            $path  = \kas::data($current[SRC])->r($name)->asStr();
        //            $path .= '/resize/s/' . $name;
        //            return $path;
        //    })->asStr()], 25
        // )->asStr();

    break;

    case L6:

        $content  = \kas::tpl(
            [CONTENT => \Core\Classes\View\SiteText\SiteTextManager::html(18, true)], 19)->asStr();

    break;

    case L7:

        $content  = \kas::tpl(
            [CONTENT => \kas::ob()->table(PUB, [], [5,6,20,8])->html()], 9)->asStr();

    break;
    
    case L8:

        $content  = \kas::tpl(
            [CONTENT => \kas::ob()->table(TPL, [], [5,6,20,8])->html()], 9, false, false)->asStr();

    break;
}

$doc->content($content);
print $doc->html(false);