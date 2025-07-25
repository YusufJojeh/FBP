<?php
require_once 'db.php';

/**
 * Register a new user (plain text password)
 */
function register($username, $email, $password, $role = 'client') {
    global $conn;
    $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $password, $role);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}

/**
 * Login user (plain text password)
 */
function login($email, $password) {
    global $conn;
    $stmt = mysqli_prepare($conn, "SELECT id, password, role FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if ($user && $password === $user['password']) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        // Update last_login
        $update = mysqli_prepare($conn, "UPDATE users SET last_login = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($update, 'i', $user['id']);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
        return true;
    }
    return false;
}