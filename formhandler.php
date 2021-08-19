<?php
/* 
error_reporting(-1);
ini_set('display_errors', 'On');
set_error_handler("var_dump"); */

$executionStartTime = microtime(true) / 1000;


// Handle onload request
if (isset($_GET) && $_GET['query'] == 'onloadData') {

  $file = file_get_contents("countries.json");
  $countries = json_decode($file, true);

  $countriesArr = [];
  foreach ($countries as $country) {
    $countriesArr[] = $country["name"];
  }

  usort($countriesArr, function ($a, $b) {
    return $a['name'] <=> $b['name'];
  });

  $output['data']['countriesArr'] = $countriesArr;

  $output['status']['code'] = "200";
  $output['status']['name'] = "ok";
  $output['status']['description'] = "countries list returned";
  $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";

  header('Content-Type: application/json; charset=UTF-8');

  echo json_encode($output);

  exit;
}

// Handle form submission
if (isset($_POST) && !empty($_POST)) {

  // Essentials
  $to = $_POST['email'];
  $subject = "Submitted form data";

  // HTML message
  $htmlMessage = '
  <h1>Submitted info:</h1>
  <p>First name: <span>' . $_POST['first-name'] . '</span></p>
  <p>Last name: <span>' . $_POST['last-name'] . '</span></p>
  <p>Email: <span>' . $_POST['email'] . '</span></p>
  <p>Telephone: <span>' . $_POST['telephone'] . '</span></p>
  <p>Address Line 1: <span>' . $_POST['address1'] . '</span></p>
  <p>Address Line 2: <span>'  . $_POST['address2'] . '</span></p>
  <p>Town: <span>' . $_POST['town'] . '</span></p>
 <p>County: <span>' . $_POST['county'] . '</span></p>
  <p>Country: <span>' . $_POST['country'] . '</span></p>
  <p>Postcode: <span>' . $_POST['postcode'] . '</span></p>
 <p>Description: <span>' . $_POST['description'] . '</span></p>';

  // Send email without attachment

  if (!isset($_FILES['cv']) || $_FILES['cv']['error'] != 0) {

    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type:text/html;charset=utf-8';
    $headers[] = 'From: FormTask<formtask@example.com>';

    if (mail($to, $subject, $htmlMessage, implode("\r\n", $headers))) {

      $output['status']['code'] = "200";
      $output['status']['name'] = "ok";
      $output['status']['description'] = "form data received and emailed";
      $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";

      header('Content-Type: application/json; charset=UTF-8');

      echo json_encode($output);

      exit;
    } else {
      $output['status']['code'] = "500";
      $output['status']['name'] = "fail";
      $output['status']['description'] = "Server error";
      $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";

      header('Content-Type: application/json; charset=UTF-8');

      echo json_encode($output);

      exit;
    }

    //Send email with aattachment
  } else {

    $file_name = $_FILES['cv']['name'];
    $temp_name = $_FILES['cv']['tmp_name'];
    $file_type = $_FILES['cv']['type'];
    $file_size = $_FILES['cv']['size'];

             

    $base = basename($file_name);

    $extension = substr($base, strlen($base) - 4, strlen($base));

    //only these file types will be allowed
    $allowed_extensions = array(".pdf", ".doc", "docx", ".xml");


    if (in_array($extension, $allowed_extensions)) {

      echo($file_size);

      $fp =    @fopen($temp_name, "rb");
      $data =  @fread($fp, filesize($temp_name));
      @fclose($fp);
      $encoded_data = chunk_split(base64_encode($data)); 

      $mime_boundary = md5(time());  //unique identifier
      
      //declare multiple kinds of email (plain text + attch)
      $headers[] = 'MIME-Version: 1.0';
      $headers[] = 'From: FormTask<formtask@example.com>';
      $headers[] = 'Content-Type: multipart/mixed; boundary="' . $mime_boundary . '"';
      
      //message part
      $message[] = '--' . $mime_boundary;
      $message[] = 'Content-type:text/html;charset=utf-8';
      $message[] = 'Content-Transfer-Encoding:7bit';
      $message[] = $htmlMessage;


      //attch part
      $message[] = '--' . $mime_boundary;
      $message[] = 'Content-Type:' . $file_type . ';name=' . $file_name;
      $message[] = 'Content-Transfer-Encoding:base64';
      $message[] = 'Content-Disposition:attachment; filename=' . $file_name . ';size=' . filesize($file_temp);
      $message[] = $encoded_data;  



      if (mail($to, $subject, implode("\r\n", $message), implode("\r\n", $headers))) {

        $output['status']['code'] = "200";
        $output['status']['name'] = "ok";
        $output['status']['description'] = "form data received and emailed";
        $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($output);

        exit;
      } else {
        $output['status']['code'] = "500";
        $output['status']['name'] = "fail";
        $output['status']['description'] = "Server error";
        $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";

        header('Content-Type: application/json; charset=UTF-8');

        echo json_encode($output);

        exit;
      }
    }
  }
}


$output['status']['code'] = "400";
$output['status']['name'] = "Bad request";
$output['status']['description'] = "Missing data! Make sure all fields are filled.";

echo json_encode($output);

exit;
