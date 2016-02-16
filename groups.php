<?php

if(ereg("groups.php", $_SERVER['PHP_SELF'])){
  header ("Location: /");
  exit;
}

if(!isset($_SESSION['role']) || decoded($_SESSION['role']) == 'User')
{
  header('Location: /');
  exit;
}
if(isset($_GET['domain']) && decoded($_SESSION['role']) != 'SuperAdmin')
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

  if(isset($_GET['domain']))
  {
    $gdomain = explode('/page',$_GET['domain']);
    $gdomain = explode('?',$gdomain[0]);
    $bind = array(
      ":ldomain" => $gdomain[0]
    );
    $get_groups = $db->get_count($TBL_groups,'*',"domain = :ldomain", $bind);
    $total_groups = $get_groups[0]['count(*)'];
  }  
  elseif(isset($_GET['srch']) && decoded($_SESSION['role']) == 'SuperAdmin')
  {
    $srch = explode('/page',$_GET['srch']);
    $srch = explode('?',$srch[0]);
    $ggroup = explode('/domain=',$srch[0]);

    $group = preg_match('/^@/',$ggroup[0]) ? "%$ggroup[0]%@%" : !preg_match('/@/',$ggroup[0]) ? "%$ggroup[0]%@%" : "%$ggroup[0]%";

    $bind = array(
      ":address" => $group,
    );

    if(isset($ggroup[1]))
      $get_groups = $db->get_count($TBL_groups,'*',"address LIKE :address AND domain ='".$ggroup[1]."'", $bind);
    else
      $get_groups = $db->get_count($TBL_groups,'*',"address LIKE :address", $bind);

    $total_groups = $get_groups[0]['count(*)'];
  }
  elseif(isset($_GET['srch']) && decoded($_SESSION['role']) != 'SuperAdmin')
  {
    $ggroup = explode('/page',$_GET['srch']);
    $ggroup = explode('?',$ggroup[0]);
    $group = preg_match('/^@/',$ggroup[0]) ? "%$ggroup[0]%@%" : !preg_match('/@/',$ggroup[0]) ? "%$ggroup[0]%@%" : "%$ggroup[0]%";

    $bind = array(
      ":address" => $group,
      ":domain" => decoded($_SESSION['domain'])
      );

    $get_groups = $db->get_count($TBL_groups,'*',"address LIKE :address AND domain = :domain", $bind);
    $total_groups = $get_groups[0]['count(*)'];
  }
  elseif(isset($_GET['group']))
  {
    if(decoded($_SESSION['role']) == 'SuperAdmin') 
    {
      $bind = array(
        ":address" => decoded($_GET['group'])
      );
     $get_groups = $db->get_count($TBL_groups,'*',"address = :address",$bind);
    }
    else
    {
      $bind = array(
        ":address" => decoded($_GET['group']),
        ":domain" => decoded($_SESSION['domain'])
      );
     $get_groups = $db->get_count($TBL_groups,'*',"address = :address AND domain = :domain",$bind);
    }
    $total_groups = $get_groups[0]['count(*)'];
  }  
  elseif(decoded($_SESSION['role']) != 'SuperAdmin')
  {
    $bind = array(
      ":domain" => decoded($_SESSION['domain'])
    );
    $get_groups = $db->get_count($TBL_groups,'*',"domain = :domain",$bind);
    $total_groups = $get_groups[0]['count(*)'];
  }
  else
  {
    $get_groups = $db->get_count($TBL_groups,'*');
    $total_groups = $get_groups[0]['count(*)'];
  }

 /* get current url */
  $get_uri = explode('/',$_SERVER['REQUEST_URI']);
  if(isset($get_uri[2]) && strpos($get_uri[2],'domain=') !== false)
    $url = $get_uri[1].'/'.$get_uri[2];
  elseif(empty($get_uri[1]))
    $url = "groups";
  else
    $url = $get_uri[1];

  /* create self url for pagination */
  $self_url = (isset($url) && $url != '' && strpos($url,'page=') !== true) ?  "/".$url : '/';

  /* get the current page number */
  if(isset($url) && $url != '' && strpos($url,'page=') !== false)
    $get_page = $url;
  elseif(isset($get_uri[2]) && $get_uri[2] != '' && strpos($get_uri[2],'page=') !== false)
    $get_page = $get_uri[2];
  elseif(isset($get_uri[3]) && $get_uri[3] != '' && strpos($get_uri[3],'page=') !== false)
    $get_page = $get_uri[3];

 /* Pagging */
   if(isset($_POST['limit']))
      $_SESSION['gSession'] = $_POST['limit'];

  $maxRows = isset($_SESSION['gSession']) ? $_SESSION['gSession'] : $CONF['page_size'];
  $pageNum = 1;
  $totPage = ceil($total_groups/$maxRows);

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

  if(isset($_GET['domain']))
     $groups = $db->select($TBL_groups,"domain = :ldomain", $bind,"ORDER BY $order LIMIT $startRow, $maxRows");
  elseif(isset($_GET['srch']))
  {
    if(decoded($_SESSION['role']) == 'SuperAdmin')
    {
      if(isset($ggroup[1]))
        $groups = $db->select($TBL_groups,"address LIKE :address AND domain ='".$ggroup[1]."'", $bind,"ORDER BY $order LIMIT $startRow, $maxRows");
      else
        $groups = $db->select($TBL_groups,"address LIKE :address", $bind,"ORDER BY $order LIMIT $startRow, $maxRows");
    }
    elseif(decoded($_SESSION['role']) != 'SuperAdmin')
    {
      $groups = $db->select($TBL_groups,"address LIKE :address AND domain = :domain", $bind,"ORDER BY $order LIMIT $startRow, $maxRows");
    }
  }
  elseif(isset($_GET['group']))
  {
    if(decoded($_SESSION['role']) == 'SuperAdmin')
      $groups = $db->select($TBL_groups,"address = :address",$bind);
    else
      $groups = $db->select($TBL_groups,"address = :address AND domain = :domain",$bind);
  }
  elseif(decoded($_SESSION['role']) != 'SuperAdmin')
    $groups = $db->select($TBL_groups,"domain = :domain",$bind,"ORDER BY $order LIMIT $startRow, $maxRows");
  else
    $groups = $db->select($TBL_groups,"","","ORDER BY $order LIMIT $startRow, $maxRows");
?>

<div class="container">
<?php
if($total_groups == 0)
{
  include("notfound.php");
  include('bottom.php');
  exit;
}
if(!isset($_GET['group']))
  echo '<div style="float: left; margin-top: 15px;" id="totalGroups"><strong>Total Number of Groups : '.$total_groups.'</strong></div>';
echo '<div style="float: right;" class="box">';
echo '<form method="post" style="margin: 0px;">';
if(decoded($_SESSION['role']) == 'SuperAdmin')
{
  $domains = $db->list_domains("SELECT DISTINCT domain FROM groups");
      echo '<select id="select-domain" name="select-domain" style="width: 283px;">';
                echo '<option value="">Select Domain</option>';
       foreach ($domains as $domain)
        {
            echo '<option value="'.$domain['domain'].'">'.$domain['domain'].'</option>';
        }
      echo '</select>';
      echo '&nbsp;';
}
?>
<div class="searchbox" style="float: right;">
    <div class="input-append">
       <input type="text" placeholder="Enter group name to find ..." class="input-medium appendedInputButton" id="search-group" style="width: 170px;"><button class="btn btn-info srgo" type="submit">Filter</button>
    </div>
  </form>
</div>
</div>

<table id="event-list" class="table table-striped table-bordered table-condensed"> 
<tbody> <tr class="showEvent">
<td style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);" class="<?php if($order == 'address ASC') echo 'headerSortasc'; else echo 'headerSortdesc';?>">Group Name</td>
<td class="event-label" style="background-color:#FFFFFF; font-weight: bold; width: 280px; color: #004A95;"><center>Goto</center></td>
<td class="<?php if($order == 'modified ASC') echo 'modSortasc'; else echo 'modSortdesc';?>" style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75); width: 140px;"><center>Last Modified</center></td>
<td class="event-label" style="border-right: 0; background-color:#FFFFFF; font-weight: bold; width: 80px; color: #004A95;"><center>Status</center></td>
<td class="event-label" style="border-left: 0; background-color:#FFFFFF; font-weight: bold; width: 125px; color: #004A95;">Action</td>

<?php
foreach ($groups as $group)
{
  echo '<tr class="showEvent" id="'.encoded($group['address']).'">';
  echo '<td style="padding-top: 9px;" class="group-label">'.$group['address'].'</td>';
  echo '<td style="padding-top: 9px; overflow: hidden; white-space:nowrap;" class="domain-label"><center>';
    $goto = explode(',',$group['goto']);
    echo $goto[0];
  echo '</center></td>';
  echo '<td style="padding-top: 9px;" class="domain-label"><center>'.$group['modified'].'</center></td>';
  echo '<td style="padding-top: 9px;"><center>';
	if($group['active'] == "1") 
	  echo "Active";
	else
	  echo "Inactive";
  echo '</center></td>';

echo '<td>
<div class="btn-group">
<a href="#" class="btn" data-toggle="dropdown"> <i class="icon icon-list-alt"></i> Manage</a>
<a data-toggle="dropdown" href="#" class="btn dropdown-toggle"><span class="caret"></span></a>
<ul class="dropdown-menu">
<li><a href="/editgroup?group='.encoded($group['address']).'"><i class="icon-edit"></i> Edit Group</a></li>
<li class="divider"></li>
<li><a href="javascript:" class="delete"><i class="icon-trash"></i> Delete Group</a></li>
</ul></div></td>';
echo '</tr>';
}
?>

</tbody> 
</table>
<?php

 $srQry = '';
 $dsrQry = '';
 $srDom = '';

 if(isset($_GET['domain']))
 {
   $dsrQry = $_GET['domain'];
   $dsrQry = explode('/page=',$dsrQry);
   $dsrQry = $dsrQry[0];
   $srDom = $dsrQry;
   $reqUrl = '/groups?domain='.$_GET['domain'];
 }
 elseif(isset($_GET['srch']))
 {
   if(strpos($_GET['srch'],'domain=') !== false)
   {
     $dsrQry = explode('domain=',$_GET['srch']);
     $dsrQry = explode('/page=',$dsrQry[1]);
     $dsrQry = $dsrQry[0];
     $srDom = explode('?orderby=',$dsrQry);
     $srDom = $srDom[0];
     $qry = explode('/domain=',$_GET['srch']);
     $srQry = $qry[1];
   }
   else
   {
     $qry = explode('?orderby=',$_GET['srch']);
     $srQry = isset($qry[1]) ? '?orderby='.$qry[1] : '';
   }
   $reqUri = explode('/page=', $_GET['srch']);
   $reqUrl = $_GET['q'].'?srch='.$reqUri[0];
 }
 else
 {
   if(strpos($_SERVER['REQUEST_URI'],'?orderby=') !== false)
   {
     $srQry = explode('?',$_SERVER['REQUEST_URI']);
     $srQry = '?'.$srQry[1];
   }
   $reqUrl = '/groups';
 }
 if($srQry != '')
 {
   $nqry = explode('/page=',$srQry);
   $srQry = $nqry[0];
 }

/* create oder url */
  if(strpos($_SERVER['REQUEST_URI'],'page=') !== false || strpos($_SERVER['REQUEST_URI'],'srch=') !== false)
  {
    $odUrl = explode('/page',$_SERVER['REQUEST_URI']);
    $odUrl = $odUrl[0];
    $odUrl = explode('?orderby',$odUrl);
    $odUrl = $odUrl[0];
  }
  else
    $odUrl = $reqUrl;

/* Paging */
echo '<div style="float: left;">';
 if($total_groups > $maxRows)
  echo paginate_two($self, $pageNum, $totPage, $adjacents, "/page=");
echo '</div>';

if(strpos($_SERVER['REQUEST_URI'],'page=') == true)
{
  $pgUrl = explode('/page=',$_SERVER['REQUEST_URI']);
  $pgUrl = $pgUrl[0];
}
else
  $pgUrl = $_SERVER['REQUEST_URI'];

?>
<div style="float: right;">
  <form method="post" action="<?php echo $pgUrl;?>" />
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

$('.delete').click( function (){
        var total_page = '<?php echo $totPage;?>';
        var page = '<?php echo $pageNum;?>';
        groupName = $(this).parents('tr').first().find('.group-label').text();
        if(confirm("Are you sure you want to delete: "+groupName+"?")){
	  $('#img_load').show();
            id=$(this).parents('tr').first().attr('id');
            $.post("/remove_group.php",{'groupID':id},function(response){
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

$('.srgo').click(function (){
        group = $("#search-group").val();
        domain = '<?php echo $dsrQry;?>';
        if(group == ''){
          alert("Please enter groupname for search");
          return false;
        }
        else
        {
          if(domain != '')
            window.location = "/groups?srch="+group+"/domain="+domain;
          else
            window.location = "/groups?srch="+group+"<?php echo $srQry;?>";
	  return false;
        }
    });

$(document).keypress(function(e) {
    if(e.which == 13) {
      if($("#search-group").val() != ''){
        group = $("#search-group").val();
        domain = '<?php echo $dsrQry;?>';
        if(domain != '')
          window.location = "/groups?srch="+group+"/domain="+domain;
        else
          window.location = "/groups?srch="+group+"<?php echo $srQry;?>";
        return false;
      }
      else {
        return false;
      }
    }
});

$('#select-domain').change(function (){
  domain = $(this).val();
  if(domain == '')
    window.location = "/groups";
  else
    window.location = "/groups?domain="+domain;
});

$('#select-domain').val('<?php echo $srDom;?>');

</script>
