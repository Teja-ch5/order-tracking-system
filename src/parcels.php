<?php
/**
 * parcels.php
 * -----------
 * Core logic for parcel management:
 * - Add new parcel
 * - Update parcel status
 * - Track parcel by reference number
 * - List parcels by status
 */

require_once '../config.php';
requireLogin();

// ─────────────────────────────────────────────
// Parcel Status Constants
// ─────────────────────────────────────────────
$PARCEL_STATUSES = [
    'Item Accepted by Courier',
    'Collected',
    'Shipped',
    'In-Transit',
    'Arrived At Destination',
    'Out for Delivery',
    'Ready to Pickup',
    'Delivered',
    'Picked-up',
    'Unsuccessfull Delivery Attempt'
];

// ─────────────────────────────────────────────
// ADD NEW PARCEL
// ─────────────────────────────────────────────
function addParcel($conn, $data) {
    // Generate unique reference number
    $reference = generateReferenceNumber();

    $order_id          = (int) $data['order_id'];
    $sender_name       = sanitize($conn, $data['sender_name']);
    $sender_address    = sanitize($conn, $data['sender_address']);
    $sender_phone      = sanitize($conn, $data['sender_phone']);
    $recipient_name    = sanitize($conn, $data['recipient_name']);
    $recipient_address = sanitize($conn, $data['recipient_address']);
    $recipient_phone   = sanitize($conn, $data['recipient_phone']);
    $weight            = (float) $data['weight'];
    $height            = sanitize($conn, $data['height']);
    $length            = sanitize($conn, $data['length']);
    $width             = sanitize($conn, $data['width']);
    $dimensions        = "{$height}x{$length}x{$width} cm";
    $parcel_type       = sanitize($conn, $data['parcel_type']);
    $delivery_type     = sanitize($conn, $data['delivery_type']);
    $price             = (float) $data['price'];
    $branch_processed  = (int) $data['branch_processed'];
    $pickup_branch     = isset($data['pickup_branch']) ? (int) $data['pickup_branch'] : null;
    $pickup_val        = $pickup_branch ? $pickup_branch : 'NULL';

    $sql = "INSERT INTO parcels
            (reference_number, order_id, sender_name, sender_address, sender_phone,
             recipient_name, recipient_address, recipient_phone, dimensions, weight,
             parcel_type, delivery_type, price, branch_processed, pickup_branch, status)
            VALUES
            ('$reference', $order_id, '$sender_name', '$sender_address', '$sender_phone',
             '$recipient_name', '$recipient_address', '$recipient_phone', '$dimensions', $weight,
             '$parcel_type', '$delivery_type', $price, $branch_processed, $pickup_val,
             'Item Accepted by Courier')";

    if ($conn->query($sql)) {
        $parcel_id = $conn->insert_id;
        // Log initial tracking record
        addTrackingRecord($conn, $parcel_id, 'Origin Branch', 'Item Accepted by Courier');
        return ['success' => true, 'reference' => $reference, 'parcel_id' => $parcel_id];
    }

    return ['success' => false, 'message' => $conn->error];
}

// ─────────────────────────────────────────────
// UPDATE PARCEL STATUS
// ─────────────────────────────────────────────
function updateParcelStatus($conn, $parcel_id, $status, $location = '') {
    $parcel_id = (int) $parcel_id;
    $status    = sanitize($conn, $status);
    $location  = sanitize($conn, $location);

    $sql = "UPDATE parcels SET status = '$status' WHERE parcel_id = $parcel_id";

    if ($conn->query($sql)) {
        addTrackingRecord($conn, $parcel_id, $location, $status);
        return ['success' => true];
    }

    return ['success' => false, 'message' => $conn->error];
}

// ─────────────────────────────────────────────
// ADD TRACKING RECORD
// ─────────────────────────────────────────────
function addTrackingRecord($conn, $parcel_id, $location, $status) {
    $parcel_id  = (int) $parcel_id;
    $location   = sanitize($conn, $location);
    $status     = sanitize($conn, $status);
    $updated_by = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

    $sql = "INSERT INTO parcel_tracks (parcel_id, location, status, updated_by)
            VALUES ($parcel_id, '$location', '$status', $updated_by)";

    return $conn->query($sql);
}

// ─────────────────────────────────────────────
// TRACK PARCEL BY REFERENCE NUMBER
// ─────────────────────────────────────────────
function trackParcel($conn, $reference_number) {
    $reference = sanitize($conn, $reference_number);

    // Get parcel info
    $sql = "SELECT p.*, b.branch_name AS processed_branch
            FROM parcels p
            LEFT JOIN branches b ON p.branch_processed = b.branch_id
            WHERE p.reference_number = '$reference'
            LIMIT 1";

    $result = $conn->query($sql);
    if (!$result || $result->num_rows === 0) {
        return ['success' => false, 'message' => 'Tracking number not found.'];
    }

    $parcel = $result->fetch_assoc();

    // Get tracking history (chronological order)
    $track_sql = "SELECT * FROM parcel_tracks
                  WHERE parcel_id = {$parcel['parcel_id']}
                  ORDER BY timestamp ASC";

    $track_result = $conn->query($track_sql);
    $tracking     = [];

    while ($row = $track_result->fetch_assoc()) {
        $tracking[] = $row;
    }

    return [
        'success'  => true,
        'parcel'   => $parcel,
        'tracking' => $tracking
    ];
}

// ─────────────────────────────────────────────
// GET ALL PARCELS (Admin)
// ─────────────────────────────────────────────
function getAllParcels($conn, $status = null) {
    $where = $status ? "WHERE p.status = '" . sanitize($conn, $status) . "'" : '';

    $sql = "SELECT p.*, b.branch_name
            FROM parcels p
            LEFT JOIN branches b ON p.branch_processed = b.branch_id
            $where
            ORDER BY p.date_created DESC";

    $result  = $conn->query($sql);
    $parcels = [];

    while ($row = $result->fetch_assoc()) {
        $parcels[] = $row;
    }

    return $parcels;
}

// ─────────────────────────────────────────────
// GET PARCELS BY BRANCH (Staff)
// ─────────────────────────────────────────────
function getParcelsByBranch($conn, $branch_id, $status = null) {
    $branch_id = (int) $branch_id;
    $where     = "WHERE p.branch_processed = $branch_id";
    if ($status) {
        $where .= " AND p.status = '" . sanitize($conn, $status) . "'";
    }

    $sql = "SELECT p.* FROM parcels p $where ORDER BY p.date_created DESC";

    $result  = $conn->query($sql);
    $parcels = [];

    while ($row = $result->fetch_assoc()) {
        $parcels[] = $row;
    }

    return $parcels;
}

// ─────────────────────────────────────────────
// DELETE PARCEL
// ─────────────────────────────────────────────
function deleteParcel($conn, $parcel_id) {
    $parcel_id = (int) $parcel_id;
    $sql = "DELETE FROM parcels WHERE parcel_id = $parcel_id";
    return $conn->query($sql)
        ? ['success' => true]
        : ['success' => false, 'message' => $conn->error];
}

// ─────────────────────────────────────────────
// GENERATE UNIQUE REFERENCE NUMBER
// ─────────────────────────────────────────────
function generateReferenceNumber() {
    return rand(100000000000, 999999999999);
}

// ─────────────────────────────────────────────
// GENERATE REPORT
// ─────────────────────────────────────────────
function generateReport($conn, $from_date, $to_date, $status = 'All') {
    $from = sanitize($conn, $from_date);
    $to   = sanitize($conn, $to_date);

    $where = "WHERE DATE(p.date_created) BETWEEN '$from' AND '$to'";
    if ($status !== 'All') {
        $s = sanitize($conn, $status);
        $where .= " AND p.status = '$s'";
    }

    $sql = "SELECT p.date_created AS date,
                   p.sender_name  AS sender,
                   p.recipient_name AS recipient,
                   p.price        AS amount,
                   p.status
            FROM parcels p
            $where
            ORDER BY p.date_created DESC";

    $result  = $conn->query($sql);
    $reports = [];

    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }

    return $reports;
}

// ─────────────────────────────────────────────
// HANDLE POST REQUESTS
// ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            echo json_encode(addParcel($conn, $_POST));
            break;

        case 'update_status':
            echo json_encode(updateParcelStatus(
                $conn,
                $_POST['parcel_id'],
                $_POST['status'],
                $_POST['location'] ?? ''
            ));
            break;

        case 'delete':
            echo json_encode(deleteParcel($conn, $_POST['parcel_id']));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit();
}

// Handle GET (track)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['track'])) {
    header('Content-Type: application/json');
    echo json_encode(trackParcel($conn, $_GET['track']));
    exit();
}
?>
