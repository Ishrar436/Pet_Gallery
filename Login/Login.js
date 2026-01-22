document.getElementById("show-text").addEventListener("click", function () {
  var passwordInput = document.querySelector("input[name='password']");
  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    this.textContent = "hide text";
  } else {
    passwordInput.type = "password";
    this.textContent = "show text";
  }
});
