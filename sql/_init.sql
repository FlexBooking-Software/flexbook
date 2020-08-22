drop database if exists flexbook;
create database flexbook;
use flexbook;

source structure.sql;
source init_data.sql.utf8;
source patch.sql
source countries.sql
source portaltemplate.sql