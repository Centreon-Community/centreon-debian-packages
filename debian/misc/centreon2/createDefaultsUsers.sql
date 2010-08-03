
-- Create default user for Centreon2
INSERT INTO `contact` (
`contact_id` ,
`timeperiod_tp_id` ,
`timeperiod_tp_id2` ,
`contact_name` ,
`contact_alias` ,
`contact_passwd` ,
`contact_lang` ,
`contact_host_notification_options` ,
`contact_service_notification_options` ,
`contact_email` ,
`contact_pager` ,
`contact_comment` ,
`contact_oreon` ,
`contact_admin` ,
`contact_type_msg` ,
`contact_activate` ,
`contact_auth_type` ,
`contact_ldap_dn` ,
`contact_acl_group_list` ,
`contact_autologin_key`
)
VALUES (
NULL , NULL , NULL , '@@admlogin@@', '@@admlogin@@', '@@admpass@@', 'en_US', NULL , NULL , '@@admmail@@', NULL , NULL , '1', '1', 'txt', '1', '', NULL , NULL , NULL
);


