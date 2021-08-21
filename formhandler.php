<?php

include('./helperFunctions.php');

$executionStartTime = microtime(true) / 1000;


// Handle onload request
if (isset($_GET['onloadData'])) {

  $json = file_get_contents("./countries.json");
  $countries = json_decode($json, true);

  $countriesArr = [];

  foreach ($countries as $country) {
    $countriesArr[] = $country['name'];
  }

  usort($countriesArr, function ($a, $b) {
    return $a <=> $b;
  });

  if (count($countriesArr) != 0) {

    $output['data']['countriesArr'] = $countriesArr;

    $output['status']['code'] = "200";
    $output['status']['name'] = "ok";
    $output['status']['description'] = "success";
    $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode($output);

    exit;
  } else {

    $output['status']['code'] = "500";
    $output['status']['name'] = "fail";
    $output['status']['description'] = "Server error. Try reloading the page.";
    $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . " ms";

    header('Content-Type: application/json; charset=UTF-8');

    echo json_encode($output);

    exit;
  }
}


// Handle form submission
if (isset($_POST) && !empty($_POST)) {

  $validatedData;

  foreach($_POST as $key => $value) {
    $validatedData[$key] = validateInput($key,$value);
  }

    
  $firstName = $validatedData['first-name'];
  $lastName = $validatedData['last-name'];
  $email = $validatedData['email'];
  $telephone = $validatedData['telephone'];
  $address1 = $validatedData['address1'];
  $address2 = isset($validatedData['address2']) ? $validatedData['address2'] : '';
  $town = $validatedData['town'];
  $county = $validatedData['county'];
  $country = $validatedData['country'];
  $postcode = $validatedData['postcode'];
  $description = htmlspecialchars($validatedData['description']);



  // Essentials
  $to = $email;
  $subject = "Submitted form data";

  // HTML message
  $htmlMessage = '
  <h1>Submitted form:</h1>
  <p>First name: <span>' . $firstName . '</span></p>
  <p>Last name: <span>' . $lastName . '</span></p>
  <p>Email: <span>' . $email . '</span></p>
  <p>Telephone: <span>' . $telephone . '</span></p>
  <p>Address Line 1: <span>' . $address1 . '</span></p>
  <p>Address Line 2: <span>'  . $address2 . '</span></p>
  <p>Town: <span>' . $town . '</span></p>
 <p>County: <span>' . $county . '</span></p>
  <p>Country: <span>' . $country . '</span></p>
  <p>Postcode: <span>' . $postcode . '</span></p>
 <p>Description: <span>' . $description . '</span></p>';

  
 // Send email without attachment
  if (!isset($_FILES['cv']) || $_FILES['cv']['error'] != 0 || !isCorrectFormat($_FILES['cv']['name'])) {

    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type:text/html;charset=utf-8';
    $headers[] = 'From: FormTask<formtask@example.com>';

    if (mail($to, $subject, $htmlMessage, implode("\r\n", $headers))) {

      $output['status']['code'] = '200';
      $output['status']['name'] = 'ok';
      $output['status']['description'] = 'Success! Form data received and emailed to ' . $email;
      $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . ' ms';

      header('Content-Type: application/json; charset=UTF-8');

      echo json_encode($output);
      exit;

    } else {

      $output['status']['code'] = '500';
      $output['status']['name'] = 'fail';
      $output['status']['description'] = 'Server error. Try again.';
      $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . ' ms';

      header('Content-Type: application/json; charset=UTF-8');

      echo json_encode($output);
      exit;
    }

  
  } else {
    //Send email with attachment


    $file_name = $_FILES['cv']['name'];
    $temp_name = $_FILES['cv']['tmp_name'];
    $file_type = $_FILES['cv']['type'];
    $file_size = $_FILES['cv']['size'];

    $base = basename($file_name);


    $file = fopen($temp_name, 'rb');
    $data = fread($file, filesize($temp_name));
    fclose($file);
    $encoded_data = chunk_split(base64_encode($data));

    $mime_boundary = md5(time());  //unique identifier

    // Standard headers
    $headers[] = 'From: FormTask<formtask@example.com>';
    $headers[] = 'Reply-To: FormTask<formtask@example.com>';
    $headers[] = 'MIME-Version: 1.0';

    //declare multiple kinds of email (plain text + attch)
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $mime_boundary;

    //message part
    $messages[] = '--' . $mime_boundary;
    $messages[] = 'Content-type:text/html;charset=utf-8';
    $messages[] = 'Content-Transfer-Encoding:7bit';
    $messages[] = $htmlMessage;


    //attch part
    $messages[] = '--' . $mime_boundary;
    $messages[] = 'Content-Type: ' . $file_type . ';name=' . $file_name;
    $messages[] = 'Content-Transfer-Encoding: base64';
    $messages[] = 'Content-Disposition: attachment;filename=' . $file_name;
    $messages[] = $encoded_data . "\r\n";
    $messages[] = '--' . $mime_boundary . "--\r\n";



    if (mail($to, $subject, implode("\r\n", $messages), implode("\r\n", $headers))) {

      $output['status']['code'] = '200';
      $output['status']['name'] = 'ok';
      $output['status']['description'] = 'Success! Form data received and emailed to' . $email;
      $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . ' ms';

      header('Content-Type: application/json; charset=UTF-8');

      echo json_encode($output);
      exit;

    } else {

      $output['status']['code'] = '500';
      $output['status']['name'] = 'fail';
      $output['status']['description'] = 'Server error. Try again.';
      $output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . ' ms';

      header('Content-Type: application/json; charset=UTF-8');

      echo json_encode($output);
      exit;

    }
  }
}


$output['status']['code'] = '400';
$output['status']['name'] = 'bad request';
$output['status']['description'] = 'Bad request. Try again.';
$output['status']['returnedIn'] = (microtime(true) - $executionStartTime) / 1000 . ' ms';

header('Content-Type: application/json; charset=UTF-8');

echo json_encode($output);

exit;
