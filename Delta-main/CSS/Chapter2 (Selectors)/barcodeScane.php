<?php
// Get the JSON data from the Flutter app
// Insert the data into the MySQL database
$db_host = "localhost";  // Replace with your host name
$db_user = "id19533263_oms";  // Replace with your database username
$db_pass = "Ketanch@8866";  // Replace with your database password
$db_name = "id19533263_oms_lirmo";  // Replace with your database name
// $conection = mysqli_connect("localhost","id19533263_oms","Ketanch@8866",'id19533263_oms_lirmo');
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
// Check the connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
$awb_no = $_POST['awb'];
$subOrder_no = $_POST['subOrderId'];
$uid = $_POST['id'];
$temp = array();
$notMatch = "";
$match = "";
$updatecntPayment = 0;
$updatePaymentrejectcnt = 0;
if ($subOrder_no == '') {
  $selectPaymentQuery = "SELECT * FROM `PaymentSheetData` WHERE `return_status`='Received' AND  `uid`='$uid' AND `awb_no` ='$awb_no' ";
  $selectReturnQuery = "SELECT * FROM `ReturnData` WHERE `return_status_to_seller`='Received' AND `uid`='$uid' AND `AWB_number` ='$awb_no'";
  $checkPaymentData = mysqli_query($conn, $selectPaymentQuery);
  $checkReturnData = mysqli_query($conn, $selectReturnQuery);
  
  if (($checkReturnData && mysqli_num_rows($checkReturnData) == 1) ||($checkPaymentData && mysqli_num_rows($checkPaymentData) == 1)) {
    if ($checkPaymentData && mysqli_num_rows($checkPaymentData) == 1) {

      $match = "Matched " . $awb_no . " In Both ReturnSheet And PaymentSheet";
    } else {
     
      $arr =mysqli_fetch_assoc($checkReturnData);
     $orderId =$arr['sub_order_number'];

      $updatePaymentQuery = "UPDATE `PaymentSheetData` SET `return_status`='Received',`awb_no` ='$awb_no' WHERE `uid`='$uid' AND `sub_order_no` ='$orderId'";
      
    $updatePayment = mysqli_query($conn, $updatePaymentQuery);
    if($updatePayment){
      $match = "Matched " . $awb_no . " In Return only, and Update payment Sheet";
    }else{
      $match = "Matched " . $awb_no . " In Return only";
    }

   
    }
  } else {
    $updatePaymentQuery = "UPDATE `PaymentSheetData` SET `return_status`='Received' WHERE `uid`='$uid' AND `awb_no` ='$awb_no' ";
    $updateQuery = "UPDATE `ReturnData` SET `return_status_to_seller`='Received' WHERE `uid`='$uid' AND `AWB_number` ='$awb_no'";

    $updatePayment = mysqli_query($conn, $updatePaymentQuery);
    $datacnt = mysqli_affected_rows($conn);
    $update = mysqli_query($conn, $updateQuery);
    $returndatacnt = mysqli_affected_rows($conn);
    if ($update && $returndatacnt > 0) {
      if ($updatePayment && $datacnt > 0) {

        $match = "Matched " . $awb_no . " In Both ReturnSheet And PaymentSheet";
      } else {
        $match = "Matched " . $awb_no . " In Return only";
      }
    } else {
      $notMatch = "Not Matched this awb No." . $awb_no;
    }
  }
} else {
  $selectPaymentQuery = "SELECT * FROM `PaymentSheetData` WHERE `return_status`='Received' AND  `uid`='$uid' AND `sub_order_no` ='$subOrder_no'";
  $selectReturnQuery = "SELECT * FROM `ReturnData` WHERE `return_status_to_seller`='Received' AND `uid`='$uid' AND `sub_order_number` ='$subOrder_no'";
  $checkPaymentData = mysqli_query($conn, $selectPaymentQuery);
  $checkReturnData = mysqli_query($conn, $selectReturnQuery);
  
  if (($checkReturnData && mysqli_num_rows($checkReturnData) == 1) || ($checkPaymentData && mysqli_num_rows($checkPaymentData) == 1) ){
    if (($checkPaymentData && mysqli_num_rows($checkPaymentData) == 1) && ($checkReturnData && mysqli_num_rows($checkReturnData) == 1)) {

      $match = "Matched " . $subOrder_no . " In Both ReturnSheet And PaymentSheet";
    } else if($checkReturnData && mysqli_num_rows($checkReturnData) == 1) {
      $match = "Matched " . $subOrder_no . " In ReturnSheet only";
      
    }else{
        $match = "Matched " . $subOrder_no . " In PaymentSheet only";
        
    }
  } else {
    $updatePaymentQuery = "UPDATE `PaymentSheetData` SET `return_status`='Received' WHERE `uid`='$uid' AND `sub_order_no` ='$subOrder_no' ";
    $updateQuery = "UPDATE `ReturnData` SET `return_status_to_seller`='Received' WHERE `uid`='$uid' AND `sub_order_number` ='$subOrder_no'";

    $updatePayment = mysqli_query($conn, $updatePaymentQuery);
    $datacnt = mysqli_affected_rows($conn);
    $update = mysqli_query($conn, $updateQuery);
    $returndatacnt = mysqli_affected_rows($conn);
      if ($update && $returndatacnt > 0) {
    if ($updatePayment && $datacnt > 0) {
    
        $match = "Matched " . $subOrder_no . " In Both ReturnSheet And PaymentSheet";
      } else {
        $match = "Matched " . $subOrder_no . " In ReturnSheet only";
      }
    } else {
      $notMatch = "Not Matched this " . $subOrder_no;
    }
  }
}

$temp["not_match"] = $notMatch;
$temp["matched"] = $match;
echo json_encode($temp);
// Close the database connection
$conn->close();
?>