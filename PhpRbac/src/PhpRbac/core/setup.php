<?php
#TODO: test on sqlite

if ($adapter=="pdo_mysql")
{
	try {
		jf::$Db=new PDO("mysql:host={$host};dbname={$dbname}",$user,$pass);
	}
	catch (PDOException $e)
	{
		if ($e->getCode()==1049) //database not found
			InstallPDOMySQL($host,$user,$pass,$dbname);
		else
			throw $e;
	}
}
elseif ($adapter=="pdo_sqlite")
{
	if (!file_exists($dbname))
		InstallPDOSQLite($host,$user,$pass,$dbname);
	else
		jf::$Db=new PDO("sqlite:{$dbname}",$user,$pass);
// 		jf::$Db=new PDO("sqlite::memory:",$user,$pass);
}
else # default to mysqli 
{
	jf::$Db=new mysqli($host,$user,$pass,$dbname);
	if(jf::$Db->connect_errno==1049);
		InstallMySQLi($host,$user,$pass,$dbname);
}
function GetSQLs($dbms)
{
	$sql=file_get_contents(__DIR__."/sql/{$dbms}.sql");
	$sql=str_replace("PREFIX_",jf::tablePrefix(),$sql);
	return explode(";",$sql);
}
function InstallPDOMySQL($host,$user,$pass,$dbname)
{
	$sqls=GetSQLs("mysql");
	$db=new PDO("mysql:host={$host};",$user,$pass);
	$db->query("CREATE DATABASE {$dbname}");
	$db->query("USE {$dbname}");
	if (is_array($sqls))
		foreach ($sqls as $query)
		$db->query($query);
	jf::$Db=new PDO("mysql:host={$host};dbname={$dbname}",$user,$pass);
	jf::$RBAC->Reset(true);
}
function InstallPDOSQLite($host,$user,$pass,$dbname)
{
	jf::$Db=new PDO("sqlite:{$dbname}",$user,$pass);
	$sqls=GetSQLs("sqlite");
	if (is_array($sqls))
		foreach ($sqls as $query)
		jf::$Db->query($query);
	jf::$RBAC->Reset(true);
}
function InstallMySQLi($host,$user,$pass,$dbname)
{
	$sqls=GetSQLs("mysql");
	$db=new mysqli($host,$user,$pass);
	$db->query("CREATE DATABASE {$dbname}");
	$db->select_db($dbname);
	if (is_array($sqls))
		foreach ($sqls as $query)
		$db->query($query);
	jf::$Db=new mysqli($host,$user,$pass,$dbname);
	jf::$RBAC->Reset(true);
}