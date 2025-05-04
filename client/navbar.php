<!--client/navbar.php-->
<nav class="navbar navbar-expand-lg navbar-custom px-4 py-3">
    <a class="navbar-brand d-flex align-items-center">
        <i class="fas fa-user-circle me-2"></i>
        <span>User</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <i class="fas fa-bars"></i>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
        <ul class="navbar-nav mb-2 mb-lg-0 align-items-center">
            <li class="nav-item"><a class="nav-link px-3" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link px-3" href="../client/lots/available_lots.php">Lots</a></li>
            <li class="nav-item"><a class="nav-link px-3" href="reservations.php">Reservations</a></li>
            <li class="nav-item"><a class="nav-link px-3" href="profile/profile.php">Profile</a></li>
            <li class="nav-item">
                <a href="../logout.php" class="btn btn-logout ms-3">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
