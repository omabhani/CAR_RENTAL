<?php
session_start();
include 'db.php';

if (!isset($_SESSION['seller_id'])) {
    header("Location: seller_login.php");
    exit;
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seller_id = $_SESSION['seller_id'];
    $owner_name = $_POST['owner_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $vehicle_no = $_POST['vehicle_no'];
    $vehicle_name = $_POST['vehicle_name'];
    $price = $_POST['price'];
    $aadhar = $_POST['aadhar'];

    // Validate Aadhar card number (should be exactly 12 digits)
    if (!preg_match('/^\d{12}$/', $aadhar)) {
        $error_message = "Aadhar Card Number must be 12 digits long.";
    } else {
        // Check if vehicle number already exists
        $check_query = "SELECT * FROM cars WHERE vehicle_no = '$vehicle_no'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Vehicle with registration number '$vehicle_no' already exists in our database.";
        } else {
            // Handle multiple photo uploads
            $photo_paths = [];
            $upload_error = false;
            
            // Check if uploads directory exists
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);  // Create the uploads directory if it doesn't exist
            }
            
            // Check if files were uploaded
            if (isset($_FILES['vehicle_photos']) && !empty($_FILES['vehicle_photos']['name'][0])) {
                // Count total files
                $countfiles = count($_FILES['vehicle_photos']['name']);
                
                // Loop through all files
                for ($i = 0; $i < $countfiles; $i++) {
                    // Get the file name
                    $filename = $_FILES['vehicle_photos']['name'][$i];
                    
                    // Generate unique filename to prevent overwriting
                    $newFilename = uniqid() . "_" . basename($filename);
                    $targetFilePath = "uploads/" . $newFilename;
                    
                    // Upload file
                    if (move_uploaded_file($_FILES['vehicle_photos']['tmp_name'][$i], $targetFilePath)) {
                        $photo_paths[] = $targetFilePath;
                    } else {
                        $upload_error = true;
                        $error_message = "Error uploading one or more files.";
                        break;
                    }
                }
            } else {
                $error_message = "Please upload at least one vehicle photo.";
                $upload_error = true;
            }

            if (!$upload_error && empty($error_message)) {
                // Convert photo paths array to JSON string for storage
                $photos_json = json_encode($photo_paths);
                
                // SQL Query to insert car details into the database
                $query = "INSERT INTO cars (seller_id, owner_name, email, phone, vehicle_no, vehicle_name, price, vehicle_photo, aadhar)
                          VALUES ('$seller_id', '$owner_name', '$email', '$phone', '$vehicle_no', '$vehicle_name', '$price', '$photos_json', '$aadhar')";

                if (mysqli_query($conn, $query)) {
                    $success_message = "Car listed successfully!";
                } else {
                    $error_message = "Failed to list car: " . mysqli_error($conn);
                    
                    // Delete uploaded files if database insert fails
                    foreach ($photo_paths as $path) {
                        if (file_exists($path)) {
                            unlink($path);
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List a Car</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        form input,
        form select,
        form button {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        form input[type="file"] {
            padding: 8px;
        }

        form button {
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #0056b3;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
            font-size: 16px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Custom Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 400px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .success-modal .modal-content {
            border-top: 5px solid #28a745;
        }

        .error-modal .modal-content {
            border-top: 5px solid #dc3545;
        }

        .modal-title {
            margin-top: 10px;
            font-size: 24px;
            font-weight: bold;
        }

        .success-modal .modal-title {
            color: #28a745;
        }

        .error-modal .modal-title {
            color: #dc3545;
        }

        .modal-message {
            margin: 20px 0;
            font-size: 18px;
        }

        .modal-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .success-modal .modal-button {
            background-color: #28a745;
        }

        .error-modal .modal-button {
            background-color: #dc3545;
        }

        .modal-button:hover {
            opacity: 0.85;
        }
        
        /* Vehicle number field highlight */
        input[name="vehicle_no"] {
            text-transform: uppercase;
        }
        
        .field-description {
            font-size: 12px;
            color: #666;
            margin-top: -15px;
            margin-bottom: 15px;
        }
        
        /* Preview images style */
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        
        .image-preview {
            width: 100px;
            height: 100px;
            border-radius: 4px;
            border: 1px solid #ddd;
            object-fit: cover;
            position: relative;
        }
        
        .preview-remove-btn {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4d4d;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 12px;
            border: 1px solid white;
        }
        
        .file-upload-container {
            position: relative;
            overflow: hidden;
            display: block;
            margin-bottom: 20px;
        }
        
        .file-upload-label {
            display: block;
            background: #f0f0f0;
            border: 1px dashed #ccc;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .file-upload-label:hover {
            background: #e9e9e9;
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .upload-icon {
            display: block;
            font-size: 24px;
            margin-bottom: 5px;
        }
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
  .nav-menu li:nth-child(4) { transition-delay: 0.4s; }
}
    </style>
</head>
<body>
<nav class="navbar">
  <div class="container">
    <div class="logo">
      <a href="seller_dashboard.php">
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
      <li><a href="seller_dashboard.php">Home</a></li>
      <li><a href="seller_profile.php">Profile</a></li>
      <li><a href="list_car.php">List Car</a></li>
      <li><a href="index.php">Logout</a></li>
    </ul>
  </div>
</nav>
    <div class="container">
        <h2>List Your Car for Rent</h2>
        <form method="post" enctype="multipart/form-data" id="carForm">
            <div class="form-group">
                <input type="text" name="owner_name" placeholder="Owner Name" required value="<?php echo isset($_POST['owner_name']) ? htmlspecialchars($_POST['owner_name']) : ''; ?>">
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <input type="text" name="phone" placeholder="Phone No" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            <div class="form-group">
                <input type="text" name="vehicle_no" placeholder="Vehicle Registration Number" required value="<?php echo isset($_POST['vehicle_no']) ? htmlspecialchars($_POST['vehicle_no']) : ''; ?>">
                <div class="field-description">Enter the registration number exactly as it appears on your vehicle document</div>
            </div>
            <div class="form-group">
                <input type="text" name="vehicle_name" placeholder="Vehicle Name" required value="<?php echo isset($_POST['vehicle_name']) ? htmlspecialchars($_POST['vehicle_name']) : ''; ?>">
            </div>
            <div class="form-group">
                <input type="number" name="price" placeholder="Per Day Price" required value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Vehicle Photos:</label>
                <div id="imagePreviewContainer" class="image-preview-container"></div>
                <div class="file-upload-container">
                    <label class="file-upload-label">
                        <span class="upload-icon">+</span>
                        <span>Upload Vehicle Photos (Select multiple files)</span>
                        <input type="file" name="vehicle_photos[]" id="vehiclePhotos" class="file-upload-input" accept="image/*" multiple required>
                    </label>
                </div>
                <div class="field-description">You can select multiple photos at once (hold Ctrl/Cmd while selecting files)</div>
            </div>
            
            <div class="form-group">
                <input type="text" name="aadhar" placeholder="Aadhar Card Number" pattern="\d{12}" title="Aadhar Card Number must be 12 digits" required value="<?php echo isset($_POST['aadhar']) ? htmlspecialchars($_POST['aadhar']) : ''; ?>">
            </div>
            <div class="form-group">
                <button type="submit">Submit</button>
            </div>
        </form>

        <div class="back-link">
            <a href="seller_dashboard.php">Back to Dashboard</a>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal success-modal">
        <div class="modal-content">
            <div class="modal-title">Success!</div>
            <div class="modal-message">Car listed successfully!</div>
            <button class="modal-button" onclick="redirectToDashboard()">OK</button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal error-modal">
        <div class="modal-content">
            <div class="modal-title">Error</div>
            <div id="errorMessage" class="modal-message"></div>
            <button class="modal-button" onclick="closeErrorModal()">OK</button>
        </div>
    </div>

    <script>
        function redirectToDashboard() {
            window.location.href = 'seller_dashboard.php';
        }

        function closeErrorModal() {
            document.getElementById('errorModal').style.display = 'none';
        }

        // Immediately execute to show modals if needed
        window.onload = function() {
            <?php if (!empty($success_message)): ?>
                document.getElementById('successModal').style.display = 'block';
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                document.getElementById('errorMessage').innerText = '<?php echo addslashes($error_message); ?>';
                document.getElementById('errorModal').style.display = 'block';
            <?php endif; ?>
        };
        
        // Auto-format vehicle registration number to uppercase
        document.querySelector('input[name="vehicle_no"]').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        
        // Image preview functionality
        document.getElementById('vehiclePhotos').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('imagePreviewContainer');
            previewContainer.innerHTML = ''; // Clear existing previews
            
            const files = e.target.files;
            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (file.type.match('image.*')) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const previewWrapper = document.createElement('div');
                            previewWrapper.style.position = 'relative';
                            previewWrapper.style.display = 'inline-block';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'image-preview';
                            previewWrapper.appendChild(img);
                            
                            previewContainer.appendChild(previewWrapper);
                        }
                        
                        reader.readAsDataURL(file);
                    }
                }
            }
        });
    </script>
</body>
</html>