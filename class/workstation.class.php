<?php


class TWorkstation extends TObjetStd{
/*
 * Atelier de fabrication d'équipement
 * */
	
	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'workstation');
    	$this->TChamps = array(); 	  
		$this->add_champs('entity,fk_usergroup','type=entier;index;');
		$this->add_champs('libelle','type=chaine;');
		$this->add_champs('nb_hour_max,nb_para_task','type=float;'); // charge maximale du poste de travail
		
	    $this->start();
	}
	
	function save(&$ATMdb) {
		global $conf;
		
		$this->entity = $conf->entity;
		
		parent::save($ATMdb);
		
		
	}
	
	static function getWorstations(&$ATMdb) {
		global $conf;
		
		$TWorkstation=array();
		$sql = "SELECT rowid, libelle FROM ".MAIN_DB_PREFIX."workstation WHERE entity=".$conf->entity;
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()){
			$TWorkstation[$ATMdb->Get_field('rowid')]=$ATMdb->Get_field('libelle');
		}
		
		
		return $TWorkstation;
	}
	
}

class TWorkstationProduct extends TObjetStd{
	
	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'workstation_product');
    	$this->TChamps = array(); 	  
		$this->add_champs('fk_product, fk_asset_workstation','type=entier;index;');
		$this->add_champs('nb_hour,rang','type=float;'); // nombre d'heure associé au poste de charge et au produit
		
		$this->start();
		
		$this->nb_hour=0;
		$this->rang=0;
	}
	
}
