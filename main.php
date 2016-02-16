<?php

if(ereg("main.php", $_SERVER['PHP_SELF']) || decoded($_SESSION['role']) != 'SuperAdmin'){
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
      $order = 'domain ASC';
    elseif($orders == 'DESC')
      $order = 'domain DESC';
    elseif($orders == 'modDESC')
      $order = 'modified DESC';
    else
      $order = 'modified ASC';
  }
  /* end order query */

  if(isset($_GET['domain']))
  {
    $bind = array(
      ":ldomain" => decoded($_GET['domain'])
    );
    $get_domains = $db->get_count($TBL_domain,'*',"domain = :ldomain", $bind);
    $total_domains = $get_domains[0]['count(*)'];
  }  
  elseif(isset($_GET['srch']))
  {
    $srch = explode('/page',$_GET['srch']);
    $srch = explode('?',$srch[0]);
    $bind = array(
      ":ldomain" => "%$srch[0]%"
    );
    $get_domains = $db->get_count($TBL_domain,'*',"domain LIKE :ldomain", $bind);
    $total_domains = $get_domains[0]['count(*)'];
  }  
  else
  {
    $get_domains = $db->get_count($TBL_domain,'*');
    $total_domains = $get_domains[0]['count(*)'];
  }

    /* get current url */
    $get_uri = explode('/',$_SERVER['REQUEST_URI']);
    $url = $get_uri[1];

    /* create self url for pagination */
    $self_url = (isset($url) && $url != '' && strpos($url,'page=') !== false) ?  "/" : "/".$url;

    /* get the current page number */
    if(isset($url) && $url != '' && strpos($url,'page=') !== false)
      $get_page = $url;
    elseif(isset($get_uri[2]) && $get_uri[2] != '' && strpos($get_uri[2],'page=') !== false)
     $get_page = $get_uri[2];

    /* Pagging */
    if(isset($_POST['limit']))
      $_SESSION['dSession'] = $_POST['limit'];

    $maxRows = isset($_SESSION['dSession']) ? $_SESSION['dSession'] : $CONF['page_size'];
    $pageNum = 1;
    $totPage = ceil($total_domains/$maxRows);

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
    $domains = $db->select($TBL_domain,"domain = :ldomain", $bind);
  elseif(isset($_GET['srch']))
    $domains = $db->select($TBL_domain,"domain LIKE :ldomain", $bind,"ORDER BY $order LIMIT $startRow, $maxRows");
  else
    $domains = $db->select($TBL_domain,'','',"ORDER BY $order LIMIT $startRow, $maxRows");
?>

<div class="container">
<?php
if($total_domains == 0)
{
  include("notfound.php");
  include('bottom.php');
  exit;
}
?>

<?php 
  if(!isset($_GET['domain']))
  echo '<div style="float: left; margin-top: 5px;" id="totalDomains"><strong>Total Number of Domains : '.$total_domains.'</strong></div>';
?>
<div class="searchbox" style="float: right;">
  <form method="post">
    <div class="input-append">
       <input type="text" placeholder="Enter domain name to find ..." class="input-medium appendedInputButton" id="search-domain" style="width: 195px;"><button class="btn btn-info srgo" type="submit">Filter</button>
    </div>
  </form>
</div>

<table id="event-list" class="table table-striped table-bordered table-condensed"> 
<tbody> <tr class="showEvent">

<td style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);" class="<?php if($order == 'domain ASC') echo 'headerSortasc'; else echo 'headerSortdesc';?>">Domain</td>
<td class="event-label" style="border-right: 0; background-color:#FFFFFF; font-weight: bold; width: 128px; color: #004A95;"><center>Mailboxes Limit</center></td>
<td class="event-label" style="border-left: 0; background-color:#FFFFFF; color: #004A95; font-weight: bold; width: 128px;"><center>Groups Limit</center></td>
<td class="event-label" style="border-left: 0; background-color:#FFFFFF; color: #004A95; font-weight: bold; width: 82px;"><center>Quota (MB)</center></td> 
<td class="<?php if($order == 'modified ASC') echo 'modSortasc'; else echo 'modSortdesc';?>" style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75); width: 102px;"><center>Modified</center></td>
<td class="event-label" style="background-color:#FFFFFF; color: #004A95; font-weight: bold; width: 60px;"><center>Status</center></td> 
<td class="event-label" style="border-left: 0; background-color:#FFFFFF; color: #004A95; font-weight: bold; width: 120px;">Action</td> 

<?php
$i=0;
foreach ($domains as $domain)
{
  $bind = array(
      ":domain" => $domain['domain']
    );

  $total_used_mboxes = $db->get_count($TBL_mailbox,'*',"domain = :domain",$bind);
  $used_mboxes = $total_used_mboxes[0]['count(*)'];

  $total_used_groups = $db->get_count($TBL_groups,'*',"domain = :domain",$bind);
  $used_groups = $total_used_groups[0]['count(*)'];

  echo '<tr class="showEvent" id="'.encoded($domain['domain']).'">';
  echo '<td style="padding-top: 9px;" class="domain-label"><a href="/users?domain='.encoded($domain['domain']).'">'.$domain['domain'].'</a>';
  if($domain['ds_mboxes'] == 'Y') echo '<img title="New user creation disabled" src="/img/user-limit.png" style="width: 14px; vertical-align: middle; margin-top: -3px; margin-left: 7px;">';
  if($domain['ds_groups'] == 'Y') echo '<img title="New group creation disabled" src="/img/alias-limit.png" style="width: 14px; vertical-align: middle; margin-top: -1px; margin-left: 7px;">';
  echo '</td>';

  echo '<td style="padding-top: 9px;"><center>';
  	if($domain['mailboxes'] == 0)
	  echo 'Unlimited / <strong>'.$used_mboxes.'</strong>';
	else
	  echo $domain['mailboxes'].' / <strong>'.$used_mboxes.'</strong>';
  echo '</center></td>';

  echo '<td style="padding-top: 9px;"><center>';
  	if($domain['groups'] == 0)
	  echo 'Unlimited / <strong>'.$used_groups.'</strong>';
	else
          echo $domain['groups'].' / <strong>'.$used_groups.'</strong>';
  echo '</center></td>';

  echo '<td style="padding-top: 9px;"><center>';
  	if($domain['maxquota'] == 0)
	  echo 'Unlimited';
	else
	  echo $domain['maxquota'];
  echo '</center></td>';

  echo '<td style="padding-top: 9px;"><center>'.date("Y-m-d",strtotime($domain['modified'])).'</center></td>';

  echo '<td style="padding-top: 9px;"><center>';
	if($domain['active'] == "1") 
	  echo "Active";
	else
	  echo "Inactive";
  echo '</center></td>';

echo '<td>
<div class="btn-group">
<a href="#" class="btn" data-toggle="dropdown"> <i class="icon icon-list-alt"></i> Manage</a>
<a data-toggle="dropdown" href="#" class="btn dropdown-toggle"><span class="caret"></span></a>
<ul class="dropdown-menu">
<li><a href="/editdomain?domain='.encoded($domain['domain']).'"><i class="icon-edit"></i> Edit Domain</a></li>
<li class="divider"></li> 
<li><a href="javascript:" class="delete"><i class="icon-trash"></i> Delete Domain</a></li>
</ul>
</div>
</td>';

echo '</tr>';
$i++;
}
echo '<div id="total_rec" style="display: none;">'.$i.'</div>';
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
   $reqUrl = '/?srch='.$reqUri[0];
  }
  else
  {
   $reqUrl = '';
  }

/* create oder url */
 $odUrl = (strpos($_SERVER['REQUEST_URI'], 'page=') !== false) ? '/' : $reqUrl;
 if(preg_match('/orderby=ASC|orderby=DESC|orderby=modASC|orderby=modDESC/', $reqUrl))
 {
   $odUri = explode('?',$reqUrl);
   $odUrl = '/?'.$odUri[1];
 }

echo '<div style="float: left;">';
 if($total_domains > $maxRows) 
 {
  if($self_url == '/')
    echo paginate_two($self, $pageNum, $totPage, $adjacents, "page=");
  else
    echo paginate_two($self, $pageNum, $totPage, $adjacents, "/page=");
 }
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

$('.delete').click( function (){
	var total_page = '<?php echo $totPage;?>';
	var page = '<?php echo $pageNum;?>';
        domainName = $(this).parents('tr').first().find('.domain-label').text();
        if(confirm("Are you sure you want to delete: "+domainName+"\nIt will also delete all users of this domain.")){
	  $('#img_load').show();
            id=$(this).parents('tr').first().attr('id');
            $.post("/remove_domain.php",{'domainID':id},function(response){
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
        site = $("#search-domain").val();
        if(site == ''){
          alert("Please enter domain name for search");
	  return false;
        }
	else
	{
	  window.location = "/?srch="+site+"<?php echo $srQry;?>";
	  return false;
	}
    });

$(document).keypress(function(e) {
    if(e.which == 13) {
      if($("#search-domain").val() != ''){
        site = $("#search-domain").val();
        window.location = "/?srch="+site+"<?php echo $srQry;?>";
	return false;
      }
      else {
        return false;
      }
    }
});
</script>
