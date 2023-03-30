var emailOk=false;

function updateLogin() {
    var x1 = document.getElementById('name_1').value;
    var x2 = document.getElementById('name_2').value;
    document.getElementById("login").value = x1+'.'+x2;
}

function cnpj(v){
    v=v.replace(/\D/g,"")                           //Remove tudo o que não é dígito
    v=v.replace(/^(\d{2})(\d)/,"$1.$2")             //Coloca ponto entre o segundo e o terceiro dígitos
    v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3") //Coloca ponto entre o quinto e o sexto dígitos
    v=v.replace(/\.(\d{3})(\d)/,".$1/$2")           //Coloca uma barra entre o oitavo e o nono dígitos
    v=v.replace(/(\d{4})(\d)/,"$1-$2")              //Coloca um hífen depois do bloco de quatro dígitos
    return v
}

function cpf(v){
    v=v.replace(/\D/g,"")                    //Remove tudo o que não é dígito
    v=v.replace(/(\d{3})(\d)/,"$1.$2")       //Coloca um ponto entre o terceiro e o quarto dígitos
    v=v.replace(/(\d{3})(\d)/,"$1.$2")       //Coloca um ponto entre o terceiro e o quarto dígitos
                                             //de novo (para o segundo bloco de números)
    v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2") //Coloca um hífen entre o terceiro e o quarto dígitos
    return v
}

function validarEmail(campo) {
    var email = campo.value;
    var expressao = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var mensagem = document.getElementById("mensagem"+campo.name);
    if (!expressao.test(email)) {
        campo.focus();
        mensagem.innerText = "É necessário digitar um email válido.";
		mensagem.style.display = "inline-block";
        return false;
    }
    mensagem.style.display = "none";
    return true;
}

function bloquearFoco(event) {
    var campo = event.target;
    var campoValidado = validarEmail(campo);
    if (!campoValidado) {
        event.preventDefault();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    var emailInput = document.getElementById("email");
    emailInput.addEventListener("keydown", function(event) {
        if (event.key === "Tab") { 
            bloquearFoco(event);
        }
    });
});

