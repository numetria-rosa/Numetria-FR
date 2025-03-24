<?php 
header('Content-Type: application/json'); // Ensure JSON response

include 'files/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['startDate'], $data['endDate'], $data['regRef'], $data['rows']) || !is_array($data['rows'])) {
    echo json_encode(["status" => "error", "message" => "Invalid data received"]);
    exit;
}

$startDate = $data['startDate'];
$endDate   = $data['endDate'];
$regRef    = $data['regRef'];
$rows      = $data['rows'];

// Prepare the statement for inserting into archive table
$stmt = $conn->prepare("INSERT INTO archive (regRef, agency_pid, res_pid, ref_booking, agency_name, date_res, price_res, startDate, endDate) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Error in preparing statement: " . $conn->error]);
    exit;
}

$agencyPids = []; // To collect agency PIDs for later use in reglement update/insert
$bookingPids = []; // To collect booking PIDs to update etat_res in booking table
$totalPrice  = 0;  // To sum up the prices of inserted bookings

foreach ($rows as $row) {
    $agencePid = $row['agencePid'] ?? null;
    $bookingPid = $row['bookingPid'] ?? null;
    $agenceName = $row['agenceName'] ?? null;
    $refRes = $row['refRes'] ?? null;
    $dateRes = $row['dateRes'] ?? null;
    $price = isset($row['price']) ? (float)$row['price'] : 0;

    // First check if booking.etat_res = 0 and booking.currentstatus = 'vouchered' for the current bookingPid
    $checkStmt = $conn->prepare("SELECT etat_res, currentstatus FROM booking WHERE pid = ?");
    $checkStmt->bind_param("s", $bookingPid);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $booking = $checkResult->fetch_assoc();
        // Proceed only if booking.etat_res is 0 and currentstatus is 'vouchered'
        if ($booking['etat_res'] == 0 && $booking['currentstatus'] == 'vouchered') {
            $stmt->bind_param("sssssssss", $regRef, $agencePid, $bookingPid, $refRes, $agenceName, $dateRes, $price, $startDate, $endDate);
            if (!$stmt->execute()) {
                echo json_encode(["status" => "error", "message" => "Insert failed: " . $stmt->error]);
                exit;
            }
            $agencyPids[] = $agencePid;    // Save agency PID for reglement update
            $bookingPids[] = $bookingPid;  // Save booking PID to update booking.etat_res later
            $totalPrice += $price;         // Add price to totalPrice
        }
    }
    $checkStmt->close();
}
$stmt->close();

// Update etat_payment in reglement where reg_ref matches the original $regRef
$updateStmt = $conn->prepare("UPDATE reglement SET etat_payment = 1 WHERE reg_ref = ?");
if ($updateStmt) {
    $updateStmt->bind_param("s", $regRef);
    if (!$updateStmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Update failed: " . $updateStmt->error]);
        exit;
    }
    $updateStmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Error in preparing update statement: " . $conn->error]);
    exit;
}

// Update booking.etat_res to 1 for all inserted booking PIDs
if (!empty($bookingPids)) {
    $placeholders = implode(',', array_fill(0, count($bookingPids), '?'));
    $updateBookingStmt = $conn->prepare("UPDATE booking SET etat_res = 1 WHERE pid IN ($placeholders)");
    if ($updateBookingStmt) {
        $updateBookingStmt->bind_param(str_repeat('s', count($bookingPids)), ...$bookingPids);
        if (!$updateBookingStmt->execute()) {
            echo json_encode(["status" => "error", "message" => "Update etat_res failed: " . $updateBookingStmt->error]);
            exit;
        }
        $updateBookingStmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Error in preparing update statement for etat_res: " . $conn->error]);
        exit;
    }
}

// -------------------------------------------------------------------------
// Now, calculate the remaining amount and insert a new reglement row
// -------------------------------------------------------------------------

// 1. Get the current reglement.reg (amount) for the given $regRef
$selectRegStmt = $conn->prepare("SELECT reg FROM reglement WHERE reg_ref = ?");
$selectRegStmt->bind_param("s", $regRef);
$selectRegStmt->execute();
$selectResult = $selectRegStmt->get_result();
if ($selectResult->num_rows > 0) {
    $regRow = $selectResult->fetch_assoc();
    $amount_reg = (float)$regRow['reg'];
} else {
    $amount_reg = 0;
}
$selectRegStmt->close();

// 2. Calculate the remaining amount
$restReg = $amount_reg - $totalPrice;

// 3. Generate a unique reg_ref for the new reglement record
function generateUniqueRegRef($conn) {
    do {
        // Generate a random 5-digit number
        $randomNumber = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        $newRegRef = "REG_" . $randomNumber;

        // Check if reg_ref already exists
        $checkQuery = "SELECT COUNT(*) AS count FROM reglement WHERE reg_ref = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param('s', $newRegRef);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } while ($count > 0); // Keep generating if the reg_ref exists

    return $newRegRef;
}

$newRegRef = generateUniqueRegRef($conn);
// 4. Prepare data for the new reglement insertion
$comment = "REST | " . $regRef;
$reg_on = date('Y-m-d H:i:s');
$agence = !empty($agencyPids) ? $agencyPids[0] : null;  // Use the first agency PID
$currency = "EUR";
$etat_reg = "non encaissé";
$etat_payment = 0;
$rest = 1;

// 5. Insert the new reglement row
$insertRegStmt = $conn->prepare("INSERT INTO reglement (comment, reg_on, reg, agence, currency, etat_reg, etat_payment, reg_ref, rest) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$insertRegStmt) {
    echo json_encode(["status" => "error", "message" => "Error preparing reglement insert: " . $conn->error]);
    exit;
}
$insertRegStmt->bind_param("ssdsssisi", $comment, $reg_on, $restReg, $agence, $currency, $etat_reg, $etat_payment, $newRegRef, $rest);
if (!$insertRegStmt->execute()){
    echo json_encode(["status" => "error", "message" => "Error inserting new reglement: " . $insertRegStmt->error]);
    exit;
}
$insertRegStmt->close();


// Send back the first agencePid to use for redirection along with a success message
echo json_encode([
    "status"    => "success", 
    "message"   => "Data inserted, statuses updated and new reglement created successfully", 
    "agencePid" => $agencyPids[0]
]);
exit;
?>
