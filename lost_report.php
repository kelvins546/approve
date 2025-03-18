<?php
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start Session
session_start();

// Database Configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "approve";

// Establish Connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Initialize Message
$message = '';

// Redirect to login if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Form Submission Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category = $_POST['category'] ?? '';
    $other_category = $_POST['other_category'] ?? ''; // Get the "Other" category if provided

    // If "Other" is selected, use the custom category
    if ($category === 'Other' && !empty($other_category)) {
        $category = $other_category;
    }

    // Proceed with the rest of your form processing...
}

// Form Submission Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $item_name = $conn->real_escape_string($_POST['item_name'] ?? '');
    $category = $conn->real_escape_string($_POST['category'] ?? '');
    $item_details = $conn->real_escape_string($_POST['item_details'] ?? '');
    $location_found = $conn->real_escape_string($_POST['location_found'] ?? '');
    $date_found = $_POST['date_found'] ?? '';
    $time_found = $_POST['time_found'] ?? '';
    $first_name = $conn->real_escape_string($_POST['first_name'] ?? '');
    $last_name = $conn->real_escape_string($_POST['last_name'] ?? '');
    $phone_number = $conn->real_escape_string($_POST['phone_number'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $primary_color = $conn->real_escape_string($_POST['primary_color'] ?? '');
    $brand = $conn->real_escape_string($_POST['brand'] ?? '');
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $tip = $conn->real_escape_string($_POST['tip'] ?? ''); // Tip as a string
    $picture = null;

    // If "Other" is selected, override the category with the "other_category" field value
    if ($category === 'Other' && !empty($other_category)) {
        $category = $other_category;
    }


    // Validation
    if (
        empty($item_name) || empty($category) || empty($location_found) ||
        empty($date_found) || empty($time_found) || empty($first_name) ||
        empty($last_name) || empty($phone_number) || empty($email) ||
        empty($primary_color) || empty($brand) || empty($description)
    ) {
        $message = "<div class='alert alert-danger'>Please fill out all required fields.</div>";
    } else {
        // File Upload
        if (!empty($_FILES['picture']['tmp_name'])) {
            if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                $fileType = mime_content_type($_FILES['picture']['tmp_name']);
                if (strpos($fileType, 'image/') === 0) {
                    $targetDir = "uploads/pending/";
                    $uniqueName = uniqid() . "_" . basename($_FILES["picture"]["name"]);
                    $targetFile = $targetDir . $uniqueName;
                    if (move_uploaded_file($_FILES['picture']['tmp_name'], $targetFile)) {
                        $picture = $conn->real_escape_string($targetFile);
                    } else {
                        $message = "<div class='alert alert-danger'>File upload failed. Please try again.</div>";
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Only image files are allowed.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>File upload error. Code: " . $_FILES['picture']['error'] . "</div>";
            }
        }

        // Database Insertion
        $sql = "INSERT INTO pending_lost_reports (
                    user_id, item_name, category, description, picture, location_found, 
                    date_found, time_found, first_name, last_name, phone_number, email, status, brand, primary_color, tip
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Unclaimed', ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            // Bind Parameters
            $stmt->bind_param(
                "issssssssssssss", // 's' added for tip as a string
                $user_id,
                $item_name,
                $category,
                $description,
                $picture,
                $location_found,
                $date_found,
                $time_found,
                $first_name,
                $last_name,
                $phone_number,
                $email,
                $brand,
                $primary_color,
                $tip
            );
            // Execute Query
            if ($stmt->execute()) {
                $_SESSION['message'] = 'success';
                echo "<script>
                        window.onload = function() {
                            document.getElementById('successModal').style.display = 'block';
                        }
                      </script>";
            } else {
                error_log("SQL Error during execute: " . $stmt->error);
                $message = "<div class='alert alert-danger'>Failed to submit report. Please try again. SQL Error: " . $stmt->error . "</div>";
            }

            $stmt->close();
        } else {
            error_log("SQL Error during preparation: " . $conn->error);
            $message = "<div class='alert alert-danger'>SQL Error: " . $conn->error . "</div>";
        }
    }
}
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');

//start deleting here to remove the count on see details
if (!isset($_SESSION['user_id'])) {
    die("User ID not set. Please log in.");
}

$userId = $_SESSION['user_id'];

// Count user reports
$approvedReportCount = countUserReports($conn, $userId);

function countUserReports($conn, $userId)
{
    $query = "
        SELECT COUNT(*) AS total_reports FROM (
            SELECT id FROM pending_lost_reports WHERE user_id = ?
            UNION ALL
            SELECT id FROM pending_found_reports WHERE user_id = ?
            UNION ALL
            SELECT id FROM approved_lost_reports WHERE user_id = ?
            UNION ALL
            SELECT id FROM approved_found_reports WHERE user_id = ?
        ) AS user_reports
    ";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param('ssss', $userId, $userId, $userId, $userId);
        $stmt->execute();

        // Initialize $totalReports to prevent IDE warnings
        $totalReports = 0;
        $stmt->bind_result($totalReports);
        $stmt->fetch();
        $stmt->close();

        return $totalReports;
    }


    return 0; // Default to 0 if query fails
}

// Display Message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Get the current script name
$currentPage = basename($_SERVER['PHP_SELF']);

// Set the button label based on the current page
$buttonLabel = ($currentPage === 'found_report.php') ? 'Report Found' : (($currentPage === 'lost_report.php') ? 'Report Lost' : 'Report');

// Close Connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Item Report</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:ital,wght@0,100..900;1,100..900&family=Londrina+Outline&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rubik+80s+Fade&family=Rubik+Burned&family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&display=swap"
        rel="stylesheet">
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Hanken Grotesk', Arial, sans-serif;
    }

    body {
        background-color: #fff;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
        display: flex;
        color: #545454;
        flex-direction: column;
        min-height: 100vh;
        background-color: #fff;
    }

    /* Navbar styles */
    .navbar {
        background-color: #fff;
        padding: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: #545454;
        position: sticky;
        top: 0;
        z-index: 10;
        width: 100%;
        display: flex;
        align-items: center;
        /* Center items vertically */
        justify-content: space-between;
        /* Distribute space between items */
    }

    /* Navigation main container */
    .nav-main {
        display: flex;
        align-items: center;
        /* Center items vertically */
        gap: 20px;
    }

    .nav-content {
        background-image: url('images/f1.png');
        background-size: cover;
        background-position: center center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        padding: 60px 0;


    }

    .nav-content-cont {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        margin-left: 70px;

    }

    .nav-main {
        display: flex;
        align-items: center;
        gap: 20px;

        /* Add some spacing between nav-main and nav-content */
    }

    .nav-btn {
        background-color: transparent;
        color: #545454;
        border: none;
        font-size: 16px;
        margin-top: 12px;
        margin-left: 30px;
        cursor: pointer;
        text-align: center;
        display: inline-block;
        transition: color 0.3s ease, text-decoration 0.3s ease;
    }

    /* Hover effect on button */
    .nav-btn:hover {

        text-decoration: underline;
    }

    .icon-btn {
        background-color: #f4f5f6;
        border: 2px solid #000;
        border-radius: 50%;
        cursor: pointer;
        padding: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 20px;
        /* Adjust this value as needed */
        transition: background-color 0.3s ease, border-color 0.3s ease;
        z-index: 99999;
        position: relative;
        /* Enable relative positioning */
        right: -100px;
    }


    .icon-btn {
        z-index: 99999;
        margin-left: auto;
        width: 40px;
        /* Set to desired size */
        height: 40px;
    }




    .nav-main>.icon-btn:hover {
        background-color: #f4f4f9;
        /* Light background on hover */
        border-color: #000;
        /* Darker border on hover */
    }



    .nav-main>.icon-btn:hover .user-icon {
        color: #000;
        /* Darker icon color on hover */
    }

    .user-icon {
        font-size: 24px;
        /* Icon size */
        color: #545454;
        transition: color 0.3s ease;
        /* Smooth color change on hover */
    }

    .user-icon:hover {
        color: #545454;
        /* Darken color on hover */
    }

    .navbar-links {
        margin-left: 100px;
        margin-right: 90px;
    }

    .navbar-links a {
        color: #545454;
        padding: 3px;
        text-decoration: none;
        margin: 20px;
        display: inline-block;

    }

    .navbar-links a:hover {
        text-decoration: underline;
    }

    .navbar-logo {
        height: 90px;
        width: auto;
        margin-right: 0px;
        margin-left: 30px;
        margin-top: 0;
    }

    .navbar-text {
        font-family: "Times New Roman", Times, serif;
        font-size: 36px;
        font-weight: bold;
        white-space: nowrap;
        color: #000 !important;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);

    }

    .LAFh1 {
        font-family: "League Spartan", sans-serif;
        font-optical-sizing: auto;
        font-weight: bold;
    }

    .nav-title h1 {
        font-size: 78px;
        color: #f6efe0;
        font-style: italic;
        font-weight: bold;
        line-height: 1.1;
        width: 700px;
        font-family: 'Hanken Grotesk', Arial, sans-serif;


    }

    .nav-text p {
        font-size: 16px;
        color: #fff;
        line-height: 1.4;
        margin-bottom: 20px;

    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 999;
    }

    .modal-content2 {
        background-color: #fefefe;
        padding: 30px;
        color: #545454;
        border-radius: 10px;
        border: 1px solid #fefefe;
        width: 200px;
        max-width: 100%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.3s ease-out;
        margin-bottom: 0px;
        position: absolute;
        top: 23%;
        right: 0;
        transform: translate(-50%, -50%);
        padding-top: 20px;

    }

    /* Adding the arrow */
    .modal-content2::after {
        content: "";
        position: absolute;
        top: 5px;
        /* Position the arrow vertically */
        right: -10px;
        /* Place the arrow to the right side of the modal */
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        /* Transparent top edge */
        border-bottom: 10px solid transparent;
        /* Transparent bottom edge */
        border-left: 10px solid #fff;
        /* The arrow color matches the modal background */
        z-index: 1000;
        /* Ensures it appears above other elements */
    }

    /* Style for the close button */
    .close-btn {
        position: absolute;
        top: 0px;
        /* Adjust based on your design */
        right: 10px;
        /* Adjust based on your design */
        background: transparent;
        border: none;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        color: #333;
        /* Change color to match your theme */
    }

    .modal-title2 {
        display: inline;
        text-align: center;
    }

    .modal-title2 h3 {
        margin-bottom: 2px;
        font-size: 17px;
    }

    .modal-title2 p {
        margin-bottom: 2px;
        font-size: 14px;
    }

    .butclass {
        display: flex;
        /* Enables flexbox */
        flex-direction: column;
        /* Align items vertically */
        align-items: center;
        /* Center items horizontally */
        gap: 10px;
        /* Adds spacing between the buttons */
        margin-top: 20px;
        /* Optional: add some spacing above the buttons */
    }

    .btn-ok2 {
        padding: 5px 20px;
        color: #545454;
        border: none;
        border-radius: 0px;
        cursor: pointer;
        margin-bottom: 10px;
        text-align: center;
        border: 2px solid #545454;

        /* Allow the button to resize based on content */
        width: 120px;
        /* Optional: Ensure buttons have consistent size */
    }

    .btn-ok2:hover {
        background-color: #ccc;
    }


    .close-btn:hover {
        color: #f00;
        /* Optional: Add hover effect */
    }


    ul {
        margin-left: 30px;
        margin-top: 5px;
        font-size: 14px;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 999;
    }

    .modal-content {
        background-color: #fefefe;
        padding: 30px;
        color: #545454;
        border-radius: 10px;
        border: 1px solid #888;
        width: 400px;
        max-width: 100%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.3s ease-out;
        margin-bottom: 0px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .modal-title {
        display: inline;
        text-align: center;
    }

    .modal-title h3 {
        margin-bottom: 10px;
        font-size: 22px;
    }

    .modal-title p {
        margin-bottom: 15px;
    }

    .modal-cont {
        font-size: 14px;
    }

    .modal-ques {
        margin-bottom: 5px;
        margin-top: 25px;
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }

    .italic {
        font-style: italic;
        color: #545454;
    }

    .button-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        margin-top: 20px;
    }

    .btn-ok {
        padding: 5px 40px;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-ok:hover {
        background-color: #45a049;
    }


    /* Dropdown button styles */

    .dropdown {
        position: relative;
        display: inline-block;
        margin-bottom: 10px;
        z-index: 1;
    }

    /* Dropdown Button */
    .dropdown-btn {
        padding: 5px 20px;
        background-color: #ff7701;
        color: #fff;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.3s ease;
    }

    /* Dropdown Arrow */
    .dropdown-btn::after {
        content: '';
        width: 0;
        height: 0;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-left: 5px solid #fff;

        margin-left: 10px;
        transition: transform 0.3s ease;
        transform: rotate(270deg);
    }

    /* Dropdown Content */
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #ff7701;
        min-width: 180px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        margin-top: 0;
        border-radius: 4px;
        left: 0 !important;
        right: auto;

    }



    /* Show Dropdown Content on Hover */
    .dropdown:hover .dropdown-content {
        display: block;
    }

    /* Dropdown Links */
    .dropdown-content a {
        padding: 10px 16px;
        text-decoration: none;
        display: block;
        color: #fff;
        /* Link text color */
        transition: background-color 0.3s ease;
    }

    /* Dropdown Links Hover Effect */
    .dropdown-content a:hover {
        background-color: #e66a00;
        /* Darker hover background color */
    }

    /* Rotate Arrow on Hover */
    .dropdown:hover .dropdown-btn::after {
        transform: rotate(90deg);
        /* Rotate arrow */
    }

    /* Button Hover Effect */
    .dropdown-btn:hover {
        background-color: #e66a00;
        /* Darker hover background color */
    }

    /* Dropdown Content */
    .dropdown-content1 {
        display: none;
        position: absolute;
        background-color: #fff;
        min-width: 180px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        margin-top: 0;
        border-radius: 4px;
        left: 0 !important;
        right: auto;

    }



    /* Show Dropdown Content on Hover */
    .dropdown:hover .dropdown-content1 {
        display: block;
    }

    /* Dropdown Links */
    .dropdown-content1 a {
        padding: 5px 5px;
        text-decoration: none;
        display: block;
        color: #333;
        /* Link text color */
        transition: background-color 0.3s ease;
    }

    /* Dropdown Links Hover Effect */
    .dropdown-content1 a:hover {
        background-color: #ccc;
        /* Darker hover background color */
    }



    .LAFh1 {
        font-family: "League Spartan", sans-serif;
        font-optical-sizing: auto;
        font-weight: bold;
    }

    .nav-title h1 {
        font-size: 78px;
        color: #f6efe0;
        font-style: italic;
        font-weight: bold;
        line-height: 1.1;
        width: 700px;
        font-family: 'Hanken Grotesk', Arial, sans-serif;


    }

    .nav-text p {
        font-size: 16px;
        color: #fff;
        line-height: 1.4;
        margin-bottom: 20px;

    }

    ul {
        margin-left: 30px;
        margin-top: 5px;
        font-size: 14px;
    }

    .container {
        max-width: 100%;
        width: 80%;
        margin: 1px;
        background-color: #ffffff;
        padding: 40px 40px;
        border-radius: 2px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        box-sizing: border-box;
        z-index: 0;
        margin-top: 925px;
        margin-bottom: 350px;
    }

    .container-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin-top: 0;
        margin-bottom: 0;
        background-color: #fff;
    }

    .container-title {
        display: flex;
        align-items: flex-end;
        justify-content: flex-start;
    }

    .container-title2 h2 {
        margin: 0;
        font-size: 24px;
        color: #333;
    }

    .container-title h2 {
        margin: 0;
        font-size: 24px;
        color: #333;
        line-height: 1.2;
    }

    .container-title p {
        margin: 0;
        font-size: 13px;
        color: #777;
        margin-left: 10px;
        line-height: 1.6;
        display: inline-block;
        vertical-align: middle;
    }

    hr {
        margin-bottom: 20px;
        margin-top: 10px;
    }

    .alert {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
        font-size: 14px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .form-group {
        margin-bottom: 15px;
        flex: 1;
    }

    .form-group p {
        font-size: 13px;
        color: #777;
        margin-top: 5px;
    }

    input[type="checkbox"] {
        width: 15px;
        height: 15px;
        vertical-align: middle;
        margin-right: 4px;
        appearance: none;
        border: 1px solid #545454;
        border-radius: 0;
        background-color: #fff;
        cursor: pointer;
        outline: none;
        display: inline-block;
        position: relative;
    }

    input[type="checkbox"]:checked {
        background-color: #fdd400;
        border-color: #545454;
    }

    input[type="checkbox"]:checked::before {
        content: "✓";
        position: absolute;
        top: 0;
        left: 2px;
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        color: #333;
    }

    input[type="checkbox"]:hover {
        border-color: #333;
    }

    label.terms {
        font-size: 14px;
        display: flex;
        align-items: flex-end;
        gap: 5px;
        color: #777;
        flex-wrap: nowrap;
    }

    .terms-link {
        text-decoration: none;
        color: #333;
        font-style: italic;
    }

    .terms-link:hover {
        text-decoration: underline;
    }

    .align-container {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-top: 40px;
    }

    .btn {
        color: #545454;
        background-color: #fdd400;
        border: 2px solid #545454;
        border-radius: 4px;
        text-align: center;
        cursor: pointer;
        width: 100px;
        height: 40px;
        font-size: 14px;
        transition: background-color 0.3s ease;
        line-height: normal;
        display: inline-block;
    }

    label {
        display: block;
        margin-bottom: 8px;
        font-weight: normal;
        color: #333;
    }

    input[type="text"],
    input[type="number"],
    input[type="date"],
    input[type="time"],
    textarea,
    select {
        width: 100%;
        padding: 6px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 0px;
        font-size: 14px;
    }

    textarea {
        resize: vertical;
    }

    .form-row {
        display: flex;
        justify-content: space-between;
        gap: 4%;
    }

    .form-row .form-group {
        width: 48%;
    }

    .form-row-submit {
        display: flex;
        justify-content: space-between;
        gap: 4%;
        align-items: flex-end;
    }

    .form-row-submit .form-group {
        width: 48%;
    }

    .btn {
        display: block;
        align-items: center;
        color: #fff;
        background-color: #545454;
        border: 2px solid #545454;
        margin-left: auto;
        margin-right: 0;
        border-radius: 4px;
        text-align: center;
        cursor: pointer;
        width: 180px;
        height: 35px;
        font-size: 14px;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Footer */
    .footer {
        background-color: #fff;
        padding: 20px 0;
        color: #545454;
        font-family: 'Hanken Grotesk', sans-serif;
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        position: relative;
        text-align: center;
        margin-top: 600px;
    }

    .footer-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        /* Space out logo and contact text */
        width: 90%;
        margin: 0 auto;
        padding-bottom: 20px;
    }

    .footer-logo {
        align-self: flex-start;
        margin-top: 25px;
    }

    .footer-logo img {
        max-width: 70px;
    }


    .footer-contact {
        text-align: right;
        /* Align text to the right */
        font-size: 14px;
        margin-left: auto;
        width: 20%;
        margin-bottom: 25px;
    }

    .footer-contact h4 {
        font-size: 18px;
        margin-bottom: 10px;
    }

    .footer-contact p {
        font-size: 14px;
        margin-top: 0;

    }

    .all-links {
        display: flex;

        width: 100%;
        margin-top: 20px;
        position: absolute;

        justify-content: center;
    }

    .footer-others {
        display: flex;
        justify-content: center;
        /* Align links in the center */
        gap: 30px;
        top: 190px;
        left: 30%;
        margin-left: 140px;
        margin-top: 20px;
        transform: translateX(-50%);
    }


    .footer-others a {
        color: #545454;
        text-decoration: none;
        font-size: 14px;
    }

    .footer-separator {
        width: 90%;
        height: 1px;
        background-color: #545454;
        margin: 10px auto;
        border: none;
        position: absolute;
        bottom: 40px;
        left: 50%;
        margin-top: 20px;
        transform: translateX(-50%);
    }

    .footer-text {
        font-size: 14px;
        margin-top: 20px;
        color: #545454;
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);

    }
    </style>
</head>


<body>
    <div class="navbar">
        <div class="nav-main">
            <img src="images/logo.png" alt="Logo" class="navbar-logo">
            <span class="navbar-text">UNIVERSITY OF CALOOCAN CITY</span>
            <div class="navbar-links">
                <a href="found_report.php">Home</a>
                <a href="guidelines.php">Guidelines</a>
                <div class="dropdown">
                    <button class="nav-btn">Browse Reports</button>
                    <div class="dropdown-content1">
                        <a href="userview.php">Found Reports</a>
                        <a href="lost_reports.php">Lost Reports</a>
                    </div>
                </div>
            </div>
            <!-- Move the icon button inside nav-main -->
            <button class="icon-btn" onclick="openModal('loginclickmodal')">
                <ion-icon name="person" class="user-icon"></ion-icon>
            </button>
        </div>
    </div>

    <div id="loginclickmodal" class="modal-overlay" style="display: none;">
        <div class="modal-content2">
            <!-- Close Button -->
            <button class="close-btn" onclick="closeModal('loginclickmodal')">&times;</button>

            <div class="modal-title2">
                <h3>Good day, <strong><?= htmlspecialchars($userName) ?></strong>!</h3>
                <p><?= htmlspecialchars($_SESSION['user_id'] ?? '') ?></p>
                <hr>
            </div>
            <div class="butclass">
                <button class="btn-ok2" onclick="window.location.href='usersoloview.php'">
                    See report details (<?= htmlspecialchars($approvedReportCount); ?>)
                </button>

                <button class="btn-ok2" onclick="window.location.href='?logout'">LOG OUT</button>
            </div>
        </div>
    </div>


    </div>
    <div class="nav-content">
        <div class="nav-content-cont">
            <div class="nav-title">
                <h1 class="LAFh1">LOST AND FOUND HELP DESKS</h1>
            </div>
            <div class="nav-text">
                <p>We are located at the main entrance right beside the ticket booth</p>
            </div>

            <!-- Dropdown button for "Report Found" or "Report Lost" -->
            <div class="dropdown">
                <button class="dropdown-btn" aria-haspopup="true" aria-expanded="false">
                    <?php echo htmlspecialchars($buttonLabel); ?>
                </button>
                <div class="dropdown-content" role="menu">
                    <a href="found_report.php" role="menuitem">Report Found</a>
                    <a href="lost_report.php" role="menuitem">Report Lost</a>
                </div>
            </div>

        </div>
    </div>
    <!-- Success Modal -->
    <div id="successModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-title">
                <h3>Lost Item Report Submitted!</h3>
                <p>Thank you for letting us know about your lost item.</p>
            </div>
            <strong>
                <p>Next Steps: <a href="Guidelines.php" class="italic">(See Guidelines)</a></p>
            </strong>
            <ul>
                <li><strong>Our team will review your report.</strong> We’ll match your description with items currently
                    in the Lost & Found.</li>
                <li><strong>If a matching item is found, </strong>we’ll contact you using the information you provided.
                </li>
                <li><strong>Check your email and phone</strong> for any updates regarding your item status.</li>
            </ul>
            <p class="modal-ques"><strong>Want to check in person? </strong></p>
            <p class="modal-cont"> If you'd like, you may visit the Lost & Found office to see if your item has been
                turned in.</p>

            <div class="button-container">
                <button class="btn-ok"
                    onclick="document.getElementById('successModal').style.display='none'">OKAY</button>
            </div>
        </div>
    </div>
    <div class="container-wrapper">
        <div class="container">
            <div class="container-title">
                <h2>Submitting a LOST item</h2>
                <p>Please double-check all the information provided</p>
            </div>
            <hr>
            <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'alert-danger') !== false ? 'alert-danger' : 'alert-success' ?>">
                <?= $message; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <!-- Object Title | Date Found -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="item_name">Object Title</label>
                        <input type="text" name="item_name" id="item_name" required aria-required="true"
                            class="form-control">
                        <p>eg. lost camera, gold ring, toyota car key</p>
                    </div>

                    <div class="form-group">
                        <label for="date_found">Date Loss</label>
                        <input type="date" name="date_found" id="date_found" required aria-required="true"
                            class="form-control">
                    </div>
                </div>

                <!-- Category | Time Found -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" id="category" required aria-required="true" class="form-control"
                            onchange="showOtherField()">
                            <option value="Electronics & Gadgets">Electronics & Gadgets</option>
                            <option value="Jewelry & Accessories">Jewelry & Accessories</option>
                            <option value="Identification & Documents">Identification & Documents</option>
                            <option value="Clothing & Footwear">Clothing & Footwear</option>
                            <option value="Bag & Carriers">Bag & Carriers</option>
                            <option value="Wallet & Money">Wallet & Money</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Other category field, hidden by default -->
                    <div class="form-group" id="otherCategoryField" style="display:none;">
                        <label for="other_category">Please specify</label>
                        <input type="text" name="other_category" id="other_category" class="form-control"
                            placeholder="Enter custom category">
                    </div>




                </div>

                <!-- Brand | Location Found -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="brand">Brand</label>
                        <input type="text" name="brand" id="brand" class="form-control">
                        <p> (Ralph Lauren, Samsung, KithenAid, etc.)</p>
                    </div>

                    <div class="form-group">
                        <label for="location_found">Last Known Location</label>
                        <select name="location_found" id="location_found" required aria-required="true"
                            class="form-control">
                            <!-- Uncertainty Option -->
                            <option value="I am not sure">I'm not sure</option>
                            <!-- Main Areas -->
                            <option value="Main Entrance">Main Entrance</option>
                            <option value="Courtyard">Courtyard</option>
                            <option value="Canteen">Canteen</option>
                            <option value="Social Hall">Social Hall</option>
                            <!-- Floor Hallways -->
                            <option value="First Floor Hallway">First Floor Hallway</option>
                            <option value="Second Floor Hallway">Second Floor Hallway</option>
                            <option value="Third Floor Hallway">Third Floor Hallway</option>
                            <option value="Fourth Floor Hallway">Fourth Floor Hallway</option>
                            <!-- Additional Landmark -->
                            <option value="Parking Area">Parking Area</option>
                        </select>
                    </div>

                </div>

                <!-- Primary Color | Image -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="primary_color">Primary Color</label>
                        <input type="text" name="primary_color" id="primary_color" class="form-control">
                        <p>Please add the color that best represents the lost property(Black, Red, Blue, etc.)</p>
                    </div>


                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control"></textarea>

                        <div class="form-group">
                            <label for="tip">Tip (Optional)</label>
                            <input type="text" name="tip" id="tip" class="form-control" step="0.01" placeholder="">
                            <p>Please especify currency(eg. 1000 PHP)</p>

                            <div class="form-group">
                                <label for="picture">Image</label>
                                <input type="file" name="picture" id="picture" class="form-control" accept="image/*"
                                    onchange="previewImage(event)">
                                <div id="image-preview" style="margin-top: 10px;">
                                    <!-- Preview will appear here -->
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
                <!-- Contact Information -->
                <div class="container-title2">
                    <h2>Contact Information</h2>
                    <hr>
                </div>
                <div class="form-group">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" required aria-required="true">
                            <div class="form-control">
                                <p>Please enter your first name (This will appear on your submission)</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" required aria-required="true">
                            <div class="form-control">
                                <p>Please enter your Last name (This will appear on your submission)</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="text" name="phone_number" id="phone_number" required aria-required="true">
                            <div class="form-control">
                                <p>Please enter your Phone number (This will appear on your submission)</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Email</label>
                            <input type="text" name="email" id="email" required aria-required="true">
                            <div class="form-control">
                                <p>Please enter your Email (This will appear on your submission)</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group align-container">
                            <label class="terms">
                                <input type="checkbox" name="terms" required>
                                I agree to the <a href="guidelines.php" class="terms-link">terms and conditions</a>
                            </label>

                            <input type="submit" class="btn" value="Submit">
                        </div>
                    </div>


                </div>

            </form>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="images/logo.png" alt="Logo" />
                <img src="images/caloocan.png" alt="Logo" />

            </div>
            <div class="all-links">
                <nav class="footer-others">
                    <a href="">ABOUT US</a>
                    <a href="">TERMS</a>
                    <a href="">FAQ</a>
                    <a href="">PRIVACY</a>
                </nav>
            </div>


            <div class="footer-contact">
                <h4>Contact us</h4>
                <p>This website is currently under construction. For futher inquires, please contact us at
                    universityofcaloocan@gmailcom</p>
            </div>
            <hr class="footer-separator">
            <p class="footer-text">&copy; University of Caloocan City, All rights reserved.</p>
        </div>
    </footer>
    <script>
    // Function to close the modal by ID
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Function to open the modal by ID
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    // Show success modal if report submission is successful
    <?php if (isset($_GET['success']) && $_GET['success'] == 'true') { ?>
    document.addEventListener('DOMContentLoaded', function() {
        openModal('successModal');

        // Remove 'success' query parameter from URL to prevent modal from showing again on refresh
        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        window.history.replaceState({}, document.title, url.toString());
    });
    <?php } ?>

    // Show greeting modal only if logged in and no report was submitted
    <?php if (isset($_SESSION['user_id']) && !isset($_GET['success']) && !isset($_SESSION['greeting_shown'])) { ?>
    document.addEventListener('DOMContentLoaded', function() {
        openModal('greetingModal');
    });
    <?php } ?>

    // Function to close the modal by ID
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Function to open the modal by ID
    function openModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Show the "Other" category input field when "Other" is selected
    // Show the "Other" category input field when "Other" is selected
    function showOtherField() {
        var category = document.getElementById("category").value;
        var otherCategoryField = document.getElementById("otherCategoryField");

        // Display input field if "Other" is selected
        if (category === "Other") {
            otherCategoryField.style.display = "block"; // Show the input field
        } else {
            otherCategoryField.style.display = "none"; // Hide the input field
        }
    }

    // Trigger the function initially to handle any pre-selected value
    window.onload = function() {
        showOtherField();
    }
    </script>
    <script>
    function previewImage(event) {
        const fileInput = event.target;
        const previewContainer = document.getElementById('image-preview');

        // Clear previous preview
        previewContainer.innerHTML = '';

        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                // Create an image element
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Uploaded Image';
                img.style.maxWidth = '100%';
                img.style.maxHeight = '200px'; // Adjust size as needed
                img.style.border = '1px solid #ccc';
                img.style.padding = '5px';
                img.style.borderRadius = '4px';

                // Append to the preview container
                previewContainer.appendChild(img);
            };

            reader.readAsDataURL(file);
        }
    }
    </script>



    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>

</html>