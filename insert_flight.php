<?php
require_once "files/db_connection.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $agency_id = $_POST['agency_id'];
    $client_name = $_POST['client_name'];
    $pnr_number = $_POST['pnr_number'];
    $pnr_company = $_POST['pnr_company'];
    $agency_price = $_POST['agency_price'];
    $total_price = $_POST['total_price'];
    $departure_location = $_POST['departure_location'];
    $arrival_location = $_POST['arrival_location'];
    $departure_date = $_POST['departure_date']; // Format: DD/MM/YYYY HH:mm
    $arrival_date = $_POST['arrival_date'];     // Format: DD/MM/YYYY HH:mm

    // Generate a unique booking reference
    do {
        $randomNumber = mt_rand(100000, 999999);
        $booking_reference = "DMCTNAM_" . $randomNumber;

        // Check if the generated reference already exists
        $checkQuery = $conn->prepare("SELECT COUNT(*) FROM booking WHERE bookingreference = ?");
        $checkQuery->bind_param("s", $booking_reference);
        $checkQuery->execute();
        $checkQuery->bind_result($count);
        $checkQuery->fetch();
        $checkQuery->close();
    } while ($count > 0); // Ensure uniqueness

    // Get the current timestamp for CreationDate and StartTime
    $creation_date = date("d/m/Y H:i");
    $start_time = date("Y-m-d H:i:s"); // MySQL DATETIME format

    // Insert into `booking` table
    $insertBookingSQL = "INSERT INTO booking (id, pidagence, pidagent, leaderfirstname, grossamount, totalcharges, bookingservicetype, bookingreference, currencycode, currentstatus, starttime) 
                         VALUES (?, ?, ?, ?, ?, ?, 'flights', ?, 'EUR', 'vouchered', ?)";

    $stmtBooking = $conn->prepare($insertBookingSQL);
    $stmtBooking->bind_param("siisdsss", $pnr_number, $agency_id, $agency_id, $client_name, $agency_price, $total_price, $booking_reference, $start_time);

    if ($stmtBooking->execute()) {
        // Get the auto-incremented `pid` from `booking` to link with `flightitinerary`
        $booking_id = $stmtBooking->insert_id;

        // Insert into `flightitinerary` table
        $insertFlightSQL = "INSERT INTO flightitinerary (pnrNum, pnrCompany, DepartureLocation, ArrivalLocation, DepartureDateTime, ArrivalDateTime, BookingId, CreationDate) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmtFlight = $conn->prepare($insertFlightSQL);
        $stmtFlight->bind_param("ssssssis", $pnr_number, $pnr_company, $departure_location, $arrival_location, $departure_date, $arrival_date, $booking_id, $creation_date);

        if ($stmtFlight->execute()) {
            echo "Flight reservation successfully inserted!";
        } else {
            echo "Error inserting flight itinerary: " . $stmtFlight->error;
        }
        $stmtFlight->close();
    } else {
        echo "Error inserting booking: " . $stmtBooking->error;
    }
    $stmtBooking->close();
}
?>
