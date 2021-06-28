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
 * \file    class/actions_workstationatm.class.php
 * \ingroup workstationatm
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ActionsWorkstationatm
 */
class ActionsWorkstationatm
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

		require_once __DIR__ . '/../lib/workstation.lib.php';

		printFilterWorkstationOnFullCalendarTaskScreen();

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

		if (in_array('fullcalendarinterface', explode(':', $parameters['context']))) {
			// Si on filtre par poste de travail, la requête sql fullcalendar est modifiée en conséquence
			$TWSFilter = GETPOST('TWSFilter', 'array');
			if (!empty($TWSFilter)) {
				$this->resprints = ' LEFT JOIN ' . MAIN_DB_PREFIX . 'projet_task_extrafields pte ON (pte.fk_object = t.rowid) ';
			}
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

		if (in_array('fullcalendarinterface', explode(':', $parameters['context']))) {
			// Si on filtre par poste de travail, la requête sql fullcalendar est modifiée en conséquence
			$TWSFilter = GETPOST('TWSFilter', 'array');
			if (!empty($TWSFilter)) {
				$TWS = $TSql = array();
				$to_ordo = false;
				foreach ($TWSFilter as $val) {
					if (strpos($val, 'ws_') !== false) $TWS[] = strtr($val, array('ws_' => ''));
					elseif (strpos($val, 'to_ordo') !== false) $to_ordo = true;
				}

				if (!empty($to_ordo)) $TSql[] = ' (pte.rowid IS NULL OR pte.fk_workstation = 0 OR pte.fk_workstation IS NULL) ';
				if (!empty($TWS)) $TSql[] = ' pte.fk_workstation IN(' . implode(', ', $TWS) . ') ';
				if (!empty($TSql)) $this->resprints = ' AND (' . (implode(' OR ', $TSql)) . ')';
			}
		}

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
		    dol_include_once('/workstationatm/class/workstation.class.php');
		    $PDOdb = new TPDOdb;
		    $workstation = new TWorkstation;
		    $res = $workstation->load($PDOdb, $parameters['task']->array_options['options_fk_workstation']);
		    if($res > 0) $object['description'] .= '<strong>'.$langs->trans('Workstation').' : </strong>'.$workstation->getNomUrl(1).'<br/>';
		}
        return 0;
	}

	function addMoreTaskEditableView($parameters, &$object, &$action, $hookmanager)
	{
        global $langs, $db;
        if(in_array('fullcalendarinterface', explode(':', $parameters['context']))) {
            $extra = new ExtraFields($db);
            $extra->fetch_name_optionals_label('projet_task');
            $object .= '<tr><td>'.$langs->trans("Workstation").'</td><td>'.$extra->showInputField('fk_workstation', $parameters['task']->array_options['options_fk_workstation']).'</td></tr>';
        }

	    return 0;
	}
}
