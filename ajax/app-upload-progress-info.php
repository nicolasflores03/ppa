<?php
if (function_exists("uploadprogress_get_info")) {
    $info = uploadprogress_get_info($_GET['id']);
} else {
    $info = "upload progress function does not exist.";
}
echo json_encode($info);
?>