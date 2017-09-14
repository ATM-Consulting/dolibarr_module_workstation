<?php

	require('config.php');
	
	$langs->load('workstation@workstation');
	
	dol_include_once('/core/class/html.form.class.php');
	dol_include_once('/core/class/html.formother.class.php');
    dol_include_once('/core/lib/functions2.lib.php');
	
	
	$action=__get('action','list');
	$fk_product=__get('fk_product',0,'integer');;
	
	$PDOdb=new TPDOdb;
	
	llxHeader('',$langs->trans('WorkStation'),'','');
	
	if($fk_product>0) {
		
		switch($action) {
			
			case 'add':
				$fk_workstation = __get('fk_workstation',0,'int');
				
				if (!$fk_workstation) 
				{
					setEventMessage('Aucun poste de travail séléctionné', 'errors');
					_liste_link($PDOdb, $fk_product);
					break;
				}
				
				$wsp = new WorkstationProduct($db);
				$wsp->fk_product = $fk_product;
				$wsp->fk_workstation = $fk_workstation;
				
				$ws = new Workstation($db);
				$ws->load($PDOdb,$fk_workstation);
				
				$wsp->nb_hour_prepare = $ws->nb_hour_prepare;
				$wsp->nb_hour_manufacture = $ws->nb_hour_manufacture;
				$wsp->nb_hour = $ws->nb_hour_prepare + $ws->nb_hour_manufacture;
				
				$wsp->save($PDOdb);
			
				setEventMessage('Poste de travail ajouté');
			
				_liste_link($PDOdb, $fk_product);
				
				break;
			
			case 'list':
				_liste_link($PDOdb, $fk_product);
				
				break;
			
			case 'save':
			
				foreach($_REQUEST['TWorkstationProduct'] as $id=>$row) {
				
					$wsp = new WorkstationProduct($db);
					//$PDOdb->debug=true;
					$wsp->load($PDOdb, $id);
					
					$wsp->nb_hour_prepare = Tools::string2num($row['nb_hour_prepare']);
					$wsp->nb_hour_manufacture = Tools::string2num($row['nb_hour_manufacture']);
					$wsp->nb_hour = $wsp->nb_hour_prepare + $wsp->nb_hour_manufacture;
					$wsp->rang = (double) $row['rang'];
					
					$wsp->save($PDOdb);
				}
				
				setEventMessage('Modifications enregistrées');
				
				_liste_link($PDOdb, $fk_product);
				break;
				
			case 'delete':				
				$wsp = new WorkstationProduct;
				$wsp->load($PDOdb, GETPOST('id_wsp'));
				$wsp->to_delete = true;
				$wsp->save($PDOdb);
				
				_liste_link($PDOdb, $fk_product);
				
				break;
			
		}
		
	}
	else {
		
		switch($action) {
			
			case 'save':				
				$ws=new Workstation($db);
				$ws->load($PDOdb, __get('id',0,'integer'));
				$ws->set_values($_REQUEST);
                
				if(!empty($_REQUEST['TWorkstationSchedule'])) {
					
	                foreach($_REQUEST['TWorkstationSchedule'] as $k=>&$wsc)
					{
						if($k == -1) $k = $ws->addChild('Workstation_schedule');
//	                    if($k == -1) $k=$ws->addChild($PDOdb, 'TWorkstationSchedule');
	                    
	                    $ws->TWoyrkstationSchedule[$k]->setValues($wsc);
	                }
	                
				}
                
                
                if(!empty($_REQUEST['TWSTask']['libelle'])) {
                	if($_REQUEST['id_task'] == 0) {
                		
//                		$k = $ws->addChild($PDOdb, 'TAssetWorkstationTask');
                		$k = $ws->addChild('Asset_workstation_task');
                		
                		$ws->TAssetWorkstationTask[$k]->setValues($_REQUEST['TWSTask']);
                		
                	}
                	else {
                		
                		foreach($ws->TAssetWorkstationTask as $k=>&$wst) {
                			
                			if($wst->getId() == $_REQUEST['id_task']) {
                				$ws->TAssetWorkstationTask[$k]->setValues($_REQUEST['TWSTask']);
                				
                				break;
                			}
                			
                		}
                		
                	}
                	
                }
                
				$r = $ws->save($PDOdb);
				var_dump($r);
				exit;
				
				_fiche($PDOdb, $ws);
				
				break;
			case 'view':
				$ws=new Workstation($db);
				$ws->load($PDOdb, __get('id',0,'integer'));

				_fiche($PDOdb, $ws);
				
				break;
			
            case 'deleteSchedule':
                $ws=new Workstation($db);
                $ws->load($PDOdb, __get('id',0,'integer'));
                
                $ws->TWorkstationSchedule[(int)GETPOST($k)]->to_delete = true;
                
                $ws->save($PDOdb);
                
                setEventMessage('ScheduleDeleted');
                
                _fiche($PDOdb, $ws,'edit');
                
                break;
            
			case 'edit':
                $ws=new Workstation($db);
                $ws->load($PDOdb, __get('id',0,'integer'));
                _fiche($PDOdb, $ws,'edit');
				
				break;
			
			case 'delete':
			
				$ws=new Workstation($db);
				$ws->load($PDOdb, __get('id',0,'integer'));
				
				$ws->delete($PDOdb);
				
				_liste($PDOdb);
				
				break;
			
			case 'new':
				
				$ws=new Workstation($db);
				$ws->set_values($_REQUEST);
				
				_fiche($PDOdb, $ws,'edit');
				
				break;
			
			case 'list':
				
				_liste($PDOdb);
				
				break;
			
			case 'editTask':
				$ws=new Workstation($db);
				$ws->load($PDOdb, __get('id',0,'integer'));
				_fiche($PDOdb, $ws, 'view', 1);
				
				break;
					
			case 'editTaskConfirm':
				$ws=new TAssetWorkstation;
				$ws->load($PDOdb, __get('id',0,'integer'));
								
				$k=$ws->addChild($PDOdb,'TAssetWorkstationTask', __get('id_task', 0, 'int'));
				$ws->TAssetWorkstationTask[$k]->fk_workstation = $ws->getId();
				$ws->TAssetWorkstationTask[$k]->libelle = __get('libelle');
				$ws->TAssetWorkstationTask[$k]->description = __get('description');
				
				if ($ws->TAssetWorkstationTask[$k]->save($PDOdb)) setEventMessage($langs->trans('WorkstationMsgSaveTask'));
				else setEventMessage($langs->trans('WorkstationErrSaveTask'));
				
				_fiche($PDOdb, $ws, 'view');
				
				break;
			
			case 'deleteTask':
				$ws=new Workstation($db);
				$ws->load($PDOdb, __get('id',0,'integer'));
				
				if ($ws->removeChild('TAssetWorkstationTask', __get('id_task',0,'integer'))) 
				{
					$ws->save($PDOdb);
					$ws->load($PDOdb, __get('id',0,'integer'));
					setEventMessage($langs->trans('WorkstationMsgDeleteTask'));
				}
				else setEventMessage($langs->trans('WorkstationErrDeleteTask'));
				
				_fiche($PDOdb, $ws, 'view');
				
				break;
				
		}
		
	}
	
	
	llxFooter();

function _liste_link(&$PDOdb, $fk_product) {
	global $db,$langs,$conf, $user;	
	
	if($fk_product>0){
		if(is_file(DOL_DOCUMENT_ROOT."/lib/product.lib.php")) require_once(DOL_DOCUMENT_ROOT."/lib/product.lib.php");
		else require_once(DOL_DOCUMENT_ROOT."/core/lib/product.lib.php");
		
		require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
			
		$product = new Product($db);
		$result=$product->fetch($fk_product);	
			
		$head=product_prepare_head($product, $user);
		$titre=$langs->trans("CardProduct".$product->type);
		$picto=($product->type==1?'service':'product');
		dol_fiche_head($head, 'tabWorkstation', $titre, 0, $picto);
        
        headerProduct($product);
	}
  
	
	$form=new TFormCore('auto','formLWS');
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_product', $fk_product);
	
	
	$l=new TListviewTBS('listWS');

	$sql= "	SELECT wsp.rowid as id, wsp.fk_workstation as id_ws, ws.name, wsp.rang, wsp.nb_hour_prepare, wsp.nb_hour_manufacture, wsp.nb_hour, '' as 'action'
			FROM ".MAIN_DB_PREFIX."workstation ws LEFT OUTER JOIN ".MAIN_DB_PREFIX."workstation_product wsp ON (wsp.fk_workstation=ws.rowid)
			WHERE entity IN(".getEntity('workstation', 1).")
			AND wsp.fk_product=".$fk_product;
	
	$liste =  $l->render($PDOdb, $sql,array(
		'link'=>array(
			'libelle'=>'<a href="?action=view&id=@id@">@val@</a>'
			,'rang'=>'<input type="text" name="TWorkstationProduct[@id@][rang]" value="@val@" size="5" />'
			,'nb_hour_prepare'=>'<input type="text" name="TWorkstationProduct[@id@][nb_hour_prepare]" value="@val@" size="5" />'
			,'nb_hour_manufacture'=>'<input type="text" name="TWorkstationProduct[@id@][nb_hour_manufacture]" value="@val@" size="5" />'
			//,'nb_hour'=>'<input type="text" name="TWorkstationProduct[@id@][nb_hour]" value="@val@" size="5" />'
			,'nb_hour'=>'@val@'
			,'action'=> '<a href="workstation.php?action=delete&fk_product='.$fk_product.'&id_wsp=@id@">'.img_picto('Supprimer', 'delete.png').'</a>'
		)
		,'title'=>array(
			'nb_hour_prepare'=>"Nombre d'heures de préparation"
			,'nb_hour_manufacture'=>"Nombre d'heures de fabrication"
			,'nb_hour'=>"Nombre d'heures totale"
			,'rang'=>"Rang"
		)
		,'hide'=>array('id_ws')
	));
	
	$TBS=new TTemplateTBS;
	
    
	print $TBS->render('./tpl/workstation_link.tpl.php',
		array()
		,array(
			'view'=>array(
				'mode'=>$mode
				,'liste'=>$liste
				,'select_workstation'=>$form->combo('', 'fk_workstation', Workstation::getWorstations($PDOdb), -1)
				,'fk_product'=>$fk_product
			)
		)
		
	);
    
    if($conf->global->WORKSTATION_LINK_SUBPRODUCT && $fk_product>0) {
            _fiche_sub_product($PDOdb, $product);
        
    }
	
	$form->end();
    
    
}

function _fiche_sub_product(&$PDOdb, &$product ) {
      global $langs, $db;
         
      $TProd = $product->getChildsArbo($product->id);
         
      if(empty($TProd)) return false;   
         
      print '<table class="liste">
            <tr class="liste_titre">
                <th class="liste_titre">'.$langs->trans('Product').'</th>
                <th>'.$langs->trans('WorkStations').'</th>
            <tr>
      ';   
         
         
         
      foreach($TProd as $prod) {
          $class = ($class=='impair') ? 'pair' : 'impair';  
                    
          list($id,$qty,$type,$label) = $prod;
          
          $sub_product = new Product($db);
          $sub_product->fetch($id);
          
          print '<tr class="'.$class.'">
            <td>'.$sub_product->getNomUrl(1).' '.$sub_product->label.'</td>
            <td></td>
          </tr>
          '; 
          
      }
    
      print '</table>';
    
}
function _fiche(&$PDOdb, &$ws, $mode='view', $editTask=false) {
	global $db,$conf,$langs;

	$TBS=new TTemplateTBS;
	
	$form=new TFormCore('auto', 'formWS', 'post', true);
	$formother=new FormOther($db);
	
	$form->Set_typeaff( $mode );
	
	echo $form->hidden('action','save');
	echo $form->hidden('id',$ws->getId());
	
	$formDoli=new Form($db);
	
    dol_include_once('/user/class/usergroup.class.php');
    
    $group=new UserGroup($db);
    $group->fetch($ws->fk_usergroup);
    
    $hour_per_day = !empty($conf->global->TIMESHEET_WORKING_HOUR_PER_DAY) ? $conf->global->TIMESHEET_WORKING_HOUR_PER_DAY : 7;
  	switch($mode)
	{
		case 'edit':
			if((float)DOL_VERSION > 3.6) $background = $formother->selectColor(colorArrayToHex(colorStringToArray($ws->background,array()),''),'background','workstationformcolor',1);
			else $background = $form->texte('', 'background', $ws->background,50,255);
			break;
		default :
			$background = '<div style="height:100%;width:50px;background:'.$ws->background.';padding:5px;">'.$ws->background.'</div>';
			break;
	}
	$TForm=array(
		'name'=>$form->texte('', 'name', $ws->name,80,255)
		,'code'=>$form->texte('', 'code', $ws->code,12,10)
		,'nb_hour_prepare'=>$form->texte('', 'nb_hour_prepare', $ws->nb_hour_prepare,3,3)
		,'nb_hour_manufacture'=>$form->texte('', 'nb_hour_manufacture', $ws->nb_hour_manufacture,3,3)
        ,'thm'=>$form->texte('', 'thm', $ws->thm,5,5)
        ,'thm_machine'=>$form->texte('', 'thm_machine', $ws->thm_machine,5,5)
		,'thm_overtime'=>$form->texte('', 'thm_overtime', $ws->thm_overtime,5,5)
		,'thm_night'=>$form->texte('', 'thm_night', $ws->thm_night,5,5)
		,'nb_hour_before'=>$form->texte('', 'nb_hour_before', $ws->nb_hour_before,3,5)
		,'nb_hour_after'=>$form->texte('', 'nb_hour_after', $ws->nb_hour_after,3,5)
        ,'nb_hour_capacity'=>$form->texte('', 'nb_hour_capacity', $ws->nb_hour_capacity,3,3).(($mode=='view') ? "h, soit une vélocité de ".round($ws->nb_hour_capacity / $hour_per_day,2) :''  )
		//,'nb_hour_capacity'=>(int) $ws->nb_hour_capacity.(($mode=='view') ? "h, soit une vélocité de ".round($ws->nb_hour_capacity / $hour_per_day,2) :''  )
		,'nb_ressource'=>$form->texte('', 'nb_ressource', $ws->nb_ressource,3,3)
    	,'background'=>$background
		,'fk_usergroup'=>($mode=='view') ? $group->name : $formDoli->select_dolgroups($ws->fk_usergroup, 'fk_usergroup',0,'' )
		,'type'=> $form->combo('', 'type', $ws->TType, $ws->type)
		,'id'=>$ws->getId()
	    ,'simple' => !empty($conf->global->BTP_SIMPLE_DISPLAY)
	);
	
	$TListTask = _liste_task($ws);
	$TFormTask = _fiche_task($PDOdb, $editTask);
	
    $head=workstation_prepare_head( $ws );
    $titre=$langs->trans('WorkStation');
    dol_fiche_head($head, 'card', $titre);
    
    $TWorkstationSchedule = _fiche_schedule($form, $ws);
    
	print $TBS->render('./tpl/workstation.tpl.php',
		array(
			'wst'=>$TListTask
			,'TWorkstationSchedule'=>$TWorkstationSchedule
		),
		array(
			'ws'=>$TForm
			,'formTask'=>$TFormTask
			,'view'=>array(
				'mode'=>$mode
				,'conf_defined_task'=>(int) $conf->global->ASSET_DEFINED_OPERATION_BY_WORKSTATION
				,'editTask'=>$editTask
				/*,'endForm'=>$form->end_form()*/
				,'actionForm'=>dol_buildpath('custom/asset/workstation.php', 1)
                ,'scheduleTitle'=>load_fiche_titre($langs->trans('WSScheduleList'))
				,'isMachine'=>($ws->type == 'MACHINE' ? 1 : 0)
				,'langs'=>$langs
			)
		)
		
	);
	
	
	
    dol_fiche_end();
	$form->end();
}

function _fiche_schedule(&$form, &$ws) {
    global $langs;
    
    $Tab=array();
    
	if (!empty($ws->TWorkstationSchedule))
	{
		foreach($ws->TWorkstationSchedule as $k=> &$sc) {
        
			if(!$sc->to_delete) {
				$Tab[] = array(
					'date_off'=>$form->calendrier('', 'TWorkstationSchedule['.$k.'][date_off]', $sc->date_off)
					,'week_day'=>$form->combo('', 'TWorkstationSchedule['.$k.'][week_day]', $sc->TWeekDay , $sc->week_day)
					,'day_moment'=>$form->combo('', 'TWorkstationSchedule['.$k.'][day_moment]', $sc->TDayMoment , $sc->day_moment)
					,'nb_ressource'=>$form->texte('', 'TWorkstationSchedule['.$k.'][nb_ressource]', $sc->nb_ressource , 3,3)
					,'action'=>($form->type_aff != 'view' && $sc->getId()>0 ? '<a href="?id='.$ws->getId().'&action=deleteSchedule&k='.$k.'">'.img_delete().'</a>' : '' )
				);


			}

		}
	}
	
	$sc=new WorkstationSchedule($db);
    if($form->type_aff != 'view' ) {
        $Tab[] = array(
            'date_off'=>$form->calendrier('', 'TWorkstationSchedule[-1][date_off]', 0)
            ,'week_day'=>$form->combo('', 'TWorkstationSchedule[-1][week_day]', $sc->TWeekDay , -1)
            ,'day_moment'=>$form->combo('', 'TWorkstationSchedule[-1][day_moment]', $sc->TDayMoment , 'ALL')
            ,'nb_ressource'=>$form->texte('', 'TWorkstationSchedule[-1][nb_ressource]', 0 , 3,3)
            ,'action'=>'Nouveau'
        );
    }

    return $Tab;
}

function _liste_task(&$ws)
{
	global $langs;
	
	$res = array();
	
    if(!empty($ws->TAssetWorkstationTask)) {
        foreach ($ws->TAssetWorkstationTask as $task)
        {
            $res[] = array(
                'id'=>$task->getId()
                ,'libelle'=>$task->libelle //TODO label
                ,'description'=>$task->description
            		,'action'=>'<a href="?id='.$ws->getId().'&action=editTask&id_task='.$task->getId().'">'.img_picto($langs->trans('Modify'), 'edit.png').'</a>
				&nbsp;&nbsp;<a onclick=\'if (!confirm("'.$langs->transnoentities('ConfirmDelete').'")) return false;\' href="?id='.$ws->getId().'&action=deleteTask&id_task='.$task->getId().'">'.img_picto($langs->trans('Delete'), 'delete.png').'</a>'
            );
        }
        
    }
    
	
	return $res;
}

function _fiche_task(&$PDOdb, $editTask)
{
	$res = array();
	
	if (!$editTask) return $res;
	
	$id_task = __get('id_task', 0, 'int');
	$res['id_task'] = $id_task;
	
	if ($id_task > 0)
	{
		$task = new TAssetWorkstationTask;
		$task->load($PDOdb, $id_task);
		$res['libelle'] = $task->libelle;
		$res['description'] = $task->description;
		
	}
	else 
	{
		$res['libelle'] = '';
		$res['description'] = '';
	}
	
	return $res;
}

function _liste(&$PDOdb) {
	global $conf, $langs, $db;
	/*
	 * Liste des poste de travail de l'entité
	 */
	
//	$l=new TListviewTBS('listWS');
	$l=new Listview($db, 'listWS');
	
	$sql= "SELECT ws.rowid as id, ws.name,ws.fk_usergroup, ws.nb_hour_prepare, ws.nb_hour_manufacture, ws.nb_hour_capacity,ws.nb_ressource 
	
	FROM ".MAIN_DB_PREFIX."workstation ws LEFT OUTER JOIN ".MAIN_DB_PREFIX."workstation_product wsp ON (wsp.fk_workstation=ws.rowid)
	 
	WHERE entity IN(".getEntity('workstation', 1).') GROUP BY ws.rowid';

	$fk_product = __get('id_product',0,'integer');
	if($fk_product>0)$sql.=" AND wsp.fk_product=".$fk_product;

	$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
	$sortfield = GETPOST("sortfield",'alpha');
	$sortorder = GETPOST("sortorder",'alpha');

	print $l->render($sql, array(
		'link'=>array(
			'name'=>'<a href="?action=view&id=@id@">@val@</a>'
		)
		,'title'=>array(
			'nb_hour_prepare'=>"Nombre d'heure de preparation",
			'nb_hour_manufacture'=>"Nombre d'heure de fabrication",
			'nb_hour_capacity'=>"Nombre d'heure maximum",
			'nb_ressource'=>'Nombre de ressource disponible',
			'id'=>"Id",
			'name'=>"Intitulé poste de travail",
			'fk_usergroup'=>"Groupe"
		)
		,'list'=>array(
			'title'=>'Liste des '.$langs->trans('WorkStation')
			,'image'=>'title.png'
			,'messageNothing'=>"Il n'y a aucun ".$langs->trans('WorkStation')." à afficher"
		)
		,'limit' => array(
			'nbLine' => $limit
		)
		,'sortfield'=>$sortfield
    	,'sortorder'=>$sortorder
		,'allow-fields-select' => 0
		,'no-auto-sql-search' => 1
		,'search' => array()
	));
	
}


function headerProduct(&$object) {
   global $langs, $conf, $db; 
    
    $form = new Form($db);
        
    print '<table class="border" width="100%">';
    
    
    // Ref
    print '<tr>';
    print '<td width="15%">' . $langs->trans("Ref") . '</td><td colspan="2">';
    print $form->showrefnav($object, 'ref', '', 1, 'ref');
    print '</td>';
    print '</tr>';
    
    // Label
    print '<tr><td>' . $langs->trans("Label") . '</td><td>' . $object->libelle . '</td>';
    
    $isphoto = $object->is_photo_available($conf->product->multidir_output [$object->entity]);
    
    $nblignes = 5;
    if ($isphoto) {
        // Photo
        print '<td valign="middle" align="center" width="30%" rowspan="' . $nblignes . '">';
        print $object->show_photos($conf->product->multidir_output [$object->entity], 1, 1, 0, 0, 0, 80);
        print '</td>';
    }
    
    print '</tr>';
    
    
    // Status (to sell)
    print '<tr><td>' . $langs->trans("Status") . ' (' . $langs->trans("Sell") . ')</td><td>';
    print $object->getLibStatut(2, 0);
    print '</td></tr>';
    
    print "</table>\n";
    
  echo '<br />';
        
   
       
        
    
}
