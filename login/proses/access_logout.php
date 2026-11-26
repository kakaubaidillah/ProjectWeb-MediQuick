<?php

session_start();
session_destroy();
header("location: ../../dashboard/user/index.php");
exit();

?>