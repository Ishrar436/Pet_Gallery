document.addEventListener("DOMContentLoaded", function () {
  const editBtn = document.getElementById("editBtn");
  const cancelBtn = document.getElementById("cancelBtn");
  const actionGroup = document.getElementById("formActions");
  const form = document.getElementById("profileForm");

  const inputs = form.querySelectorAll(
    'input:not([type="email"]), textarea, select',
  );

  editBtn.addEventListener("click", () => {
    inputs.forEach((el) => {
      el.removeAttribute("readonly");
      el.removeAttribute("disabled");
      el.style.border = "1px solid #007bff";
      el.style.background = "#fff";
    });
    actionGroup.classList.remove("hidden");
    editBtn.classList.add("hidden");
  });

  cancelBtn.addEventListener("click", () => {
    window.location.reload();
  });
});
