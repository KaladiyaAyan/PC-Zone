// Popup Remove

function popup_remove() {
  let popup = document.querySelector(".popup");
  setTimeout(() => {
    popup.style.display = "none";
  }, 3000);
}
popup_remove();
