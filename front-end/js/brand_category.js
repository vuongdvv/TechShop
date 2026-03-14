
const Toast = {
    show(message, type = "info") {
        const container = document.getElementById("toastContainer");
        const toast = document.createElement("div");
        toast.className = `toast ${type}`;
        let icon = "fa-info-circle";
        if (type === "success") icon = "fa-check-circle";
        if (type === "error") icon = "fa-exclamation-circle";
        toast.innerHTML = `
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
};

const Slug = {
    create(str) {
        return str
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/đ/g, "d")
            .replace(/[^a-z0-9\s-]/g, "")
            .trim()
            .replace(/\s+/g, "-");
    }
};

const Modal = {
    open(id) {
        document.getElementById(id).style.display = "block";
    },
    close(id) {
        document.getElementById(id).style.display = "none";
    }
};

const Category = {
    openAdd() {
        Modal.open("categoryModal");
    },
    closeAdd() {
        Modal.close("categoryModal");
        document.getElementById("addCategoryForm").reset();
        document.getElementById("slug").value = "";
    },
    openEdit(id) {
        let row = document.querySelector(`[data-id='category-${id}']`);
        if (!row) {
            Toast.show("Không tìm thấy category!", "error");
            return;
        }
        let name = row.querySelector(".category-name").innerText;
        Modal.open("editModal");
        document.getElementById("edit_id").value = id;
        document.getElementById("edit_name").value = name;
        document.getElementById("edit_slug").value = Slug.create(name);
        document.getElementById("edit_name").oninput = function () {
            document.getElementById("edit_slug").value = Slug.create(this.value);
        };
    },
    closeEdit() {
        Modal.close("editModal");
    },
    submitEdit() {
        const form = document.getElementById("editForm");
        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch(BASE_URL + "/back-end/admin/categories/update_brand_category.php", {
                method: "POST",
                headers: { "X-Fetch": "true" },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let id = formData.get("id");
                    let newName = formData.get("name");
                    let row = document.querySelector(`[data-id='category-${id}']`);
                    row.querySelector(".category-name").innerText = newName;
                    Category.closeEdit();
                    Toast.show("Cập nhật danh mục thành công!", "success");
                } else {
                    Toast.show("Lỗi!", "error");
                }
            });
        });
    },

    submitAdd() {
        const form = document.getElementById("addCategoryForm");
        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch(BASE_URL + "/back-end/admin/categories/brand_category.php", {
                method: "POST",
                headers: { "X-Fetch": "true" },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Category.closeAdd();
                    Toast.show("Thêm danh mục thành công!", "success");
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Toast.show(data.message || "Lỗi thêm danh mục!", "error");
                }
            })
            .catch(err => {
                console.error(err);
                Toast.show("Không gửi được request!", "error");
            });
        });
    }
};

const Brand = {
    openAdd() {
        Modal.open("brandModal");
    },
    closeAdd() {
        Modal.close("brandModal");
        document.getElementById("addBrandForm").reset();
        document.getElementById("brand_slug").value = "";
        document.getElementById("add_brand_preview").style.display = "none";
    },

    openEdit(id) {
        let row = document.querySelector(`[data-id='brand-${id}']`);
        if (!row) {
            Toast.show("Không tìm thấy brand!", "error");
            return;
        }
        let name = row.querySelector(".brand-name").innerText;
        let img = row.querySelector("img")?.getAttribute("src") || "";
        Modal.open("editBrandModal");
        document.getElementById("edit_brand_id").value = id;
        document.getElementById("edit_brand_name").value = name;
        document.getElementById("edit_brand_slug").value = Slug.create(name);
        document.getElementById("edit_brand_preview").src = img;
    },

    closeEdit() {
        Modal.close("editBrandModal");
    },
    previewAddLogo() {
        const input = document.getElementById("add_brand_logo");
        if (!input) return;

        input.addEventListener("change", function (e) {
            const file = e.target.files[0];
            const preview = document.getElementById("add_brand_preview");

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = "block";
            } else {
                preview.style.display = "none";
            }
        });
    },

    previewEditLogo() {
        const input = document.getElementById("edit_brand_logo");
        if (!input) return;
        input.addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (file) {
                document.getElementById("edit_brand_preview").src =
                    URL.createObjectURL(file);
            }
        });
    },
    submitAdd() {
        const form = document.getElementById("addBrandForm");
        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch(BASE_URL + "/back-end/admin/categories/add_brand.php", {
                method: "POST",
                headers: { "X-Fetch": "true" },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Brand.closeAdd();
                    Toast.show("Thêm thương hiệu thành công!", "success");
                    location.reload();
                } else {
                    Toast.show(data.message || "Lỗi thêm thương hiệu!", "error");
                }
            })
            .catch(err => {
                console.error(err);
                Toast.show("Không gửi được yêu cầu!", "error");
            });
        });
    },
    submitEdit() {
        const form = document.getElementById("editBrandForm");
        if (!form) return;
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            let id = formData.get("id");
            fetch(BASE_URL + "/back-end/admin/categories/update_brand_category.php", {
                method: "POST",
                headers: { "X-Fetch": "true" },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let newName = formData.get("name");
                    let row = document.querySelector(`[data-id='brand-${id}']`);
                    row.querySelector(".brand-name").innerText = newName;
                    if (data.logo_updated && data.logo) {
                        let imgTag = row.querySelector("img");
                        imgTag.src =
                            FRONT_URL + "/assets/images/brands/" +
                            data.logo +
                            "?t=" +
                            Date.now();
                    }
                    Brand.closeEdit();
                    Toast.show("Cập nhật thương hiệu thành công!", "success");
                } else {
                    Toast.show(data.message || "Lỗi cập nhật!", "error");
                }
            })
            .catch(err => {
                console.error(err);
                Toast.show("Không gửi được yêu cầu!", "error");
            });
        });
    }
};

const AutoSlug = {
    init() {
        const nameInput = document.getElementById("category_name");
        const slugInput = document.getElementById("slug");
        const brandNameInput = document.getElementById("brand_name");
        const brandSlugInput = document.getElementById("brand_slug");
        const editBrandNameInput = document.getElementById("edit_brand_name");
        const editBrandSlugInput = document.getElementById("edit_brand_slug");
        if (nameInput) {
            nameInput.addEventListener("input", function () {
                slugInput.value = Slug.create(this.value);
            });
        }
        if (brandNameInput && brandSlugInput) {
            brandNameInput.addEventListener("input", function () {
                brandSlugInput.value = Slug.create(this.value);
            });
        }
        if (editBrandNameInput && editBrandSlugInput) {
            editBrandNameInput.addEventListener("input", function () {
                editBrandSlugInput.value = Slug.create(this.value);
            });
        }
    }
};

window.onclick = function (e) {

    ["editModal", "editBrandModal"].forEach(id => {
        let modal = document.getElementById(id);
        if (e.target === modal) modal.style.display = "none";
    });
};

document.addEventListener("DOMContentLoaded", () => {
    AutoSlug.init();
    Category.submitEdit();
    Category.submitAdd();
    Brand.previewAddLogo();
    Brand.previewEditLogo();
    Brand.submitAdd();
    Brand.submitEdit();
});

function showToast(message, type) {
    Toast.show(message, type);
}
function createSlug(str) {
    return Slug.create(str);
}
function openCategoryModal() {
    Category.openAdd();
}
function closeCategoryModal() {
    Category.closeAdd();
}
function openBrandModal() {
    Brand.openAdd();
}
function closeBrandModal() {
    Brand.closeAdd();
}
function openEditModal(id) {
    Category.openEdit(id);
}
function closeEditModal() {
    Category.closeEdit();
}
function openEditBrandModal(id) {
    Brand.openEdit(id);
}
function closeEditBrandModal() {
    Brand.closeEdit();
}