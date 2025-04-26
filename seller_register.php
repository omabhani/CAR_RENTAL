<?php
include 'db.php';

if (isset($_POST['register'])) {
    $name            = mysqli_real_escape_string($conn, $_POST['name']);
    $email           = mysqli_real_escape_string($conn, $_POST['email']);
    $phone           = mysqli_real_escape_string($conn, $_POST['phone']);
    $password        = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password !== $confirmPassword) {
        echo "<script>alert('Passwords do not match');</script>";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $checkEmail = mysqli_query($conn, "SELECT * FROM sellers WHERE email='$email'");
        if (mysqli_num_rows($checkEmail) > 0) {
            echo "<script>alert('Email already registered');</script>";
        } else {
            $query = "INSERT INTO sellers (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$hashedPassword')";
            if (mysqli_query($conn, $query)) {
                echo "<script>alert('Registration successful!'); window.location.href='seller_login.php';</script>";
            } else {
                echo "<script>alert('Error during registration');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Seller Register</title>
  
  <style>
    :root {
  --primary-color: #4361ee;
  --secondary-color: #3a0ca3;
  --text-color: #2b2d42;
  --light-text: #ffffff;
  --background-color: #ffffff;
  --navbar-height: 70px;
  --shadow-color: rgba(0, 0, 0, 0.1);
  --transition-speed: 0.3s;
  --border-radius: 8px;
}

.navbar {
  position: sticky;
  top: 0;
  background-color: var(--background-color);
  box-shadow: 0 4px 20px var(--shadow-color);
  z-index: 1000;
  height: var(--navbar-height);
  transition: all var(--transition-speed) ease;
}

.navbar:hover {
  box-shadow: 0 6px 25px rgba(67, 97, 238, 0.15);
}

.navbar .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 100%;
  padding: 0 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.logo a {
  display: flex;
  align-items: center;
  text-decoration: none;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-color);
  transition: all var(--transition-speed) ease;
}

.logo a:hover {
  transform: scale(1.05);
}

.logo i {
  margin-right: 0.5rem;
  font-size: 1.6rem;
}

#menu-toggle {
  display: none;
}

.menu-icon {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  height: 24px;
  cursor: pointer;
}

.menu-icon span {
  display: block;
  width: 30px;
  height: 3px;
  background-color: var(--primary-color);
  border-radius: 3px;
  transition: all var(--transition-speed) ease;
}

.nav-menu {
  display: flex;
  align-items: center;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-menu li {
  position: relative;
  margin: 0 0.25rem;
}

.nav-menu li a {
  display: block;
  padding: 0.5rem 1rem;
  text-decoration: none;
  color: var(--text-color);
  font-weight: 500;
  font-size: 1rem;
  border-radius: var(--border-radius);
  transition: all var(--transition-speed) ease;
}

.nav-menu li a:hover {
  color: var(--primary-color);
  background-color: rgba(67, 97, 238, 0.08);
}

.nav-menu li a.active {
  color: var(--light-text);
  background-color: var(--primary-color);
}

.nav-menu li a.active:hover {
  background-color: var(--secondary-color);
}

/* Animated underline effect for non-active links */
.nav-menu li a:not(.active)::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 0;
  left: 50%;
  background-color: var(--primary-color);
  transition: all var(--transition-speed) ease;
}

.nav-menu li a:not(.active):hover::after {
  width: 80%;
  left: 10%;
}

/* Responsive styles */
@media screen and (max-width: 900px) {
  .menu-icon {
    display: flex;
    z-index: 1010;
  }
  
  .nav-menu {
    position: fixed;
    top: var(--navbar-height);
    left: -100%;
    width: 100%;
    height: calc(100vh - var(--navbar-height));
    flex-direction: column;
    background-color: var(--background-color);
    box-shadow: 0 10px 20px var(--shadow-color);
    transition: all var(--transition-speed) ease;
    align-items: flex-start;
    padding: 1rem 0;
    overflow-y: auto;
  }
  
  .nav-menu li {
    width: 100%;
    margin: 0;
  }
  
  .nav-menu li a {
    padding: 1rem 2rem;
    border-radius: 0;
  }
  
  #menu-toggle:checked ~ .nav-menu {
    left: 0;
  }
  
  #menu-toggle:checked ~ .menu-icon span:nth-child(1) {
    transform: rotate(45deg) translate(5px, 6px);
  }
  
  #menu-toggle:checked ~ .menu-icon span:nth-child(2) {
    opacity: 0;
  }
  
  #menu-toggle:checked ~ .menu-icon span:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -6px);
  }
  
  .nav-menu li a:not(.active)::after {
    display: none;
  }
}

/* Animation for menu items when mobile menu opens */
@media screen and (max-width: 900px) {
  .nav-menu li {
    opacity: 0;
    transform: translateX(-20px);
    transition: all var(--transition-speed) ease;
  }
  
  #menu-toggle:checked ~ .nav-menu li {
    opacity: 1;
    transform: translateX(0);
  }
  
  /* Staggered animation for menu items */
  .nav-menu li:nth-child(1) { transition-delay: 0.1s; }
  .nav-menu li:nth-child(2) { transition-delay: 0.2s; }
  .nav-menu li:nth-child(3) { transition-delay: 0.3s; }
}
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f4f4;
    }

    .form-container {
      width: 360px;
      margin: 100px auto;
      padding: 30px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
    }

    form input {
      width: 100%;
      padding: 12px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    form button {
      width: 100%;
      padding: 12px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
    }

    form button:hover {
      background-color: #0056b3;
    }

    p {
      text-align: center;
      margin-top: 15px;
    }

    p a {
      color: #007bff;
      text-decoration: none;
    }

    p a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
<nav class="navbar">
  <div class="container">
    <div class="logo">
      <a href="index.php">
        <i class="fas fa-car"></i> CarRental
      </a>
    </div>
    <input type="checkbox" id="menu-toggle">
    <label for="menu-toggle" class="menu-icon">
      <span></span>
      <span></span>
      <span></span>
    </label>
    <ul class="nav-menu">
      <li><a href="index.php">Home</a></li>
      <li><a href="buyer_login.php">Buyer Login</a></li>
      <li><a href="seller_login.php">Seller Login</a></li>
    </ul>
  </div>
</nav>

  <div class="form-container">
    <h2>Seller Register</h2>
    <form method="post" action="">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="text" name="phone" placeholder="Phone Number" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit" name="register">Register</button>
      <p>Already have an account? <a href="seller_login.php">Login</a></p>
    </form>
  </div>
</body>
</html>
