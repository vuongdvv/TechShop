function decreaseQty(btn, cartItemId, price) {
    const input = btn.nextElementSibling;
    const oldQty = parseInt(input.value);
    if (input.value > 1) {
        input.value--;
        updateQuantity(cartItemId, input.value, price, input, oldQty);
    }
}
function increaseQty(btn, cartItemId, price) {
    const input = btn.previousElementSibling;
    const oldQty = parseInt(input.value);
    input.value++;
    updateQuantity(cartItemId, input.value, price, input, oldQty);
}
function updateQuantity(cartItemId, quantity, price, inputElement, oldQty) {
    fetch(BASE_URL + "/back-end/cart/update.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `cart_item_id=${cartItemId}&quantity=${quantity}`
        })
        .then(response => response.text())
        .then(() => {
            // Cập nhật tổng tiền của sản phẩm này
            const totalPrice = price * quantity;
            const row = inputElement.closest('tr');
            const totalCell = row.querySelector('td:nth-child(5)'); 
            totalCell.innerText = formatCurrency(totalPrice);
            // Cập nhật data-price của checkbox
            const checkbox = row.querySelector('.item-checkbox');
            checkbox.dataset.price = totalPrice;
            // Cập nhật cart-badge
            const cartBadge = document.querySelector('.cart-badge');
            if (cartBadge) {
                const qtyDiff = quantity - oldQty;
                const newBadgeCount = parseInt(cartBadge.innerText) + qtyDiff;
                if (newBadgeCount > 0) {
                        cartBadge.innerText = newBadgeCount;
                } else {
                    cartBadge.remove();
                }
            } else if (quantity - oldQty > 0) {
                // Nếu không có badge, tạo mới
                const cartLink = document.querySelector('a[href="' + FRONT_URL + '/cart/index.php"]');
                if (cartLink) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'cart-badge';
                    newBadge.innerText = quantity - oldQty;
                    cartLink.appendChild(newBadge);
                }
            }
            // Recalculate tổng tiền đơn hàng
            updateTotal();
        })
        .catch(error => console.error("Error:", error));
}
function formatCurrency(number) {
    return number.toLocaleString('vi-VN') + " đ";
}
function showToastError(message) {
    const oldToast = document.querySelector('.toast-error.js-toast');
    if (oldToast) {
        oldToast.remove();
    }
    const toast = document.createElement('div');
    toast.className = 'toast-error js-toast';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}
function updateTotal() {
    let total = 0;
    document.querySelectorAll(".item-checkbox:checked").forEach(cb => {
        total += parseInt(cb.dataset.price);
    });
    document.getElementById("subtotal").innerText = formatCurrency(total);
    document.getElementById("total").innerText = formatCurrency(total);
}
document.querySelectorAll(".item-checkbox").forEach(cb => {
    cb.addEventListener("change", updateTotal);
});
const checkoutForm = document.getElementById("checkoutForm");
if (checkoutForm) {
    checkoutForm.addEventListener("submit", function(e) {
        const checkedItems = document.querySelectorAll(".item-checkbox:checked").length;
        if (checkedItems === 0) {
            e.preventDefault();
            showToastError("Vui lòng chọn ít nhất 1 sản phẩm để thanh toán");
        }
    });
}
const checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.addEventListener("change", function() {
        document.querySelectorAll(".item-checkbox").forEach(cb => {
            cb.checked = this.checked;
        });
        updateTotal();
    });
}
// Bắt sự kiện nhập tay số lượng
document.querySelectorAll('input[name="quantity"]').forEach(input => {
    input.addEventListener("input", function() {
        let quantity = parseInt(this.value);
        const cartItemId = this.dataset.cartItemId;
        const price = parseInt(this.dataset.price);
        const oldQty = parseInt(this.defaultValue);
        if (!quantity || quantity < 1) {
            quantity = 1;
            this.value = 1;
        }
        updateQuantity(cartItemId, quantity, price, this, oldQty);
        // Cập nhật defaultValue để lần sau tính đúng chênh lệch
        this.defaultValue = quantity;
    });
});
// Chặn Enter trong input số lượng
document.querySelectorAll('input[name="quantity"]').forEach(input => {
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault(); // Ngăn submit form
            this.blur(); // Bỏ focus để kích hoạt input event
        }
    });
});