--Update ndo2cfg config
UPDATE `@@db_centreon2@@`.`cfg_ndo2db` SET `db_host` = '@@db_server@@',
`db_name` = '@@db_name@@',
`db_user` = '@@db_user@@',
`db_pass` = '@@db_pass@@' WHERE `cfg_ndo2db`.`id` =1 LIMIT 1 ;

