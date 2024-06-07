-- ===================================================================
-- Copyright (C) 2014      Juanjo Menent        <jmenent@2byte.es>
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


create table llx_discount
(
  rowid          INTEGER                    AUTO_INCREMENT PRIMARY KEY,
  entity         INTEGER DEFAULT 1 NOT NULL, -- multi company id
  type_dto       SMALLINT          NOT NULL DEFAULT 1, -- 1=Comm, 4=BuyXPayY, 5=SecondUnit
  qtybuy         INTEGER                    DEFAULT 0,
  qtypay         INTEGER                    DEFAULT 0,
  type_source    SMALLINT          NOT NULL DEFAULT 1, -- 1=Third, 2=Product, 3=Category
  type_target    SMALLINT          NULL     DEFAULT 3, -- 1=Third, 2=Product, 3=Category
  fk_source      INT(11),
  fk_target      INT(11)                    DEFAULT 0,
  description    VARCHAR(255)               DEFAULT NULL,
  dto_rate       DOUBLE(6, 3)               DEFAULT 0,
  payment_cond   INTEGER,
  tms            TIMESTAMP,
  datec          DATETIME,
  date_start     DATETIME,
  date_end       DATETIME,
  fk_user_author INTEGER           NOT NULL,
  priority       SMALLINT          NOT NULL DEFAULT 99
)ENGINE=innodb;
