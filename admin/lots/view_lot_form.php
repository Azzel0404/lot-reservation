<!-- admin/lots/view_lot_form.php -->

<?php
include('../../config/db.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM lot WHERE lot_id = $id";
    $result = mysqli_query($conn, $query);
    $lot = mysqli_fetch_assoc($result);
}
?>

<style>
    /* Modal Content Styling */
    
    .close-btn {
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 1.5rem;
        color: #7f8c8d;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0.25rem 0.5rem;
    }
    
    .close-btn:hover {
        color: #e74c3c;
    }
    
    .view-lot-header {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.25rem;
        padding-right: 2rem; /* Space for close button */
    }
    
    /* Image Preview Section */
    .image-preview-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    
    .image-preview-container div {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 6px;
        text-align: center;
    }
    
    .image-preview-container p {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #34495e;
        font-size: 0.9rem;
    }
    
    .image-preview-container img {
        max-width: 100%;
        max-height: 180px;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    /* PDF Section */
    .pdf-section {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 1.25rem;
    }
    
    .pdf-section p {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #34495e;
        font-size: 0.9rem;
    }
    
    .pdf-link {
        color: #3498db;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
    }
    
    .pdf-link:hover {
        text-decoration: underline;
    }
    
    /* Form Styling */
    #editLotForm {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    
    .form-group {
        margin-bottom: 0.75rem;
    }
    
    .form-group.full-width {
        grid-column: span 2;
    }
    
    label {
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 500;
        color: #34495e;
        font-size: 0.9rem;
    }
    
    input[type="text"],
    input[type="number"],
    select {
        width: 100%;
        padding: 0.6rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9rem;
        transition: border-color 0.2s;
    }
    
    input[type="text"]:focus,
    input[type="number"]:focus,
    select:focus {
        border-color: #3498db;
        outline: none;
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }
    
    input[type="file"] {
        width: 100%;
        padding: 0.4rem;
        border: 1px dashed #ddd;
        border-radius: 4px;
        background: #f8f9fa;
        font-size: 0.85rem;
    }
    
    /* Button Styling */
    .button-group {
        grid-column: span 2;
        display: flex;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }
    
    button[type="submit"] {
        background-color: #2ecc71;
        color: white;
        border: none;
        padding: 0.6rem 1rem;
        border-radius: 4px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background-color 0.2s;
        flex: 1;
    }
    
    button[type="submit"]:hover {
        background-color: #27ae60;
    }
    
    .delete-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 0.6rem 1rem;
        border-radius: 4px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background-color 0.2s;
        flex: 1;
    }
    
    .delete-btn:hover {
        background-color: #c0392b;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .lot-details-container {
            padding: 1rem;
        }
        
        #editLotForm {
            grid-template-columns: 1fr;
        }
        
        .form-group.full-width,
        .button-group {
            grid-column: span 1;
        }
        
        .image-preview-container {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="lot-details-container">
    <button class="close-btn" onclick="closeLotDetailsModal()">×</button>
    <h2 class="view-lot-header">Lot Details</h2>

    <!-- Image Preview Container -->
    <div class="image-preview-container">
        <div>
            <p><strong>Aerial Image:</strong></p>
            <?php if (!empty($lot['aerial_image'])): ?>
                <img src="uploads/<?php echo $lot['aerial_image']; ?>" alt="Aerial Image">
            <?php else: ?>
                <p>No image available</p>
            <?php endif; ?>
        </div>
        <div>
            <p><strong>Numbered Image:</strong></p>
            <?php if (!empty($lot['numbered_image'])): ?>
                <img src="uploads/<?php echo $lot['numbered_image']; ?>" alt="Numbered Image">
            <?php else: ?>
                <p>No image available</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- PDF Preview/Download -->
    <div class="pdf-section">
        <p><strong>PDF File:</strong></p>
        <?php if (!empty($lot['pdf_file'])): ?>
            <a href="uploads/<?php echo $lot['pdf_file']; ?>" target="_blank" class="pdf-link">
                <i class="fas fa-file-pdf"></i> View/Download PDF
            </a>
        <?php else: ?>
            <p>No PDF uploaded.</p>
        <?php endif; ?>
    </div>

    <!-- Lot Edit Form -->
    <form action="update_lot.php" method="POST" enctype="multipart/form-data" id="editLotForm">
        <input type="hidden" name="lot_id" value="<?php echo $lot['lot_id']; ?>">

        <div class="form-group">
            <label for="lot_number">Lot Number:</label>
            <input type="text" id="lot_number" name="lot_number" value="<?php echo $lot['lot_number']; ?>" required>
        </div>

        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" id="location" name="location" value="<?php echo $lot['location']; ?>" required>
        </div>

        <div class="form-group">
            <label for="size_meter_square">Size (m²):</label>
            <input type="number" id="size_meter_square" name="size_meter_square" step="0.01" value="<?php echo $lot['size_meter_square']; ?>" required>
        </div>

        <div class="form-group">
            <label for="price">Price (₱):</label>
            <input type="number" id="price" name="price" step="0.01" value="<?php echo $lot['price']; ?>" required>
        </div>

        <div class="form-group">
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="Available" <?php if ($lot['status'] === 'Available') echo 'selected'; ?>>Available</option>
                <option value="Reserved" <?php if ($lot['status'] === 'Reserved') echo 'selected'; ?>>Reserved</option>
            </select>
        </div>

        <div class="form-group full-width">
            <label for="aerial_image">Change Aerial Image (optional):</label>
            <input type="file" id="aerial_image" name="aerial_image" accept="image/*">
        </div>

        <div class="form-group full-width">
            <label for="numbered_image">Change Numbered Image (optional):</label>
            <input type="file" id="numbered_image" name="numbered_image" accept="image/*">
        </div>

        <div class="form-group full-width">
            <label for="pdf_file">Replace PDF File (optional):</label>
            <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf">
        </div>

        <div class="button-group">
            <button type="submit" id="editBtn" name="update_lot">Update Lot</button>
            <button type="button" class="delete-btn" onclick="deleteLot(<?php echo $lot['lot_id']; ?>)">Delete Lot</button>
        </div>
    </form>
</div>

<!-- Font Awesome for PDF icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">