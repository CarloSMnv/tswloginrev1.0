function startCountdown() {
  var remainingTime = <?php echo strtotime($time_limit) - time(); ?>;
  var button = document.getElementById("loginButton");

  if (remainingTime > 0) {
    button.disabled = true;
    button.innerHTML = 'Iniciar Sesión <span id="countdown">' + remainingTime + '</span>';
    countdown(remainingTime); // Inicia el contador
  }
}

function countdown(remainingTime) {
  var countdownElement = document.getElementById('countdown');

  if (remainingTime > 0) {
    countdownElement.innerText = remainingTime;
    remainingTime--;
    setTimeout(function() {
      countdown(remainingTime);
    }, 1000); // Actualiza el contador cada segundo
  } else {
    var button = document.getElementById("loginButton");
    button.disabled = false;
    button.innerHTML = 'Iniciar Sesión';
  }
}
