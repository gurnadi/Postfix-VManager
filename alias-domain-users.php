<?php

if(ereg("alias-domain-users.php", $_SERVER['PHP_SELF'])){
  header ("Location: /");
  exit;
}
if(!isset($_SESSION['role']) || decoded($_SESSION['role']) != 'SuperAdmin' || !isset($_GET['domain']))
{
  header('Location: /');
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
      $order = 'address ASC';
    elseif($orders == 'DESC')
      $order = 'address DESC';
    elseif($orders == 'modDESC')
      $order = 'modified DESC';
    else
      $order = 'modified ASC';
  }
  /* end order query */

  $gdomain = explode('/page',$_GET['domain']);
  $domain = explode('?',$gdomain[0]);
  $domain = $domain[0];

  $bind = array(
    ":ldomain" => decoded($domain)
  );

  $get_users = $db->get_count($TBL_alias_domain,'*',"alias_domain = :ldomain", $bind);
  $total_users = $get_users[0]['count(*)'];

  /* get current url */
  $get_uri = explode('/',$_SERVER['REQUEST_URI']);
  if(isset($get_uri[2]) && strpos($get_uri[2],'domain=') !== false)
    $url = $get_uri[1].'/'.$get_uri[2];
  elseif(empty($get_uri[1]))
    $url = "users";
  else
    $url = $get_uri[1];

  /* create self url for pagination */
  $self_url = (isset($url) && $url != '' && strpos($url,'page=') !== true) ? "/".$url : '/';

  /* get the current page number */
  if(isset($url) && $url != '' && strpos($url,'page=') !== false)
    $get_page = $url;
  elseif(isset($get_uri[2]) && $get_uri[2] != '' && strpos($get_uri[2],'page=') !== false)
    $get_page = $get_uri[2];
  elseif(isset($get_uri[3]) && $get_uri[3] != '' && strpos($get_uri[3],'page=') !== false)
    $get_page = $get_uri[3];

  /* Pagging */
   if(isset($_POST['limit']))
      $_SESSION['aluSession'] = $_POST['limit'];

  $maxRows = isset($_SESSION['aluSession']) ? $_SESSION['aluSession'] : $CONF['page_size'];
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

  $users = $db->select($TBL_alias_domain,"alias_domain = :ldomain",$bind,"ORDER BY $order LIMIT $startRow, $maxRows");
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
<div style="float: left; margin-top: 4px;"><strong>Alias Users List: 
<?php 
  echo strtoupper(decoded($domain));
?>
</strong></div>
<div style="float: right; margin-bottom: 12px;">
<button class="btn btn-primary newalias">New alias Email</button>
</div>

<table id="event-list" class="table table-striped table-bordered table-condensed"> 
<tbody> <tr class="showEvent">
<td style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);" class="<?php if($order == 'address ASC') echo 'headerSortasc'; else echo 'headerSortdesc';?>">Email Alias</td>
<td class="event-label" style="background-color:#FFFFFF; font-weight: bold; width: 270px; color: #004A95;"><center>Target Destination</center></td> 
<td class="<?php if($order == 'modified ASC') echo 'modSortasc'; else echo 'modSortdesc';?>" style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75); width: 140px;"><center>Last Modified</center></td>
<td class="event-label" style="border-right: 0; background-color:#FFFFFF; font-weight: bold; width: 80px; color: #004A95;"><center>Status</center></td> 
<td class="event-label" style="border-left: 0; background-color:#FFFFFF; font-weight: bold; width: 125px; color: #004A95;">Action</td> 

<?php
foreach ($users as $user)
{
  echo '<tr class="showEvent" id="'.encoded($user['address']).'">';
  echo '<td style="padding-top: 9px;" class="user-label">'.$user['address'].'</td>';
  echo '<td style="padding-top: 9px;" class="domain-label"><center>'.$user['target_domain'].'</center></td>';
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
<li><a href="/edit-alias-user?userID='.encoded($user['id']).'"><i class="icon-edit"></i> Edit User</a></li>
<li class="divider"></li>';
if($user['isDefault'] != 'Y') echo '<li><a href="javascript:" class="delete"><i class="icon-trash"></i> Delete User</a></li>';
echo '</ul></div></td>';
  echo '</tr>';
}
?>

</tbody> 
</table>

<?php

$reqUrl = '/alias-domain-users?domain='.$domain;
if(strpos($_SERVER['REQUEST_URI'],'page=') == true)
{
  $pgUrl = explode('/page=',$_SERVER['REQUEST_URI']);
  $pgUrl = $pgUrl[0];
}
else
  $pgUrl = $_SERVER['REQUEST_URI'];

/* create oder url */
if(strpos($_SERVER['REQUEST_URI'],'page=') !== false)
{
  $odUrl = explode('/page',$_SERVER['REQUEST_URI']);
  $odUrl = $odUrl[0];
  $odUrl = explode('?orderby',$odUrl);
  $odUrl = $odUrl[0];
}
else
{
  $odUrl = explode('?orderby',$reqUrl);
  $odUrl = $odUrl[0];
}

/* Paging */
echo '<div style="float: left;">';
 if($total_users > $maxRows)
  echo paginate_two($self, $pageNum, $totPage, $adjacents, "/page=");
echo '</div>';
?>      
<div style="float: right;">
  <form method="post" action="<?php echo $pgUrl;?>" />

<?php
  if(!isset($_GET['user']))
  echo '<strong>Total Users : '.$total_users.'</strong>&nbsp;&nbsp;&nbsp;';
?>
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
        userName = $(this).parents('tr').first().find('.user-label').text();
        if(confirm("Are you sure you want to delete: "+userName+"?")){
	  $('#img_load').show();
            id=$(this).parents('tr').first().attr('id');
            $.post("/remove_aliasdomain_user.php",{'userID':id},function(response){
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

$('.newalias').click(function (){
  window.location = "<?php echo "/new-alias-user?newUser=$domain";?>";
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

</script>
