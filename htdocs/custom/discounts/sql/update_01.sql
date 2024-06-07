-- ===================================================================
-- Copyright (C) 2017      Ferran Marcet        <fmarcet@2byte.es>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ===================================================================
ALTER TABLE llx_discount CHANGE fk_category fk_target INT (11) NULL DEFAULT '0';
ALTER TABLE llx_discount ADD type_target SMALLINT NULL DEFAULT '0';
ALTER TABLE llx_discount ADD priority SMALLINT NOT NULL DEFAULT '99';
ALTER TABLE llx_discount ADD active SMALLINT NOT NULL DEFAULT 1;