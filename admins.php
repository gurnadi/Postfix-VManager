<?php

if(ereg("admins.php", $_SERVER['PHP_SELF']) || decoded($_SESSION['role']) == 'User'){
  header ("Location: /");
  exit;
}

/* create order query */
  $order = 'modified DESC';
  $ord = explode("?orderby=", $_SERVER['REQUEST_URI']);
  if(isset($ord[1]))
  {
    $orders = explode("/page=", $ord[1]);
    $orders = $orders[0];

    if($orders == 'ASC')
      $order = 'username ASC';
    elseif($orders == 'DESC')
      $order = 'username DESC';
    elseif($orders == 'modDESC')
      $order = 'modified DESC';
    else
      $order = 'modified ASC';
  }
  /* end order query */

  if(isset($_GET['srch']))
  {
    if(decoded($_SESSION['role']) != 'SuperAdmin' && decoded($_SESSION['role']) != 'DomainAdmin')
    {
      header ("Location: /");
      exit;
    }

    if(decoded($_SESSION['role']) == 'SuperAdmin')
    {
      $guser = explode('/page',$_GET['srch']);
      $guser = explode('?',$guser[0]);
      $user = "%$guser[0]%";

      $bind = array(
        ":username" => $user,
        ":admin" => 'DomainAdmin'
      );
      $get_users = $db->get_count($TBL_mailbox,'*',"username LIKE :username AND admin = :admin", $bind);
    }
    else
    {
      $guser = explode('/page',$_GET['srch']);
      $guser = explode('?',$guser[0]);
      $user = preg_match('/^@/',$guser[0]) ? "%$guser[0]%@%" : !preg_match('/@/',$guser[0]) ? "%$guser[0]%@%" : "%$guser[0]%";

      $bind = array(
        ":username" => $user,
        ":admin" => 'SubAdmin',
        ":domain" => decoded($_SESSION['domain'])
      );
      $get_users = $db->get_count($TBL_mailbox,'*',"username LIKE :username AND admin = :admin AND domain = :domain", $bind);
    }
    $total_users = $get_users[0]['count(*)'];
  }
  elseif(decoded($_SESSION['role']) != 'SuperAdmin')
  {
    $bind = array(
      ":ldomain" => decoded($_SESSION['domain']),
      ":admin" => 'SubAdmin'
    );
    $get_users = $db->get_count($TBL_mailbox,'*',"domain = :ldomain AND admin = :admin", $bind);
    $total_users = $get_users[0]['count(*)'];
  }  
  else
  {
    $bind = array(
     ":admin" => 'DomainAdmin'
    );
    $get_users = $db->get_count($TBL_mailbox,'*',"admin = :admin",$bind);
    $total_users = $get_users[0]['count(*)'];
  }

  /* get current url */
  $get_uri = explode('/',$_SERVER['REQUEST_URI']);
  $url = $get_uri[1];

  /* create self url for pagination */
  $self_url = (isset($url) && $url != '' && strpos($url,'page=') !== true) ?  "/".$url : '/';

  /* get the current page number */
  if(isset($url) && $url != '' && strpos($url,'page=') !== false)
    $get_page = $url;
  elseif(isset($get_uri[2]) && $get_uri[2] != '' && strpos($get_uri[2],'page=') !== false)
   $get_page = $get_uri[2];

  /* Pagging */
   if(isset($_POST['limit']))
      $_SESSION['aSession'] = $_POST['limit'];

  $maxRows = isset($_SESSION['aSession']) ? $_SESSION['aSession'] : $CONF['page_size'];
  $pageNum = 1;
  $totPage = ceil($total_users/$maxRows);

  if(isset($get_page)){
    $ipage = split('=',$get_page);
    $ipage = $ipage[1];
    $pageNum = $ipage;
        if($pageNum > $totPage)
          $pageNum = $totPage;
  }

  if($pageNum>=1 && $pageNum <5)  $adjacents = 10 - $pageNum;
  if($pageNum>=5) $adjacents = 5;
  $startRow = ($pageNum - 1) * $maxRows;
  $self = $self_url;

  if(isset($_GET['srch']))
  {
    if(decoded($_SESSION['role']) == 'SuperAdmin')
      $users = $db->select($TBL_mailbox,"username LIKE :username AND admin = :admin", $bind,"ORDER BY $order LIMIT $startRow, $maxRows","username,admin,domain,quota,modified,active");
    else
      $users = $db->select($TBL_mailbox,"username LIKE :username AND admin = :admin AND domain = :domain", $bind,"ORDER BY $order LIMIT $startRow, $maxRows","username,admin,domain,quota,modified,active");
  }
  elseif(decoded($_SESSION['role']) != 'SuperAdmin')
     $users = $db->select($TBL_mailbox,"domain = :ldomain AND admin = :admin",$bind,"ORDER BY $order LIMIT $startRow, $maxRows");
  else
    $users = $db->select($TBL_mailbox,"admin = :admin",$bind,"ORDER BY $order LIMIT $startRow, $maxRows");
?>
<div class="container">
<?php
if($total_users == 0)
{
  include("notfound.php");
  include('bottom.php');
  exit;
}
?>
<div style="float: left; margin-top: 4px;"><strong>Admins List:</strong></div>
<div class="searchbox" style="float: right;">
  <form method="post">
    <div class="input-append">
      <input type="text" placeholder="Enter admin name to find ..." class="input-medium appendedInputButton" id="search-admin" style="width: 195px;"><button class="btn btn-info srgo" type="submit">Filter</button>
    </div>
  </form>
</div>

<table id="event-list" class="table table-striped table-bordered table-condensed"> 
<tbody> <tr class="showEvent">
<td style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);" class="<?php if($order == 'username ASC') echo 'headerSortasc'; else echo 'headerSortdesc';?>">User Name</td>
<?php 
  if(decoded($_SESSION['role']) == 'SuperAdmin')
    echo '<td class="event-label" style="background-color:#FFFFFF; font-weight: bold; width: 260px; color: #004A95;"><center>Domain</center></td>';
  else
    echo '<td class="event-label" style="background-color:#FFFFFF; font-weight: bold; width: 130px; color: #004A95;"><center>Quota (MBs)</center></td>';
?>
<td class="<?php if($order == 'modified ASC') echo 'modSortasc'; else echo 'modSortdesc';?>" style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75); width: 140px;"><center>Last Modified</center></td>
<td class="event-label" style="border-right: 0; background-color:#FFFFFF; font-weight: bold; width: 70px; color: #004A95;"><center>Status</center></td>
<td class="event-label" style="border-left: 0; background-color:#FFFFFF; font-weight: bold; width: 125px; color: #004A95;">Action</td>

<?php
foreach ($users as $user)
{
  echo '<tr class="showEvent" id="'.encoded($user['username']).'">';
  echo '<td style="padding-top: 9px;" class="admin-label">'.$user['username'].'</td>';

  if(decoded($_SESSION['role']) == 'SuperAdmin')
    echo '<td style="padding-top: 9px;" class="domain-label">'.$user['domain'].'</td>';
  else
  {
    echo '<td style="padding-top: 9px;"><center>';
  	if($user['quota'] == 0)
	  echo 'Unlimited';
	else
	  echo divide_quota($user['quota']);
    echo '</center></td>';
  }
  echo '<td style="padding-top: 9px;" class="domain-label"><center>'.$user['modified'].'</center></td>';

  echo '<td style="padding-top: 9px;"><center>';
	if($user['active'] == "1") 
	  echo "Active";
	else
	  echo "Inactive";
  echo '</center></td>';

echo '<td>
<div class="btn-group">
<a href="#" class="btn" data-toggle="dropdown"> <i class="icon icon-list-alt"></i> Manage</a>
<a data-toggle="dropdown" href="#" class="btn dropdown-toggle"><span class="caret"></span></a>
<ul class="dropdown-menu">
<li><a href="/edituser?user='.encoded($user['username']).'"><i class="icon-edit"></i> Edit User</a></li>
<li class="divider"></li>';
if($user['admin'] != 'DomainAdmin') echo '<li><a href="javascript:" class="delete"><i class="icon-trash"></i> Delete 
User</a></li>';
echo '</ul></div></td>';
echo '</tr>';
}
?>

</tbody> 
</table>

<?php 

 if(strpos($_SERVER['REQUEST_URI'],'?orderby=') !== false)
 {
   $srQry = explode('?orderby=',$_SERVER['REQUEST_URI']);
   $srQry = '?orderby='.$srQry[1];
   $nqry = explode('/page=',$srQry);
   $srQry = $nqry[0];
 }
 else
 {
   $srQry = '';
 }

 if(isset($_GET['srch']))
 {
   $reqUri = explode('/page=', $_GET['srch']);
   $reqUrl = '/admins?srch='.$reqUri[0];
 }
 else
 {
  $reqUrl = '/admins';
 }

/* create oder url */
 $odUrl = (strpos($_SERVER['REQUEST_URI'],'page=') !== false) ? '/admins' : $reqUrl;
 if(preg_match('/orderby=ASC|orderby=DESC|orderby=modASC|orderby=modDESC/', $reqUrl))
 {
   $odUri = explode('?',$reqUrl);
   $odUrl = '/admins?'.$odUri[1];
 }

 if(strpos($_SERVER['REQUEST_URI'],'page=') == true)
 {
   $pgUrl = explode('/page=',$_SERVER['REQUEST_URI']);
   $pgUrl = $pgUrl[0];
 }
 else
   $pgUrl = $_SERVER['REQUEST_URI'];

/* Paging */
echo '<div style="float: left;">';
 if($total_users > $maxRows)
  echo paginate_two($self, $pageNum, $totPage, $adjacents, "/page=");
echo '</div>';
?>
<div style="float: right;">
  <form method="post" action="<?php echo $pgUrl;?>" />
  <?php echo '<strong>Total Admins : '.$total_users.'</strong>&nbsp;&nbsp;&nbsp;'; ?>
    <select onchange="submit()" name="limit" style="width: 150px;">
        <option>Results Per Page</option>
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
        <option value="200">200</option>
        <option value="500">500</option>
    </select>
  </form>
</div>

</div><!-- end container -->

<script type="text/javascript">

$('.delete').click( function (){
        var total_page = '<?php echo $totPage;?>';
        var page = '<?php echo $pageNum;?>';
        adminName = $(this).parents('tr').first().find('.admin-label').text();
        if(confirm("Are you sure you want to delete: "+adminName+"?")){
          $('#img_load').show();
            id=$(this).parents('tr').first().attr('id');
            $.post("/remove_user.php",{'userID':id},function(response){
            $('#total_rec').html( $('#total_rec').html() -1);
                if($('#total_rec').html() == 0 && total_page == page){
                        page = page - 1;
                }
              if(page == 1 || page == 0)
                  window.location='<?php echo $reqUrl;?>';
              else
                 window.location='<?php echo $reqUrl."/page=";?>'+page;
            });
        }
});

$('a,button').focus(function() {
  $(this).css('outline','none');
});

$('input').focus(function() {
  $(this).css('outline','none');
});

$('.headerSortdesc').click(function (){
  window.location = "<?php echo $odUrl.'?orderby=ASC'?>";
});

$('.headerSortasc').click(function (){
  window.location = "<?php echo $odUrl.'?orderby=DESC'?>";
});

$('.modSortdesc').click(function (){
  window.location = "<?php echo $odUrl.'?orderby=modASC'?>";
});

$('.modSortasc').click(function (){
  window.location = "<?php echo $odUrl.'?orderby=modDESC'?>";
});

$('.srgo').click(function (){
        admin = $("#search-admin").val();
        if(admin == ''){
          alert("Please enter admin name for search");
          return false;
        }
        else
        {
          window.location = "/admins?srch="+admin+"<?php echo $srQry;?>";
	  return false;
        }
    });

$(document).keypress(function(e) {
    if(e.which == 13) {
      if($("#search-admin").val() != ''){
        admin = $("#search-admin").val();
        window.location = "/admins?srch="+admin+"<?php echo $srQry;?>";
        return false;
      }
      else {
        return false;
      }
    }
});

</script>
