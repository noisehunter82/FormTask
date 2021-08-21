<?php

// Checks file format by its extension
function isCorrectFormat($file_name) {

  if (!isset($file_name)) {
    return false;
  }

  $base = basename($file_name);
  $extension = substr($base, strlen($base) - 4, strlen($base));

  $allowed_extensions = array(".pdf", ".doc", "docx", ".xml");

  if (in_array($extension, $allowed_extensions)) {
    return true;
  }

  return false;
}

// Validate data from the client
function validateInput($type, $string) {

  switch ($type) {
    case 'first-name':
    case 'last-name':
    case 'town':
    case 'county':
    case 'country':
      $pattern = "/^([A-Za-z])([A-Za-z\.\'\-\s])*$/";
      break;
    case 'email':
      $pattern = "/^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/";
      break;
    case 'telephone':
      $pattern = "/^([0-9\+\-\(\)\s]){7,25}$/";
      break;
    case 'address1':
    case 'address2':
      $pattern = "/^([a-zA-Z0-9,'-\/().\s])+$/";
      break;
    case 'postcode':
      $pattern = "/^([a-zA-Z0-9\s])*$/";
      break;
    default:
      return $string;
  }

  return filter_var($string, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $pattern)));
}
