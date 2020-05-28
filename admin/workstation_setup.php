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
 * 	\file		admin/workstation.php
 * 	\ingroup	workstation
 * 	\brief		This file is an example module setup page
 * 				Put some comments here
 */
// Dolibarr environment
$res = @include("../../main.inc.php"); // From htdocs directory
if (! $res) {
    $res = @include("../../../main.inc.php"); // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once '../lib/workstation.lib.php';
dol_include_once('abricot/includes/lib/admin.lib.php');

// Translations
$langs->load("workstation@workstation");

// Access control
if (! $user->admin) {
    accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');

/*
 * Actions
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_set_const($db, $code, GETPOST($code), 'chaine', 0, '', $conf->entity) > 0)
	{
		header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

if (preg_match('/del_(.*)/',$action,$reg))
{
	$code=$reg[1];
	if (dolibarr_del_const($db, $code, 0) > 0)
	{
		Header("Location: ".$_SERVER["PHP_SELF"]);
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}

/*
 * View
 */
$page_name = "WorkstationSetup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
    . $langs->trans("BackToModuleList") . '</a>';
print_fiche_titre($langs->trans($page_name), $linkback);

// Configuration header
$head = workstationAdminPrepareHead();
$notab = 1;
dol_fiche_head(
    $head,
    'settings',
    $langs->trans("Module104320Name"),
	$notab,
    "workstation@workstation"
);
// Setup page goes here
$form=new Form($db);
$var=false;
print '<table class="noborder" width="100%">';

if(function_exists('setup_print_title')){

	setup_print_title("Parameters");

	setup_print_on_off('WORKSTATION_LINK_SUBPRODUCT', $langs->trans('paramWORKSTATION_LINK_SUBPRODUCT'), '', $langs->trans('paramWORKSTATION_LINK_SUBPRODUCT_HELP'));

	$params = array('placeholder' => '2000-0600"', 'pattern' => "[0-2]{1}[0-9]{1}[0-5]{1}[0-9]{1}-[0-2]{1}[0-9]{1}[0-5]{1}[0-9]{1}");
	$help = $langs->trans('paramWORKSTATION_TRANCHE_HORAIRE_THM_NUIT_HELP');
	$help.= '<br/><br/>' . $langs->trans('paramWORKSTATION_TRANCHE_HORAIRE_THM_NUIT_HELP_other_module');



	setup_print_input_form_part('WORKSTATION_TRANCHE_HORAIRE_THM_NUIT', $langs->trans('paramWORKSTATION_TRANCHE_HORAIRE_THM_NUIT'), '', $params, 'input', $help);

	$help = $langs->trans('WORKSTATION_CAPACITY_OF_UNCONFIGURED_WS_IS_INFINITE_HELP');

	setup_print_on_off('WORKSTATION_CAPACITY_OF_UNCONFIGURED_WS_IS_INFINITE','','', $help);

}else{
	print '<div class="error" >'.$langs->trans('AbricotNeedUpdate').' : <a href="http://wiki.atm-consulting.fr/index.php/Accueil#Abricot" target="_blank"><i class="fa fa-info"></i> Wiki</a></div>';
}

print '</table>';
dol_fiche_end($notab);

llxFooter();

$db->close();
