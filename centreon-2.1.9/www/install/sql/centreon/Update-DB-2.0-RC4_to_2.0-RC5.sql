UPDATE `informations` SET `value` = '2.0-RC5' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.0-RC4' LIMIT 1;
UPDATE `topology_JS` SET `Init` = 'initChangeTab' WHERE `topology_JS`.`Init` = 'initChangeTab()';