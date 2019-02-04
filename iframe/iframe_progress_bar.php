<?php $id = @$_GET['id'];?>
<html> 
<head> 
<link rel="stylesheet" href="../css/style.css"  media="screen" rel="stylesheet" type="text/css"/>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252"> 
<script src="../js/jquery.min.js"></script>
<script type='text/javascript'>  
    var progressStarted  = false;
	$(document).ready(function(){
		// window.setTimeout("getProgress()", 500);
        getProgress();
	});

    function getProgress() {
		$.get("../ajax/app-upload-progress-info.php?id=<?php echo $id;?>&" + Math.random(), {},
			function(data)	
            {
                var percent = 0;
                if(data != null){
                    if(!progressStarted) {
                        progressStarted = true;
                    }
                    percent = parseInt(data.bytes_uploaded / data.bytes_total * 100);
                    $('#bar').css("width", percent + "%");	
                    $('#bar').html(percent + "%");
                } else if(data == null &&  progressStarted ) {
                    percent = 100;
                    $('#bar').css("width", "100%");	
                    $('#bar').html("100%");
                }

                if(percent < 100) {
                    window.setTimeout("getProgress()", 500);
                }
            },'JSON'
		);
	}
</script>
</head> 
<body style="margin: 0px; "> 
<form id="form_uploader" onsubmit="on_submit_form()" name="form_uploader" action="<?php echo $_SERVER['PHP_SELF']?>?login=<?php echo $login;?>&year=<?php echo $year?>&version=<?php echo $version?>&reference_no=<?php echo $ref_no?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="UPLOAD_IDENTIFIER" id="progress_key" value="<?php echo $id;?>" /> 
    <table width="100%" cellspacing="0" cellpadding="0" border="1" class="listpop-progress">	
        <tr id="tr_progress">
            <td>Uploading Progress</td>
            <td colspan="2">
                <div id="progress">
                    <div id="bar" class="active" style="width:100%">In Progress</div>
                </div>
            </td>
        </tr>
    </table>
</form>
</html>