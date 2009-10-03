UPDATE `general_opt` SET `nagios_path` = '/usr/share/@@nagios_version@@',
`nagios_path_bin` = '/usr/sbin/@@nagios_version@@',
`nagios_init_script` = '/etc/init.d/@@nagios_version@@',
`nagios_path_img` = '/usr/share/nagios/htdocs/images',
`nagios_path_plugins` = '/usr/bin/nagios/plugins',
`snmpttconvertmib_path_bin` = '/usr/share/centreon2/bin/snmpttconvertmib',
`mailer_path_bin` = '/usr/bin/mail',
`rrdtool_path_bin` = '/usr/bin/rrdtool',
`oreon_path` = '/usr/share/centreon2/',
`oreon_web_path` = '/centreon2/',
`debug_path` = '/var/log/centreon2/' WHERE `general_opt`.`gopt_id` =1 LIMIT 1 ;

UPDATE `cfg_nagios` SET `log_file` = '/var/log/@@nagios_version@@/nagios.log',
`cfg_dir` = '/etc/centreon2/nagioscfg',
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
`broker_module` = '/usr/lib/ndoutils/ndomod.o config_file=/etc/centreon2/nagioscfg/ndomod.cfg' WHERE `cfg_nagios`.`nagios_id` =1 LIMIT 1 ;

UPDATE `cfg_cgi` SET `main_config_file` = '/etc/centreon2/nagioscfg/nagios.cfg',
`physical_html_path` = '/usr/lib/cgi-bin/@@nagios_version@@/',
`url_html_path` = '/@@nagios_version@@',
`nagios_check_command` = '/usr/lib/nagios/plugins/check_nagios /var/lib/@@nagios_version@@/status.dat 5 /usr/sbin/@@nagios_version@@' WHERE `cfg_cgi`.`cgi_id` =10 LIMIT 1 ;

UPDATE `cfg_ndo2db` SET `ndo2db_user` = 'nagios',
`ndo2db_group` = 'nagios' WHERE `cfg_ndo2db`.`id` =1 LIMIT 1 ;

UPDATE `cfg_resource` SET `resource_line` = '/usr/lib/nagios/plugins' WHERE `cfg_resource`.`resource_id` =1 LIMIT 1 ;


