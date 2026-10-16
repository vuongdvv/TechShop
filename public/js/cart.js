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
    fetch(FRONT_URL + "/cart/update.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            },
            body: `cart_item_id=${cartItemId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(() => {
            const totalPrice = price * quantity;
            const row = inputElement.closest('tr');
            const totalCell = row.querySelector('td:nth-child(5)'); 
            totalCell.innerText = formatCurrency(totalPrice);
            const checkbox = row.querySelector('.item-checkbox');
            checkbox.dataset.price = totalPrice;

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

                const cartLink = document.querySelector('a[href="' + FRONT_URL + '/cart/index.php"]');
                if (cartLink) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'cart-badge';
                    newBadge.innerText = quantity - oldQty;
                    cartLink.appendChild(newBadge);
                }
            }
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
document.querySelectorAll('input[name="quantity"]').forEach(input => {
    input.addEventListener("change", function() {
        let quantity = parseInt(this.value);
        const cartItemId = this.dataset.cartItemId;
        const price = parseInt(this.dataset.price);
        const oldQty = parseInt(this.defaultValue);
        if (!quantity || quantity < 1) {
            quantity = 1;
            this.value = 1;
        }
        updateQuantity(cartItemId, quantity, price, this, oldQty);
        this.defaultValue = quantity;
    });
});

document.querySelectorAll('.js-decrease-qty').forEach(button => {
    button.addEventListener('click', function () {
        const cartItemId = parseInt(this.dataset.cartItemId || '0', 10);
        const price = parseInt(this.dataset.price || '0', 10);
        if (cartItemId > 0 && price >= 0) {
            decreaseQty(this, cartItemId, price);
        }
    });
});

document.querySelectorAll('.js-increase-qty').forEach(button => {
    button.addEventListener('click', function () {
        const cartItemId = parseInt(this.dataset.cartItemId || '0', 10);
        const price = parseInt(this.dataset.price || '0', 10);
        if (cartItemId > 0 && price >= 0) {
            increaseQty(this, cartItemId, price);
        }
    });
});

document.querySelectorAll('input[name="quantity"]').forEach(input => {
    input.addEventListener("keydown", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            this.blur();
        }
    });
});