<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */
 
if(!defined('INC_FROM_DOLIBARR')) {
    define('INC_FROM_CRON_SCRIPT', true);
    
    require('../config.php');
    
}


$o=new Workstation($db);
$o->init_db_by_vars();

$o=new WorkstationProduct($db);
$o->init_db_by_vars();

$o=new WorkstationSchedule($db);
$o->init_db_by_vars();


