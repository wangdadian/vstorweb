<?php 
require_once("./include/function.php");
require_once("./include/nfs_client.php");

if( !IsVisExisted())
	exit("no vis server!");


$lang=load_lang();

$vod_server_config_str=array(
	"VOD����������",
	"VOD Server Config"
);
$storage_ip_str=array(
	"�洢������IP��ַ",
	"Storage ip address"
);
$nfs_shared_list_str=array(
	"�����б�",
	"Shared List"
);
$get_target_str=array(
	"��ȡĿ��",
	"Get targets"
);
$target_dir_str=array(
	"Ŀ��Ŀ¼",
	"Target"
);
$hosts_to_share_str=array(
	"����������/����",
	"Network/Host Shared to"
);
$storage_str=array(
	"�洢������",
	"Storage Server"
);
$operation_str=array(
	"����",
	"Operate"
);
$mount_str=array(
	"����",
	"Mount"
);
$unmount_str=array(
	"ж��",
	"Unmount"
);
$storage_connect_state_str=array(
	"VOD����������Ŀ¼������Ϣ",
	"VOD Server Mounted Information"
);
$mounted_shareddir_list_str=array(
	"���ص�NFS����Ŀ¼�б�",
	"Mounted NFS List"
);
$ip_error_str=array(
	"IP��ʽ����",
	"It's not a IP address"
);
$ok_str=array(
	"�ɹ�",
	"OK"
);
$failed_str=array(
	"ʧ��",
	"failed"
);
$none_str=array(
	"- �� -",
	"- None -"
);
$confirm_mount_str=array(
	"ȷ�Ϲ��أ�",
	"Confirm to mount?"
);
$confirm_unmount_str=array(
	"ȷ��ж�أ�",
	"Confirm to unmount?"
);
$has_mounted_str=array(
	"�ѹ���",
	"Has Mounted"
);
?>

<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\" <html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<link rel="stylesheet" href="css/target.css" type="text/css" />
<script defer type="text/javascript" src="js/pngfix.js"></script>
<script type="text/javascript">
function do_submit(value, msg)
{
	document.storage_form.text1.value = value;
	if( confirm(msg) )
		return true;
	else
		return false;
}
function do_submit2(value1, value2, msg)
{
	document.storage_state_form.text2.value = value1 + ";" + value2;
	if( confirm(msg) )
		return true;
	else
		return false;
}
</script>
</head>

<body>
<?php 
/*
 * ������������
 */
/*
if( isset($_GET['access']) && $_GET['access']=="yes" )
{
	
}
else
{
	exit("No Access!");
}
*/

$storage_ip = "";
if( isset($_GET['sip']) )
{
	$storage_ip = trim($_GET['sip']);
}
if( isset($_POST['storage_server_ip']) )
{
	$storage_ip = trim($_POST['storage_server_ip']);
}

$nfs_client = new NfsClient();

// ���ش���
$sharedir = "";
if( $storage_ip != "" && IsIpOk($storage_ip) )
{
	if( isset($_POST['text1']) )
	{
		$sharedir = $_POST['text1'];
		//��֤��������/mntĿ¼����
		if(!preg_match("|^(/mnt)|i", $sharedir))
			$mountdir = "/mnt/" . $sharedir;
		else
			$mountdir = $sharedir;
	
		// ����Ŀ¼
		exec("export LANG=C; /usr/bin/sudo /bin/mkdir -p " . $mountdir);
		$nfs_client->Mount($sharedir, $mountdir, $storage_ip);
	}
}

// ж�ش���
$sharedir = "";
if( isset($_POST['text2']) )
{
	$str = $_POST['text2'];
	$items = explode(";", $str);
	$sharedir = $items[0];
	$server = $items[1];
	$nfs_client->UnMount($sharedir, $server);
}
?>

<div>
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $vod_server_config_str[$lang];?></td>
	</tr>
</table>

<form name="storage_ip_form" id="storage_ip_form" action="va_vod_target.php?sip=<?php print $storage_ip;?>" method="post">
<table width="70%" border="0" cellpadding="6" align="center">
  <tr>
    <td class="field_title"><?php print $storage_ip_str[$lang];?></td>
    <td class="field_data1">
		<input name="storage_server_ip" maxlength="15" type="text" class="field_data1" value="<?php print $storage_ip;?>">
		&emsp;
		<input type="submit" name="storage_ip_submit" value="<?php print $get_target_str[$lang];?>">
	</td>
  </tr>
</table>
</form>

<form name="storage_form" id="storage_form" action="va_vod_target.php?sip=<?php print $storage_ip;?>" method="post">
  <input type="hidden" name="text1" id="text1" value="">
  <table width="70%" border="0" align="center" cellpadding="6">
    <tr>
      <td colspan="3" class="title"><?php print  $storage_ip . ": " . $nfs_shared_list_str[$lang];?></td>
    </tr>
    <tr>
      <td class="field_title"><?php print $target_dir_str[$lang];?></td>
      <td class="field_title"><?php print $hosts_to_share_str[$lang];?></td>
	  <td class="field_title"><?php print $operation_str[$lang];?></td>
    </tr>
<?php 
$bHasItem = FALSE;
if( $storage_ip != "" && IsIpOk($storage_ip) )
{
	$shareList = $nfs_client->GetShareLists($storage_ip);
	if($shareList !== FALSE)
	{
		$td_class = "field_data1";
		foreach( $shareList as $entry )
		{
			print "<tr>";
			print "<td class=\"{$td_class}\">{$entry['sharedir']}</td>";
			print "<td class=\"{$td_class}\">{$entry['hosts']}</td>";
			print "<td class=\"{$td_class}\">";
			if( $nfs_client->IsMounted($entry['sharedir'], $storage_ip) )
			{
				print $has_mounted_str[$lang];
			}
			else
			{
				print "<input type=\"submit\" name=\"submit\" onClick=\"return do_submit('{$entry['sharedir']}', '{$confirm_mount_str[$lang]}');\" value=\"{$mount_str[$lang]}\" />";
			}
			print "</td>";
			print "</tr>";
			
			$bHasItem = TRUE;
			if($td_class == "field_data1")
			{
				$td_class = "field_data2";
			}
			else
			{
				$td_class = "field_data1";
			}
		}
	} 
}	
if($bHasItem == FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}

?>
  </table>
</form>
 
 <!-- -->
<table align="center" width="100%">
	<tr>
	<td class="bar_nopanel"><?php print $storage_connect_state_str[$lang];?></td>
	</tr>
</table>

<form name="storage_state_form" id="storage_state_form" action="va_vod_target.php?sip=<?php print $storage_ip;?>" method="post">
  <input type="hidden" name="text2" id="text2" value="">
  <table width="70%" border="0" align="center" cellpadding="6">
    <tr>
      <td colspan="3" class="title"><?php print  $mounted_shareddir_list_str[$lang];?></td>
    </tr>
    <tr>
	  <td class="field_title"><?php print $storage_str[$lang];?></td>
      <td class="field_title"><?php print $target_dir_str[$lang];?></td>
      <td class="field_title"><?php print $operation_str[$lang];?></td>
    </tr>
<?php 
$bHasItem = FALSE;

$mountedList = $nfs_client->GetNfsMounted();
if( $mountedList !== FALSE )
{
	$td_class = "field_data1";
	foreach( $mountedList as $entry )
	{
		print "<tr>";
		print "<td class=\"{$td_class}\">
		<a href=\"http://{$entry['server']}\" target=\"_blank\" >{$entry['server']}</a>
		</td>";
		print "<td class=\"{$td_class}\">{$entry['sharedir']}</td>";
		print "<td class=\"{$td_class}\">";
		if(IsDirUsing($entry['mountdir']))
		{
			$disabled = "disabled=\"disabled\"";
		}
		else
		{
			$disabled = "";
		}
		print "<input type=\"submit\" name=\"submit\" {$disabled} onClick=\"return do_submit2('{$entry['sharedir']}', '{$entry['server']}', '{$confirm_unmount_str[$lang]}');\" value=\"{$unmount_str[$lang]}\" />";
		print "</td>";
		print "</tr>";
		
		$bHasItem = TRUE;
		if($td_class == "field_data1")
		{
			$td_class = "field_data2";
		}
		else
		{
			$td_class = "field_data1";
		}
	}
}

if($bHasItem == FALSE)
{
	print "<tr><td class=\"field_data2\" colspan=\"3\">{$none_str[$lang]}</td></tr>";
}
?>
  </table>
</form>

</div>
</body>
</html>