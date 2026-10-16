const grid = document.querySelector('.product-grid');
const loadMoreBtn = document.getElementById('loadMoreBtn');


let nextPageToLoad = 2;
let totalProducts = loadMoreBtn ? parseInt(loadMoreBtn.dataset.total) : 0;
let loadedProducts = loadMoreBtn ? parseInt(loadMoreBtn.dataset.loaded) : 0;
let initialCount = loadedProducts;
let isCollapsed = false;

function updateButtonText() {
    if (!loadMoreBtn) return;

    if (loadedProducts >= totalProducts) {

        if (!isCollapsed) {
            loadMoreBtn.innerHTML = 'Ẩn bớt <i class="fa fa-chevron-up"></i>';
        }
    } else {
        loadMoreBtn.innerHTML = 'Xem thêm sản phẩm <i class="fa fa-chevron-down"></i>';
    }
}

function toggleCollapse() {
    if (loadedProducts < totalProducts) {
        loadProducts(false);
    } else {

        const cards = grid.querySelectorAll('.product-card');

        if (!isCollapsed) {

            for (let i = initialCount; i < cards.length; i++) {
                cards[i].style.display = 'none';
            }
            loadMoreBtn.innerHTML = 'Xem thêm sản phẩm <i class="fa fa-chevron-down"></i>';
            isCollapsed = true;
        } else {

            for (let i = initialCount; i < cards.length; i++) {
                cards[i].style.display = '';
            }
            loadMoreBtn.innerHTML = 'Ẩn bớt <i class="fa fa-chevron-up"></i>';
            isCollapsed = false;
        }
    }
}

function loadProducts(reset = false) {
    if (!grid || !loadMoreBtn) return;

    const targetPage = reset ? 1 : nextPageToLoad;

    if (reset) {
        nextPageToLoad = 2;
        loadedProducts = 0;
        isCollapsed = false;
        updateButtonText();
    }

    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('page', targetPage);

    fetch(`load-more.php?${urlParams.toString()}`)
        .then(res => res.text())
        .then(html => {
            if (html.trim() === '') {
                if (reset) {
                    grid.innerHTML = '';
                    loadedProducts = 0;
                } else {
                    loadedProducts = totalProducts;
                }
                updateButtonText();
                return;
            }

            if (reset) {
                grid.innerHTML = html;
                nextPageToLoad = 2;
                initialCount = grid.querySelectorAll('.product-card').length;
            } else {
                grid.insertAdjacentHTML('beforeend', html);
                nextPageToLoad++;
            }

            const allCards = grid.querySelectorAll('.product-card');
            loadedProducts = allCards.length;
            updateButtonText();
        })
        .catch(err => {
            console.error('Load error:', err);
        });
}
if (loadMoreBtn) {
    updateButtonText();

    loadMoreBtn.addEventListener('click', () => {
        toggleCollapse();
    });
}
