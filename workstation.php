<?php

	require('config.php');
	
	$langs->load('workstation@workstation');
	
	dol_include_once('/core/class/html.form.class.php');
	
	
	$action=__get('action','list');
	$fk_product=__get('fk_product',0,'integer');;
	
	$ATMdb=new TPDOdb;
	
	llxHeader('',$langs->trans('workstation'),'','');
	
	if($fk_product>0) {
		
		switch($action) {
			
			case 'add':
				$fk_workstation = __get('fk_workstation',0,'int');
				
				if (!$fk_workstation) 
				{
					setEventMessage('Aucun poste de travail séléctionné', 'errors');
					_liste_link($ATMdb, $fk_product);
					break;
				}
				
				$wsp = new TWorkstationProduct;
				$wsp->fk_product = $fk_product;
				$wsp->fk_workstation = $fk_workstation;
				
				$ws = new TWorkstation;
				$ws->load($ATMdb,$fk_workstation);
				
				$wsp->nb_hour_prepare = $ws->nb_hour_prepare;
				$wsp->nb_hour_manufacture = $ws->nb_hour_manufacture;
				$wsp->nb_hour = $ws->nb_hour_prepare + $ws->nb_hour_manufacture;
				
				$wsp->save($ATMdb);
			
				setEventMessage('Poste de travail ajouté');
			
				_liste_link($ATMdb, $fk_product);
				
				break;
			
			case 'list':
				_liste_link($ATMdb, $fk_product);
				
				break;
			
			case 'save':
			
				foreach($_REQUEST['TWorkstationProduct'] as $id=>$row) {
				
					$wsp = new TWorkstationProduct;
					//$ATMdb->debug=true;
					$wsp->load($ATMdb, $id);
					
					$wsp->nb_hour_prepare = Tools::string2num($row['nb_hour_prepare']);
					$wsp->nb_hour_manufacture = Tools::string2num($row['nb_hour_manufacture']);
					$wsp->nb_hour = $wsp->nb_hour_prepare + $wsp->nb_hour_manufacture;
					$wsp->rang = (double) $row['rang'];
					
					$wsp->save($ATMdb);
				}
				
				setEventMessage('Modifications enregistrées');
				
				_liste_link($ATMdb, $fk_product);
				break;
				
			case 'delete':				
				$wsp = new TWorkstationProduct;
				$wsp->load($ATMdb, GETPOST('id_wsp'));
				$wsp->to_delete = true;
				$wsp->save($ATMdb);
				
				_liste_link($ATMdb, $fk_product);
				
				break;
			
		}
		
	}
	else {
		
		switch($action) {
			
			case 'save':				
				$ws=new TWorkstation;
				$ws->load($ATMdb, __get('id',0,'integer'));
				$ws->set_values($_REQUEST);
				$ws->nb_hour_capacity = $ws->nb_hour_prepare + $ws->nb_hour_manufacture;
				$ws->save($ATMdb);
				
				_fiche($ATMdb, $ws);
				
				break;
			case 'view':
				$ws=new TWorkstation;
				$ws->load($ATMdb, __get('id',0,'integer'));

				_fiche($ATMdb, $ws);
				
				break;
			
			case 'edit':
				$ws=new TWorkstation;
				$ws->load($ATMdb, __get('id',0,'integer'));
				_fiche($ATMdb, $ws,'edit');
				
				break;
			
			case 'delete':
			
				$ws=new TWorkstation;
				$ws->load($ATMdb, __get('id',0,'integer'));
				
				$ws->delete($ATMdb);
				
				_liste($ATMdb);
				
				break;
			
			case 'new':
				
				$ws=new TWorkstation;
				$ws->set_values($_REQUEST);
				
				_fiche($ATMdb, $ws,'edit');
				
				break;
			
			case 'list':
				
				_liste($ATMdb);
				
				break;
			
			case 'editTask':
				$ws=new TWorkstation;
				$ws->load($ATMdb, __get('id',0,'integer'));
				_fiche($ATMdb, $ws, 'view', 1);
				
				break;
					
			case 'editTaskConfirm':
				$ws=new TAssetWorkstation;
				$ws->load($ATMdb, __get('id',0,'integer'));
								
				$k=$ws->addChild($PDOdb,'TAssetWorkstationTask', __get('id_task', 0, 'int'));
				$ws->TAssetWorkstationTask[$k]->fk_workstation = $ws->getId();
				$ws->TAssetWorkstationTask[$k]->libelle = __get('libelle');
				$ws->TAssetWorkstationTask[$k]->description = __get('description');
				
				if ($ws->TAssetWorkstationTask[$k]->save($ATMdb)) setEventMessage($langs->trans('WorkstationMsgSaveTask'));
				else setEventMessage($langs->trans('WorkstationErrSaveTask'));
				
				_fiche($ATMdb, $ws, 'view');
				
				break;
			
			case 'deleteTask':
				$ws=new TWorkstation;
				$ws->load($ATMdb, __get('id',0,'integer'));
				
				if ($ws->removeChild('TAssetWorkstationTask', __get('id_task',0,'integer'))) 
				{
					$ws->save($ATMdb);
					$ws->load($ATMdb, __get('id',0,'integer'));
					setEventMessage($langs->trans('WorkstationMsgDeleteTask'));
				}
				else setEventMessage($langs->trans('WorkstationErrDeleteTask'));
				
				_fiche($ATMdb, $ws, 'view');
				
				break;
				
		}
		
	}
	
	
	llxFooter();

function _liste_link(&$ATMdb, $fk_product) {
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
		dol_fiche_head($head, 'tabOF1', $titre, 0, $picto);
	}
	
	$form=new TFormCore('auto','formLWS');
	echo $form->hidden('action', 'save');
	echo $form->hidden('fk_product', $fk_product);
	
	
	$l=new TListviewTBS('listWS');


	$sql= "	SELECT wsp.rowid as id, wsp.fk_workstation as id_ws, ws.name, wsp.rang, wsp.nb_hour_prepare, wsp.nb_hour_manufacture, wsp.nb_hour, '' as 'action'
			FROM ".MAIN_DB_PREFIX."workstation ws LEFT OUTER JOIN ".MAIN_DB_PREFIX."workstation_product wsp ON (wsp.fk_workstation=ws.rowid)
			WHERE entity=".$conf->entity."
			AND wsp.fk_product=".$fk_product;
	
	$liste =  $l->render($ATMdb, $sql,array(
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
			,'nb_hour'=>"Nombre d'heure maximum"
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
				,'select_workstation'=>$form->combo('', 'fk_workstation', TWorkstation::getWorstations($ATMdb), -1)
				,'fk_product'=>$fk_product
			)
		)
		
	);
	
	$form->end();
}


function _fiche(&$ATMdb, &$ws, $mode='view', $editTask=false) {
	global $db,$conf;

	$TBS=new TTemplateTBS;
	
	$form=new TFormCore('auto', 'formWS', 'post', true);
	
	$form->Set_typeaff( $mode );
	
	echo $form->hidden('action','save');
	echo $form->hidden('id',$ws->getId());
	
	$formDoli=new Form($db);
	
    dol_include_once('/user/class/usergroup.class.php');
    
    $group=new UserGroup($db);
    $group->fetch($ws->fk_usergroup);
    
    $hour_per_day = !empty($conf->global->TIMESHEET_WORKING_HOUR_PER_DAY) ? $conf->global->TIMESHEET_WORKING_HOUR_PER_DAY : 7;
  
	$TForm=array(
		'name'=>$form->texte('', 'name', $ws->name,80,255)
		,'nb_hour_prepare'=>$form->texte('', 'nb_hour_prepare', $ws->nb_hour_prepare,3,3)
		,'nb_hour_manufacture'=>$form->texte('', 'nb_hour_manufacture', $ws->nb_hour_manufacture,3,3)
		//,'nb_hour_capacity'=>$form->texte('', 'nb_hour_capacity', $ws->nb_hour_capacity,3,3).(($mode=='view') ? "h, soit une vélocité de ".round($ws->nb_hour_capacity / $hour_per_day,2) :''  )
		,'nb_hour_capacity'=>(int) $ws->nb_hour_capacity.(($mode=='view') ? "h, soit une vélocité de ".round($ws->nb_hour_capacity / $hour_per_day,2) :''  )
		,'nb_ressource'=>$form->texte('', 'nb_ressource', $ws->nb_ressource,3,3)
        ,'background'=>$form->texte('', 'background', $ws->background,50,255)
		,'fk_usergroup'=>($mode=='view') ? $group->name : $formDoli->select_dolgroups($ws->fk_usergroup, 'fk_usergroup',0,'' )
		,'id'=>$ws->getId()
	);
	
	$TListTask = _liste_task($ws);
	$TFormTask = _fiche_task($ATMdb, $editTask);
	
	print $TBS->render('./tpl/workstation.tpl.php',
		array(
			'wst'=>$TListTask
		),
		array(
			'ws'=>$TForm
			,'formTask'=>$TFormTask
			,'view'=>array(
				'mode'=>$mode
				,'conf_defined_task'=>(int) $conf->global->ASSET_DEFINED_OPERATION_BY_WORKSTATION
				,'editTask'=>$editTask
				,'endForm'=>$form->end_form()
				,'actionForm'=>dol_buildpath('custom/asset/workstation.php', 1)
			)
		)
		
	);
	
	//$form->end();
}

function _liste_task($ws)
{
	$res = array();
	
	foreach ($ws->TAssetWorkstationTask as $task)
	{
		$res[] = array(
			'id'=>$task->getId()
			,'libelle'=>$task->libelle
			,'description'=>$task->description
			,'action'=>'<a href="?id='.$ws->getId().'&action=editTask&id_task='.$task->getId().'">'.img_picto('Modifier', 'edit.png').'</a>&nbsp;&nbsp;<a onclick=\'if (!confirm("Confirmez-vous la suppression ?")) return false;\' href="?id='.$ws->getId().'&action=deleteTask&id_task='.$task->getId().'">'.img_picto('Supprimer', 'delete.png').'</a>'
		);
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

function _liste(&$ATMdb) {
	global $conf, $langs;
	/*
	 * Liste des poste de travail de l'entité
	 */
	
	$l=new TListviewTBS('listWS');

	$sql= "SELECT ws.rowid as id, ws.name,ws.fk_usergroup, ws.nb_hour_prepare, ws.nb_hour_manufacture, ws.nb_hour_capacity,ws.nb_ressource 
	
	FROM ".MAIN_DB_PREFIX."workstation ws LEFT OUTER JOIN ".MAIN_DB_PREFIX."workstation_product wsp ON (wsp.fk_workstation=ws.rowid)
	 
	WHERE entity=".$conf->entity.' GROUP BY ws.rowid';

	$fk_product = __get('id_product',0,'integer');
	if($fk_product>0)$sql.=" AND wsp.fk_product=".$fk_product;

	print $l->render($ATMdb, $sql,array(
	
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
		,'liste'=>array(
			'titre'=>'Liste des '.$langs->trans('Workstation')
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'noheader'=> (int)isset($_REQUEST['fk_soc']) | (int)isset($_REQUEST['fk_product'])
			,'messageNothing'=>"Il n'y a aucun ".$langs->trans('Workstation')." à afficher"
			,'picto_search'=>img_picto('','search.png', '', 0)
		)
	
	));
	
}
