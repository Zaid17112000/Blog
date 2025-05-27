const profile = document.querySelector(".profile-container");
const sidebar = document.querySelector(".sidebar");

profile.addEventListener("click",() => {
    sidebar.classList.toggle("display-sidebar")
});