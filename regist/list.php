<<<<<<< HEAD
ddddd
=======
<?
##### including function definition file.
include $_SERVER['DOCUMENT_ROOT'] . "/func/function.user.php";
include $_SERVER['DOCUMENT_ROOT'] . "/member/config.php";

LoginedChk();

if($_COOKIE['p_status']=="N" && $_COOKIE['member_id']!="cdc"){
  RefreshURL("/member/modifyform.php");
}

SetCookie("search_keyfield",$keyfield,0,"/",ereg_replace('www.','',$HTTP_HOST));
SetCookie("search_key",$key,0,"/",ereg_replace('www.','',$HTTP_HOST));
if(!eregi("[^[:space:]]+",$key)) {
	$keyfield = $_COOKIE['search_keyfield'];
	$key = $_COOKIE['search_key'];
}


include $_SERVER['DOCUMENT_ROOT'] . "/include/top.php";
//echo $selNewChk;

##### including board class files.
include $class_path . "class.Contents.php";
include $class_path . "class.BoardRecord.php";
include $class_path . "class.Page.php";
include $class_path . "class.Block.php";

##### connecting database.
include $_SERVER['DOCUMENT_ROOT'] . "/func/include.connect.php";

$selNewChk = $_POST['selNewChk'];
$page = $_GET['page'];
$keyfield = $_POST['keyfield'];
$key = $_POST['key'];
//echo $selNewChk;
//echo $selNewChk;

##### 페이지 설정
if(!$page)
   $page = 1;

#####기본적인 정렬순서
if(!$order){
	if ($_COOKIE['member_hospital']=="BRM") {
		$order="T1.input_date";
	} else {
	//	$order="T2.signdate";
		$order="T2.new_regist_num";
	}
	$stand="desc";
}

//if (!$selNewChk) $selNewChk = "new";

$join_Tbl_name = " regist_list T1 inner join CDR_hos_move T2 ";
$join_Tbl_name .= " on T1.regist_num=T2.regist_num  ";
if ($_COOKIE['member_id']=="cdc") $join_Tbl_name .= " and T1.signdate >= UNIX_TIMESTAMP('2013-01-01') ";
$join_Tbl_name .= " and T2.sid=(select sid from CDR_hos_move where T2.regist_num=regist_num order by sid desc limit 0,1) ";

if($_COOKIE['member_level'] =="M") {
$query_tol = "SELECT count(T1.sid) FROM ${join_Tbl_name} where T1.sid is not null ";

if ($_SERVER['REMOTE_ADDR']!="218.235.94.224" && $_SERVER['REMOTE_ADDR']!="218.235.94.239") {
	$query_tol .= " AND T2.new_id in (select id from user_binfo where member_level = 'Y') ";
}
}else if($_COOKIE['member_level'] =="I") {
$query_tol="SELECT count(T1.sid) FROM ${join_Tbl_name} where T1.sid is not null AND T2.new_id='" . $_COOKIE['member_id'] . "'";
}else if($_COOKIE['member_level'] =="P") {
$query_tol="SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') AND T2.new_reg_hospital in ('" . str_replace(";","','",$_COOKIE['member_admin_hospital']) . "')";
}else{
	if ($_COOKIE['member_id']=="viewer_cdc") {
		$query_tol="SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') AND T2.new_reg_hospital in ('" . str_replace(";","','",$_COOKIE['member_admin_hospital']) . "')";
	} else {
		$query_tol="SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') AND T2.new_reg_hospital='" . $_COOKIE['member_hospital'] . "'";
	}
}
if ($selNewChk == "new") {
	$query_tol.= " and cast(T1.yearv as unsigned)>=2022 ";
} else if ($selNewChk == "old") {
	$query_tol.= " and cast(T1.yearv as unsigned)<2022 ";
}

//echo $order;
//echo "<br>";
//echo $stand;
//echo $query_tol;
##### 게시물의 총 개수($tol_Record) 구하기
$tol_Record = $conn->getOne($query_tol);


if(!$keyfield || !eregi("[^[:space:]]+",$key)) {
  if ($keyfield=="hos_move") $hos_move_search = " and (T1.regist_num != T2.new_regist_num or T1.id != T2.new_id)";
  if($_COOKIE['member_level'] =="M") {
    $query="SELECT count(T1.sid) FROM ${join_Tbl_name} where T1.sid is not null" . $hos_move_search;
		if ($_SERVER['REMOTE_ADDR']!="218.235.94.224" && $_SERVER['REMOTE_ADDR']!="218.235.94.239") {
			$query .= " AND T2.new_id in (select id from user_binfo where member_level = 'Y') ";
		}
  }else if($_COOKIE['member_level'] =="I") {
	$query="SELECT count(T1.sid) FROM ${join_Tbl_name} where T1.sid is not null AND T2.new_id='" . $_COOKIE['member_id'] . "'" . $hos_move_search;
  }else if($_COOKIE['member_level'] =="P") {
    $query="SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') AND T2.new_reg_hospital in ('" . str_replace(";","','",$_COOKIE['member_admin_hospital']) . "')" . $hos_move_search;
  }else{
	 if ($_COOKIE['member_id']=="viewer_cdc") {
		$query="SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') AND T2.new_reg_hospital in ('" . str_replace(";","','",$_COOKIE['member_admin_hospital']) . "')" . $hos_move_search;
	 } else {
		$query="SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') AND T2.new_reg_hospital='" . $_COOKIE['member_hospital'] . "'" . $hos_move_search;
	 }
  }
	
}else{
	$encoded_key = urlencode($key);
  if($_COOKIE['member_level'] =="M") {
    $query = "SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null and " . $keyfield . " LIKE '%" . $key . "%'";  
		if ($_SERVER['REMOTE_ADDR']!="218.235.94.224" && $_SERVER['REMOTE_ADDR']!="218.235.94.239") {
			$query .= " AND T2.new_id in (select id from user_binfo where member_level = 'Y') ";
		}
  }else if($_COOKIE['member_level'] =="I") {
	$query = "SELECT count(T1.sid) FROM ${join_Tbl_name} where T1.sid is not null AND T2.new_id='" . $_COOKIE['member_id'] . "' and " . $keyfield . " LIKE '%" . $key . "%'";
  }else if($_COOKIE['member_level'] =="P") {
    $query = "SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') and " . $keyfield . " LIKE '%" . $key . "%' and T2.new_reg_hospital in ('" . str_replace(";","','",$_COOKIE['member_admin_hospital']) . "')";  
  }else{
	if ($_COOKIE['member_id']=="viewer_cdc") {
		$query = "SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') and " . $keyfield . " LIKE '%" . $key . "%' and T2.new_reg_hospital in ('" . str_replace(";","','",$_COOKIE['member_admin_hospital']) . "')";  
	}else {
		$query = "SELECT count(T1.sid) FROM ${join_Tbl_name}  where T1.sid is not null AND T2.new_id in (select id from user_binfo where member_level = 'Y') and " . $keyfield . " LIKE '%" . $key . "%' and T2.new_reg_hospital='" . $_COOKIE['member_hospital'] . "'";  
	}
  }
}
if ($selNewChk == "new") {
	$query.= " and cast(T1.yearv as unsigned)>=2022 ";
} else if ($selNewChk == "old") {
	$query.= " and cast(T1.yearv as unsigned)<2022 ";
}

//echo $selNewChk;
//echo $query;
##### 게시물의 총 개수($totalRecord=>검색결과) 구하기
$totalRecord = $conn->getOne($query);

$pageNav = new Page($page,$totalRecord,$num_per_page);
$totalPage = $pageNav->getTotalPage();
$firstRecord = $pageNav->getFirstRecordInPage();



if(!strcmp($order,"p_initials")){
	if(!strcmp($stand,"desc")){
		$order1_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&selNewChk=$selNewChk&order=p_initials&stand=asc\" onMouseOver=\"status='환자 이니셜을 오름차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"red\">환자 이니셜↓</font></a>";
	}else{
		$order1_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&selNewChk=$selNewChk&order=p_initials&stand=desc\" onMouseOver=\"status='환자 이니셜을 내림차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"red\">환자 이니셜↑</font></a>";
	}
}else{
	$order1_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&selNewChk=$selNewChk&order=p_initials&stand=desc\" onMouseOver=\"status='환자 이니셜을 내림차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"000000\">환자 이니셜↓</font></a>";
}

/*
if(!strcmp($order,"regist_num")){
	if(!strcmp($stand,"desc")){
		$order2_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&order=regist_num&stand=asc\" onMouseOver=\"status='코호트번호을 오름차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"red\">등록번호↓</font></a>";
	}else{
		$order2_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&order=regist_num&stand=desc\" onMouseOver=\"status='코호트번호을 내림차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"red\">등록번호↑</font></a>";
	}
}else{
	$order2_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&order=regist_num&stand=desc\" onMouseOver=\"status='코호트번호을 내림차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"000000\">등록번호↓</font></a>";
}


if(!strcmp($order,"signdate")){
	if(!strcmp($stand,"desc")){
		$order5_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&order=signdate&stand=asc\" onMouseOver=\"status='등록자료입력일을 오름차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"red\">등록자료입력일↓</font></a>";
	}else{
		$order5_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&order=signdate&stand=desc\" onMouseOver=\"status='등록자료입력일을 내림차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"red\">등록자료입력일↑</font></a>";
	}
}else{
	$order5_color="<a href=\"list.php?keyfield=$keyfield&key=$encoded_key&order=signdate&stand=desc\" onMouseOver=\"status='등록자료입력일을 내림차순으로 정렬합니다.';return true;\" onMouseOut=\"status='';return true;\"><font color=\"000000\">등록자료입력일↓</font></a>";
}
*/
?>
<script language="javascript">
<!--
	function popup_getCookie( name )
		{
				var nameOfCookie = name + "=";
				var x = 0;
				while ( x <= document.cookie.length )
				{
								var y = (x+nameOfCookie.length);
								if ( document.cookie.substring( x, y ) == nameOfCookie ) {
												if ( (endOfCookie=document.cookie.indexOf( ";", y )) == -1 )
																endOfCookie = document.cookie.length;
												return unescape( document.cookie.substring( y, endOfCookie ) );
								}
								x = document.cookie.indexOf( " ", x ) + 1;
								if ( x == 0 )
												break;
				}
				return "";
	}
	<?if ($_COOKIE['member_id']!="viewer_cdc") {?>
	if(popup_getCookie("query_noChk") != "popup_query"){
		window.open('/query/pop_query_noChk.php', 'pop_query_noChk','width=770,height=750,marginwidth=0,marginheight=0,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,top=10,left=10');
	}
	<?}?>

  function delete_check(regist_num){
    if(confirm('삭제하시겠습니까?')){
      location.href = 'delete.php?regist_num='+regist_num+'&page=<?=$page?>&selNewChk=<?=$selNewChk?>';
    }
  }  

	function win_open(url,width,height,scroll){
  window.open(url,"","width="+width+",height="+height+",scrollbars="+scroll+",status=no,resizable=no, top=0, left=0");
}

$(document).ready(function() {
	$('input[name=allChk]').bind('click', function() {
		if($(this).attr('checked')) {
			$('.isChk').each(function() {
				$(this).attr('checked', true);
			});
		} else {
			$('.isChk').each(function() {
				$(this).attr('checked', false);
			});
		}
	});
});

function isChkChange() {
	var f = document.isChkForm;

	sidStr = "";
	stsStr = "";
	$('.isChk').each(function() {
		if($(this).attr('checked')) {
			sidStr += $(this).attr('sid') + ",";
			stsStr += "Y,";
		} else {
			sidStr += $(this).attr('sid') + ",";
			stsStr += ",";
		}
	});

	f.sid.value = sidStr;
	f.status.value = stsStr;

	f.submit();

}

// -->
</script>



<?
##### 데이터베이스 연결 
$query = "SELECT T1.*, T2.new_regist_num,T2.new_reg_hospital,T2.new_id,T2.new_hos_name,T2.new_d_name FROM ${join_Tbl_name}  WHERE T1.sid is not null ";
if ($selNewChk == "new") {
	$query.= " and cast(T1.yearv as unsigned)>=2022 ";
} else if ($selNewChk == "old") {
	$query.= " and cast(T1.yearv as unsigned)<2022 ";
}

if($_COOKIE['member_level']=="M"){
	if ($_SERVER['REMOTE_ADDR']!="218.235.94.224" && $_SERVER['REMOTE_ADDR']!="218.235.94.239") {
		$level_query = " AND T2.new_id in (select id from user_binfo where member_level = 'Y') ";
	}
}else if($member_level =="I") {
  $level_query = " AND T2.new_id='" . $_COOKIE["member_id"] . "'";
}else if($member_level =="P") {
  $level_query = " AND T2.new_reg_hospital in ('" . str_replace(";","','",$_COOKIE["member_admin_hospital"]) . "')";
}else{
	if ($_COOKIE['member_id']=="viewer_cdc") {
		$level_query = " AND T2.new_reg_hospital in ('" . str_replace(";","','",$_COOKIE["member_admin_hospital"]) . "')";
	} else {
		$level_query = " AND T2.new_id in (select id from user_binfo where member_level = 'Y') AND T2.new_reg_hospital='" . $_COOKIE["member_hospital"] . "'";
	}
}

$query .= $level_query;

if ($_COOKIE['member_level']=="M") {
	$query_orderby = " ORDER BY left(T1.input_date,4) desc, ". $order ." ". $stand;
} else {
	$query_orderby = " ORDER BY ". $order ." ". $stand;
}
if(!$keyfield || !eregi("[^[:space:]]+",$key)) {
	$make_query = $query . $hos_move_search . $query_orderby;
	$query .= $hos_move_search . $query_orderby . " LIMIT " . $pageNav->getFirstRecordInPage() . ", " . $num_per_page;
} else {
	$make_query = $query . " AND  " . $keyfield . " LIKE '%" . $key . "%' " . $query_orderby;
	$encoded_key = urlencode($key);
	$query .= " AND  " . $keyfield . " LIKE '%" . $key . "%' ". $query_orderby . " LIMIT " . $pageNav->getFirstRecordInPage() . ", " . $num_per_page; 
}
if ("218.235.94.239" == $_SERVER['REMOTE_ADDR']) //echo $query;
?>
<form name="isChkForm" method="post" action="isChkChange.php">
	<input type="hidden" name="page" value="<?=$page?>" />
	<input type="hidden" name="keyfield" value="<?=$keyfield?>" />
	<input type="hidden" name="key" value="<?=$encoded_key?>" />
	<input type="hidden" name="sid" />
	<input type="hidden" name="status" />
</form>
<form method="post" action="<?=$PHP_SELF?>">

      <table border='0' cellpadding='0' cellspacing='0' width='100%'>
        <tr height='37'>
          <td align="center">
          <table border="0" cellpadding="0" cellspacing="0" width="975" background='/image/bar_bg_r.gif' style='background-repeat:no-repeat;' height="57">
          <tr>
            <td style='padding-left:180px;'>
							<select name="selNewChk" id="selNewChk">
								<option value="all" <? if ($selNewChk == "all") echo "selected"; ?>>전체</option>
								<option value="new" <? if ($selNewChk == "new") echo "selected"; ?>>2022년~</option>
								<option value="old" <? if ($selNewChk == "old") echo "selected"; ?>>~2021년</option>
							</select>
              <input type="radio" name="keyfield" style='border:0' value="p_initials" <?if ($keyfield=="p_initials") echo 'checked';?> /> 환자 이니셜&nbsp;
              <input type="radio" name="keyfield" style='border:0' value="T2.new_regist_num" <?if ($keyfield=="T2.new_regist_num") echo 'checked';?>> 피험자 번호&nbsp;&nbsp;
              <input type="text" name="key" size="15" value="<?=$key?>"/>
			  <?if($_COOKIE['member_level']=="M"){?>
              <input type="radio" name="keyfield" style='border:0' value="hos_move" <?if ($keyfield=="hos_move") echo 'checked';?>> 병원 및 담당자 변경&nbsp;&nbsp;
			  <?}?>
              <input type="image" src="/image/search.gif" border="0" alt="검색" align="absmiddle" style="border:0px;">
			  &nbsp; <!-- <a href="make_excel.php?make_query=<? echo $make_query  ?>"><img src="/image/bt6.gif" border=0></a> -->
            </td>
          </tr>
          </table>
          </td>
        </tr>

        <tr>
          <td bgcolor='FFFFFF'>
            <table border='0' cellpadding='0' cellspacing='0' width="100%">
              <tr>
                <td style='padding-top:15px;padding-bottom:10px'>
                  <table border='0' cellpadding='0' cellspacing='0' width='100%' align='center'>
                    <tr height='15'>
                      <td style="padding-left:20px;font-weight:bold;background:url('/image/icon02.gif') 2px no-repeat;">
                      <b>신환자 등록 리스트</b> : 총 <?=$tol_Record?> 명 환자 중 <? echo $totalRecord ?> 명의 환자가 검색되었습니다.
                      </td>
                      <td width='190' background='/image/data/box_bg.gif' align='right'></td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td align='center'>
                  <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                    <tr>
                      <td bgcolor='C8C8C8'>
                        <table border='0' cellpadding='5' cellspacing='1' width='100%'>
                          <tr bgcolor='F1F1F1' align='center' height='35'>
						  <? if($_COOKIE['member_level'] == "M") { ?>
                            <td width="5%"><input type="checkbox" name="allChk"></td>
						  <? } ?>
                            <td width="5%"><b>NO</b></td>
                            <td width="9%"><b><? echo $order1_color ?></b></td>
                            <td width="8%"><b>나이/성별</b></td>
                            <td width="11%"><b>피험자번호</b></td>
							<td width="7%"><b>담당의</b></td>
                            <td width="8%"><b>동의일</b></td>
                            <td width="7%"><b>등록</b></td>
							<td width="9%"><b>추적<br>(증상/징후)</b></td>
							<td width="9%"><b>약제/수술<br>입원/임신</b></td>
                            <td width="8%"><b>병원이동</b></td>
                            <td width="7%"><b>진행상태</b></td>
                            <td><b>관리</b></td>
                          </tr>
<?
$result = $conn->query($query);
if(DB::isError($result)) {
   die($result->getMessage());
}

$virtualRecordNo = $pageNav->getVirtualRecordNoInPage($totalRecord);
$kk = 0;
while ($col = $result->fetchRow(DB_FETCHMODE_ASSOC)) {

if($kk % 2) {
   $LIST_TD_COLOR="#F0F0F0";
}else {
   $LIST_TD_COLOR="#FFFFFF";
}

$query_v = "select * from CDR_Table WHERE regist_num ='$col[regist_num]'";
$result_v = $conn->query($query_v);
//die($query);
	if(DB::isError($result_v)) {
	   die($result_v->getMessage());
	}
$row_v = $result_v->fetchRow(DB_FETCHMODE_ASSOC);
$age_chk = explode('-',$col['jumin_no']);

?>
                          <tr bgcolor="<? echo $LIST_TD_COLOR ?>" align='center'>
						  <? if($_COOKIE['member_level'] == "M") { ?>
                            <td width="5%"><input type="checkbox" name="isChk" class="isChk" sid="<?=$col['sid']?>" <?=$col['isChk'] == "Y" ? "checked" : ""?>></td>
						  <? } ?>
                            <td><? echo $virtualRecordNo ?></td>
                            <td><? echo $col['p_initials'] ?></td>
                            <td><? echo chkAge($age_chk[0], $age_chk[1]); ?> / <? echo $col['p_sex']; ?></td>
                            <td><? echo $col['new_regist_num'] ?><?if ($_COOKIE['member_level']=="M") echo "<br>" . $col['new_hos_name'] ?></td>
                            <td><?// echo $col[hos_no] ?> <? echo $col['new_d_name']; ?></td>
                            <td><? echo $col['input_date'] ?></td>
                            <?
                            if($col['new_id']==$_COOKIE['member_id'] || $_COOKIE['member_level']=="M"){  
                            ?>
								<td><a href='view.php?regist_num=<? echo $col['regist_num'] ?>&page=<?=$page?>&keyfield=<?=$keyfield?>&key=<?=$encoded_key?>&selNewChk=<?=$selNewChk?>'><img src="/image/bt10.gif" border="0"></a></td>
<?
$query_E = "select user_id,final_check_date, death_followup_check from Final_Table where regist_num='" . $col['regist_num'] . "' order by final_check_date desc LIMIT 0,1";
$result_E = $conn->query($query_E);
if(DB::isError($result_E)) {
	 die($result_E->getMessage());
}
$row_E = $result_E->fetchRow(DB_FETCHMODE_ASSOC);

?>
								<td><a href='f_list.php?regist_num=<? echo $col['regist_num'] ?>&page=<?=$page?>&keyfield=<?=$keyfield?>&key=<?=$encoded_key?>&selNewChk=<?=$selNewChk?>'><img src="/image/bt11.gif" border="0"></a></td>
								<td><a href='view.php?regist_num=<? echo $col['regist_num'] ?>&tab_chk=u&page=<?=$page?>&keyfield=<?=$keyfield?>&key=<?=$encoded_key?>&selNewChk=<?=$selNewChk?>'><img src="/image/bt10.gif" border="0"></a></td>
                            <?
                            }else{  
                            ?>
								<td><a href='view.php?regist_num=<? echo $col['regist_num'] ?>&page=<?=$page?>&keyfield=<?=$keyfield?>&key=<?=$encoded_key?>&selNewChk=<?=$selNewChk?>'><img src="/image/bt3.gif" border="0"></a></td>
								<td><a href='f_list.php?regist_num=<? echo $col['regist_num'] ?>&page=<?=$page?>&keyfield=<?=$keyfield?>&key=<?=$encoded_key?>&selNewChk=<?=$selNewChk?>'><img src="/image/bt3.gif" border="0"></a></td>
								<td><a href='view.php?regist_num=<? echo $col['regist_num'] ?>&tab_chk=u&page=<?=$page?>&keyfield=<?=$keyfield?>&key=<?=$encoded_key?>&selNewChk=<?=$selNewChk?>'><img src="/image/bt3.gif" border="0"></a></td>
                            <?
                            }  
                            ?>
                            <td>
								<a href="JavaScript:win_open('pop_hos_history.php?regist_num=<? echo $col['regist_num'] ?>','700','350','Yes');">
								<?=($col['regist_num'] != $col['new_regist_num'])?"병원이동":""?>
								<?=($col['regist_num'] == $col['new_regist_num'] && $col['id'] != $col['new_id'])?"담당의 변경":""?>
								</a>
							</td>
                            <!--진행상태-->
                            <td align="center">
                            <?
							if ($row_E['death_followup_check'] == "2") {
								echo "<font color='#FF0000'><b>[사망]</b></font>";
							} else {
								$query_end = "SELECT count(sid) FROM Final_Table WHERE regist_num='$col[regist_num]'";
								$end_cnt = $conn->getOne($query_end);
								if($end_cnt>0){
								  echo "<b>" . $end_cnt . "차 평가</b>";
								}else{
								  echo "<img src='/image/bt4.gif' border='0'>";
								}
							}
                            ?>
                            </td>                     
                            <td>
                            <?
                            //if($_COOKIE[member_id]=="webmaster"){  
							if($_COOKIE['member_id']!="cdc"){  
                            ?>
                            <a href="JavaScript:win_open('p_report.php?regist_num=<? echo $col['regist_num'] ?>','900','550','Yes');"><img src='/image/bt_report.gif' width='90' height='16' alt='Patient Report'></a>
                            <?
                            }  
                            ?>
                            <?
                            if($col['new_id']==$_COOKIE['member_id'] || $_COOKIE['member_level']=="M"){  
                            ?>
                            <a href="modify_form.php?regist_num=<?=$col['regist_num']?>&page=<?=$page?>&keyfield=<?=$keyfield?>&key=<?=$encoded_key?>&selNewChk=<?=$selNewChk?>"><img src='/icon/a_modify01.gif' width='26' height='14' alt='수정'></a>&nbsp;/&nbsp;<a href="javascript:delete_check('<? echo $col['regist_num'] ?>')"><img src='/icon/a_delete02.gif' width='26' height='14' alt='삭제'></a><!-- &nbsp;/&nbsp;<a href="JavaScript:win_open('make_print.php?regist_num=<? echo $col[regist_num] ?>',900,600,'yes');"><img src='/icon/a_print.gif' height='14' alt='인쇄'></a> -->
                            <?
                            }  
                            ?>
                            </td>
                          </tr>
<?
$kk++;
$virtualRecordNo--;
}
if($kk=='0') {
  ?>
                           <tr bgcolor='FFFFFF' align='center' height='28'>
                            <td colspan="13">등록된 환자가 없습니다.</td>
                          </tr>
  <?
}
?>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>  
			<tr><td height="10" colspan="11"></td></tr>
			<? if($_COOKIE['member_level'] == "M") { ?>
			<tr><td height="10" colspan="11"><img src="/icon/but_04.gif" style="cursor: pointer;" onclick="isChkChange();"></td></tr>
			<? } ?>
              <tr height='50'>
                <td align='center'>
				<?
				$blockNav = new Block("",$totalPage,$page_per_block);
				$totalBlock = $blockNav->getTotalBlock();
				$blockNav->setBlock($page);
				$block = $blockNav->getBlock();
				$firstPageInBlock = $blockNav->getFirstPageInBlock();
				$lastPageInBlock = $blockNav->getLastPageInBlock();
				if($block >= $totalBlock) 
					 $lastPageInBlock = $totalPage;
				?>
				<table width="100%" border="0" cellspacing="0" cellpadding="3" align="center"  valign="top">
				<tr>
					 <td align="center"  valign="top">
				<?
				if($blockNav->loadPreviousBlock()) 
					 echo "<a href=\"list.php?page=$firstPageInBlock&keyfield=$keyfield&key=$encoded_key&selNewChk=$selNewChk\" onMouseOver=\"status='이전 $page_per_block 개 페이지를 불러들입니다.';return true;\" onMouseOut=\"status='';return true;\">[이전 ${page_per_block}개]</a>";
					 
				for($direct_page = $firstPageInBlock+1; $direct_page <= $lastPageInBlock; $direct_page++) {
					 if($page == $direct_page) {
							echo "<b>[$direct_page]</b>";
					 } else {
							echo "<a href=\"list.php?page=$direct_page&keyfield=$keyfield&key=$encoded_key&selNewChk=$selNewChk\" onMouseOver=\"status='$direct_page 페이지로 이동합니다.';return true;\" onMouseOut=\"status='';return true;\">[$direct_page]</a>";
					 }
				}

				if($blockNav->loadNextBlock()) {
					 echo "<a href=\"list.php?page=" . ($lastPageInBlock + 1) . "&keyfield=$keyfield&key=$encoded_key&selNewChk=$selNewChk\" onMouseOver=\"status='다음 $page_per_block 개 페이지를 불러들입니다.';return true;\" onMouseOut=\"status='';return true;\">[다음 ${page_per_block}개]</a>";
				}
				?>
					 </td>
				</tr>
				<tr><td height="10" colspan="11"></td></tr>	
				</table>
				</td>
				</tr>
				<tr height='20'><td></td></tr>
			</table>
		</td>
	</tr>
        
  </table>
</form>
<?
$conn->disconnect();
include $_SERVER['DOCUMENT_ROOT'] . "/include/bottom_content.php";
?>
>>>>>>> 75ea3cd60180c16f2bada2c7eda6cd342d2d3873
