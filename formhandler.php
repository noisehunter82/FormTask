<?php

include('./helperFunctions.php');

$executionStartTime = microtime(true) / 1000;

function respond($code, $name, $description, $start) {

  $output['status']['code'] = $code;
  $output['status']['name'] = $name;
  $output['status']['description'] = $description;
  $output['status']['returnedIn'] = (microtime(true) - $start) / 1000 . ' ms';

  header('Content-Type: application/json; charset=UTF-8');

  echo json_encode($output);
  exit;

}


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

  foreach ($_POST as $key => $value) {
    $validatedData[$key] = validateInput($key, $value);
  }


  $firstName = $validatedData['first-name'];
  $lastName = $validatedData['last-name'];
  $email = $validatedData['email'];
  $telephone = $validatedData['telephone'];
  $address1 = $validatedData['address1'];
  $address2 = isset($validatedData['address2']) ? $validatedData['address2'] : '';
  $town = $validatedData['town'];
  $county = isset($validatedData['county']) ? $validatedData['county'] : '';
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

  // Formatting, placement and count of $eol string in $headers and $messages mustn't be changed.
  $eol = "\r\n";


  // Send email without attachment
  if (!is_uploaded_file($_FILES['cv']['tmp_name']) || $_FILES['cv']['error'] != 0 || !isCorrectFormat($_FILES['cv']['name'])) {

    $headers = 'MIME-Version: 1.0' . $eol;
    $headers .= 'Content-type:text/html;charset=utf-8' . $eol;
    $headers .= 'From: FormTask<formtask@example.com>';

    if (mail($to, $subject, $htmlMessage, $headers)) {

      respond('200', 'ok', 'Success! Form data received and emailed to: ' . $email, $executionStartTime);

    } else {

      respond('500', 'fail', 'Server error. Try again.', $executionStartTime);

    }
  } else {
    //Send email with attachment

    $fileName = $_FILES['cv']['name'];
    $tempPath = $_FILES['cv']['tmp_name'];
    $fileType = $_FILES['cv']['type'];
    $fileSize = $_FILES['cv']['size'];

    $base = basename($fileName);

    $encoded_data = chunk_split(base64_encode(file_get_contents($tempPath)));

    $mime_boundary = md5(time());  //unique identifier

    // Standard headers
    $headers = 'From: FormTask<formtask@example.com>' . $eol;
    $headers .= 'Reply-To: FormTask<formtask@example.com>' . $eol;
    $headers .= 'MIME-Version: 1.0' . $eol;
    $headers .= 'Content-Type: multipart/mixed; boundary="' . $mime_boundary . $eol;
    $headers .= 'This is a MIME encoded message.';

    //message part
    $messages = '--' . $mime_boundary . $eol;
    $messages .= 'Content-type:text/html;charset=utf-8' . $eol;
    $messages .= 'Content-Transfer-Encoding:7bit' . $eol;
    $messages .= $htmlMessage . $eol;

    //attch part
    $messages .= '--' . $mime_boundary . $eol;
    $messages .= 'Content-Type:' . $fileType .  ';name="' . $base . '"' . $eol;
    $messages .= 'Content-Transfer-Encoding: base64' . $eol;
    $messages .= 'Content-Disposition:attachment;filename="' . $base . '";size ="' . $fileSize . '"' . $eol . $eol;
    $messages .= $encoded_data . $eol . $eol;
    $messages .= '--' . $mime_boundary . "--";



    if (mail($to, $subject, $messages, $headers)) {

      respond('200', 'ok', 'Success! Form data received and emailed to: ' . $email, $executionStartTime);

    } else {

      respond('500', 'fail', 'Server error. Try again.', $executionStartTime);

    }
  }
}

respond('400', 'bad request', 'Bad request. Try again.', $executionStartTime);

