<?php

if(ereg("functions.php", $_SERVER['PHP_SELF'])){
   header ("Location: /");
   exit;
}

/* Databae Connection */
$link = mysqli_connect($CONF['database_host'], $CONF['database_user'], $CONF['database_password'], $CONF['database_name']);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$dbhost = $CONF['database_host'];
$dbport = $CONF['database_port'];
$dbname = $CONF['database_name'];
$dbuser = $CONF['database_user'];
$dbpassword = $CONF['database_password'];

function myErrorHandlder($error) {
  $msg = "<div style='color: red; font-weight: bold;'>".date("Y-m-d h:i:s")."</div>";
  $msg .= $error."<br>";
  $data=fopen('logs/db.html','a');
  fwrite($data,$msg);
  fclose($data);
}

$db = new db("mysql:host=$dbhost;port=$dbport;dbname=$dbname", $dbuser, $dbpassword);
$db->setErrorCallbackFunction("myErrorHandler","html");

function encoded($ses)
{
 $sesencoded = $ses;
 $num = mt_rand(0,4);
 for($i=0;$i<$num;$i++)
 {
    $sesencoded = base64_encode($sesencoded);
 }
 $alpha_array = array('T','E','A','R','S');
 $sesencoded = $sesencoded . "+" . $alpha_array[$num];
 $sesencoded = base64_encode($sesencoded);
 return $sesencoded;
}

function decoded($str)
{
  $alpha_array = array('T','E','A','R','S');
  $decoded =  base64_decode($str);
  list($decoded,$letter) =
  split("\+",$decoded);
  for($i=0;$i<count($alpha_array);$i++)
  {
  if($alpha_array[$i] == $letter)
  break;
  }
  for($j=1;$j<=$i;$j++)
  {
     $decoded = base64_decode($decoded);
  }
  return $decoded;
}

function maildir ($email)
{
  global $CONF;

  $emailId = explode('@',$email);
  $address = $emailId[0];
  $domain = $emailId[1];
  $maildir = $address . "/Maildir/";
  return $maildir;
}

function table_by_key ($table_key)
{       
  global $CONF;
  $table = $CONF['database_prefix'].$CONF['database_tables'][$table_key];
  if(empty($table)) $table = $table_key;
    return $table;
}

function alist_domains()
{
  global $TBL_domain;
  global $db;
  $result = $db->select($TBL_domain, 'active=1', '', 'ORDER BY modified desc','domain,aliases,mailboxes,maxquota');
  return $result;
}

function domain_property($domain)
{
  global $db;
  $result = $db->list_domains("SELECT count(groups.domain) tgroups, (SELECT count(mailbox.domain) from mailbox where mailbox.domain = domain.domain) as users, domain.domain,mailboxes,groups,maxquota,domain.ds_groups FROM domain left join groups ON (domain.domain = groups.domain) WHERE domain.domain ='".$domain."' group by domain.domain");
  return $result;
}

function user_property($username)
{
  global $db;
  $result = $db->list_domains("SELECT name,quota,admin,alias,domain,autoresponse,active FROM mailbox WHERE username ='".$username."'");
  return $result;
}

function list_admins($start,$end)
{
  global $TBL_domain;
  global $db;
  $result = $db->select($TBL_domain,'','',"LIMIT $startRow, $maxRows");
  return $result;
}

function user_exists($email)
{
  global $TBL_mailbox;
  global $db;

  $bind = array (
    ":username" => $email,
  );

  $result = $db->get_count($TBL_mailbox,'*',"username = :username", $bind);
  $count = $result[0]['count(*)'];

  if($count == 1)
  {
    return false;
  }
  return true;
}

function group_exists($group,$domain)
{
  global $TBL_groups;
  global $db;

  $group = preg_match('/^catch-all@/i', $group) ? '@'.$domain : $group;

  $bind = array (
    ":address" => $group,
  );

  $result = $db->get_count($TBL_groups,'*',"address = :address", $bind);
  $count = $result[0]['count(*)'];

  if($count == 1)
  {
    return false;
  }
  return true;
}

function check_domain ($domain)
{
    global $CONF;

    if (!preg_match ('/([-0-9A-Z]+\.)+' . '([0-9A-Z]){2,6}$/i', trim($domain)))
    {
        return false;
    }
    if (isset($CONF['emailcheck_resolve_domain']) && 'YES' == $CONF['emailcheck_resolve_domain'] && 'WINDOWS'!=(strtoupper(substr(php_uname('s'), 0, 7))))
    {
        // Look for an AAAA, A, or MX record for the domain
        if(function_exists('checkdnsrr')) {
            // AAAA (IPv6) is only available in PHP v. >= 5
            if (version_compare(phpversion(), "5.0.0", ">="))
            {
                if (checkdnsrr($domain,'AAAA')) return true;
            }
            if (checkdnsrr($domain,'A')) return true;
            if (checkdnsrr($domain,'MX')) return true;
            return false;
        }
        else {
            echo "emailcheck_resolve_domain is enabled, but function (checkdnsrr) missing!";
        }
    }
    return true;
}

function validateEmail ($email)
{
    global $CONF;

    /*
    if ($CONF['vacation'] == 'YES')
    {
        $vacation_domain = $CONF['vacation_domain'];
        $email = preg_replace ("/@$vacation_domain/", '', $email);
        $email = preg_replace ("/#/", '@', $email);
    }
    */

    // Perform non-domain-part sanity checks
    if (!preg_match ('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+' . '@' . '[^@]+$/i', trim ($email)))
    {
        return false;
    }

    // Determine domain name
    $matches = array();
    if (!preg_match('|@(.+)$|', $email, $matches))
    {
        return false;
    }
    $domain = $matches[1];
    return check_domain($domain);
}

function check_alias_domain ($email)
{
    global $CONF;

    if(!preg_match('/^@+[a-z0-9\.-]/', $email))
    {
      if (!preg_match ('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+' . '@' . '[^@]+$/i', trim ($email)))
      {
        return false;
      }
    }

    // Determine domain name
    $matches = array();
    if (!preg_match('|@(.+)$|', $email, $matches))
    {
        return false;
    }
    $domain = $matches[1];
    return check_domain($domain);
}

function EmailLength ($email)
{
  $emailid = explode("@",$email);
  if(strlen($emailid[0]) < 2 || strlen($emailid[0]) > 32)
  {
    return false;
  }
  return true;
}

function EmailChars ($emailid,$dom='')
{
  if($dom != '')
  {
    $email = explode('@',$emailid);
    $emailaddress = $email[0];
  }
  else
    $emailaddress = $emailid;

  if (preg_match('/[\\-\\+\\.]+$/', $emailaddress) || !preg_match('/(^[a-z0-9\\.\\-\\+]+$)/', $emailaddress) || !preg_match('/(^[a-z])/', $emailaddress) || preg_match('/^[0-9]/', $emailaddress))
  {
    return false;
  }
  return true;
}

function escape_string ($string)
{
    global $CONF;
    global $link;

    if(is_array($string)) {
        $clean = array();
        foreach(array_keys($string) as $row) {
            $clean[$row] = escape_string($string[$row]);
        }
        return $clean;
    }

    if (get_magic_quotes_gpc ())
    {
        $string = stripslashes($string);
    }
    if (!is_numeric($string))
    {
       $escaped_string = mysqli_real_escape_string($link, $string);
    }
    else
    {
        $escaped_string = $string;
    }
    return $escaped_string;
}

// multiply_quota : Action: Recalculates the quota from bytes to MBs (multiply, *) Call: multiply_quota (string $quota)
function multiply_quota($quota)
{
  global $CONF;
  if ($quota == -1 || $quota == 0) return $quota;
  $value = $quota * $CONF['quota_multiplier'];
  return $value;
}

// divide_quota Action: Recalculates the quota from MBs to bytes (divide, /) Call: divide_quota (string $quota)
function divide_quota ($quota)
{
  global $CONF;
  if ($quota == -1 || $quota == 0) return $quota;
  $value = round($quota / $CONF['quota_multiplier'],2);
  return $value;
}

function encpasswd ($pw, $pw_db="")
{
  global $CONF;
  global $db;

  $pw = stripslashes($pw);
  $password = "";
  $salt = "";

  if($CONF['encrypt'] == 'md5crypt') 
  {
    $split_salt = preg_split ('/\$/', $pw_db);
    if(isset ($split_salt[2])) 
      $salt = $split_salt[2];
    $password = md5crypt ($pw, $salt);
  }

  elseif($CONF['encrypt'] == 'md5') 
    $password = md5($pw);

  elseif ($CONF['encrypt'] == 'system') 
  {
    if(preg_match("/\\$1\\$/", $pw_db))
    {
      $split_salt = preg_split ('/\$/', $pw_db);
      $salt = "\$1\$${split_salt[2]}\$";
    }
    else 
    {
      if(strlen($pw_db) == 0) 
        $salt = substr (md5 (mt_rand ()), 0, 2);
      else 
        $salt = substr ($pw_db, 0, 2);
    }
      $password = crypt ($pw, $salt);
    }

    elseif($CONF['encrypt'] == 'cleartext') 
      $password = $pw;

    $password = escape_string($password);
    return $password;
}

// md5crypt: 
function md5crypt ($pw, $salt="", $magic="")
{
    $MAGIC = "$1$";

    if ($magic == "") $magic = $MAGIC;
    if ($salt == "") $salt = create_salt ();
    $slist = explode ("$", $salt);
    if ($slist[0] == "1") $salt = $slist[1];

    $salt = substr ($salt, 0, 8);
    $ctx = $pw . $magic . $salt;
    $final = rhex2bin (md5 ($pw . $salt . $pw));

    for ($i=strlen ($pw); $i>0; $i-=16)
    {
        if ($i > 16)
        {
            $ctx .= substr ($final,0,16);
        }
        else
        {
            $ctx .= substr ($final,0,$i);
        }
    }
    $i = strlen ($pw);

    while ($i > 0)
    {
        if ($i & 1) $ctx .= chr (0);
        else $ctx .= $pw[0];
        $i = $i >> 1;
    }
    $final = rhex2bin (md5 ($ctx));

    for ($i=0;$i<1000;$i++)
    {
        $ctx1 = "";
        if ($i & 1)
        {
            $ctx1 .= $pw;
        }
        else
        {
            $ctx1 .= substr ($final,0,16);
        }
        if ($i % 3) $ctx1 .= $salt;
        if ($i % 7) $ctx1 .= $pw;
        if ($i & 1)
        {
            $ctx1 .= substr ($final,0,16);
        }
        else
        {
            $ctx1 .= $pw;
        }
        $final = rhex2bin (md5 ($ctx1));
    }
    $passwd = "";
    $passwd .= rto64 (((ord ($final[0]) << 16) | (ord ($final[6]) << 8) | (ord ($final[12]))), 4);
    $passwd .= rto64 (((ord ($final[1]) << 16) | (ord ($final[7]) << 8) | (ord ($final[13]))), 4);
    $passwd .= rto64 (((ord ($final[2]) << 16) | (ord ($final[8]) << 8) | (ord ($final[14]))), 4);
    $passwd .= rto64 (((ord ($final[3]) << 16) | (ord ($final[9]) << 8) | (ord ($final[15]))), 4);
    $passwd .= rto64 (((ord ($final[4]) << 16) | (ord ($final[10]) << 8) | (ord ($final[5]))), 4);
    $passwd .= rto64 (ord ($final[11]), 2);
    return "$magic$salt\$$passwd";
}

function create_salt ()
{
  srand ((double) microtime ()*1000000);
  $salt = substr (md5 (rand (0,9999999)), 0, 8);
  return $salt;
}

function rhex2bin ($str)
{
  $len = strlen ($str);
  $nstr = "";
  for ($i=0;$i<$len;$i+=2)
  {
    $num = sscanf (substr ($str,$i,2), "%x");
    $nstr.=chr ($num[0]);
  }
  return $nstr;
}

function rto64 ($v, $n)
{
  $ITOA64 = "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
  $ret = "";
  
  while (($n - 1) >= 0)
  {
    $n--;
    $ret .= $ITOA64[$v & 0x3f];
    $v = $v >> 6;
  }
  return $ret;
}

function check_owner($username,$domain)
{
  global $db;
  global $TBL_mailbox;
  $bind = array (
    ":username" => $username,
    ":domain" => $domain
  );
  $result = $db->select($TBL_mailbox, "username = :username AND domain = :domain", $bind, '', "domain");
  return $result;
}

function get_admin($domain)
{
  global $CONF;
  global $db;
  global $TBL_mailbox;

  $bind = array (
    ":domain" => $domain,
    ":admin" => 'DomainAdmin'
  );

  $admin = $db->select($TBL_mailbox, "domain = :domain AND admin= :admin", $bind, '', 'username,password');
  return $admin;
}

$TBL_admin = table_by_key ('admin');
$TBL_domain = table_by_key ('domain');
$TBL_mailbox = table_by_key ('mailbox');
$TBL_groups = table_by_key ('groups');
$TBL_forwarders = table_by_key ('forwarders');
$TBL_backupmx = table_by_key ('parking_domains');
$TBL_alias_domain = table_by_key ('alias_domain');
$TBL_transport = table_by_key ('transport');
$TBL_vacation = table_by_key ('vacation');
$TBL_vacation_notification = table_by_key('vacation_notification');

function paginate_two($reload, $page, $tpages, $adjacents, $style) 
{

  $firstlabel = "&laquo;&nbsp;";
  $prevlabel  = "Previous";
  $nextlabel  = "Next";
  $lastlabel  = "&nbsp;Last";

  $out = "<div class=\"pagination pagination-centered\"><ul>";

  // previous
  if($page >2)
  {
    $out.= "<li><a href=\"" . $reload . $style . ($page-1) . "\">" . $prevlabel . "</a></li>";
  }
  elseif($page == 2)
  {
    $out.= "<li><a href=\"" . $reload . "\">" . $prevlabel . "</a></li>";
  }

  // 1 2 3 4 etc
  $pmin = ($page>$adjacents) ? ($page-$adjacents) : 1;
  $pmax = ($page<($tpages-$adjacents)) ? ($page+$adjacents) : $tpages;
  for($i=$pmin; $i<=$pmax; $i++) 
  {
    if($i==$page) 
    {
      $out.= "<li class=\"active\"><a href=''>" . $i . "</a></li>";
    }
    elseif($i==1)
    {
      $out.= "<li><a href=\"" . $reload . "\">" . $i . "</a></li>";
    }
    else 
    {
      $out.= "<li><a href=\"" . $reload . $style . $i . "\">" . $i . "</a></li>";
    }
  }

  // next
  if($page<$tpages) 
  {
    $out.= "<li><a href=\"" . $reload . $style . ($page+1) . "\">" . $nextlabel . "</a></li>";
  }

  $out.= "</ul></div>";
  return $out;
}

function strstr_after($haystack, $needle, $case_insensitive = false) {
    $strpos = ($case_insensitive) ? 'stripos' : 'strpos';
    $pos = $strpos($haystack, $needle);
    if (is_int($pos)) {
        return substr($haystack, $pos + strlen($needle));
    }
    // Most likely false or null
    return $pos;
}

function sendMail($from, $to, $subject, $msg)
{
  $to = $to;
  $from = '<'.$from.'>';
  $headers  = "From: Admin $from\r\n";
  $headers .= "Reply-To: Admin $from\r\n";
  $headers .= "Content-type: text/html\r\n";
  mail($to, $subject, $msg, $headers);
}

function addDomain($domain,$description,$mailboxes,$groups,$maxquota,$domain_admin,$admin_pass,$defaultmbox)
{

  global $CONF;
  global $db;
  global $TBL_domain;
  global $TBL_mailbox;

  $newdomain = array(
   "domain" => $domain,
   "description" => $description,
   "mailboxes" => $mailboxes,
   "groups" => $groups,
   "maxquota" => $maxquota,
   "transport" => 'virtual',
   "created" => date('Y-m-d H:i:s'),
   "modified" => date('Y-m-d H:i:s'),
   "active" => 1
  );

  $admin = array(
   "username" => $domain_admin,
   "password" => encpasswd($admin_pass),
   "name" => 'Domain Admin',
   "domain" => $domain,
   "maildir" => maildir($domain_admin),
   "quota" => multiply_quota($maxquota),
   "created" => date('Y-m-d H:i:s'),
   "modified" => date('Y-m-d H:i:s'),
   "admin" => 'DomainAdmin',
   "active" => 1
  );

  $db->insert($TBL_domain, $newdomain);
  $db->insert($TBL_mailbox, $admin);
  sendMail($_SESSION['userName'],$domain_admin, $CONF['subject'], $CONF['welcome_text']);

  if($defaultmbox != 'N')
  {
    foreach ($CONF['default_mailboxes'] as $address)
    {
      $userAddr = $address . "@" . $domain;

      $default = array(
        "username" => $userAddr,
        "password" => encpasswd($CONF['default_mailboxes_passwd']),
        "name" => 'Default Postmaster Account',
        "domain" => $domain,
        "maildir" => maildir($userAddr),
        "quota" => multiply_quota($maxquota),
        "created" => date('Y-m-d H:i:s'),
        "modified" => date('Y-m-d H:i:s'),
        "admin" => 'User',
        "active" => 1
      );
      $db->insert($TBL_mailbox, $default);
      sendMail($_SESSION['userName'],$userAddr, $CONF['subject'], $CONF['welcome_text']);
    }
  }
}

function addgoto($user,$gofor,$actiond,$isa,$domain,$localcopy){

  global $CONF;
  global $db;
  global $TBL_forwarders;
  global $TBL_mailbox;

if($actiond != 'remove')
{
    $goto = strtolower($gofor);
    $goto = preg_replace ('/\\\r\\\n/', ',', $goto);
    $goto = preg_replace ('/\r\n/', ',', $goto);
    $goto = preg_replace ('/\n/', ',', $goto);
    $goto = preg_replace ('/[\s]+/i', '', $goto);
    $goto = preg_replace ('/,*$|^,*/', '', $goto);
    $goto = preg_replace ('/,,*/', ',', $goto);
    if(isset($localcopy))
      $goto .= ',' . $user;

    $goto_aliases = explode(',', $goto);
    $goto_aliases = array_unique($goto_aliases);

    foreach ($goto_aliases as $goto_address)
    {
      if(!validateEmail ($goto_address))
      {
	return "Wrong Email ". $goto_address;
      }
    }
    $goto = join(",", $goto_aliases);
}

if($actiond == 'remove')
{
  if($isa == 'Y')
    $db->delete($TBL_forwarders, "address='".$user."' AND active=0");
  else
    $db->delete($TBL_forwarders, "address='".$user."'");
  $mbox = array("alias" => 'N');
  $db->update($TBL_mailbox, $mbox, "username='".$user."'");
}
else
{
  $bind = array (
    ":username" => $user
  );

  $active = ($isa == 'N') ? 1 : 0;

  $alias = array(
   "address" => $user,
   "goto" => $goto,
   "domain" => $domain,
   "created" => date('Y-m-d H:i:s'),
   "modified" => date('Y-m-d H:i:s'),
   "active" => $active
  );

  if($actiond == 'save')
  {
    $db->insert($TBL_forwarders, $alias);
    $mbox = array("alias" => 'Y');
    $db->update($TBL_mailbox, $mbox, "username = :username", $bind);
  }
  else
  {
    unset($alias['address']);
    unset($alias['domain']);
    unset($alias['active']);
    unset($alias['created']);

    if($isa == 'Y')
      $db->update($TBL_forwarders, $alias, "address = :username AND active=0", $bind);
    else
      $db->update($TBL_forwarders, $alias, "address = :username", $bind);
  }
}
  return 'true';
}
?>
