<?php
session_start();

$username=$_POST['userEmail'];
$password=$_POST['userPassword'];
$now = date("Y-m-d H:i:s");

date_default_timezone_set('Asia/Kuala_Lumpur');
$db = mysqli_connect("srv597.hstgr.io", "u664110560_tsp3g_portal", "@Sync5500", "u664110560_tsp3g_portal");

if(mysqli_connect_errno()){
	echo 'Database connection failed with following errors: ' . mysqli_connect_error();
	die();
}
else{
	$stmt = $db->prepare("SELECT * from users where username= ?");
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$result = $stmt->get_result();

	if(($row = $result->fetch_assoc()) !== null){
		$password = hash('sha512', $password . $row['salt']);
		
		if($password == $row['password']){
			$_SESSION['userID']=$row['id'];
			$stmt->close();
			$db->close();
			
			echo '<script type="text/javascript">';
			echo 'window.location.href = "../index.php";</script>';
		} 
		else{
			echo '<script type="text/javascript">alert("Login unsuccessful, password or username is not matched");';
			echo 'window.location.href = "../login.php";</script>';
		}
		
	} 
	else{
		echo '<script type="text/javascript">alert("Login unsuccessful, password or username is not matched");';
		echo 'window.location.href = "../login.php";</script>';
	}
}
?>
