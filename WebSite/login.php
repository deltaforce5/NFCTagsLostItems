<!DOCTYPE html>
<html>
<head>
	<title>Login</title>
</head>
<body>
<link href="site.css" rel="stylesheet">
	<h2>Login</h2>
	<form action="authenticate.php" method="post" class="login">
		<label>Username:</label>
		<input type="text" name="username" required><br><br>
		<label>Password:</label>
		<input type="password" name="password" required><br><br>
		<input type="submit" value="Login">
	</form>
</body>
</html>
