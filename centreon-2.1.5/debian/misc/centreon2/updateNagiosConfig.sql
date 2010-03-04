UPDATE `options` SET `value`='/usr/share/@@nagios_version@@' WHERE options.key='nagios_path';
UPDATE `options` SET `value`='/usr/sbin/@@nagios_version@@' WHERE options.key='nagios_path_bin';
UPDATE `options` SET `value`='/etc/init.d/@@nagios_version@@' WHERE options.key='nagios_init_script';
UPDATE `options` SET `value`='/usr/share/nagios/htdocs/images' WHERE options.key='nagios_path_img';
UPDATE `options` SET `value`='/usr/bin/nagios/plugins' WHERE options.key='nagios_path_plugins';
UPDATE `options` SET `value`='/usr/share/centreon2/bin/snmpttconvertmib' WHERE options.key='snmpttconvertmib_path_bin';
UPDATE `options` SET `value`='/usr/bin/mail' WHERE options.key='mailer_path_bin';
UPDATE `options` SET `value`='/usr/bin/rrdtool' WHERE options.key='rrdtool_path_bin';
UPDATE `options` SET `value`='/usr/share/centreon2/' WHERE options.key='oreon_path';
UPDATE `options` SET `value`='/centreon2/' WHERE options.key='oreon_web_path';
UPDATE `options` SET `value`='/var/log/centreon2/' WHERE options.key='debug_path';


UPDATE `cfg_nagios` SET `log_file` = '/var/log/@@nagios_version@@/nagios.log',
`cfg_dir` = '/etc/nagios3/',
`temp_file` = '/var/cache/@@nagios_version@@/nagios.tmp',
`status_file` = '/var/cache/@@nagios_version@@/status.dat',
`p1_file` = '/usr/lib/@@nagios_version@@/p1.pl',
`nagios_user` = 'nagios',
`nagios_group` = 'nagios',
`log_archive_path` = '/var/log/@@nagios_version@@/archives/',
`command_file` = '/var/lib/@@nagios_version@@/rw/nagios.cmd',
`downtime_file` = '/var/lib/@@nagios_version@@/downtime.dat',
`comment_file` = '/var/lib/@@nagios_version@@/comments.dat',
`lock_file` = '/var/log/@@nagios_version@@/nagios.lock',
`state_retention_file` = '/var/lib/@@nagios_version@@/retention.dat',
`service_perfdata_file` = '/tmp/service-perfdata',
`broker_module` = '/usr/lib/ndoutils/ndomod.o config_file=/etc/nagios3/ndomod.cfg' WHERE `cfg_nagios`.`nagios_id` =1 LIMIT 1 ;

UPDATE `cfg_cgi` SET `main_config_file` = '/etc/nagios3/nagios.cfg',
`physical_html_path` = '/usr/lib/cgi-bin/@@nagios_version@@/',
`url_html_path` = '/@@nagios_version@@',
`nagios_check_command` = '/usr/lib/nagios/plugins/check_nagios /var/lib/@@nagios_version@@/status.dat 5 /usr/sbin/@@nagios_version@@' WHERE `cfg_cgi`.`cgi_id` =10 LIMIT 1 ;

UPDATE `cfg_ndo2db` SET `ndo2db_user` = 'nagios',
`ndo2db_group` = 'nagios' WHERE `cfg_ndo2db`.`id` =1 LIMIT 1 ;

UPDATE `cfg_resource` SET `resource_line` = '/usr/lib/nagios/plugins' WHERE `cfg_resource`.`resource_id` =1 LIMIT 1 ;


