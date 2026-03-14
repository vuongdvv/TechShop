setTimeout(() => {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.remove();
    }


    if (window.location.search.includes('success')) {
        window.history.replaceState({}, document.title, 
            window.location.pathname + '?id=' + new URLSearchParams(window.location.search).get('id'));
    }
}, 3500);

const imageInput = document.getElementById("imageInput");
const previewImage = document.getElementById("previewImage");
const removeImage = document.getElementById("removeImage");
const uploadBox = document.getElementById("uploadBox");
const uploadContent = document.getElementById("uploadContent");

const ratingInput = document.querySelector('input[name="rating"]');
const ratingValue = document.getElementById("ratingValue");

if (ratingInput) {
    ratingInput.addEventListener("input", function() {
        ratingValue.innerText = this.value || 0;
    });
}

imageInput.addEventListener("change", function(e) {
    const file = e.target.files[0];

    if (file) {
        previewImage.src = URL.createObjectURL(file);
        previewImage.style.display = "block";
        removeImage.style.display = "block";
        uploadContent.style.display = "none";
    }
});


removeImage.addEventListener("click", function(e) {
    e.stopPropagation();

    imageInput.value = "";
    previewImage.src = defaultImage;


    if (!imageInput.files.length) {
        uploadContent.style.display = "block";
        previewImage.style.display = "none";
        removeImage.style.display = "none";
    }
});


uploadContent.addEventListener("click", function() {
    imageInput.click();
});

uploadBox.addEventListener("dragover", (e) => {
    e.preventDefault();
    uploadBox.style.borderColor = "#2563eb";
        });

uploadBox.addEventListener("dragleave", () => {
    uploadBox.style.borderColor = "#d1d5db";
});

uploadBox.addEventListener("drop", (e) => {
    e.preventDefault();
    uploadBox.style.borderColor = "#d1d5db";

    const file = e.dataTransfer.files[0];
    if (file) {
        imageInput.files = e.dataTransfer.files;

        previewImage.src = URL.createObjectURL(file);
        previewImage.style.display = "block";
        removeImage.style.display = "block";
        uploadContent.style.display = "none";
    }
});