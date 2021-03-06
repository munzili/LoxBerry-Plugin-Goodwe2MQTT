<?php
require_once "loxberry_system.php";
require_once "loxberry_web.php";
require_once "loxberry_log.php";

$L = LBWeb::readlanguage("language.ini");

$template_title = "Goodwe 2 MQTT";
$helplink = $L['LINKS.WIKI'];
$helptemplate = "pluginhelp.html";

$navbar[1]['Name'] = $L['NAVBAR.FIRST'];
$navbar[1]['URL'] = 'index.php';

$navbar[2]['Name'] = $L['NAVBAR.SECOND'];
$navbar[2]['URL'] = 'log.php';


// NAVBAR
$navbar[2]['active'] = True;

LBWeb::lbheader($template_title, $helplink, $helptemplate);

$html = LBLog::get_notifications_html( LBPPLUGINDIR, null);
echo $html;

if(empty($html))
{
    echo $L['LOGFILES.EMPTY'];
}

LBWeb::lbfooter();