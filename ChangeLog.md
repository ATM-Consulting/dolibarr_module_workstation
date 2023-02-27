# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]

- FIX : Prevention of sql error in case extrafield from gantt module is not created

## Version 2.0

- FIX : PHP 8 *19/08/2022* - 2.0.2
- FIX : Compatibilité V16 *13/06/2022* - 2.0.1
- NEW : compatibility with Dolibarr v14 *28/06/2021* - 2.0.0:
     * [x] Rename module directory from workstation to workstationatm
     * [x] Rename module descriptor file and class from modWorkstation to modWorkstationatm
     * [x] Adapt module descriptor content (module name etc.)
        - module rights name
     * [x] Rename trigger / hooks classes:
        - `actions_workstation.class.php` → `actions_workstationatm.class.php`
        - `interface_99_modWorkstation_Workstationtrigger.class.php` → `interface_99_modWorkstationatm_Workstationatmtrigger.class.php`
     * [x] Change loadlangs domain (workstation@workstation → workstationatm@workstationatm)
     * [x] Rename tables: not needed because the core module tables are prefixed with the module name
     * [ ] (phase 2) Migration script for existing data based on Workstation:
        - detect tables in which a column (module / type / element) has 'workstation' in it

    * A faire dans les modules custom :  
        - rechercher le dossier  :  (workstation)(?!atm).{0,5}/
        - rechercher les droits  :  ->(workstation)(?!atm)->  
        - rechercher activation module  :  (conf|rights)->(workstation)(?!atm)->  
        - rechercher langs  :  workstation@workstation  
        - recherhcer dépendance : modWorkstation  
        - en une ligne de recherche php storm type regex :
          ```((workstation)(?!atm).{0,5}/)|(->(workstation)(?!atm)->)|((conf|rights)->(workstation)(?!atm)->)|(workstation@workstation)|(modWorkstation)```

- FIX : Add missing translations *19/04/2021* - 1.4.1

## Version 1.4

- NEW : Workstations filter on fullcalendar tasks view (T2699) *24/03/2021* - 1.4
- NEW : Add workstation edition in fullcalendar hook *23/03/2021* - 1.4.0

## Version 1.3

- Add getNomUrl() workstation linked to task on fullCalendar tasks view

## Version 1.2

- Add hookmanager context and doActions hook [10/02/2021]

## Version 1.1.2 [ 16/12/2020 ]

### Fix 

- Remove unused Box

