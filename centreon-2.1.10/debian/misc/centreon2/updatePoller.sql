-- Update config for first poller
UPDATE `nagios_server` SET `init_script` = '/etc/init.d/@@nagios_version@@',
`nagios_bin` = '/usr/sbin/@@nagios_version@@',
`nagiostats_bin` = '/usr/sbin/@@nagios_version@@stats' WHERE `nagios_server`.`id` =1 LIMIT 1 ;

