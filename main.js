document.addEventListener("DOMContentLoaded", function () {
    // Handle URL Parameters for Errors
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
  
    if (error) {
        let message = error.replace("_", " ");
        let errorMessage = document.getElementById('error-tag');
        if (errorMessage) {
            errorMessage.textContent = message;
        }
    }
  
    // Add hovered class to selected list item
    let list = document.querySelectorAll(".navigation li");
  
    function activeLink() {
        list.forEach((item) => {
            item.classList.remove("hovered");
        });
        this.classList.add("hovered");
    }
  
    list.forEach((item) => item.addEventListener("mouseover", activeLink));
  
    // Menu Toggle
    let toggle = document.querySelector(".toggle");
    let navigation = document.querySelector(".navigation");
    let main = document.querySelector(".main");
  
    if (toggle && navigation && main) {
        toggle.onclick = function () {
            navigation.classList.toggle("active");
            main.classList.toggle("active");
        };
    } else {
        console.error("One or more elements (.toggle, .navigation, .main) are missing!");
    }
  });
  