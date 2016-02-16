<?php

if(ereg("alias-domains.php", $_SERVER['PHP_SELF']) || decoded($_SESSION['role']) != 'SuperAdmin'){
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
      $order = 'alias_domain ASC';
    elseif($orders == 'DESC')
      $order = 'alias_domain DESC';
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
    $get_domains = $db->get_count($TBL_alias_domain,'alias_domain',"alias_domain = :ldomain AND isDefault = 'Y'", $bind);
    $total_domains = $get_domains[0]['count(alias_domain)'];
  }
  elseif(isset($_GET['srch']))
  {
    $srch = explode('/page',$_GET['srch']);
    $srch = explode('?',$srch[0]);
    $bind = array(
      ":ldomain" => "%$srch[0]%"
    );
    $get_domains = $db->get_count($TBL_alias_domain,'alias_domain',"alias_domain LIKE :ldomain AND isDefault = 'Y'", $bind);
    $total_domains = $get_domains[0]['count(alias_domain)'];
  }
  else
  {
    $get_domains = $db->get_count($TBL_alias_domain,'alias_domain',"isDefault = 'Y'");
    $total_domains = $get_domains[0]['count(alias_domain)'];
  }

    /* get current url */
    $get_uri = explode('/',$_SERVER['REQUEST_URI']);
    $url = $get_uri[1];

    /* create self url for pagination */
    $self_url = (isset($url) && $url != '' && strpos($url,'page=') !== true) ?  "/".$url : '/alias_domains';

    /* get the current page number */
    if(isset($url) && $url != '' && strpos($url,'page=') !== false)
      $get_page = $url;
    elseif(isset($get_uri[2]) && $get_uri[2] != '' && strpos($get_uri[2],'page=') !== false)
     $get_page = $get_uri[2];

    /* Pagging */
    if(isset($_POST['limit']))
      $_SESSION['aldSession'] = $_POST['limit'];

    $maxRows = isset($_SESSION['aldSession']) ? $_SESSION['aldSession'] : $CONF['page_size'];
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
    $domains = $db->select($TBL_alias_domain,"alias_domain = :ldomain AND isDefault = 'Y'", $bind,'','id,alias_domain,modified,active');
  elseif(isset($_GET['srch']))
    $domains = $db->select($TBL_alias_domain,"alias_domain LIKE :ldomain AND isDefault = 'Y'", $bind,"ORDER BY $order LIMIT $startRow, $maxRows",'id,alias_domain,modified,active');
  else
    $domains = $db->select($TBL_alias_domain,"isDefault = 'Y'",'',"ORDER BY $order LIMIT $startRow, $maxRows",'id,alias_domain,modified,active');
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

<div style="float: left; margin-top: 4px;"><strong>Alias Domains List:</strong></div>
<div class="searchbox" style="float: right;">
  <form method="post">
    <div class="input-append">
      <input type="text" placeholder="Enter domain name to find ..." class="input-medium appendedInputButton" id="search-domain" style="width: 195px;"><button class="btn btn-info srgo" type="submit">Filter</button>
    </div>
  </form>
</div>

<table id="event-list" class="table table-striped table-bordered table-condensed"> 
<tbody> <tr class="showEvent">
<td style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);" class="<?php if($order == 'alias_domain ASC') echo 'headerSortasc'; else echo 'headerSortdesc';?>">Domain Name</td>
<td class="<?php if($order == 'modified ASC') echo 'modSortasc'; else echo 'modSortdesc';?>" style="background-color: rgba(141, 192, 219, 0.25); text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75); width: 160px;"><center>Last Modified</center></td>
<td class="event-label" style="border-right: 0; background-color:#FFFFFF; font-weight: bold; width: 110px; color: #004A95;"><center>Status</center></td>
<td class="event-label" style="border-left: 0; background-color:#FFFFFF; font-weight: bold; width: 145px; color: #004A95;">Action</td>

<?php
foreach ($domains as $domain)
{
  echo '<tr class="showEvent" id="'.encoded($domain['alias_domain']).'">';
  echo '<td style="padding-top: 9px;" class="domain-label"><a href="/alias-domain-users?domain='.encoded($domain['alias_domain']).'">'.$domain['alias_domain'].'</a></td>';
  echo '<td style="padding-top: 9px;" class=""><center>'.$domain['modified'].'</center></td>';

  echo '<td style="padding-top: 9px;"><center>';
	if($domain['active'] == "1") 
	  echo "Active";
	else
	  echo "Inactive";
  echo '</center></td>';

echo '<td>';
echo '<div class="btn-group">';
echo '<a href="#" class="btn" data-toggle="dropdown"> <i class="icon icon-list-alt"></i> Manage</a>';
echo '<a data-toggle="dropdown" href="#" class="btn dropdown-toggle"><span class="caret"></span></a>';
echo '<ul class="dropdown-menu">';
 if($domain['active'] == "1")
  echo '<li><a href="javascript:" class="disable"><i class="icon-off"></i> Disable Domain</a></li>';
 else
  echo '<li><a href="javascript:" class="active"><i class="icon-ok"></i> Active Domain</a></li>';
echo '<li class="divider"></li>';
?>
<li><a href="" onclick="javascript:window.location='/edit-alias-domain?domainName=<?php echo encoded($domain['alias_domain']);?>'; return false;"><i class="icon-file"></i> Convert into real Domain</a></li>
<?php
echo '<li class="divider"></li>';
echo '<li><a href="javascript:" class="delete"><i class="icon-trash"></i> Delete Domain</a></li>';
echo '</ul></div></td></tr>';
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
   $reqUrl = '/alias-domains?srch='.$reqUri[0];
 }
 else
 {
  $reqUrl = '/alias-domains';
 }

/* create oder url */
 $odUrl = (strpos($_SERVER['REQUEST_URI'],'page=') !== false) ? '/alias-domains' : $reqUrl;
 if(preg_match('/orderby=ASC|orderby=DESC|orderby=modASC|orderby=modDESC/', $reqUrl))
 {
   $odUri = explode('?',$reqUrl);
   $odUrl = '/alias-domains?'.$odUri[1];
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
 if($total_domains > $maxRows)
  echo paginate_two($self, $pageNum, $totPage, $adjacents, "/page=");
echo '</div>';
?>
<div style="float: right;">
  <form method="post" action="<?php echo $pgUrl;?>" />
  <?php echo '<strong>Total Alias Domains : '.$total_domains.'</strong>&nbsp;&nbsp;&nbsp;'; ?>
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

$('.disable, .active').click( function (){
        var myEvent = $(this).attr("class");
        if(myEvent == 'disable')
	  var action = 'disable';
	else
	  var action = 'active';

        domainName = $(this).parents('tr').first().find('.domain-label').text();
        if(confirm("Are you sure you want to "+action+ ": "+domainName+"?")){
          $('#img_load').show();
            id=$(this).parents('tr').first().attr('id');
            $.post("/edit_alias_domain.php",{'domainID':id,'active':action},function(response){
		alert(domainName+' sucussfully '+action);
                window.location='<?php echo $reqUrl;?>';
            });
        }
});

$('.delete').click( function (){
        var total_page = '<?php echo $totPage;?>';
        var page = '<?php echo $pageNum;?>';
        domainName = $(this).parents('tr').first().find('.domain-label').text();
        if(confirm("Are you sure you want to delete: "+domainName+"?")){
          $('#img_load').show();
            id=$(this).parents('tr').first().attr('id');
            $.post("/remove_alias_domain.php",{'domainID':id},function(response){
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
        domain = $("#search-domain").val();
        if(admin == ''){
          alert("Please enter domain name for search");
          return false;
        }
        else
        {
          window.location = "/alias-domains?srch="+domain+"<?php echo $srQry;?>";
	  return false;
        }
    });

$(document).keypress(function(e) {
    if(e.which == 13) {
      if($("#search-admin").val() != ''){
        domain = $("#search-domain").val();
        window.location = "/alias-domains?srch="+domain+"<?php echo $srQry;?>";
        return false;
      }
      else {
        return false;
      }
    }
});

</script>
