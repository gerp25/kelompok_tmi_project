<?php  
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$con  = mysqli_connect('localhost','root','','db_resto'); 

if (!$con) 
{
    die('Connect Error: ' . mysqli_connect_errno());
}


function base_url($url = null)

  {
    $base_url = "";
    if ($url != null)
    {
    	return $base_url."/".$url;
    }
    else
    {
    	return $base_url;
    }

  } 

?>