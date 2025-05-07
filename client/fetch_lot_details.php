<?php
include('../config/db.php');

if (!isset($_GET['lot_id'])) {
    echo "<div class='alert alert-danger'>Invalid request.</div>";
    exit();
}

$lot_id = intval($_GET['lot_id']);
$stmt = $conn->prepare("SELECT * FROM lot WHERE lot_id = ?");
$stmt->bind_param("i", $lot_id);
$stmt->execute();
$result = $stmt->get_result();
$lot = $result->fetch_assoc();
$stmt->close();

if (!$lot) {
    echo "<div class='alert alert-danger'>Lot not found.</div>";
    exit();
}

// Get the correct base URL path for images
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['SCRIPT_NAME']));
?>

<style>
    .lot-detail-img {
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-height: 250px;
        object-fit: cover;
        width: 100%;
        border: 1px solid #eee;
    }
    .detail-label {
        font-weight: 600;
        color: #2c3e50;
        min-width: 100px;
        display: inline-block;
    }
    .detail-value {
        color: #34495e;
    }
    .price-highlight {
        font-size: 1rem;
        color: #e74c3c;
        font-weight: 700;
    }
    .status-badge {
        padding: 0.25em 0.35em;
        font-weight: 600;
        border-radius: 4px;
    }
    .status-available {
        background-color: #2ecc71;
        color: white;
    }
    .status-reserved {
        background-color: #f39c12;
        color: white;
    }
    .status-sold {
        background-color: #e74c3c;
        color: white;
    }
    .pdf-link {
        display: inline-block;
        background: #f8f9fa;
        padding: 8px 15px;
        border-radius: 4px;
        color: #3498db;
        text-decoration: none;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }
    .pdf-link:hover {
        background: #e9ecef;
        color: #2874a6;
    }
    .pdf-icon {
        margin-right: 5px;
    }
    .image-container {
        position: relative;
        margin-bottom: 15px;
    }
    .image-label {
        position: absolute;
        bottom: 10px;
        left: 10px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
    }
</style>

<div class="row g-4">
    <div class="col-md-6">
        <div class="d-flex flex-column h-100">
            <?php if (!empty($lot['aerial_image'])): ?>
                <?php 
                $aerial_path = $base_url . '/admin/lots/uploads/' . htmlspecialchars($lot['aerial_image']);
                ?>
                <div class="image-container">
                    <img src="<?= $aerial_path ?>" 
                         class="lot-detail-img" alt="Aerial View of Lot"
                         onerror="this.onerror=null;this.src='https://via.placeholder.com/600x400?text=Aerial+Image+Not+Found';">
                    <span class="image-label">Aerial View</span>
                </div>
            <?php endif; ?>

            <?php if (!empty($lot['numbered_image'])): ?>
                <?php 
                $numbered_path = $base_url . '/admin/lots/uploads/' . htmlspecialchars($lot['numbered_image']);
                ?>
                <div class="image-container">
                    <img src="<?= $numbered_path ?>" 
                         class="lot-detail-img" alt="Numbered Layout of Lot"
                         onerror="this.onerror=null;this.src='https://via.placeholder.com/600x400?text=Numbered+Image+Not+Found';">
                    <span class="image-label">Numbered Layout</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="d-flex flex-column h-100">
            <h4 class="mb-4">Lot <?= htmlspecialchars($lot['lot_number']) ?></h4>
            
            <div class="mb-3">
                <span class="detail-label">Location:</span>
                <span class="detail-value"><?= htmlspecialchars($lot['location']) ?></span>
            </div>
            
            <div class="mb-3">
                <span class="detail-label">Size:</span>
                <span class="detail-value"><?= number_format($lot['size_meter_square'], 2) ?> sqm</span>
            </div>
            
            <div class="mb-3">
                <span class="detail-label">Price:</span>
                <span class="price-highlight">₱<?= number_format($lot['price'], 2) ?></span>
            </div>
            
            <div class="mb-3">
                <span class="detail-label">Status:</span>
                <span class="status-badge status-<?= strtolower($lot['status']) ?>">
                    <?= htmlspecialchars($lot['status']) ?>
                </span>
            </div>
            
            <?php if (!empty($lot['pdf_file'])): ?>
                <?php 
                $pdf_path = $base_url . '/admin/lots/uploads/' . htmlspecialchars($lot['pdf_file']);
                ?>
                <div class="mb-3 mt-auto">
                    <a href="<?= $pdf_path ?>" 
                       class="pdf-link" target="_blank" download>
                        <i class="fas fa-file-pdf pdf-icon"></i>Download Lot Documents
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>