<!DOCTYPE html>
<html lang="en-gb">

<head>
<title>9XB form</title>
<style>
    .error   {color: red ; font-weight: bold}
</style>
</head>

<body>

<?php
require('connect_db.php');
session_start();

/*
 *    Determine if the form is to be reloaded directly from the database, or if it is to be reloaded with 
 *    previous values following failed validation
 */
$loadFromDB = true;
if(isset($_SESSION['errors'])) {
	if(isset($_SESSION['post'])) {
		$loadFromDB = false;
	}
}


/*
 *   If indicated then retreive the current data from the database
 */ 
if($loadFromDB) {
	$sql = "select id, first_name, last_name, email, job_role from 9xb_data";
    $rows = array();
    if ($result = $dbc->query($sql)) {
        $rowCount = 0;
        while($selected = $result->fetch_assoc()) {
    	    $rows[] = $selected;
            $rowCount ++;
        }
    }
}
?>

<br>
<br>
<br>
<form action="9xb_process.php" method="post">
    <table>
        <tr>
            <th>First name</th>
            <th>Last name</th>
            <th>Email Address</th>
            <th>Job Role</th>
            <th>Delete</th>
        </tr>

<?php
/*
 *    Display existing data rows for editing
 */
if($loadFromDB) {
	if($rowCount > 0) {
        foreach(range(0,$rowCount-1) as $ind) {
	        echo "        <tr>\n";
    	    echo "            <td>
	                              <input type='hidden' name='people[$ind][init_first_name]'  value='{$rows[$ind]['first_name']}'>
	                              <input type='hidden' name='people[$ind][init_last_name]'   value='{$rows[$ind]['last_name']}'>
	                              <input type='hidden' name='people[$ind][init_email]'       value='{$rows[$ind]['email']}'>
	                              <input type='hidden' name='people[$ind][init_job_role]'    value='{$rows[$ind]['job_role']}'>
	                              <input type='hidden' name='people[$ind][id]'               value='{$rows[$ind]['id']}'>
    	                          <input type='text' name='people[$ind][first_name]'  value='{$rows[$ind]['first_name']}'> </td>\n";
	        echo "            <td><input type='text' name='people[$ind][last_name]'   value='{$rows[$ind]['last_name']}'>  </td>\n";
	        echo "            <td><input type='text' name='people[$ind][email]'       value='{$rows[$ind]['email']}'>      </td>\n";
        	echo "            <td><input type='text' name='people[$ind][job_role]'    value='{$rows[$ind]['job_role']}'>   </td>\n";
	        echo "            <td><input type='checkbox' name='people[$ind][delete]'  value='1'>                           </td>\n";
	        echo "            <td>&nbsp;</td>";
        	echo "        </tr>\n";
        }
    }
} else {
	$rowCount = 0;
	foreach($_SESSION['post']['people'] as $key=>$row)
	{
		if(is_int($key))
		{
			$rowCount++;
			echo "        <tr>\n";
			echo "            <td>
				                  <input type='hidden' name='people[$key][init_first_name]'  value='{$row['init_first_name']}'>
	                              <input type='hidden' name='people[$key][init_last_name]'   value='{$row['init_last_name']}'>
	                              <input type='hidden' name='people[$key][init_email]'       value='{$row['init_email']}'>
	                              <input type='hidden' name='people[$key][init_job_role]'    value='{$row['init_job_role']}'>
			                      <input type='text' name='people[$key][first_name]' value='{$row['first_name']}'>  </td>\n";
			echo "            <td><input type='text' name='people[$key][last_name]'  value='{$row['last_name']}'>  </td>\n";
			echo "            <td><input type='text' name='people[$key][email]'      value='{$row['email']}'>  </td>\n";
			echo "            <td><input type='text' name='people[$key][job_role]'   value='{$row['job_role']}'>  </td>\n";
			echo "            <td><input type='checkbox' name='people[$key][delete]' value='1'>                   </td>\n";
			if(array_key_exists($key, $_SESSION['errors'])) {
				echo "<td class='error'>{$_SESSION['errors'][$key]}</td>";
			} else {
				echo "<td>&nbsp;</td>";
			}
			echo "        </tr>\n";
		}
	}
}


/*
 *    Display row for new data. Conditional on there being less than 10 rows currently in the table.
 */
if($rowCount < 10) {
    echo "        <tr>\n";
    if($loadFromDB) {
    	echo "            <td><input type='text' name='people[new][first_name]'' placeholder='Add new...' /></td>\n";
	    echo "            <td><input type='text' name='people[new][last_name]' placeholder='Add new...' /></td>\n";
        echo "            <td><input type='text' name='people[new][email]' placeholder='Add new...' /></td>\n";
        echo "            <td><input type='text' name='people[new][job_role]' placeholder='Add new...' /></td>\n";
    } else {
	    echo "            <td><input type='text' name='people[new][first_name]' placeholder='Add new...'  value='{$_SESSION['post']['people']['new']['first_name']}'/></td>\n";
    	echo "            <td><input type='text' name='people[new][last_name]' placeholder='Add new...'  value='{$_SESSION['post']['people']['new']['last_name']}'/></td>\n";
	    echo "            <td><input type='text' name='people[new][email]' placeholder='Add new...'  value='{$_SESSION['post']['people']['new']['email']}'/></td>\n";
    	echo "            <td><input type='text' name='people[new][job_role]' placeholder='Add new...'  value='{$_SESSION['post']['people']['new']['job_role']}'/></td>\n";
	    echo "            <td>&nbsp;</td>";
    	if(array_key_exists('new', $_SESSION['errors'])) {
	    			echo "<td class='error'>{$_SESSION['errors']['new']}</td>";
    			} else {
		    		echo "<td>&nbsp;</td>";
	    		}
    }
    echo "        </tr>\n";
}
?>
    </table>
    <input type="submit" value="Submit!" />
</form>


</body>

</html>