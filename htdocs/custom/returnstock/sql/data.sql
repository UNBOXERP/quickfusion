-- Copyright (C) 2019 SuperAdmin
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see <http://www.gnu.org/licenses/>.
INSERT INTO llx_extrafields( name, label, type, pos, size, entity, elementtype, fieldunique, fieldrequired, param, alwayseditable, perms, langs, list, printable, fielddefault, fieldcomputed, fk_user_author, fk_user_modif, datec, enabled, help, totalizable ) VALUES('return_cash', 'Return_cash', 'boolean', 100, '', 1, 'facture', 0, 0, 'a:1:{s:7:\"options\";a:1:{s:0:\"\";N;}}', 0, null, 'returnstock@returnstock', '1', '1', null, null, 1, 1,'2021-10-06 16:21:01', '1', 'Return Stock', FALSE);
INSERT INTO llx_extrafields( name, label, type, pos, size, entity, elementtype, fieldunique, fieldrequired, param, alwayseditable, perms, langs, list, printable, fielddefault, fieldcomputed, fk_user_author, fk_user_modif, datec, enabled, help, totalizable ) VALUES('return_stock', 'Return_stock', 'boolean', 100, '', 1, 'facture_fourn', 0, 0, 'a:1:{s:7:\"options\";a:1:{s:0:\"\";N;}}', 0, null, 'returnstock@returnstock', '1', '1', null, null, 1, 1,'2021-10-06 16:21:01', '1', 'Return Stock', FALSE);
