/**
 * Created by goat on 28/02/2017.
 */

var emailField = document.getElementById('emailinput');
var usernameField = document.getElementById('usernameinput');
var passwordField = document.getElementById('passwordinput');
var repeatField = document.getElementById('repeatpassword');

var emailTimer = null;
var usernameTimer = null;
var passwordTimer = null;
var repeatTimer = null;





emailField.addEventListener('input', function(){
    var value = this.value;
    var id = this.getAttribute('id');
    var errorMessage = document.getElementById('error-' + id);


    clearTimeout(emailTimer);
    emailTimer = setTimeout(function() {
        if(validateEmail(value)){
            emailField.style.borderColor = 'green';
            errorMessage.style.display = 'none';
        }else{
            if(value == ''){
                errorMessage.innerHTML = 'Email is required.'
            }else{
                emailField.style.borderColor = '#fa5c6a';
                errorMessage.innerHTML = 'Email format must be "someone@example.com".';
                errorMessage.style.display = 'block';
            }
        }
    }, 500);
});

usernameField.addEventListener('input', function(){
    var value = this.value;
    var id = this.getAttribute('id');
    var errorMessage = document.getElementById('error-' + id);

    clearTimeout(usernameTimer);
    usernameTimer = setTimeout(function() {
        if(value.length >= 6){
            usernameField.style.borderColor = 'green';
            errorMessage.innerHTML = '';
        }else if(value == '') {
            errorMessage.innerHTML = 'Username is required.';
            usernameField.style.borderColor = '#fa5c6a';
        }else if(value.length < 6) {
            errorMessage.innerHTML = 'Username must be longer than 6 characters.';
            usernameField.style.borderColor = '#fa5c6a';
        }
    }, 500);
});


passwordField.addEventListener('input', function(){
    var field = this;
    var value = this.value;
    var id = this.getAttribute('id');
    var errorMessage = document.getElementById('error-' + id);
    var errMatch = document.getElementById('error-repeatpassword');

    clearTimeout(passwordTimer);
    passwordTimer = setTimeout(function() {
        if(value.length >= 6){
            field.style.borderColor = 'green';
            errorMessage.innerHTML = '';
        }else if(value == '') {
            errorMessage.innerHTML = 'Password is required.';
            field.style.borderColor = '#fa5c6a';
        }else if(value.length < 6) {
            errorMessage.innerHTML = 'Password must be longer than 6 characters.';
            field.style.borderColor = '#fa5c6a';
        }
        check_matching_passwords(errMatch, repeatField);

    }, 500);
});

repeatField.addEventListener('input', function(){
    var field = this;
    var value = this.value;
    var id = this.getAttribute('id');
    var errorMessage = document.getElementById('error-' + id);

    clearTimeout(repeatTimer);
    repeatTimer = setTimeout(function() {
        check_matching_passwords(errorMessage, field);
    }, 500);
});


function check_matching_passwords(errorMessage, field){
    if(passwordField.value == repeatField.value){
        field.style.borderColor = 'green';
        errorMessage.innerHTML = '';
    }else{
        errorMessage.innerHTML = 'Passwords are not matching.';
        field.style.borderColor = '#fa5c6a';
    }
}



function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}


