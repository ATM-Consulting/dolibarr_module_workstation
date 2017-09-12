<?php

require_once DOL_DOCUMENT_ROOT.'/core/class/coreobject.class.php';

if($conf->of->enabled) dol_include_once('/of/class/ordre_fabrication_asset.class.php');

class TWorkstation extends SeedObject
{
	public $element='workstation';
    public $table_element = 'workstation';
	public $fk_element='fk_workstation';
    protected $childtables=array('workstation_schedule');
	
	protected $fields = array(
		'entity' => array('type' => 'int', 'index' => true)
		,'fk_usergroup' => array('type' => 'int', 'index' => true)
		,'name' => array('type' => 'string')
		,'background' => array('type' => 'string')
		,'type' => array('type' => 'string', 'length' => 10)
		,'code' => array('type' => 'string', 'length' => 10)
		,'nb_hour_prepare' => array('type' => 'double')
		,'nb_hour_manufacture' => array('type' => 'double')
		,'nb_hour_capacity' => array('type' => 'double')
		,'nb_ressource' => array('type' => 'double')
		,'thm' => array('type' => 'double')
		,'thm_machine' => array('type' => 'double')
		,'thm_overtime' => array('type' => 'double')
		,'thm_night' => array('type' => 'double')
		,'nb_hour_before' => array('type' => 'double')
		,'nb_hour_after' => array('type' => 'double')
	);
	
	function __construct($db='') 
	{
		if (empty($db)) 
		{
			global $db;
		}
		
		$this->init();
		
//		if(class_exists('TAssetWorkstationTask')) $this->setChild('TAssetWorkstationTask','fk_workstation');
		if(class_exists('TAssetWorkstationTask')) $this->childtables[] = 'asset_workstation_task';
		
		$this->Workstation_schedule = array();
		$this->Asset_workstation_task = array();
		
		// TODO add $langs->trans()
		$this->TType=array(
			'HUMAN'=>'Humain (+ Machine)'
			,'MACHINE'=>'Machine'
		);
		
		parent::__construct($db);
	}
	
	// rétrocompatibilité si d'autres modules utilise cet objet
	function get_table()
	{
		return $this->table_element;
	}
	
	function getId()
	{
		return $this->id;
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
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			while($row = $this->db->fetch_object($resql))
			{
				$nb_day = floor(($row->datee - ($row->dateo > 0 ? $row->dateo : $row->datee) ) / 86400) + 1;
				$t_needs = ($row->planned_workload / 3600) / $nb_day;
				$capacity -= $t_needs;
			}
		}
		else
		{
			dol_print_error($this->db);
            return -1;
		}
		
		
		return $capacity;
	}
	
	function load(&$PDOdb, $id, $annexe = true)
	{
	    global $conf;
        
		$res = $this->fetch($id, $annexe);
		
		// Rétrocompatibilité
        if (isset($this->Workstation_schedule)) $this->TWorkstationSchedule = &$this->Workstation_schedule;
        if (isset($this->Asset_workstation_task)) $this->TAssetWorkstationTask = &$this->Asset_workstation_task;
		
        if(!empty($conf->global->TIMESHEET_DAYOFF) && empty($this->TWorkstationSchedule)) {
            
            $TJourOff = explode(',', $conf->global->TIMESHEET_DAYOFF);
            
            foreach($TJourOff as $jo) {
// TODO remove
//                $k = $this->addChild($PDOdb, 'TWorkstationSchedule');
                $k = $this->addChild('Workstation_schedule');
				
                $this->TWorkstationSchedule[$k]->week_day = $jo; // On charge le jour off dans le système, sera up à la sauvegarde
                $this->TWorkstationSchedule[$k]->nb_ressource = $this->nb_ressource;
                
            }
            
        }
		
		return $res;
	}
	
	function save(&$PDOdb)
	{
		global $conf,$user;
		
		$this->entity = $conf->entity;
		
		if((float)DOL_VERSION > 3.6 && $this->background[0]!='#')$this->background='#'.$this->background;

		//return parent::save($PDOdb);
		return $this->update($user);
	}
	
	function set_values($Tab)
	{
		if (isset($Tab['nb_hour_prepare']) && isset($Tab['nb_hour_manufacture']))
		{
			$Tab['nb_hour_capacity'] = $Tab['nb_hour_prepare'] + $Tab['nb_hour_manufacture'];
		}
		
		return $this->setValues($Tab);
	}
	
	/**
	 * Méthode pour récupérer la liste des objets "Postes de travail"
	 * 
	 * @param type $PDOdb
	 * @return $TWorkstation array of object
	 */
	static function getAllWorkstationObject(&$PDOdb)
	{
		global $db;
		
		$TWorkstation = array();
		$TDetail = self::getWorstations($PDOdb);
		
		if (!empty($TDetail))
		{
			foreach ($TDetail as $fk_workstation => $Tab)
			{
				if ($fk_workstation > 0)
				{
					$ws = new TWorkstation($db);
					$ws->fetch($fk_workstation);
					
					$TWorkstation[$ws->id] = $ws;
				}
			}
		}
		
		return $TWorkstation;
	}
	
	static function getWorstations(&$PDOdb, $details = false, $initEmpty=false, $TWorkstation=array(), $only_with_ressource = false) {
		global $conf;
		
        dol_include_once('/user/class/usergroup.class.php');
        
        $hour_per_day = !empty($conf->global->TIMESHEET_WORKING_HOUR_PER_DAY) ? $conf->global->TIMESHEET_WORKING_HOUR_PER_DAY : 7;
   
		$sql = "SELECT rowid, background,name,nb_ressource,nb_hour_capacity,nb_hour_before,nb_hour_after,fk_usergroup 
				FROM ".MAIN_DB_PREFIX."workstation WHERE entity IN(".getEntity('workstation', 1).')';
			
		if($only_with_ressource) $sql.=" AND nb_ressource>0 ";
				
		$sql.="	ORDER BY name ";
		
		$resql = $this->db->query($sql);
		if ($resql)
		{
			if($initEmpty) $TWorkstation[-1] = '';

			while ($obj = $this->db->fetch_object($resql))
			{
				$fk_workstation = $obj->rowid;

				if ($details)
				{
					$fk_usergroup = $obj->fk_usergroup;
					//                $TUser = $g->listUsersForGroup('statut = 1');
					$sql = "SELECT u.rowid FROM " . MAIN_DB_PREFIX . "user as u, " . MAIN_DB_PREFIX . "usergroup_user as ug WHERE 1 ";
					$sql .= " AND ug.fk_user = u.rowid";
					$sql .= " AND ug.fk_usergroup = " . $fk_usergroup;
					$sql .= " AND u.statut != 0 "; //on ne prend que les utilisateurs actifs
					$resUser = $this->db->query($sql);
					$TUser = array();
					while ($obju = $this->db->fetch_object($resUser))
					{
						$newuser = new User($this->db);
						$newuser->fetch($obju->rowid);

						$TUser[$obju->rowid] = $newuser;
					}

					$TWorkstation["$fk_workstation"] = array(
						'nb_ressource' => $obj->nb_ressource
						, 'velocity' => $obj->nb_hour_capacity / $hour_per_day
						, 'background' => $obj->background
						, 'name' => $obj->name
						, 'nb_hour_before' => $obj->nb_hour_before
						, 'nb_hour_after' => $obj->nb_hour_after
						, 'TUser' => $TUser
						, 'id' => $fk_workstation
					);
				}
				else
				{
					$TWorkstation["$fk_workstation"] = $obj->name;
				}
			}

			return $TWorkstation;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}
	
	function getNomUrl($withPicto = 0) {
	            
	      return '<a href="'.dol_buildpath('/workstation/workstation.php?action=view&id='.$this->getId(),1).'" >'.($withPicto ? img_picto('', 'object_generic').' ' : '' ).$this->name.'</a>';  
	    
	}
	
	
}

// Compatibilité CoreObject de Dolibarr pour les objets "enfants"
class Workstation_schedule extends TWorkstationSchedule {}

class TWorkstationSchedule extends SeedObject
{
    public $element='workstation_schedule';
    public $table_element = 'workstation_schedule';
	public $fk_element='fk_workstation_schedule';
    protected $childtables=array();
	
	protected $fields = array(
		'fk_workstation' => array('type' => 'int', 'index' => true)
		,'day_moment' => array('type' => 'string')
		,'week_day' => array('type' => 'int')
		,'nb_ressource' => array('type' => 'int')
		,'date_off' => array('type' => 'date')
	);
	
    function __construct($db='')
	{
        global $langs;
        
		if (empty($db))
		{
			global $db;
		}
		
		$this->init();
    
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
		
		return parent::__construct($db);
    }
    
    function save(&$PDOdb)
	{
        global $user;
		
        if($this->date_off>0) $this->week_day =-1;
        
        if($this->date_off == 0 && $this->week_day == -1) return false;
        
		return $this->update($user);
    }
    
}

class TWorkstationProduct extends CoreObject
{
	public $element='workstation_product';
    public $table_element = 'workstation_product';
	public $fk_element='fk_workstation_product';
    protected $childtables=array();
	
	protected $fields = array(
		'fk_product' => array('type' => 'int', 'index' => true)
		,'fk_workstation' => array('type' => 'int', 'index' => true)
		,'nb_hour' => array('type' => 'double')
		,'nb_hour_prepare' => array('type' => 'double')
		,'nb_hour_manufacture' => array('type' => 'double')
		,'rang' => array('type' => 'int')
	);
	
	function __construct($db='')
	{
		if (empty($db))
		{
			global $db;
		}
		
		$this->init();
		
		$this->nb_hour=0;
		$this->rang=0;
		
		return parent::__construct($db);
	}
	
	function load(&$PDOdb, $id, $loadChild = true)
	{
		return $this->fetch($id, $loadChild);
	}
	
	function save(&$PDOdb)
	{
		global $user;
		
		return $this->update($user);
	}
}
