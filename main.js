const firstName = document.getElementById('first-name'),
  lastName = document.getElementById('last-name'),
  email = document.getElementById('email'),
  telephone = document.getElementById('telephone'),
  address1 = document.getElementById('address1'),
  address2 = document.getElementById('address2'),
  town = document.getElementById('town'),
  county = document.getElementById('county'),
  country = document.getElementById('country'),
  description = document.getElementById('description'),
  cv = document.getElementById('cv');


// Populates 'select' element of the form.

const populateSelect = (array) => {
  const select = document.getElementById('country');

  const empty = document.createElement('option');
  empty.setAttribute('value', '');
  empty.innerText = '-- Select country --';

  select.append(empty);

  array.forEach(country => {


    const option = document.createElement('option');
    option.setAttribute('value', country);
    option.innerText = country;

    select.append(option);

  });

}

// HTTP Requests

const fetchCountries = () => {

  const xhr = new XMLHttpRequest();

  const url = './formhandler.php?query=onloadData';

  xhr.responseType = 'json';
  xhr.onreadystatechange = () => {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.response.status.name === 'ok') {
        populateSelect(xhr.response.data.countriesArr);
      } else {
        return;
      }
    }
  }

  xhr.open('GET', url);

  xhr.send();
}



const postFormData = () => {

  const xhr = new XMLHttpRequest(),
    url = './formhandler.php',
    fd = new FormData(document.getElementById('my-form'));

  if (cv.value) {
    fd.append("cv", cv.value);
  }
  
  console.log(fd);

  xhr.responseType = 'json';
  xhr.onreadystatechange = () => {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.response) {
        console.log(xhr.response);
        return;
      }
    }
  }

  xhr.open('POST', url);

  xhr.send(fd);

}


// Input validation

const validateInput = (el) => {
  let pattern;

  switch (el.id) {
    case 'first-name':
    case 'last-name':
    case 'town':
    case 'county':
    case 'country':
      pattern = /^([A-Za-z])([A-Za-z\.\'\-\s])*$/;
      break;
    case 'email':
      pattern = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
      break;
    case 'telephone':
      pattern = /^(?:(?:\(?(?:00|\+)([1-4]\d\d|[1-9]\d?)\)?)?[\-\.\ \\\/]?)?((?:\(?\d{1,}\)?[\-\.\ \\\/]?){0,})(?:[\-\.\ \\\/]?(?:#|ext\.?|extension|x)[\-\.\ \\\/]?(\d+))?$/;
      break;
    case 'address1':
    case 'address2':
      pattern = /^([a-zA-Z0-9,'-\\/().\s])+$/;
      break;
    case 'postcode':
      pattern = /^([a-zA-Z0-9\s])*$/;


  }

  if (el.value.match(pattern)) {
    el.removeAttribute('class', 'invalid');
  } else {
    el.setAttribute('class', 'invalid');
  }

};



const checkRequiredFields = () => {

  const requiredFields = document.querySelectorAll("*[required]");

  requiredFields.forEach(field => {
    if (field.value === '') return false;
  });

  return true;
}


// Event handlers

document.getElementById('submit').onclick = (event) => {
  
  event.preventDefault();

  if (checkRequiredFields()) {
    postFormData();
  } else {
    return;
  }

}



window.onload = () => fetchCountries();