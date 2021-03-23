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
 * \file    class/actions_workstation.class.php
 * \ingroup workstation
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsWorkstation
 */
class ActionsWorkstation
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the formObjectOptions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function formObjectOptions($parameters, &$object, &$action, $hookmanager) {

		global $form, $langs;

		require_once __DIR__ . '/workstation.class.php';

		$h = 0;
		$head = array();
		$head[$h][0] = $_SERVER["PHP_SELF"];
		$head[$h][1] = $langs->trans("ToFilter").'&nbsp;'.strtolower($langs->trans('By')).'&nbsp;'.$langs->trans('Workstations');
		$head[$h][2] = 'ProjetcTasks';

		dol_fiche_head($head, 'ProjetcTasks');

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


		?>

		<script language="JavaScript" type="text/JavaScript">

			$("[name=checkallactions]").attr('checked', true);

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
				$('#calendar').fullCalendar('refetchEvents');


			});

		</script>

		<?php

		print '</div><br><br>';

	}

	/**
	 * Overloading the printFieldListJoin function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function printFieldListJoin($parameters, &$object, &$action, $hookmanager) {

		$TWSFilter = GETPOST('TWSFilter');
		if(!empty($TWSFilter)) {
			$this->resprints = ' LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields pte ON (pte.fk_object = t.rowid) ';
		}

	}

	/**
	 * Overloading the printFieldListWhere function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function printFieldListWhere($parameters, &$object, &$action, $hookmanager) {

		$TWSFilter = GETPOST('TWSFilter');
		if(!empty($TWSFilter)) {
			$TWS=$TSql=array();
			$to_ordo=false;
			foreach ($TWSFilter as $val) {
				if(strpos($val, 'ws_') !== false) $TWS[] = strtr($val, array('ws_'=>''));
				elseif(strpos($val, 'to_ordo') !== false) $to_ordo=true;
			}

			if(!empty($to_ordo)) $TSql[] = ' (pte.rowid IS NULL OR pte.fk_workstation = 0 OR pte.fk_workstation IS NULL) ';
			if(!empty($TWS)) $TSql[] = ' pte.fk_workstation IN('.implode(', ', $TWS).') ';
		}

		if(!empty($TSql)) $this->resprints = ' AND ('.(implode(' OR ', $TSql)).')';

	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function setFullcalendarOrdoTask($parameters, &$object, &$action, $hookmanager)
	{

		if (in_array('fullcalendarinterface', explode(':', $parameters['context']))
            && !empty($parameters['task']->array_options['options_fk_workstation']))
		{
		    global $langs;
		    dol_include_once('/workstation/class/workstation.class.php');
		    $PDOdb = new TPDOdb;
		    $workstation = new TWorkstation;
		    $res = $workstation->load($PDOdb, $parameters['task']->array_options['options_fk_workstation']);
		    if($res > 0) $object['description'] .= '<strong>'.$langs->trans('Workstation').' : </strong>'.$workstation->getNomUrl(1).'<br/>';
		}
        return 0;
	}
}
