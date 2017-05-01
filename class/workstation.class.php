<?php

if($conf->of->enabled) dol_include_once('/of/class/ordre_fabrication_asset.class.php');

class TWorkstation extends TObjetStd{
/*
 * Atelier de fabrication d'équipement
 * */
	
	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'workstation');
    	  
		$this->add_champs('entity,fk_usergroup','type=entier;index;');
		$this->add_champs('name,background',array('type'=>'string'));
		$this->add_champs('type,code',array('type'=>'string','length'=>10));
		$this->add_champs('nb_hour_prepare,nb_hour_manufacture,nb_hour_capacity,nb_ressource,thm,thm_machine,thm_overtime,thm_night,nb_hour_before,nb_hour_after',array('type'=>'float')); // charge maximale du poste de travail
	
	   	$this->_init_vars();
	
	    	$this->start();
		
		if(class_exists('TAssetWorkstationTask')) $this->setChild('TAssetWorkstationTask','fk_workstation');
        	$this->setChild('TWorkstationSchedule', 'fk_workstation');
		
		$this->TType=array(
			'HUMAN'=>'Humain (+ Machine)'
			,'MACHINE'=>'Machine'
		);
	}
	
	// return capacity in hour for a day
	function getCapacityLeft(&$PDOdb, $date, $forGPAO = true) {
		
		$time = strtotime($date);
		
		$capacity = $this->nb_hour_capacity;
		foreach( $this->TWorkstationSchedule as &$sc ) {
			
			if((!empty($sc->date_off) && $time == $sc->date_off) || $sc->week_day == date('w', $time) ){
				if($sc->day_moment=='ALL') $capacity = 0;
				else $capacity == $capacity / 2;
				
				break;
			}
			
		}
		
		$sql = "SELECT t.rowid, t.planned_workload, t.dateo,t.datee 
				FROM ".MAIN_DB_PREFIX."projet_task t 
					LEFT JOIN ".MAIN_DB_PREFIX."projet_task_extrafields tex ON (tex.fk_object=t.rowid)
						LEFT JOIN ".MAIN_DB_PREFIX."projet p ON (p.rowid=t.fk_projet)
				WHERE tex.fk_of IS NOT NULL AND tex.fk_of>0 AND t.progress<100
						AND p.fk_statut = 1 AND tex.fk_workstation = ".$this->id." AND '".$date."' BETWEEN t.dateo AND t.datee
				"; // TODO $forGPAO check
		
		$Tab = $PDOdb->ExecuteASArray($sql);
		//var_dump($Tab,$sql);exit;
		foreach($Tab as &$row) {
			
			$nb_day = floor( ($row->datee - ($row->dateo>0 ? $row->dateo : $row->datee) ) / 86400 ) + 1;
			
			$t_needs = ($row->planned_workload / 3600) / $nb_day;
		
			$capacity-=$t_needs;
		}
		
		return $capacity;
	}
	
	function load(&$PDOdb, $id, $annexe = true)
	{
	    global $conf;
        
		$res = parent::load($PDOdb, $id, $annexe);
        
        if(!empty($conf->global->TIMESHEET_DAYOFF) && empty($this->TWorkstationSchedule)) {
            
            $TJourOff = explode(',', $conf->global->TIMESHEET_DAYOFF);
            
            foreach($TJourOff as $jo) {
                
                $k = $this->addChild($PDOdb, 'TWorkstationSchedule');
                
                $this->TWorkstationSchedule[$k]->week_day = $jo; // On charge le jour off dans le système, sera up à la sauvegarde
                $this->TWorkstationSchedule[$k]->nb_ressource = $this->nb_ressource;
                
            }
            
        }
		
		return $res;
	}
	
	function save(&$PDOdb) {
		global $conf;
		
		$this->entity = $conf->entity;
		
		if((float)DOL_VERSION > 3.6 && $this->background[0]!='#')$this->background='#'.$this->background;

		return parent::save($PDOdb);
	}
	
	function set_values($Tab)
	{
		if (isset($Tab['nb_hour_prepare']) && isset($Tab['nb_hour_manufacture']))
		{
			$Tab['nb_hour_capacity'] = $Tab['nb_hour_prepare'] + $Tab['nb_hour_manufacture'];
		}
		
		parent::set_values($Tab);
	}
	
	static function getWorstations(&$PDOdb, $details = false, $initEmpty=false, $TWorkstation=array(), $only_with_ressource = false) {
		global $conf,$db;
		
        dol_include_once('/user/class/usergroup.class.php');
        
        $hour_per_day = !empty($conf->global->TIMESHEET_WORKING_HOUR_PER_DAY) ? $conf->global->TIMESHEET_WORKING_HOUR_PER_DAY : 7;
   
		$sql = "SELECT rowid, background,name,nb_ressource,nb_hour_capacity,nb_hour_before,nb_hour_after,fk_usergroup 
				FROM ".MAIN_DB_PREFIX."workstation WHERE entity IN(".getEntity('workstation', 1).')';
			
		if($only_with_ressource) $sql.=" AND nb_ressource>0 ";
				
		$sql.="	ORDER BY name ";
		
		$PDOdb->Execute($sql);
		
		if($initEmpty)$TWorkstation[-1] = '';
		
		while($PDOdb->Get_line()){
			
			 $fk_workstation = $PDOdb->Get_field('rowid');
			
		    if($details) {
		        
                $fk_usergroup = $PDOdb->Get_field('fk_usergroup');
//                $TUser = $g->listUsersForGroup('statut = 1');
		$sql = "SELECT u.rowid FROM ".MAIN_DB_PREFIX."user as u, ".MAIN_DB_PREFIX."usergroup_user as ug WHERE 1 ";
		$sql.= " AND ug.fk_user = u.rowid";
		$sql.= " AND ug.fk_usergroup = ".$fk_usergroup;
		$sql.= " AND u.statut != 0 "; //on ne prend que les utilisateurs actifs
		$resUser = $db->query($sql);
		$TUser=array();
		while($obj = $db->fetch_object($resUser)) {
			$newuser=new User($db);
			$newuser->fetch($obj->rowid);

			$TUser[$obj->rowid] = $newuser;
		}
               
               
		        $TWorkstation["$fk_workstation"]=array(
		              'nb_ressource'=>$PDOdb->Get_field('nb_ressource')
                      ,'velocity'=>$PDOdb->Get_field('nb_hour_capacity') / $hour_per_day
                      ,'background'=>$PDOdb->Get_field('background')
                      ,'name'=>$PDOdb->Get_field('name')
					  ,'nb_hour_before'=>$PDOdb->Get_field('nb_hour_before')
					  ,'nb_hour_after'=>$PDOdb->Get_field('nb_hour_after')
                      ,'TUser'=>$TUser
                      ,'id'=>$fk_workstation
                );
		    }
            else{
                $TWorkstation["$fk_workstation"]=$PDOdb->Get_field('name');    
            }
			
		}
		
		
		return $TWorkstation;
	}
	
	function getNomUrl($withPicto = 0) {
	            
	      return '<a href="'.dol_buildpath('/workstation/workstation.php?action=view&id='.$this->getId(),1).'" >'.($withPicto ? img_picto('', 'object_generic').' ' : '' ).$this->name.'</a>';  
	    
	}
	
	
}
class TWorkstationSchedule extends TObjetStd {
    
    function __construct() {
        global $langs;
        
        $this->set_table(MAIN_DB_PREFIX.'workstation_schedule');
        $this->add_champs('fk_workstation','type=entier;index;');
        $this->add_champs('day_moment',array('type'=>'string'));
        $this->add_champs('week_day,nb_ressource',array('type'=>'int')); 
        $this->add_champs('date_off',array('type'=>'date','index'=>true));
        
        $this->_init_vars();
    
        $this->date_off = 0;
        $this->week_day = -1;
    
        $this->TDayMoment=array(
            'ALL'=>$langs->trans('AllDay')
            ,'AM'=>$langs->trans('Morning')
            ,'PM'=>$langs->trans('Afternoon')
        );
    
        $this->TWeekDay=array(
            -1=>''
            ,0=>$langs->trans('Sunday')
            ,1=>$langs->trans('Monday')            
            ,2=>$langs->trans('Tuesday')            
            ,3=>$langs->trans('Wednesday')            
            ,4=>$langs->trans('Thursday')            
            ,5=>$langs->trans('Friday')            
            ,6=>$langs->trans('Saturday')            
        );
    
        $this->day_moment = 'ALL';
    
        $this->start();
        
    }
    
    function save(&$PDOdb) {
        
        if($this->date_off>0) $this->week_day =-1;
        
        if($this->date_off == 0 && $this->week_day == -1) return false;
        
        return parent::save($PDOdb);
        
    }
    
}
class TWorkstationProduct extends TObjetStd{
	
	function __construct() {
		$this->set_table(MAIN_DB_PREFIX.'workstation_product');
    	$this->TChamps = array(); 	  
		$this->add_champs('fk_product, fk_workstation','type=entier;index;');
		$this->add_champs('nb_hour,rang,nb_hour_prepare,nb_hour_manufacture','type=float;'); // nombre d'heure associé au poste de charge et au produit
		
		$this->start();
		
		$this->nb_hour=0;
		$this->rang=0;
	}
	
}
