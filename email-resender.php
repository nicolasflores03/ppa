<?php
//Include external Files
include("include/connect.php");
include("class/crud.php");

//phpinfo();
$crudapp = new crudClass();


$emailArray = $crudapp->checkEmailResend($conn);

$to="";
$subject="";
$message="";
foreach($emailArray as $emailDetails){
$id = $emailDetails['MAE_ID'];
$to = $emailDetails['MAE_EMAILRECIPIENT'];
$subject = $emailDetails['MAE_SUBJECT'];
$message = $emailDetails['MAE_BODY'];

$crudapp->sentEmail2($conn,$id,"eam@fdcutilities.com",$to,$subject,$message);

//echo $id."--".$to."--".$subject."--".$message."<br>";
}

//$crudapp->sentEmail($conn,"adoboresthouse@gmail.com","bbma","Adobo Resthouse 2014",$message);
	
?>
