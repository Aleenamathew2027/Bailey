<?php
include 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = trim($_POST['full_name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // Validation
  if (empty($name)) {
    $error = "Please enter your full name";
  } elseif (empty($email)) {
    $error = "Please enter your email";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Please enter a valid email";
  } elseif (empty($password)) {
    $error = "Please enter a password";
  } elseif (strlen($password) < 6) {
    $error = "Password must be at least 6 characters";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match";
  } else {
    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
      $error = "Email already registered. Please login or use a different email.";
      $check_stmt->close();
    } else {
      $check_stmt->close();
      
      // Hash password and insert
      $hashed_password = password_hash($password, PASSWORD_BCRYPT);
      $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $name, $email, $hashed_password);

      if ($stmt->execute()) {
        $success = "Account created successfully! Redirecting to login...";
        header("refresh:2;url=login.php?signup=success");
      } else {
        $error = "Error creating account. Please try again.";
      }

      $stmt->close();
    }
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up | Beauty Cosmetics</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #ffc0cb 0%, #ffb3c1 50%, #ff9fb2 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .auth-container {
      background: white;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(255, 105, 180, 0.2);
      width: 100%;
      max-width: 450px;
      padding: 50px 40px;
      animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
    }

    .logo {
      font-size: 32px;
      margin-bottom: 15px;
    }

    .header h2 {
      color: #333;
      font-size: 28px;
      margin-bottom: 8px;
      font-weight: 600;
      background: linear-gradient(135deg, #ff69b4 0%, #ffb3c1 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .header p {
      color: #999;
      font-size: 14px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      color: #555;
      font-weight: 500;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .form-group input {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #ffe4e9;
      border-radius: 10px;
      font-size: 14px;
      transition: all 0.3s ease;
      font-family: inherit;
    }

    .form-group input:focus {
      outline: none;
      border-color: #ff69b4;
      box-shadow: 0 0 0 3px rgba(255, 105, 180, 0.1);
    }

    .form-group input::placeholder {
      color: #ddd;
    }

    .password-match {
      font-size: 12px;
      margin-top: 5px;
      display: none;
      align-items: center;
      gap: 5px;
    }

    .password-match.show {
      display: flex;
    }

    .password-match.match {
      color: #4caf50;
    }

    .password-match.nomatch {
      color: #f44336;
    }

    .match-icon {
      font-size: 14px;
    }

    .error-message {
      background-color: #ffebee;
      color: #c62828;
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: none;
      border-left: 4px solid #c62828;
    }

    .error-message.show {
      display: block;
    }

    .success-message {
      background-color: #e8f5e9;
      color: #2e7d32;
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 14px;
      display: none;
      border-left: 4px solid #2e7d32;
    }

    .success-message.show {
      display: block;
    }

    .submit-btn {
      width: 100%;
      padding: 12px;
      background: linear-gradient(135deg, #ff69b4 0%, #ffb3c1 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 10px;
    }

    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(255, 105, 180, 0.4);
    }

    .submit-btn:active {
      transform: translateY(0);
    }

    .form-footer {
      text-align: center;
      margin-top: 20px;
      color: #666;
      font-size: 14px;
    }

    .form-footer a {
      color: #ff69b4;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .form-footer a:hover {
      color: #ff1493;
    }

    @media (max-width: 480px) {
      .auth-container {
        padding: 40px 25px;
      }

      .header h2 {
        font-size: 24px;
      }
    }
  </style>
</head>
<body>
  <div class="auth-container">
    <div class="header">
      <div class="logo">✨</div>
      <h2>Create Account</h2>
      <p>Join our beauty community</p>
    </div>

    <?php if ($success) { ?>
      <div class="success-message show"><?php echo $success; ?></div>
    <?php } ?>

    <?php if ($error) { ?>
      <div class="error-message show"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
      <div class="form-group">
        <label for="fullName">Full Name</label>
        <input type="text" id="fullName" name="full_name" placeholder="Enter your full name" required>
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
      </div>

      <div class="form-group">
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm your password" required>
        <div id="passwordMatch" class="password-match">
          <span class="match-icon">✓</span>
          <span id="matchText">Passwords match</span>
        </div>
      </div>

      <button type="submit" class="submit-btn">Sign Up</button>

      <div class="form-footer">
        <p>Already have an account? <a href="login.php">Login</a></p>
      </div>
    </form>
  </div>

  <script>
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordMatch = document.getElementById('passwordMatch');
    const matchText = document.getElementById('matchText');

    function checkPasswordMatch() {
      if (confirmPasswordInput.value === '') {
        passwordMatch.classList.remove('show');
        return;
      }

      passwordMatch.classList.add('show');

      if (passwordInput.value === confirmPasswordInput.value) {
        passwordMatch.classList.remove('nomatch');
        passwordMatch.classList.add('match');
        matchText.textContent = '✓ Passwords match';
      } else {
        passwordMatch.classList.remove('match');
        passwordMatch.classList.add('nomatch');
        matchText.textContent = '✗ Passwords do not match';
      }
    }

    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    passwordInput.addEventListener('input', checkPasswordMatch);
  </script>
</body>
</html>