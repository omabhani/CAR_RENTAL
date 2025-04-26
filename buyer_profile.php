<?php 
session_start(); 
include 'db.php'; 

if (!isset($_SESSION['buyer_id'])) {     
    header("Location: buyer_login.php");     
    exit; 
}  

$id = $_SESSION['buyer_id']; 
$success_message = "";
$error_message = "";

// Handle form submission for profile update
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    
    $update_query = "UPDATE buyers SET name='$name', email='$email', phone='$phone' WHERE id=$id";
    
    if (mysqli_query($conn, $update_query)) {
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Error updating profile: " . mysqli_error($conn);
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $old_password = mysqli_real_escape_string($conn, $_POST['old_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    // Fetch current password
    $pass_query = mysqli_query($conn, "SELECT password FROM buyers WHERE id=$id");
    $pass_result = mysqli_fetch_assoc($pass_query);
    
    // Verify old password
    if (password_verify($old_password, $pass_result['password']) || $old_password == $pass_result['password']) {
        // Check if new passwords match
        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_pass_query = "UPDATE sellers SET password='$hashed_password' WHERE id=$id";
            
            if (mysqli_query($conn, $update_pass_query)) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Error changing password: " . mysqli_error($conn);
            }
        } else {
            $error_message = "New passwords do not match!";
        }
    } else {
        $error_message = "Old password is incorrect!";
    }
}

// Get updated user data
$result = mysqli_query($conn, "SELECT * FROM buyers WHERE id=$id"); 
$buyer = mysqli_fetch_assoc($result); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 40px;
            color: #007bff;
        }
        
        .profile-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .profile-email {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .profile-content {
            padding: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .profile-section {
            flex: 1;
            min-width: 300px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="tel"]:focus {
            border-color: #007bff;
            outline: none;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #0056b3;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .btn-logout {
            background-color: #dc3545;
        }
        
        .btn-logout:hover {
            background-color: #c82333;
        }
        
        .message {
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .navbar {
            background-color: #007bff;
            padding: 15px 0;
        }
        
        .navbar .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        
        .navbar ul {
            display: flex;
            list-style: none;
        }
        
        .navbar ul li {
            margin: 0 15px;
        }
        
        .navbar ul li a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }
        
        .navbar ul li a:hover {
            opacity: 0.8;
        }
        
        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .profile-content {
                flex-direction: column;
            }
            
            .profile-section {
                width: 100%;
            }
        }
    </style>
</head>
<body>


    <div class="container">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo substr($buyer['name'], 0, 1); ?>
            </div>
            <div class="profile-name"><?php echo $buyer['name']; ?></div>
            <div class="profile-email"><?php echo $buyer['email']; ?></div>
        </div>
        
        <div class="profile-content">
            <!-- Profile Information Section -->
            <div class="profile-section">
                <h3 class="section-title">Profile Information</h3>
                
                <?php if (!empty($success_message)): ?>
                    <div class="message success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="message error"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $buyer['name']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo $buyer['email']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo $buyer['phone']; ?>" required>
                    </div>
                    
                    <div class="buttons">
                        <button type="submit" name="update_profile" class="btn">Update Profile</button>
                        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </form>
            </div>
            
            <!-- Change Password Section -->
            <div class="profile-section">
                <h3 class="section-title">Change Password</h3>
                <form method="post" action="">
                    <div class="form-group">
                        <label for="old_password">Current Password</label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="buttons">
                        <button type="submit" name="change_password" class="btn">Change Password</button>
                        <a href="logout.php" class="btn btn-logout">Logout</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>