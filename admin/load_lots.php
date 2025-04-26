<?php
// admin/load_lots.php

include('../config/db.php');

$query = "SELECT * FROM lot ORDER BY lot_id DESC";
$result = $conn->query($query);

// Start building the table HTML
$output = '';
while ($lot = $result->fetch_assoc()) {
    $output .= '<tr>';
    $output .= '<td>' . htmlspecialchars($lot['lot_number']) . '</td>';
    $output .= '<td>' . htmlspecialchars($lot['location']) . '</td>';
    $output .= '<td>' . $lot['size_meter_square'] . '</td>';
    $output .= '<td>₱' . number_format($lot['price'], 2) . '</td>';
    $output .= '<td>' . $lot['status'] . '</td>';
    $output .= '<td>';
    if (!empty($lot['aerial_image'])) {
        $output .= '<img src="../' . htmlspecialchars($lot['aerial_image']) . '" class="thumbnail" alt="Aerial">';
    } else {
        $output .= 'N/A';
    }
    $output .= '</td>';
    $output .= '<td>';
    if (!empty($lot['numbered_image'])) {
        $output .= '<img src="../' . htmlspecialchars($lot['numbered_image']) . '" class="thumbnail" alt="Numbered View">';
    } else {
        $output .= 'N/A';
    }
    $output .= '</td>';
    $output .= '<td><button class="editBtn" onclick="openEditLotModal(' . $lot['lot_id'] . ', \'' . htmlspecialchars($lot['lot_number']) . '\', \'' . htmlspecialchars($lot['location']) . '\', ' . $lot['size_meter_square'] . ', ' . $lot['price'] . ', \'' . $lot['status'] . '\')">Edit</button></td>';
    $output .= '</tr>';
}

echo $output;
?>
