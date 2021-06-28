# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]
- FIX : compatibility with Dolibarr v14 *28/06/2021* - 2.0.0:
     * [x] Rename module directory from workstation to workstationatm
     * [x] Rename module descriptor file and class from modWorkstation to modWorkstationatm
     * [x] Adapt module descriptor content (module name etc.)
        - module rights name
     * [x] Rename trigger / hooks classes:
        - `actions_workstation.class.php` → `actions_workstationatm.class.php`
        - `interface_99_modWorkstation_Workstationtrigger.class.php` → `interface_99_modWorkstationatm_Workstationatmtrigger.class.php`
     * [x] Change loadlangs domain (workstation@workstation → workstationatm@workstationatm)
           + load picto
     * [x] Rename tables in source code and in existing databases
        - `llx_workstation` → `llx_workstationatm`
        - `llx_workstation_schedule` → `llx_workstationatm_schedule`
        - `llx_workstation_product` → `llx_workstationatm_product`
            ```sql
            CREATE TABLE llx_workstationatm LIKE llx_workstation;
            INSERT INTO llx_workstationatm SELECT * FROM llx_workstation;
            DROP TABLE llx_workstation;
            
            CREATE TABLE llx_workstationatm_product LIKE llx_workstation_product;
            INSERT INTO llx_workstationatm_product SELECT * FROM llx_workstation_product;
            DROP TABLE llx_workstation_product;
            
            CREATE TABLE llx_workstationatm_schedule LIKE llx_workstation_schedule;
            INSERT INTO llx_workstationatm_schedule SELECT * FROM llx_workstation_schedule;
            DROP TABLE llx_workstation_schedule;
            ```
       (use regex: `MAIN_DB_PREFIX.{1,10}workstation` for detection in your custom module
       directory)
     * [ ] (phase 2) Migration script for existing data based on Workstation:
        - detect tables in which a column (module / type / element) has 'workstation' in it


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

