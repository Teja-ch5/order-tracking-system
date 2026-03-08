<?php
/**
 * auth.php
 * --------
 * Handles user authentication: login, logout, registration.
 * Uses MD5 hashed passwords and session-based role management.
 */

require_once 'config.php';

// ─────────────────────────────────────────────
// LOGIN
// ─────────────────────────────────────────────
function loginUser($conn, $email, $password) {
    $email    = sanitize($conn, $email);
    $password = hashPassword($password);

    $sql = "SELECT user_id, firstname, lastname, email, type, branch_id
            FROM users
            WHERE email = '$email' AND password = '$password'
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Set session variables
        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['user_name']  = $user['firstname'] . ' ' . $user['lastname'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type']  = $user['type'];
        $_SESSION['branch_id']  = $user['branch_id'];

        return ['success' => true, 'user' => $user];
    }

    return ['success' => false, 'message' => 'Invalid email or password.'];
}

// ─────────────────────────────────────────────
// LOGOUT
// ─────────────────────────────────────────────
function logoutUser() {
    session_unset();
    session_destroy();
    redirect('index.php?msg=logged_out');
}

// ─────────────────────────────────────────────
// REGISTER NEW USER
// ─────────────────────────────────────────────
function registerUser($conn, $data) {
    $firstname = sanitize($conn, $data['firstname']);
    $lastname  = sanitize($conn, $data['lastname']);
    $email     = sanitize($conn, $data['email']);
    $password  = hashPassword($data['password']);
    $phone     = sanitize($conn, $data['phone'] ?? '');
    $address   = sanitize($conn, $data['address'] ?? '');
    $type      = ROLE_CUSTOMER; // Default: customer

    // Check if email already exists
    $check = $conn->query("SELECT user_id FROM users WHERE email = '$email'");
    if ($check && $check->num_rows > 0) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }

    $sql = "INSERT INTO users (firstname, lastname, email, password, phone, address, type)
            VALUES ('$firstname', '$lastname', '$email', '$password', '$phone', '$address', $type)";

    if ($conn->query($sql)) {
        return ['success' => true, 'message' => 'Registration successful. Please login.'];
    }

    return ['success' => false, 'message' => 'Registration failed. Please try again.'];
}

// ─────────────────────────────────────────────
// GET CURRENT USER INFO
// ─────────────────────────────────────────────
function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;

    $user_id = (int) $_SESSION['user_id'];
    $result  = $conn->query("SELECT * FROM users WHERE user_id = $user_id LIMIT 1");

    if ($result && $result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    return null;
}

// ─────────────────────────────────────────────
// HANDLE POST REQUESTS
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Login
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $result = loginUser($conn, $_POST['email'], $_POST['password']);

        if ($result['success']) {
            $type = $_SESSION['user_type'];
            if ($type == ROLE_ADMIN)    redirect('admin/dashboard.php');
            elseif ($type == ROLE_STAFF) redirect('staff/dashboard.php');
            else                         redirect('user/dashboard.php');
        } else {
            redirect('index.php?error=' . urlencode($result['message']));
        }
    }

    // Register
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $result = registerUser($conn, $_POST);
        if ($result['success']) {
            redirect('index.php?msg=' . urlencode($result['message']));
        } else {
            redirect('register.php?error=' . urlencode($result['message']));
        }
    }

    // Logout
    if (isset($_POST['action']) && $_POST['action'] === 'logout') {
        logoutUser();
    }
}
?>
