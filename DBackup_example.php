<?php
	include 'DBackup.Class.php';
//	$Tabls=array('Table1','Table2','Table3');
	$DBackup = New DBackup('localhost','root','','dhesab');
	if($DBackup -> DBackup_Start())
		echo 'Backup Was Created';
	else
		echo 'Error';
?>
