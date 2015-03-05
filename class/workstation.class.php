<?php


class TWorkstation extends TObjetStd{
/*
 * Atelier de fabrication d'équipement
 * */
	
	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'workstation');
    	$this->TChamps = array(); 	  
		$this->add_champs('entity,fk_usergroup','type=entier;index;');
		$this->add_champs('name,background','type=chaine;');
		$this->add_champs('nb_hour_capacity,nb_ressource','type=float;'); // charge maximale du poste de travail
		
	    $this->start();
	}
	
	function save(&$ATMdb) {
		global $conf;
		
		$this->entity = $conf->entity;
		
		parent::save($ATMdb);
		
		
	}
	
	static function getWorstations(&$ATMdb, $details = false) {
		global $conf,$db;
		
        dol_include_once('/user/class/usergroup.class.php');
        
        /*
        ,1=>array('nb_ressource'=>2, 'velocity'=>(5/7), 'background'=>'linear-gradient(to right,white, #660000)', 'name'=>'Stagiaire') // base de 7h par jour
        ,2=>array('nb_ressource'=>2, 'velocity'=>(5.5/7), 'background'=>'linear-gradient(to right,white, #cccc00)', 'name'=>'devconfirme')
        ,3=>array('nb_ressource'=>1, 'velocity'=>1, 'background'=>'linear-gradient(to right,white,#00cc00)', 'name'=>'DSI')
    
         * 
         */
        $hour_per_day = !empty($conf->global->TIMESHEET_WORKING_HOUR_PER_DAY) ? $conf->global->TIMESHEET_WORKING_HOUR_PER_DAY : 7;
   
		$TWorkstation=array();
		$sql = "SELECT rowid, background,name,nb_ressource,nb_hour_capacity ,fk_usergroup FROM ".MAIN_DB_PREFIX."workstation WHERE entity=".$conf->entity;
		
		$ATMdb->Execute($sql);
		while($ATMdb->Get_line()){
		    if($details) {
		        
                $fk_usergroup = $ATMdb->Get_field('fk_usergroup');
                $g=new UserGroup($db);
                $TUser = $g->listUsersForGroup();
                
		        $TWorkstation[$ATMdb->Get_field('rowid')]=array(
		              'nb_ressource'=>$ATMdb->Get_field('nb_ressource')
                      ,'velocity'=>$ATMdb->Get_field('nb_hour_capacity') / $hour_per_day
                      ,'background'=>$ATMdb->Get_field('background')
                      ,'name'=>$ATMdb->Get_field('name')
                      ,'TUser'=>$TUser
                );
		    }
            else{
                $TWorkstation[$ATMdb->Get_field('rowid')]=$ATMdb->Get_field('name');    
            }
			
		}
		
		
		return $TWorkstation;
	}
	
	
	
}

class TWorkstationProduct extends TObjetStd{
	
	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'workstation_product');
    	$this->TChamps = array(); 	  
		$this->add_champs('fk_product, fk_workstation','type=entier;index;');
		$this->add_champs('nb_hour,rang','type=float;'); // nombre d'heure associé au poste de charge et au produit
		
		$this->start();
		
		$this->nb_hour=0;
		$this->rang=0;
	}
	
}
