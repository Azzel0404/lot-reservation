<!-- header.php -->
<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?? "Admin Dashboard"; ?></title>
    <link rel="stylesheet" href="../admin/admin.css"> <!-- Your common admin CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
