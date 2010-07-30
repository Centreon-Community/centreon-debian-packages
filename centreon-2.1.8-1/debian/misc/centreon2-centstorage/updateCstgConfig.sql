-- Config centstorage db with correct nagios version
UPDATE `config` SET `RRDdatabase_path` = '/var/lib/centreon2-centstorage/metrics/',
`RRDdatabase_status_path` = '/var/lib/centreon2-centstorage/status/',
`RRDdatabase_nagios_stats_path` = '/var/lib/centreon2-centstorage/nagios-perf/',
`drop_file` = '/tmp/service-perfdata.tmp',
`perfdata_file` = '/tmp/service-perfdata',
`nagios_log_file` = '/var/log/@@nagios_version@@/nagios.log' WHERE `config`.`id` =1 LIMIT 1 ;
