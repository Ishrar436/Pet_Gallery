document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".btn-reject");

  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (event) {
      const actionType = this.innerText.toLowerCase();

      if (
        actionType.includes("reject") ||
        actionType.includes("delete") ||
        actionType.includes("confirm")
      ) {
        const confirmed = confirm(
          "Are you sure you want to perform this action? This cannot be undone.",
        );
        if (!confirmed) {
          event.preventDefault();
        }
      }
    });
  });
});
