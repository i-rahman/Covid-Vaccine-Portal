<?php
// Include config file
require_once "config.php";
include('header.php');

// Note: $username = $patientEmail
// Define variables and initialize with empty values
$patientEmail =  $patientPassword = $confirm_password = $patientName = $ssn = $dob = $patientAddress = $patientPhone = $distancePreference = "";
$patientEmail_err = $password_err = $confirm_password_err = $patientName_err = $ssn_err = $dob_err = $patientAddress_err = $patientPhone_err = $distancePreference_err = "";


echo "start";
if($_SERVER["REQUEST_METHOD"] == "POST") {

	$err = false;

	$patientName = trim($_POST["patientName"]);
    if (empty($patientName)){
       $patientName_err = "Please enter a full name.";
		   $err = true;
    }
	$ssn = trim($_POST["ssn"]);
    if (empty($ssn)){
       $ssn_err = "Please enter a SSN.";
		   $err = true;
    }
	$dob = trim($_POST["dob"]);
    if (empty($dob)){
       $dob_err = "Please enter a Date of Birth.";
		   $err = true;
    }
	$patientAddress = trim($_POST["patientAddress"]);
    if (empty($patientAddress)){
     $patientAddress_err = "Please enter a Address.";
		 $err = true;
    }
	$patientPhone = trim($_POST["patientPhone"]);
    if (empty($patientPhone)){
     $patientPhone_err = "Please enter a Phone Number.";
		 $err = true;
    }
	$distancePreference = trim($_POST["distancePreference"]);
    if (empty($distancePreference)){
     $distancePreference_err = "Please enter a Distance Preference.";
		 $err = true;
    }
	$patientEmail = trim($_POST["patientEmail"]);
	if (empty($patientEmail)){
     $patientEmail_err = "Please enter an Email.";
		 $err = true;
    }
//echo $patientName; echo $ssn; echo $dob; echo $patientAddress; echo $patientPhone; echo $distancePreference; echo $patientEmail;
	// Validate password
    if (empty(trim($_POST["patientPassword"]))){
      $password_err = "Please enter a password.";
		  $err = true;
    } elseif(strlen(trim($_POST["patientPassword"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
		$err = true;
    } else{
        $patientPassword = trim($_POST["patientPassword"]);
		$err = true;
    }
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
    $confirm_password_err = "Please confirm password.";
		$err = true;
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($patientPassword != $confirm_password)){
            $confirm_password_err = "Password did not match.";
			$err = true;
        }
    }
echo "checking user name";
	// Prepare a select statement
	$sql = "SELECT patientId FROM patient WHERE patientEmail = ?";

  if($stmt = mysqli_prepare($link, $sql)){
echo "checking sql statement";     // Bind variables to the prepared statement as parameters
     mysqli_stmt_bind_param($stmt, "s", $param_patientEmail);
     // Set parameters
     $param_patientEmail = trim($_POST["patientEmail"]);
     // Attempt to execute the prepared statement
     if(mysqli_stmt_execute($stmt)){
    echo "executed statement";  /* store result */
        mysqli_stmt_store_result($stmt);

       if(mysqli_stmt_num_rows($stmt) == 1){
          $patientEmail_err = "This Email is already taken.";
					$err = true;
       }
    } else {
             echo "Oops! Something went wrong. Please try again later.";
           }

      // Close statement
            mysqli_stmt_close($stmt);
        }
// } added before but it is not worked
if (!$err) {
        $patientName = trim($_POST["patientName"]);
		    $ssn = trim($_POST["ssn"]);
		    $dob = trim($_POST["dob"]);
        $patientAddress = trim($_POST["patientAddress"]);
        $patientPhone = trim($_POST["patientPhone"]);
        $distancePreference = trim($_POST["distancePreference"]);
        $patientEmail = trim($_POST["patientEmail"]);
        $patientPassword = trim($_POST["patientPassword"]);


        // Get Longitude and Latitude from providerAddress using Google Maps API
        $address = str_replace(" ", "+", $patientAddress);
        $json = file_get_contents("https://maps.google.com/maps/api/geocode/json?address=$address&key=AIzaSyA3mM2cTa1pPBc73_wsR2YEkpEb-W45b8k");
        $json = json_decode($json);
        $patientLatitude = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
        $patientLongitude = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};

	   // Check input errors before inserting in database
    if(empty($patientEmail_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO Patient (patientName, ssn, dob, patientAddress, patientLatitude, patientLongitude,
                                    patientPhone, distancePreference, patientEmail, patientPassword
                                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if($stmt = mysqli_prepare($link, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param(
			            									$stmt, "sissiiiiss",
																		$param_patientName,
                  									$param_ssn,
																		$param_dob,
																		$param_patientAddress,
																		$param_patientLatitude,
                  									$param_patientLongitude,
																		$param_patientPhone,
																		$param_distancePreference,
                  									$param_patientEmail,
																		$param_patientPassword
								   								);
	        // Set parameters
            $param_patientName = $patientName;
            $param_ssn = $ssn;
            $param_dob = $dob;
            $param_patientAddress = $patientAddress;
            $param_patientLatitude = $patientLatitude;
            $param_patientLongitude = $patientLongitude;
            $param_patientPhone = $patientPhone;
            $param_distancePreference = $distancePreference;
            $param_patientEmail = $patientEmail;
            $param_patientPassword = password_hash($patientPassword, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)) {
                // Redirect to login page
                echo "Login Succesfull";
                header("location: index.php");
            } else {
                echo "Oops! Something went wrong. Please try again later. after validate success";
            }
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }

}
	// Close connection
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 350px;
            padding: 20px;
            align-self: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <aside class="col-sm-8" style="margin: auto">
                <div class="card">
                    <article class="card-body">
                        <h4 class="card-title text-center mb-4 mt-1">
                            Create New Account
                        </h4>
                        </hr>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="patientEmail" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $patientEmail; ?>">
                <span class="invalid-feedback"><?php echo $patientEmail_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="patientPassword" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $patientPassword; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
						<div class="form-group">
                <label>Patient Name</label>
                <input type="text" name="patientName" class="form-control <?php echo (!empty($patientName_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $patientName; ?>">
                <span class="invalid-feedback"><?php echo $patientName_err; ?></span>
            </div>
            <div class="form-group">
                <label>SSN</label>
                <input type="number" name="ssn" class="form-control <?php echo (!empty($ssn_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $ssn; ?>">
                <span class="invalid-feedback"><?php echo $ssn_err; ?></span>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control <?php echo (!empty($dob_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $dob; ?>">
                <span class="invalid-feedback"><?php echo $dob_err; ?></span>
            </div>
            <div class="form-group">
                <label>Patient Address</label>
                <input type="text" name="patientAddress" class="form-control <?php echo (!empty($patientAddress_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $patientAddress; ?>">
                <span class="invalid-feedback"><?php echo $patientAddress_err; ?></span>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="number" name="patientPhone" class="form-control <?php echo (!empty($patientPhone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $patientPhone; ?>">
                <span class="invalid-feedback"><?php echo $patientPhone_err; ?></span>
            </div>
            <div class="form-group">
                <label>Distance Preference</label>
                <input type="number" name="distancePreference" class="form-control <?php echo (!empty($distancePreference_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $distancePreference; ?>">
                <span class="invalid-feedback"><?php echo $distancePreference_err; ?></span>
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Already have an account? <a href="index.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>