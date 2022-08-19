<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file		lib/workstation.lib.php
 *	\ingroup	workstation
 *	\brief		This file is an example module library
 *				Put some comments here
 */

function workstation_prepare_head(&$ws) {

    global $langs, $conf;

    $langs->load("workstationatm@workstationatm");

    $head=array();

    $head[]=array(
        dol_buildpath("/workstationatm/workstation.php?action=view&id=".$ws->getId(), 1)
        ,$langs->trans("Workstation")
        ,'card'
    );

    return $head;

}

function workstationAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load("workstationatm@workstationatm");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/workstationatm/admin/workstation_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/workstationatm/admin/workstation_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@workstationatm:/workstationatm/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@workstationatm:/workstationatm/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, new stdClass(), $head, $h, 'workstation');

    return $head;
}

function printFilterWorkstationOnFullCalendarTaskScreen() {

	global $form, $langs;

	require_once __DIR__ . '/../class/workstation.class.php';

	$h = 0;
	$head = array();
	$head[$h][0] = $_SERVER["PHP_SELF"];
	$head[$h][1] = $langs->trans("ToFilter").'&nbsp;'.strtolower($langs->trans('By')).'&nbsp;'.$langs->trans('Workstations');

	dol_fiche_head($head);

	// Affichage de la liste des postes de travail, ainsi que d'une case "A ordonnancer" pour sélectionner les tâches n'ayant pas de poste de travail :
	$PDOdb = new TPDOdb();
	print '<div class="tabBarWithBottom">';
	$TRes = TWorkstation::getWorstations($PDOdb);
	if(!empty($TRes)) {
		print $langs->trans('All').'&nbsp;/&nbsp;'.$langs->trans('None').'&nbsp;';
		print $form->showCheckAddButtons().'<br>';
		print '<input checked="checked" type="CHECKBOX" class="checkforaction" id="to_ordo" />'.$langs->trans('ToOrdo').'&nbsp;&nbsp;&nbsp;';
		foreach ($TRes as $ws_id => $ws_name) {
			print '<input checked="checked" type="CHECKBOX" class="checkforaction" id="ws_'.$ws_id.'" />'.$ws_name.'&nbsp;&nbsp;&nbsp;';
		}
	}

	print '<input id="filter_by_ws" class="button" type="SUBMIT" value="Filtrer" />';

	// Script de gestion du rechargement de la liste des tâhces en fonction des postes de travail sélectionnés
	?>

	<script language="JavaScript" type="text/JavaScript">

		$("[name=checkallactions]").attr('checked', true); // Par défaut lors du premier affichage, on coche tout

		$("#filter_by_ws").click(function() {

			var $TParams = [];
			$('input[type=checkbox]').each(function () {
				if(this.checked) {
					$TParams.push("TWSFilter[]=" + $(this).attr('id'));
				}
			});

			var url = '<?php echo dol_buildpath('/fullcalendar/script/interface.php', 1) ?>'+'?get=tasks';
			var calendar = $('#calendar');

			calendar.fullCalendar('removeEventSources');
			calendar.fullCalendar('addEventSource', url + "&" + $TParams.join("&"));
			calendar.fullCalendar('refetchEvents');


		});

	</script>

	<?php

	print '</div><br><br>';

}
