![WebHost logo](img/workstationatm.png)
# DOLIBARR MODULE WORKSTATION ATM
![Last realease](https://img.shields.io/github/v/release/ATM-Consulting/dolibarr_module_workstation)

Manage workstations

## LICENSE
Copyright (C) 2019 ATM Consulting <contact@atm-consulting.fr>
Workstation ATM is released under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version (GPL-3+).

See the [COPYING](https://github.com/Dolibarr/dolibarr/blob/develop/COPYING) file for a full copy of the license.

## UPDATE

### BEWARE ! WHEN UPDATING WORKSTION ATM From 1.x to 2.x or obove

For compatibility with Dolibarr V14 this module is renamed from workstation to workstationatm  
So to update this module you need to follow these steps :

#### Case 1 : you make a Dolibarr update to V14 or above
- You must run case 2 before update your dolibarr

#### Case 2 : update only workstationatm module
- Before update : Disable your current workstaton
- Rename your module folder from `workstation` to `workstationatm`
- Replace your files with the last version
- Activate module

## INSTALL

### Dependencies

This module needs these modules to be installed on your dolibarr :
- abricot : a tools library

### From the ZIP file and GUI interfaces

- If you get the module in a zip file (like when downloading it from the market place [Dolistore](https://www.dolistore.com)), go into
  menu ```Home - Setup - Modules - Deploy external module``` and upload the zip file.


Note: If this screen tell you there is no custom directory, check your setup is correct:

- In your Dolibarr installation directory, edit the ```htdocs/conf/conf.php``` file and check that following lines are not commented:

    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment them if necessary (delete the leading ```//```) and assign a sensible value according to your Dolibarr installation

  For example :

 - UNIX:
     ```php
     $dolibarr_main_url_root_alt = '/custom';
     $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
     ```

 - Windows:
     ```php
     $dolibarr_main_url_root_alt = '/custom';
     $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
     ```

### From a GIT repository

- Clone the repository in ```$dolibarr_main_document_root_alt/workstationatm```

```sh
cd ....../custom
git clone git@github.com:gitlogin/workstation.git workstationatm
```

### <a name="final_steps"></a>Final steps

From your browser:

- Log into Dolibarr as a super-administrator
- Go to "Setup" -> "Modules"
- You should now be able to find and enable the module


