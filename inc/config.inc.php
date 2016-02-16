<?php

if(ereg("config.inc.php", $_SERVER['PHP_SELF']))
{
   header ("Location: /");
   exit;
}

// Database Config
$CONF['database_host'] = 'localhost';
$CONF['database_user'] = 'vadmin';
$CONF['database_password'] = 'vadmin_password';
$CONF['database_name'] = 'vmanager';
$CONF['database_port'] = '3306';
$CONF['database_prefix'] = '';

// Tables names:
$CONF['database_tables'] = array (
    'admin' => 'admin',  
    'domain' => 'domain', 
    'mailbox' => 'mailbox',
    'groups' => 'groups',
    'forwarders' => 'forwarders',
    'parking_domains' => 'parking_domains',
    'alias_domain' => 'alias_domain',
    'transport' => 'transport',
    'vacation' => 'vacation',
    'vacation_notification' => 'vacation_notification'
);

/* Password Encryption:
 md5crypt = internal postfix admin md5
 md5 = md5 sum of the password
 system = whatever you have set as your PHP system default
 cleartext = clear text passwords (bad!)
*/
$CONF['encrypt'] = 'md5crypt';

// Page Size Limit: Set the number of entries that you would like to see in one page.
$CONF['page_size'] = '20';

// Default Mailboxes will be create automatically on new domain creation: 
$CONF['default_mailboxes'] = array (
    'abuse',
    'hostmaster',
    'postmaster',
    'webmaster'
);
// Set your password for default mailboxes
$CONF['default_mailboxes_passwd'] = 'pass123';

// Quota You can either use '1024000' or '1048576'
$CONF['quota_multiplier'] = '1024000';

// Perfome DNS check: When creating mailboxes or aliases, check that the domain-part of the address is legal by performing a name server look-up.
$CONF['emailcheck_resolve_domain'] = 'YES';

// Send Welcome Message on every newly created mailbox (Set 'YES' OR 'NO')
$CONF['welcome_message'] = 'YES';
$CONF['subject'] = 'Welcome';
$CONF['welcome_text'] = <<<EOM
Hi,

Welcome to your new account.
EOM;

// Send Mail: If you don't want sendmail tab set this to 'NO';
$CONF['sendEmail'] = 'YES';
$CONF['sendEmail_From'] = 'admin@yourdomain.com';

// Virtual Vacation : set YES if you want vacation for your users.
$CONF['vacation'] = 'YES';
$CONF['vacation_subject'] = 'Out of Office';
$CONF['vacation_message'] = <<<EOM
Hi,

I'm on vacation from <date> until <date>.
I'll get back to you as soon as I can after that.
EOM;

?>
