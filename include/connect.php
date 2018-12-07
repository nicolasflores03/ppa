<?php
//DEV
/*
$serverName = "192.168.1.37, 1433"; //serverName\instanceName, portNumber (default is 1433)
$connectionInfo = array( "Database"=>"EAMDEV", "UID"=>"EAMDEV", "PWD"=>"EAMDEVpASSWORD1401()");
*/
//UAT

// $serverName = "NFLORESW7L/MSSQL"; //serverName\instanceName, portNumber (default is 1433)
$serverName = "127.0.0.1, 1433"; //serverName\instanceName, portNumber (default is 1433)
//$connectionInfo = array( "Database"=>"EAMPRD", "UID"=>"sa", "PWD"=>"root");
// $connectionInfo = array( "Database"=>"test");
$connectionInfo = array( "Database"=>"EAMPRD", "UID"=>"sa", "PWD"=>"root");
/*
$serverName = "localhost\SQLEXPRESS"; //serverName\instanceName, portNumber (default is 1433)
$connectionInfo = array( "Database"=>"EAMDEV4", "UID"=>"sa", "PWD"=>"P3ople4u");
*/

$conn = sqlsrv_connect( $serverName, $connectionInfo);

if(!$conn) {
     echo "Connection could not be established.<br />";
     die( print_r( sqlsrv_errors(), true));
}


?>