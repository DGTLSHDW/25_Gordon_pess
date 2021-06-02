<!doctype html>
<html>
<head>
    <title>Police Emergency Service System</title>
    <link href="header_style.css" rel="stylesheet" type="text/css">
    <link href="content_style.css" rel="stylesheet" type="text/css">

	<?php
	if (isset($_POST["btnUpdate"])){
		require_once 'db.php';
		//craete database connection
	$mysqli =mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	//check connection
	if ($mysqli->connect_errno) {
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);

	}

	//retrieve patrol car detail
	$sql ="SELECT * FROM patrolcar WHERE patrolcar_id = ?";

	if (!($stmt =$mysqli->prepare($sql))){
		die("Prepare failed: ".$mysqli->errno);

	}

	if (!($stmt->bind_param('s', $_POST['patrolCarId']))){
		die("Binding parameters failed: ".$stmt->errno);


	}


	if (!($stmt->execute())) {
		die("Execute failed failed: ".$stmt->errno);

	}

	//if patrolcar status is Arrived(4) then capture time of arrival

	if($_POST["patrolCarStatus"] =='4'){

		$sql ="UPDATE dispatch SET time_arrived = NOW()
		WHERE time_arrived is NULL AND patrolcar_id = ?";


		if (!($stmt =$mysqli->prepare($sql))){
		die("Prepare failed: ".$mysqli->errno);

	}

	if (!($stmt->bind_param('s', $_POST['patrolCarId']))){
		die("Binding parameters failed: ".$stmt->errno);


	}


	if (!($stmt->execute())) {
		die("Execute failed failed: ".$stmt->errno);
	}
	} else if($_POST["patrolCarStatus"] =='3'){ // else if patrol car status is FREE (3) then capture the time of completion

	//First,retrieve the incident ID from dispatch table handled by that patrol car
	$sql ="SELECT incident_id FROM dispatch WHERE time_completed IS NULL AND patrolCar_id =?";


	if (!($stmt =$mysqli->prepare($sql))){
		die("Prepare failed: ".$mysqli->errno);

	}

	if (!($stmt->bind_param('s', $_POST['patrolCarId']))){
		die("Binding parameters failed: ".$stmt->errno);


	}


	if (!($stmt->execute())) {
		die("Execute failed failed: ".$stmt->errno);

	}

	if (!($resultset = $stmt->get_result())) {
		die("Getting results set failed: ".$stmt->errno);

	}

	$incidentId;

	while ($row = $resultset->fetch_assoc()) {
		$incidentId =$row['incident_id'];
	}
	//next update dispatch table
	$sql="UPDATE dispatch SET time_completed = NOW()
			WHERE time_completed is NULL AND  patrolcar_id =?";

	if (!($stmt =$mysqli->prepare($sql))){
		die("Prepare failed: ".$mysqli->errno);

	}

	if (!($stmt->bind_param('s', $_POST['patrolCarId']))){
		die("Binding parameters failed: ".$stmt->errno);


	}


	if (!($stmt->execute())) {
		die("Execute failed failed: ".$stmt->errno);

	}

	//last but not least, update incident table to complete (3) all patrol car attended to it are FREE now

	$sql="UPDATE  incident SET incident_status_id ='3' WHERE incident_id = '$incidentId' AND NOT EXISTS (SELECT * FROM dispatch WHERE time_completed IS NULL AND incident_id ='$incidentId')";


	if (!($stmt =$mysqli->prepare($sql))){
		die("Prepare failed: ".$mysqli->errno);

	}

	if (!($stmt->bind_param('s', $_POST['patrolCarId']))){
		die("Binding parameters failed: ".$stmt->errno);


	}


	if (!($stmt->execute())) {
		die("Execute failed failed: ".$stmt->errno);

	}

	$resultset->close();
	}

	$stmt->close();
	$mysqli()->close();
	?>
	<script type="text/javascript">window.location="./logcall.php";</script>


	<?php } ?>

	
</head>
<body>
<?php require_once 'nav.php'; ?>
<br><br>
<?php
if (!isset($_POST["btn_Search"])) {
?>
<form name="form1" method="post"
	action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
	<table class="ContentStyle">
		<tr></tr>
		<tr>
			<td>Patrol Car ID:</td>
			<td><input type="text" name="patrolCarId" id="patrolCarId"></td>
			<td><input type='submit' name="btnSearch" id="btnSearch" value="Search"></td>
		</tr>
	</table>
</form>
<?php

}else
{ //post back here after clicking the btnSearch button
	require_once 'db.php';


	//craete database connection
	$mysqli =mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	//check connection
	if ($mysqli->connect_errno) {
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);

	}

	//retrieve patrol car detail
	$sql ="SELECT * FROM patrolcar WHERE patrolcar_id = ?";

	if (!($stmt =$mysqli->prepare($sql))){
		die("Prepare failed: ".$mysqli->errno);

	}

	if (!($stmt->bind_param('s', $_POST['patrolCarId']))){
		die("Binding parameters failed: ".$stmt->errno);


	}


	if (!($stmt->execute())) {
		die("Execute failed failed: ".$stmt->errno);

	}

	if (!($resultset = $stmt->get_result())) {
		die("Getting results set failed: ".$stmt->errno);

	}
	//IF THE PATROL CAR DOES NOT EXIST,REDIRECT BACK TO UPDATE.PHP
	if ($resultset->num_rows == 0) {
		?>
			<script type="text/javascript">window.location="./update.php";</script>
	<?php }
	//else if the patrol car found
	$patrolCarId;
	$patrolCarStatusId;

	while($row = $resultset->fetch_assoc()) {
		$patrolcarId = $row['patrolcar_id'];
		$patrolCarStatusId = $row['patrolcar_status_id'];
	}

//retrieve from patrolcar_status table for populating the combo box
$sql ="SELECT * FROM patrolcar_status";
if(!($stmt = $mysqli->prepare($sql))) {
	die("Prepare failed: ".$mysqli->errno);
}

if (!$stmt->execute()) {
	die("Execute failed: ".$stmt->errno);
}

if(!($resultset = $stmt->get_result())) {
	die("Getting result set failed: ".$stmt->errno);
}

$patrolCarStatusArray;; //an array variable

while ($row = $resultset->fetch_assoc()) {
	$patrolCarStatusArray[$row['patrolcar_Status_Id']] = $row['patrolcar_status_desc'];

}


$stmt->close();
$resultset->close();
$mysqli->close();

?>

	<form name="form2" method="post"
		action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">

		<table class="ContentStyle">
			<tr></tr>
			<tr>
				
				<td>ID :</td>
				<td><?php echo $patrolCarId?>
				<input type="hidden" name="patrolCarId" id="patrolcarId" value="<?php echo $patrolCarId ?>">
				
				</td>
				</tr>
				<tr>
				
				<td>Status :</td>
				<td><select name="patrolCarStatus" id="patrolCarStatus">
				<?php foreach( $patrolCarStatusArray as $key => $value) 
				{ ?>
				<option value ="<?php echo $key ?>"
				<?php if ($key==$patrolCarStatusId) { ?>  selected="selected"
				<?php }?>

			>

				<?php echo $value ?>
				</option>
				<?php }?>
				</select></td>
				</tr>
				<tr>
				<td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
				<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="btnUpdate" id="btnUpdate" value="Update">
				</td>
				</tr>
				</table>
				</form>
				<?php } ?>
				</body>
				</html>