const passwordField = document.getElementById('password');
const confirmPasswordField = document.getElementById('password_c');

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var mensagem = document.getElementById("mensagem"+evt.target.name);
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        mensagem.innerText = "Digite apenas números.";
		mensagem.style.display = "inline-block";
        return false;
    }
    mensagem.style.display = "none";
    return true;
}

// Get the form and listen for the submit event
const form = document.getElementById('device');
form.addEventListener('submit', (event) => {
  // If the password and confirmation fields don't match, prevent the form from submitting
  if (passwordField.value !== confirmPasswordField.value) {
    event.preventDefault();
    alert('Passwords do not match');
  }
});

function comparePasswords(evt){
    var mensagem = document.getElementById("mensagem"+evt.target.name);
    
    if (passwordField.value !== confirmPasswordField.value) {
        mensagem.innerText = "Senhas não conferem";
		mensagem.style.display = "inline-block";
    }else{
        mensagem.style.display = "none";
    }
}