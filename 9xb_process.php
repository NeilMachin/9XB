<?php
require('connect_db.php');
session_start();

$validationErrorsFound = false;
$validationErrorMessages = array();
$sqlUpdates = array();
$checkJobRoles = array();

/*
 *    Check job roles here so that the limit on 4 employees per role can be checked later for each row of changed or new data
 */
foreach($_POST['people'] as $row) {
    $thisJobRole = $row['job_role'];
    if(array_key_exists($thisJobRole, $checkJobRoles)) {
        $checkJobRoles[$thisJobRole] = $checkJobRoles[$thisJobRole] + 1;
    } else {
        $checkJobRoles[$thisJobRole] = 1;
    }
}


/*
 *    Check for new data, if any columns have been entered then validate the row
 */
$errorNew = "";
$thisFirstName = filter_var(trim($_POST['people']['new']['first_name']),FILTER_SANITIZE_STRING);
$thisLastName  = filter_var(trim($_POST['people']['new']['last_name']),FILTER_SANITIZE_STRING);
$thisEmail     = filter_var(trim($_POST['people']['new']['email']),FILTER_SANITIZE_STRING);
$thisJobRole   = filter_var(trim($_POST['people']['new']['job_role']),FILTER_SANITIZE_STRING);
$checkNew = $thisFirstName . $thisLastName . $thisEmail . $thisJobRole;
if(strlen($checkNew) > 0) {
    $error = ValidateRow($thisFirstName, $thisLastName, $thisEmail, $thisJobRole, $checkJobRoles);
    if(strlen($error) > 0) {
        $validationErrorsFound = true;
        $validationErrorMessages['new'] = $error;
    } else {
        $sqlUpdates[] = "insert into 9xb_data (first_name,last_name,email,job_role) 
                         values('$thisFirstName', '$thisLastName', '$thisEmail', '$thisJobRole')";
    }
}

/*
 *    Check each of the existing records, firstly for deletes, then for any changes
 */
$count = 0;
foreach($_POST['people'] as $key=>$row) {
    if(is_int($key)) {
        $thisId        = $row['id'];
        $thisDelete    = array_key_exists("delete",$row);

        if($thisDelete) {
            $sqlUpdates[] = "delete from 9xb_data where id = $thisId";
        } else {
            $thisFirstName = filter_var(trim($row['first_name']),FILTER_SANITIZE_STRING);
            $thisLastName  = filter_var(trim($row['last_name']),FILTER_SANITIZE_STRING);
            $thisEmail     = filter_var(trim($row['email']),FILTER_SANITIZE_STRING);
            $thisJobRole   = filter_var(trim($row['job_role']),FILTER_SANITIZE_STRING);
            if(   ($thisFirstName <> $row['init_first_name'])
               or ($thisLastName  <> $row['init_last_name'])
               or ($thisEmail     <> $row['init_email'])
               or ($thisJobRole   <> $row['init_job_role'])) {
                $error = ValidateRow($thisFirstName, $thisLastName, $thisEmail, $thisJobRole, $checkJobRoles);
                if(strlen($error) > 0) {
                    $validationErrorsFound = true;
                    $validationErrorMessages[$count] = $error;
                } else {
                    $sqlUpdates[] = "update 9xb_data set 
                                     first_name='$thisFirstName', 
                                     last_name='$thisLastName', 
                                     email='$thisEmail', 
                                     job_role='$thisJobRole' 
                                     where id = $thisId";
                }
            }
        }
        $count++;
    }
}

/*
 *    If data has been changed, and no validation errors have been found, then apply the updates 
 */
if(!$validationErrorsFound) {
    foreach($sqlUpdates as $sql) {
        $result = mysqli_query($dbc, $sql);
        if(mysqli_affected_rows($dbc) < 0) {
            $_SESSION['db_error'] = mysqli_error($dbc);
        } else {
            unset($_SESSION['db_error']);
        }
    }
}


if($validationErrorsFound) {
    $_SESSION['validation_status'] = false;
    $_SESSION['post'] = $_POST;
    $_SESSION['errors'] = $validationErrorMessages;
} else {
    $_SESSION['validation_status'] = true;
    unset($_SESSION['post']);
    unset($_SESSION['errors']);

}

mysqli_close($dbc);
header('Location: 9xb_form.php');
exit();




function ValidateRow($inFirstName, $inLastName, $inEmail, $inJobRole, $inCheckJobRoles)
{
    /*
     *  Validate a row of data
     */
    $errMessage = "";
    if(strlen($inFirstName) == 0)                        $errMessage = "First name must be entered";
    elseif(strlen($inLastName) == 0)                     $errMessage = "Last name must be entered";
    elseif(strlen($inEmail)  == 0)                       $errMessage = "Email must be entered";
    elseif(strlen($inJobRole) == 0)                      $errMessage = "Job Role must be entered";
    elseif(!filter_var($inEmail, FILTER_VALIDATE_EMAIL)) $errMessage = "Email address must be a valid format";
    elseif($inCheckJobRoles[$inJobRole] > 4)             $errMessage = "Maximum of 4 employees per job role";
    
    return $errMessage;
}
