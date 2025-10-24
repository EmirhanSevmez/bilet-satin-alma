<?php 

include __DIR__ . '/cities.php'; 
require_once __DIR__ ."/auth/src/bootstrap.php";
require_once __DIR__ ."/fpdf/fpdf.php";

$user_logged_in = is_user_logged_in();
$current_user = $user_logged_in ? current_user() : null;
$user_role = $current_user['role'] ?? '';
if ( $user_role != 'user') {
    redirect_with_message("/Ebilet/","You can't access this page.","error") ;
}


if ($_POST){
    $ticket_id = $_POST["ticket_id"];
    $ticket = get_ticket_by_ticket_id($ticket_id);
    $route = find_bus_routes_from_id($ticket['trip_id']);
}
else {
    redirect_with_message("myaccount","Error","error") ;
}

if ($current_user["id"] != $ticket["user_id"]) {
    redirect_with_message("myaccount","This is not your ticket","error") ;
    }


$booked_seats_data = get_taken_seats_by_ticket_id($ticket['id']);
$seat_numbers = array_column($booked_seats_data, 'seat_number');
$seat_info =  implode(', ', $seat_numbers);
                
function to_pdf_text($text) {
    if (!is_string($text)) {
        $text = (string)$text;
    }
    return iconv('UTF-8', 'ISO-8859-9//IGNORE', $text);
}

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont("Arial","B",16);

$pdf->Cell(40,10,"Ebilet - Ticket Receipt");
$pdf->Ln(10);

$ticket_number = $ticket_id;
$departure = $cities[$route["departure_city"]];
$arrival = $cities[$route["destination_city"]];
$departure_time = $route["departure_time"];
$arrival_time = $route["arrival_time"];
$cost = $ticket["total_price"];
$seat_info =  implode(', ', $seat_numbers);


$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10,'Name Surname: ' . htmlspecialchars($current_user['full_name']),0,1,"C");
$pdf->Cell(0,10, to_pdf_text('Ticket No: ') . $ticket_number ,0,1,'C');
$pdf->Cell(0,10, to_pdf_text("Departure: " . $departure),0,1 ,"C" );
$pdf->Cell(0,10, to_pdf_text("Arrival: " . $arrival), 0,1,"C");
$pdf->Cell(0,10, to_pdf_text("Departure Time: ") . format_datetime($departure_time),0,1,"C");
$pdf->Cell(0,10, to_pdf_text("Arrival Time: ") . format_datetime($arrival_time),0,1,"C");
$pdf->Cell(0,10, to_pdf_text("Cost: ") . $cost . " TL",0,1,"C");
$pdf->Cell(0,10, to_pdf_text("Seats: ") . $seat_info,0,1,"C");
$pdf->Ln(10);

$filename = "Ticket_" . $ticket_number .".pdf";
$pdf->Output("D",$filename);


?>
