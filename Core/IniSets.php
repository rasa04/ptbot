<?php
namespace Core;
trait IniSets{
    public function setIni()
    {
        ini_set('error_reporting', E_ALL);
        ini_set('allow_url_fopen', 1);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    }
}
