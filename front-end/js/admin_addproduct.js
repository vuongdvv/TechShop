setTimeout(() => {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.remove();
    }
    // Xoá ?success=1 khỏi URL
    if (window.location.search.includes('success')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

}, 3500);
function createSlug(str) {
    return str
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d")
        .replace(/[^a-z0-9\s-]/g, "")
        .trim()
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");
}
document.querySelector('input[name="name"]').addEventListener("input", function() {
    const slugInput = document.querySelector('input[name="slug"]');
    slugInput.value = createSlug(this.value);
});
document.getElementById("imageInput").addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("previewImage").src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

const imageInput = document.getElementById("imageInput");
const previewImage = document.getElementById("previewImage");
const removeImage = document.getElementById("removeImage");
const uploadBox = document.getElementById("uploadBox");
const uploadContent = document.getElementById("uploadContent");

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