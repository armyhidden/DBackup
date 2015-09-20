<?php
/*
###################################################
#	Dump data from MySQL database
#	Name    : DBackup (2011-23-2)
#	Version : 1.0
#	Author  : Army.Hidden
#	Description :
#		$Backup = New DBackup('Location','Username','Password','DBName' [,'Table Name']);
#	Example :
#		$Backup = New DBackup('localhost','root','1234','Blog');
#			And
#		$Tables=array('Table1','Table2','Table3');
#		$Backup = New DBackup('localhost','root','1234','Blog',$Tables);
###################################################
*/
class DBackup
{
	private $GBLocation;
	private $GBUsername;
	private $GBPassword;
	private $GBDBName;
	private $GBTable;
	private $GBDump;
	public function __construct($Location,$Username,$Password,$DBName,$Table = Null)
	{
		$this -> GBLocation = $Location;
		$this -> GBUsername = $Username;
		$this -> GBPassword = $Password;
		$this -> GBDBName = $DBName;
		$this -> GBTable = $Table;
	}
	public function DBackup_Start()
	{
		$this -> DBackup_DBConnect();
		$MySQLinfo = $this -> DBackup_MySQLinfo();
		$Tablelist = $this -> DBackup_Tablelist();
		set_time_limit(0);
		$this -> GBDump = '
-- PhpGrp.Com [ Army.Hidden ]
-- http://www.phpmyadmin.net
-- 
-- Host:'.$this -> GBLocation.'
-- Generation Time:'.date(" M j, Y ").' at '.date(" G:i A ").'
-- Server version:'.$MySQLinfo[0].'
-- Server User:'.$MySQLinfo[1].'
-- 
-- Database:'.$this -> GBDBName.'
-- 
-- --------------------------------------------------------
';
		if($this -> GBTable)
		{
			Foreach ($this -> GBTable as $value)
			{
				$this -> DBackup_Tableinfo($value);
				$this -> DBackup_Columns($value);
			}
		}else{
			Foreach ($Tablelist as $value)
			{
				$this -> DBackup_Tableinfo($value);
				$this -> DBackup_Columns($value);
			}
		}
		return $this -> DBackup_Created();
	}
	private function DBackup_DBConnect()
	{
		@mysql_connect($this -> GBLocation,$this -> GBUsername,$this -> GBPassword);
		mysql_select_db($this -> GBDBName);
		mysql_query("SET character_set_results = 'utf8'");
	}
	private function DBackup_MySQLinfo()
	{
			$iRow = @mysql_fetch_array(mysql_query("SELECT @@version"));	$return[] = $iRow['@@version'];
			$iRow = @mysql_fetch_array(mysql_query("SELECT user();"));		$return[] = $iRow['user()'];
		return $return;
	}
	private function DBackup_Tablelist()
	{
		$return='';
		$iResult = @mysql_query("SHOW TABLES ;"); 
			while($iRow = @mysql_fetch_array($iResult)){
				$return[] = $iRow[0];	
			}
		return $return;
	}
	private function DBackup_Tableinfo($TABLE)
	{
$this -> GBDump .="
-- 
-- Table Structure For Table `$TABLE`
--
";
		$iRow = @mysql_fetch_array(mysql_query("SHOW CREATE TABLE $TABLE"));
		$return = $iRow[1].";\n\n";
		$this -> GBDump .= $return;
	}
	private function DBackup_Columns($TABLE)
	{
		$return ='';
		$iResult = @mysql_query("Select * From $TABLE"); 
		$iResult_iRow = mysql_num_rows($iResult);
		if($iResult_iRow)
		{
		$return = "
-- 
-- Dumping Data For Table `$TABLE`
-- 
INSERT INTO `$TABLE` VALUES \n";
		}
		$iResult = @mysql_query("SHOW COLUMNS FROM $TABLE"); 
		$iResult_iRow = mysql_num_rows($iResult);		
		$this -> GBDump .= $return ;
		echo $this -> DBackup_ColumnsValues($TABLE,$iResult_iRow);
	}
	private function DBackup_ColumnsValues($TABLE,$Tcc)
	{
		$return='';
		$iResult = @mysql_query("Select * From $TABLE"); 
		$iResult_iRow = mysql_num_rows($iResult);
			for ($ir=1 ; $ir <= $iResult_iRow ; $ir++)
			{
				$iRow = @mysql_fetch_array($iResult);
				$return .= "(";
					for ($i=1 ; $i <= $Tcc ; $i++)
					{
						if($i == $Tcc)
							$return .= "'".str_replace("'","''",$iRow[$i-1])."'";
						else
							$return .= "'".str_replace("'","''",$iRow[$i-1])."',";
					}
				if($ir == $iResult_iRow)
					$return .= ");\n";
				else
					$return .= "),\n";

			}
		$this -> GBDump .= $return;
	}
	private function DBackup_Created()
	{
		$tableslist="";
		if($this -> GBTable)
		{
			foreach($this -> GBTable as $value)
			{
				$tableslist .="-$value";
			}
			$Name = $this -> GBDBName."(".$tableslist.")(".time().")".".sql";
			$Name = str_replace("(-","(",$Name);
		}else{
			$Name = $this -> GBDBName."(AllTable)(".time().")".".sql";
		}	
				$fwrite = fopen($Name,"w");
				if(fwrite($fwrite,$this -> GBDump)){
					return $Name;
				}
				fclose($fwrite);
	}
}
?>
