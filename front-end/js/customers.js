function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
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

function openEditUserModal(id) {
    let row = document.querySelector(`[data-user-id='${id}']`);
    if (!row) {
        showToast("Không tìm thấy người dùng!", "error");
        return;
    }
    document.getElementById("editUserModal").style.display = "block";
    document.getElementById("edit_user_id").value = id;
    let statusText = row.querySelector(".status").innerText;
    let role = row.querySelector(".user-role").innerText;
    document.getElementById("edit_user_status").value =
        statusText === "Hoạt động" ? "1" : "0";
    document.getElementById("edit_user_role").value = role;  
    document.getElementById("editUserForm").addEventListener("submit", function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        fetch(BASE_URL + "/back-end/admin/customer/update_user.php", {
                method: "POST",
                headers: { "X-Fetch": "true" },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast("Cập nhật thành công!", "success");
                    closeEditUserModal();
                    // Nếu logout=true thì redirect về login page
                    if (data.logout) {
                        setTimeout(() => {
                            window.location.href = FRONT_URL + "/admin/admin_auth/login.php?message=role_changed";
                        }, 1500);
                    } else {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    showToast("Lỗi cập nhật!", "error");
                }
            })
            .catch(err => {
                console.error("FETCH ERROR:", err);
                showToast("Không gửi được request!", "error");
            });
    });
} 
function closeEditUserModal() {
    document.getElementById("editUserModal").style.display = "none";
}

    // Click ngoài modal
window.onclick = function(e) {
    let modal = document.getElementById("editUserModal");
    if (e.target === modal) modal.style.display = "none";
};