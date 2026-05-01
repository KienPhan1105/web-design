<?php

$servername = "localhost"; //địa chỉ máy chủ database
$username = "root"; //tên đăng nhập database
$password = ""; //mật khẩu database
$dbname = "shopweb"; //tên database
$port = 3306; //cổng kết nối, thường là 3306 cho MySQL

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Kiểm tra kết nối
if ($conn->connect_error) {
    echo "Kết nối thất bại: " . $conn->connect_error;
    die("Kết nối thất bại: " . $conn->connect_error);
}else{
    //echo "Kết nối thành công!";
}