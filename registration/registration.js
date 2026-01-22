document.getElementById("btnMember").addEventListener("click", function () {
  document.getElementById("displayTitle").style.display = "block";
  document.getElementById("displayTitle2").style.display = "none";
  document.getElementById("regForm-member").style.display = "block";
  document.getElementById("regForm-shelter").style.display = "none";
  document.getElementById("btnMember").classList.add("active");
  document.getElementById("btnShelter").classList.remove("active1");
  document.getElementById("imageSection").classList.remove("shelter");
  document.getElementById("imageSection").classList.add("member");
});
document.getElementById("btnShelter").addEventListener("click", function () {
  document.getElementById("displayTitle").style.display = "none";
  document.getElementById("displayTitle2").style.display = "block";
  document.getElementById("regForm-member").style.display = "none";
  document.getElementById("regForm-shelter").style.display = "block";
  document.getElementById("btnMember").classList.remove("active");
  document.getElementById("btnShelter").classList.add("active1");
  document.getElementById("imageSection").classList.remove("member");
  document.getElementById("imageSection").classList.add("shelter");
});

document
  .getElementById("locationSelect")
  .addEventListener("change", function () {
    const locId = this.value;
    const shelterDropdown = document.getElementById("shelterSelect");

    shelterDropdown.innerHTML = '<option value="">Loading shelters...</option>';

     Use path relative to registration.php
    fetch("../registration/fetch_shelters.php?location_id=" + locId)
      .then((response) => response.json())
      .then((data) => {
        console.log(data);

        shelterDropdown.innerHTML =
          '<option value="" disabled selected>Choose Shelter</option>';
        if (data.length > 0) {
          data.forEach((shelter) => {
            let option = document.createElement("option");
            option.value = shelter.id;
            option.textContent = shelter.name;
            shelterDropdown.appendChild(option);
          });
        } else {
          shelterDropdown.innerHTML =
            '<option value="" disabled>No shelters found</option>';
        }
      })
      .catch((err) => {
        console.log("Error fetching shelters:", err);
        shelterDropdown.innerHTML =
          '<option value="" disabled>Error loading data</option>';
      });
  });
