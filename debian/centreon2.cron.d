#
# Regular cron jobs for the centreon2 package
#

# Cron for CentACL
*/2 * * * * nagios php /usr/share/centreon2/cron/centAcl.php  >> /var/log/centreon2/centAcl.log 2>&1
