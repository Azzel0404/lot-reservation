<!--client/index.php-->

<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="../client/client.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="body">

<?php include('navbar.php'); ?>

<div class="hero">
    <h1 class="text-center">Welcome to Lot Reservation System!</h1>
</div>

<div class="container my-5">
    <div class="bg-white p-4 rounded shadow-sm">
        <h2 class="text-center section-title">Recommended For You</h2>
        <div class="row g-4 justify-content-center">
        <?php
            $lots = [
                ["img" => "../img/lot1.jpg", "num" => 9, "size" => 150, "city" => "City A"],
                ["img" => "../img/lot2.jpg", "num" => 13, "size" => 250, "city" => "City B"],
                ["img" => "../img/lot3.jpg", "num" => 28, "size" => 350, "city" => "City C"],
                ["img" => "../img/lot4.jpg", "num" => 21, "size" => 450, "city" => "City D"]
                ];
                ?>
            <?php foreach ($lots as $lot): ?>
                <div class="col-md-3">
                    <div class="card shadow h-100">
                        <img src="<?= $lot['img'] ?>" class="card-img-top" alt="Lot Image" style="height: 180px; object-fit: cover;">
                        <div class="card-body">
                            <p><strong>Lot Number:</strong> <?= $lot['num'] ?> <br>
                            <strong>Size:</strong> <?= $lot['size'] ?> sqm <br>
                            <strong>Location:</strong> <?= $lot['city'] ?> <br>
                            <strong>Status:</strong> <span class="status-available">Available</span></p>
                            <a href="#" class="btn btn-primary w-100 mt-2">View</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>
