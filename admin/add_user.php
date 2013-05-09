<?php 

    // First we execute our common code to connection to the database and start the session 
    require("../inc/db.php"); 
	session_start();
	
	if( (empty($_SESSION['login']) && empty($_SESSION['AdminAccess'])) || $_SESSION['AdminAccess'] == false )
	{
		header("Location: login.php");
		die("not authroized");
	}
     
    // This if statement checks to determine whether the registration form has been submitted 
    // If it has, then the registration code is run, otherwise the form is displayed 
    if(!empty($_POST)) 
    { 
        // Ensure that the user has entered a non-empty login 
        if(empty($_POST['login'])) 
        { 
            // Note that die() is generally a terrible way of handling user errors 
            // like this.  It is much better to display the error with the form 
            // and allow the user to correct their mistake.  However, that is an 
            // exercise for you to implement yourself. 
            die("Please enter a login."); 
        } 
         
        // Ensure that the user has entered a non-empty PWD 
        if(empty($_POST['PWD'])) 
        { 
            die("Please enter a PWD."); 
        } 
         
        // We will use this SQL query to see whether the login entered by the 
        // user is already in use.  A SELECT query is used to retrieve data from the database. 
        // :login is a special token, we will substitute a real value in its place when 
        // we execute the query. 
        $query = " 
            SELECT 
                1 
            FROM UserDB 
            WHERE 
                login = :login 
        "; 
         
        // This contains the definitions for any special tokens that we place in 
        // our SQL query.  In this case, we are defining a value for the token 
        // :login.  It is possible to insert $_POST['login'] directly into 
        // your $query string; however doing so is very insecure and opens your 
        // code up to SQL injection exploits.  Using tokens prevents this. 
        // For more information on SQL injections, see Wikipedia: 
        // http://en.wikipedia.org/wiki/SQL_Injection 
        $query_params = array( 
            ':login' => $_POST['login'] 
        ); 
         
        try 
        { 
            // These two statements run the query against your database table. 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
        } 
        catch(PDOException $ex) 
        { 
            // Note: On a production website, you should not output $ex->getMessage(). 
            // It may provide an attacker with helpful information about your code.  
            die("Failed to run query: " . $ex->getMessage()); 
        } 
         
        // The fetch() method returns an array representing the "next" row from 
        // the selected results, or false if there are no more rows to fetch. 
        $row = $stmt->fetch(); 
         
        // If a row was returned, then we know a matching login was found in 
        // the database already and we should not allow the user to continue. 
        if($row) 
        { 
            die("This login is already in use"); 
        } 
         
        // An INSERT query is used to add new rows to a database table. 
        // Again, we are using special tokens (technically called parameters) to 
        // protect against SQL injection attacks. 
        $query = " 
            INSERT INTO UserDB ( 
                login, 
                PWD, 
                salt,
				first_name,
				last_name	
            ) VALUES ( 
                :login, 
                :PWD, 
                :salt, 
                :first_name,
				:last_name
            ) 
        "; 
         
        // A salt is randomly generated here to protect again brute force attacks 
        // and rainbow table attacks.  The following statement generates a hex 
        // representation of an 8 byte salt.  Representing this in hex provides 
        // no additional security, but makes it easier for humans to read. 
        // For more information: 
        // http://en.wikipedia.org/wiki/Salt_%28cryptography%29 
        // http://en.wikipedia.org/wiki/Brute-force_attack 
        // http://en.wikipedia.org/wiki/Rainbow_table 
        $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647)); 
         
        // This hashes the PWD with the salt so that it can be stored securely 
        // in your database.  The output of this next statement is a 64 byte hex 
        // string representing the 32 byte sha256 hash of the PWD.  The original 
        // PWD cannot be recovered from the hash.  For more information: 
        // http://en.wikipedia.org/wiki/Cryptographic_hash_function 
        $PWD = hash('sha256', $_POST['PWD'] . $salt); 
         
        // Next we hash the hash value 65536 more times.  The purpose of this is to 
        // protect against brute force attacks.  Now an attacker must compute the hash 65537 
        // times for each guess they make against a PWD, whereas if the PWD 
        // were hashed only once the attacker would have been able to make 65537 different  
        // guesses in the same amount of time instead of only one. 
        for($round = 0; $round < 65536; $round++) 
        { 
            $PWD = hash('sha256', $PWD . $salt); 
        } 
         
        // Here we prepare our tokens for insertion into the SQL query.  We do not 
        // store the original PWD; only the hashed version of it.  We do store 
        // the salt (in its plaintext form; this is not a security risk). 
        $query_params = array( 
            ':login' => $_POST['login'], 
            ':PWD' => $PWD, 
            ':salt' => $salt, 
            ':first_name' => $_POST['first_name'] ,
			':last_name' => $_POST['last_name'] 
        ); 
         
        try 
        { 
            // Execute the query to create the user 
            $stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 
			
			$userID = $db->lastInsertId();
			
			// give them user access by default
			$query = "INSERT INTO UserAccess (UserID, StatusID) VALUES (?, 1)";
			$query_params = array($userID);
			
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
        } 
        catch(PDOException $ex) 
        { 
            // Note: On a production website, you should not output $ex->getMessage(). 
            // It may provide an attacker with helpful information about your code.  
            die("Failed to run query: " . $ex->getMessage()); 
        }
         
        // This redirects the user back to the login page after they register 
        header("Location: index.php"); 
         
        // Calling die or exit after performing a redirect using the header function 
        // is critical.  The rest of your PHP script will continue to execute and 
        // will be sent to the user if you do not die or exit. 
        die("Redirecting to login.php"); 
    } 
     
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd ">
<html>
    <head>

<!--ed-->
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
  <link rel="stylesheet" type="text/css" href="../css/html.css" media="screen, projection, tv " />
  <link rel="stylesheet" type="text/css" href="../css/style.css" media="screen, projection, tv" />
  <link rel="stylesheet" type="text/css" href="../css/print.css" media="print" />
<!--/ed-->
    </head>
	<body>
		<?php include("../inc/header.php")?>
 
<h1>Add User</h1> 
<form action="add_user.php" method="post"> 
  <p id="auto">
    <label>Login: </label> 
      <input type="text" name="login" value="" /> 
      <br /> 
    <label>PWD: </label> 
      <input type="PWD" name="PWD" value="" /> 
      <br /> 
    <label>First Name: </label> 
      <input type="text" name="first_name" value="" /> 
      <br /> 
    <label>Last Name: </label>
      <input type="text" name="last_name" value="" /> 
      <br/><br/> 
    <input type="submit" value="Register" /> 
  </p>
</form>

<!--ed-->
	<?php include("../inc/footer.php")?>
<!--/ed-->
	</body>
</html>