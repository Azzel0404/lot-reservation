<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    
</head>
<body class="body">

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-4">
    <a class="navbar-brand" href="#">User</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
        <ul class="navbar-nav mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="available_lots.php">Lots</a></li>
            <li class="nav-item"><a class="nav-link" href="my_reservations.php">Reservations</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Payments</a></li>
            <li class="nav-item">
                <a href="../logout.php" class="btn btn-outline-light ms-3">Logout</a>
            </li>
        </ul>
    </div>
</nav>


<div class="hero">
    <div class="hero-text">
        Welcome to Lot Reservation System!
    </div>
</div>


<div class="container my-5">
    <h2 class="text-center section-title">Recommended For You</h2>
    <div class="row g-4 justify-content-center">

        <?php
        // Sample static content, replace with loop pulling from DB
        $lots = [
            ["img" => "../img/lot1.jpg", "num" => 9, "size" => 150, "city" => "City A"],
            ["img" => "../img/lot2.jpg", "num" => 13, "size" => 250, "city" => "City B"],
            ["img" => "../img/lot3.jpg", "num" => 28, "size" => 350, "city" => "City C"],
            ["img" => "../img/lot4.jpg", "num" => 21, "size" => 450, "city" => "City D"]
        ];

        foreach ($lots as $lot): ?>
            <div class="col-md-3">
                <div class="card">
                    <img src="<?= $lot['img'] ?>" class="card-img-top" alt="Lot Image">
                    <div class="card-body">
                        <p>Lot Number: <?= $lot['num'] ?> <br>
                        Size: <?= $lot['size'] ?> sqm <br>
                        Location: <?= $lot['city'] ?> <br>
                        Status: <span class="status-available">Available</span></p>
                        <a href="#" class="btn btn-primary w-100">View</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

</body>
</html>
