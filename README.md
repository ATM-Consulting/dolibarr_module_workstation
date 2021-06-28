/* 
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program and files/directory inner it is free software: you can 
 * redistribute it and/or modify it under the terms of the 
 * GNU General Public License as published by
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
