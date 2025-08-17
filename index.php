<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "healthzone";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$signupMessage = "";
$loginMessage = "";

// Validation function
function isValidInput($name, $email, $password) {
  if (!preg_match("/^[A-Z][a-zA-Z]*$/", $name)) {
    return "Name must start with a capital letter and contain only letters.";
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@gmail\.com$/", $email)) {
    return "Email must be a valid Gmail address.";
  }

  if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
    return "Password must include uppercase, lowercase, digit, special character and be at least 8 characters.";
  }

  return true;
}

// Handle Sign Up
if (isset($_POST['signup'])) {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $rawPassword = $_POST['password'];

  $validationResult = isValidInput($name, $email, $rawPassword);

  if ($validationResult !== true) {
    $signupMessage = $validationResult;
  } else {
    $password = password_hash($rawPassword, PASSWORD_DEFAULT);

    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
      $signupMessage = "Email already registered!";
    } else {
      $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
      if ($conn->query($sql) === TRUE) {
        $signupMessage = "Sign up successful! You can now log in.";
      } else {
        $signupMessage = "Error: " . $conn->error;
      }
    }
  }
}

// Handle Login
if (isset($_POST['login'])) {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  $sql = "SELECT * FROM users WHERE email='$email'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($password, $row['password'])) {
      header("Location: getstarted.html");
      exit();
    } else {
      $loginMessage = "Incorrect password!";
    }
  } else {
    $loginMessage = "No user found with this email.";
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>HealthZone Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: url('homee2.png') no-repeat center center fixed;
      background-size: cover;
      margin: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      height: 100vh;
    }

    header {
      margin-top: 0;
      margin-bottom: 20px;
      width: 100%;
      padding: 20px 0;
      font-size: 30px;
      font-weight: bold;
      color: white;
      background-color: rgba(0, 0, 0, 0.4);
      text-align: center;
      text-transform: uppercase;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .container {
      margin-top: 70px;
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      width: 350px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-top: 10px;
      margin-bottom: 5px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;0
    }

    button:hover {
      background-color: #45a049;
    }

    .toggle-link {
      text-align: center;
      margin-top: 15px;
    }

    .toggle-link a {
      color: #007BFF;
      cursor: pointer;
      text-decoration: none;
    }

    .toggle-link a:hover {
      text-decoration: underline;
    }

    .form {
      display: none;
    }

    .form.active {
      display: block;
    }

    .show-password {
      display: flex;
      align-items: center;
      margin: 5px 0 10px 0;
      font-size: 14px;
    }

    .show-password input {
      width: auto;
      margin-right: 5px;
    }

    .message {
      text-align: center;
      color: red;
      margin-top: 10px;
      font-size: 14px;
    }
  </style>
</head>
<body>

<header>Welcome to HealthZone</header>

<div class="container">
  <!-- Sign Up Form -->
  <div id="signupForm" class="form <?php if (!isset($_POST['login'])) echo 'active'; ?>">
    <h2>Sign Up</h2>
    <form method="POST" autocomplete="off" onsubmit="return validateSignupForm()">
      <label for="signupName">Full Name:</label>
      <input type="text" id="signupName" name="name" required placeholder="Enter your full name">

      <label for="signupEmail">Email:</label>
      <input type="email" id="signupEmail" name="email" required placeholder="Enter your Gmail" autocomplete="off">

      <label for="signupPassword">Password:</label>
      <input type="password" id="signupPassword" name="password" required placeholder="Enter your password" autocomplete="new-password">

      <label class="show-password">
        <input type="checkbox" onclick="togglePassword('signupPassword')"> Show Password
      </label>

      <button type="submit" name="signup">Sign Up</button>
    </form>
    <div class="toggle-link">
      Already registered? <a href="#" onclick="showLogin()">Log in</a>
    </div>
    <?php if ($signupMessage) echo "<div class='message'>$signupMessage</div>"; ?>
  </div>

  <!-- Login Form -->
  <div id="loginForm" class="form <?php if (isset($_POST['login'])) echo 'active'; ?>">
    <h2>Log In</h2>
    <form method="POST" autocomplete="off">
      <label for="loginEmail">Email:</label>
      <input type="email" id="loginEmail" name="email" required placeholder="Enter your email">

      <label for="loginPassword">Password:</label>
      <input type="password" id="loginPassword" name="password" required placeholder="Enter your password">

      <label class="show-password">
        <input type="checkbox" onclick="togglePassword('loginPassword')"> Show Password
      </label>

      <button type="submit" name="login">Log In</button>
    </form>
    <div class="toggle-link">
      New here? <a href="#" onclick="showSignup()">Sign up</a>
    </div>
    <?php if ($loginMessage) echo "<div class='message'>$loginMessage</div>"; ?>
  </div>
</div>

<script>
  function showLogin() {
    document.getElementById('signupForm').classList.remove('active');
    document.getElementById('loginForm').classList.add('active');
  }

  function showSignup() {
    document.getElementById('loginForm').classList.remove('active');
    document.getElementById('signupForm').classList.add('active');
  }

  function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === "password" ? "text" : "password";
  }

  function validateSignupForm() {
    const name = document.getElementById('signupName').value.trim();
    const email = document.getElementById('signupEmail').value.trim();
    const password = document.getElementById('signupPassword').value;

    const nameRegex = /^[A-Z][a-zA-Z]*$/;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    if (!nameRegex.test(name)) {
      alert("Name must start with a capital letter and contain only letters.");
      return false;
    }

    if (!emailRegex.test(email)) {
      alert("Please enter a valid Gmail address.");
      return false;
    }

    if (!passwordRegex.test(password)) {
      alert("Password must contain uppercase, lowercase, number, special character and be at least 8 characters.");
      return false;
    }

    return true;
  }
</script>

</body>
</html>
