<?php
include 'db.php';
session_start();

$error = '';
$success = '';

// Check for signup success message
if (isset($_GET['signup']) && $_GET['signup'] == 'success') {
  $success = "Account created successfully! Please login with your credentials.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  // Validation
  if (empty($email)) {
    $error = "Please enter your email";
  } elseif (empty($password)) {
    $error = "Please enter your password";
  } else {
    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $stmt->bind_result($id, $name, $hashed_password);
      $stmt->fetch();

      if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        header("Location: home.php");
        exit();
      } else {
        $error = "Invalid password!";
      }
    } else {
      $error = "No account found with that email.";
    }

    $stmt->close();
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Beauty Cosmetics</title>
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

    .forgot-password {
      text-align: right;
      margin-top: 15px;
    }

    .forgot-password a {
      color: #ff69b4;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      transition: color 0.3s ease;
    }

    .forgot-password a:hover {
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
      <h2>Welcome Back</h2>
      <p>Login to your account</p>
    </div>

    <?php if ($success) { ?>
      <div class="success-message show"><?php echo $success; ?></div>
    <?php } ?>

    <?php if ($error) { ?>
      <div class="error-message show"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
      </div>

      <div class="forgot-password">
        <a href="forgot-password.php">Forgot Password?</a>
      </div>

      <button type="submit" class="submit-btn">Login</button>

      <div class="form-footer">
        <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
      </div>
    </form>
  </div>
</body>
</html>