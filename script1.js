let imageIndex = 1;

function swapImages() {
  const image1 = document.getElementById("image1");
  const image2 = document.getElementById("image2");
  const textOverlay1 = document.getElementById("textOverlay1");
  const textOverlay2 = document.getElementById("textOverlay2");

  if (imageIndex === 1) {
    image1.style.zIndex = 1;
    image2.style.zIndex = 0;
    textOverlay1.style.zIndex = 1;
    textOverlay2.style.zIndex = 0;
    textOverlay1.style.opacity = 1;
    textOverlay2.style.opacity = 0;
    imageIndex = 2;
  } else {
    image1.style.zIndex = 0;
    image2.style.zIndex = 1;
    textOverlay1.style.zIndex = 0;
    textOverlay2.style.zIndex = 1;
    textOverlay1.style.opacity = 0;
    textOverlay2.style.opacity = 1;
    imageIndex = 1;
  }
}

// Initially hide the second overlay
document.getElementById("textOverlay2").style.opacity = 0;

setInterval(swapImages, 8000);