 <?php
$admin_username = 'admin'; // Change this to your desired admin username
$admin_password = 'test'; // Change this to your desired password

 //Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

 //Save credentials to a file
$credentials = [
    //'username' => $admin_username,
   // 'password' => $hashed_password
];

file_put_contents('../includes/admin_credentials.php', '<?php return ' . var_export($credentials, true) . ';');

echo "Admin credentials have been securely stored."; 
?> 

