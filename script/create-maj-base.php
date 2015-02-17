<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */
define('INC_FROM_CRON_SCRIPT', true);

require('../config.php');
ini_set('display_errors', 1);


$PDOdb=new TPDOdb;
$PDOdb->db->debug=true;

$o=new TWorkstation;
$o->init_db_by_vars($PDOdb);

print 1;
$o=new TWorkstationProduct;
$o->init_db_by_vars($PDOdb);
