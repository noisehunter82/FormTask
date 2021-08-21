const form = document.getElementById('my-form'),
  firstName = document.getElementById('first-name'),
  lastName = document.getElementById('last-name'),
  email = document.getElementById('email'),
  telephone = document.getElementById('telephone'),
  address1 = document.getElementById('address1'),
  address2 = document.getElementById('address2'),
  town = document.getElementById('town'),
  county = document.getElementById('county'),
  country = document.getElementById('country'),
  description = document.getElementById('description'),
  cv = document.getElementById('cv'),
  submitButton = document.getElementById('submit'),
  status = document.getElementById('status');



// DISPLAY

const populateSelect = array => {

  array.forEach(name => {

    const option = document.createElement('option');
    option.setAttribute('value', name);
    option.innerText = name;

    country.append(option);

  });
}


const resetForm = () => {

  const inputsFields = document.querySelectorAll('input');

  inputsFields.forEach(input => {
    input.value = '';
  });

  document.getElementById('empty').setAttribute('selected', true);

  description.value = '';

}


const outputStatus = string => status.innerText = string;


// HTTP REQUESTS

const fetchCountries = () => {

  const xhr = new XMLHttpRequest(),
    url = './formhandler.php?onloadData=true';

  xhr.responseType = 'json';

  xhr.onreadystatechange = () => {

    if (xhr.readyState === XMLHttpRequest.DONE) {

      if (xhr.response.status.name === 'ok') {

        populateSelect(xhr.response.data.countriesArr);

      } else {

        outputStatus(xhr.response.status.description);
      }

    }
  }

  xhr.open('GET', url);

  xhr.send();
}



const postFormData = () => {

  const xhr = new XMLHttpRequest(),
    url = './formhandler.php',
    fd = new FormData(form);

  if (cv.files[0]) {
    fd.append("cv", cv.files[0]);
  }

  xhr.responseType = 'json';

  xhr.onreadystatechange = () => {

    if (xhr.readyState === XMLHttpRequest.DONE) {

      if (xhr.response.status.name === 'ok') resetForm();

      outputStatus(xhr.response.status.description);

    }

  }
  xhr.open('POST', url);

  xhr.send(fd);
}



// VALIDATION

const validateInput = el => {
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
      pattern = /^([0-9\+\-\(\)\s]){7,25}$/;
      break;
    case 'address1':
    case 'address2':
      pattern = /^([a-zA-Z0-9,'-\\/().\s])+$/;
      break;
    case 'postcode':
      pattern = /^([a-zA-Z0-9\s])*$/;
      break;
   
  }

  if (el.value.match(pattern)) {
    el.removeAttribute('class', 'invalid');
  } else {
    el.setAttribute('class', 'invalid');
  }

};


const checkIfNotEmpty= el => {
  if (el.value == "") {
    el.classList.add('invalid');
  } else {
    el.classList.remove('invalid');
  }
}


const checkRequiredFields = () => {

  let allRequiredFilled = true;

  const requiredFields = document.querySelectorAll("*[required]");

  requiredFields.forEach(field => {
    if (!field.value) {
      if (!field.classList.contains('invalid')) field.classList.add('invalid');
      allRequiredFilled = false;
    }

  });

  return allRequiredFilled;
}



const checkIfAllValid = () => {
  
  allFieldsValid = true;

  const fields = document.querySelectorAll('input');


  fields.forEach(field => {
    if (field.classList.contains('invalid')) {
      allFieldsValid = false;
    };
  });

  return allFieldsValid;
}



// EVENT HANDLERS

submitButton.onclick = e => {

  e.preventDefault();

  if (!checkRequiredFields()) {
    outputStatus('One or more required fields are empty.');
    return;
  }

  if (!checkIfAllValid()) {
    outputStatus('Some of the fields contain invalid characters.');
    return;
  }

  postFormData();

}



window.onload = () => fetchCountries();