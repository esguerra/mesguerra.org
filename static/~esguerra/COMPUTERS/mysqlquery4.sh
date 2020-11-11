#!/bin/bash
# By Mauricio Esguerra Neira
# April 23, 2009
#
# Script to count Yurong's Base Pairing Patterns from BPS Database
# according to whether they are parallel or anti.
mysql -ppassword rna<<EOFMYSQL
select count(*) from Basepair where pat_id RLIKE "^gc" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^gc" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^au" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^au" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^ga" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^ga" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^gu" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^gu" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^ac" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^ac" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^aa" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^aa" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^gg" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^gg" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^cu" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^cu" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^cc" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^cc" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^uu" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^uu" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^at" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^at" and z_orie = "anti-parallel";

select count(*) from Basepair where pat_id RLIKE "^ct" and z_orie = "parallel";
select count(*) from Basepair where pat_id RLIKE "^ct" and z_orie = "anti-parallel";
EOFMYSQL
