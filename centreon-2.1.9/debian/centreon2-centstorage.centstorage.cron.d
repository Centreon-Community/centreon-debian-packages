#
# Regular cron jobs for the centreon2-centstorage package
#

# Cron for log analyser - for reporting
0 1 1-31 * * nagios perl /usr/sbin/archiveDayLog >> /var/log/centreon2/centstorage/archiveDayLog.log 2>&1

# Cron for log parsor
* * * * * nagios /usr/sbin/logAnalyser >> /var/log/centreon2/centstorage/logAnalyser.log 2>&1

# Cron for tracing Nagios Poller Performances
*/5 * * * * nagios /usr/sbin/nagiosPerfTrace >> /var/log/centreon2/centstorage/nagiosPerfTrace.log 2>&1

