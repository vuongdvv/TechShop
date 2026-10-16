document.addEventListener('DOMContentLoaded', function () {
    const starInputs = document.querySelectorAll('.star-input');
    const ratingValue = document.getElementById('rating-value');

    starInputs.forEach(function (star) {
        star.addEventListener('mouseenter', function () {
            const val = parseInt(this.dataset.value);
            starInputs.forEach(function (s) {
                s.classList.toggle('hovered', parseInt(s.dataset.value) <= val);
            });
        });

        star.addEventListener('mouseleave', function () {
            starInputs.forEach(function (s) {
                s.classList.remove('hovered');
            });
        });

        star.addEventListener('click', function () {
            const val = parseInt(this.dataset.value);
            ratingValue.value = val;
            starInputs.forEach(function (s) {
                if (parseInt(s.dataset.value) <= val) {
                    s.classList.add('selected');
                    s.classList.remove('far');
                    s.classList.add('fas');
                } else {
                    s.classList.remove('selected');
                    s.classList.remove('fas');
                    s.classList.add('far');
                }
            });
        });
    });

    var reviewForm = document.getElementById('review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = reviewForm.querySelector('.btn-submit-review');
            var rating = document.getElementById('rating-value').value;

            if (parseInt(rating) < 1) {
                showToast('Vui lòng chọn số sao đánh giá', 'error');
                return;
            }


            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang gửi...';

            var formData = new FormData(reviewForm);

            fetch(BASE_URL + '/back-end/product/reviews.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    showToast(data.message, 'success');

                    var wrap = document.getElementById('review-form-wrap');
                    wrap.innerHTML = '<div class="review-notice"><i class="fa fa-check-circle"></i> Bạn đã đánh giá sản phẩm này.</div>';

                    // Thêm review mới vào danh sách
                    var noReviews = document.getElementById('no-reviews');
                    if (noReviews) noReviews.remove();

                    var reviewHtml = buildReviewHTML(data.review);
                    var list = document.getElementById('reviews-list');
                    list.insertAdjacentHTML('afterbegin', reviewHtml);
                    bindCommentEvents(list.firstElementChild);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(function () {
                showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
            })
            .finally(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-paper-plane"></i> Gửi đánh giá';
            });
        });
    }

    document.querySelectorAll('.btn-toggle-comments').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var reviewId = this.dataset.reviewId;
            var wrap = document.getElementById('comments-' + reviewId);
            if (wrap.style.display === 'none') {
                wrap.style.display = 'block';
            } else {
                wrap.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.comment-form').forEach(function (form) {
        bindCommentForm(form);
    });

    document.querySelectorAll('.btn-reply').forEach(function (btn) {
        bindReplyButton(btn);
    });

    var loadMoreBtn = document.querySelector('.btn-load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            var page = parseInt(this.dataset.page);
            var productId = this.dataset.productId;
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang tải...';
            var self = this;
            fetch(BASE_URL + '/back-end/product/reviews.php?product_id=' + productId + '&page=' + page)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.reviews.length > 0) {
                    var list = document.getElementById('reviews-list');

                    data.reviews.forEach(function (review) {
                        var html = buildReviewHTML(review);
                        list.insertAdjacentHTML('beforeend', html);
                        bindCommentEvents(list.lastElementChild);
                    });
                    if (page >= data.total_pages) {
                        self.parentElement.remove();
                    } else {
                        self.dataset.page = page + 1;
                        self.disabled = false;
                        self.innerHTML = 'Xem thêm đánh giá <i class="fa fa-chevron-down"></i>';
                    }
                } else {
                    self.parentElement.remove();
                }
            })
            .catch(function () {
                self.disabled = false;
                self.innerHTML = 'Xem thêm đánh giá <i class="fa fa-chevron-down"></i>';
            });
        });
    }
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function buildReviewHTML(review) {
        var stars = '';
        for (var i = 1; i <= 5; i++) {
            stars += '<i class="fa' + (i <= review.rating ? 's' : 'r') + ' fa-star"></i>';
        }
        var initial = review.user_name.charAt(0).toUpperCase();
        var date = formatDate(review.created_at);

        return '<div class="review-item" data-review-id="' + review.id + '">' +
            '<div class="review-header">' +
                '<div class="review-user">' +
                    '<div class="user-avatar">' + escapeHtml(initial) + '</div>' +
                    '<div>' +
                        '<strong class="user-name">' + escapeHtml(review.user_name) + '</strong>' +
                        '<div class="review-stars">' + stars + '</div>' +
                    '</div>' +
                '</div>' +
                '<span class="review-date">' + escapeHtml(date) + '</span>' +
            '</div>' +
            '<div class="review-content">' + escapeHtml(review.content).replace(/\n/g, '<br>') + '</div>' +
            '<div class="comments-section">' +
                '<button class="btn-toggle-comments" data-review-id="' + review.id + '">' +
                    '<i class="far fa-comment"></i> <span class="comment-count">Bình luận</span>' +
                '</button>' +
                '<div class="comments-wrap" id="comments-' + review.id + '" style="display:none;">' +
                    '<div class="comments-list"></div>' +
                    '<form class="comment-form" data-review-id="' + review.id + '">' +
                        '<input type="hidden" name="product_id" value="' + review.product_id + '">' +
                        '<input type="hidden" name="review_id" value="' + review.id + '">' +
                        '<input type="hidden" name="parent_id" value="0" class="comment-parent-id">' +
                        '<div class="comment-input-wrap">' +
                            '<input type="text" name="content" placeholder="Viết bình luận..." required class="comment-input">' +
                            '<button type="submit" class="btn-send-comment"><i class="fa fa-paper-plane"></i></button>' +
                        '</div>' +
                    '</form>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    function buildCommentHTML(comment) {
        var initial = comment.user_name.charAt(0).toUpperCase();
        var date = formatDate(comment.created_at);
        var isReply = comment.parent_id && parseInt(comment.parent_id) > 0;

        return '<div class="comment-item ' + (isReply ? 'comment-reply' : '') + '" data-comment-id="' + comment.id + '">' +
            '<div class="comment-header">' +
                '<div class="comment-user">' +
                    '<div class="user-avatar small">' + escapeHtml(initial) + '</div>' +
                    '<strong>' + escapeHtml(comment.user_name) + '</strong>' +
                '</div>' +
                '<span class="comment-date">' + escapeHtml(date) + '</span>' +
            '</div>' +
            '<div class="comment-content">' + escapeHtml(comment.content).replace(/\n/g, '<br>') + '</div>' +
            '<button class="btn-reply" data-review-id="' + comment.review_id + '" data-parent-id="' + comment.id + '" data-user-name="' + escapeHtml(comment.user_name) + '">' +
                '<i class="fa fa-reply"></i> Trả lời' +
            '</button>' +
        '</div>';
    }

    function formatDate(dateStr) {
        var d = new Date(dateStr);
        var day = String(d.getDate()).padStart(2, '0');
        var month = String(d.getMonth() + 1).padStart(2, '0');
        var year = d.getFullYear();
        var hours = String(d.getHours()).padStart(2, '0');
        var mins = String(d.getMinutes()).padStart(2, '0');
        return day + '/' + month + '/' + year + ' ' + hours + ':' + mins;
    }

    function bindCommentEvents(reviewElement) {
        var toggleBtn = reviewElement.querySelector('.btn-toggle-comments');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                var wrap = document.getElementById('comments-' + this.dataset.reviewId);
                wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
            });
        }

        var form = reviewElement.querySelector('.comment-form');
        if (form) bindCommentForm(form);

        reviewElement.querySelectorAll('.btn-reply').forEach(function (btn) {
            bindReplyButton(btn);
        });
    }

    function bindCommentForm(form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = form.querySelector('.comment-input');
            var content = input.value.trim();
            if (!content) return;

            var formData = new FormData(form);
            var sendBtn = form.querySelector('.btn-send-comment');
            sendBtn.disabled = true;

            fetch(BASE_URL + '/back-end/product/comments.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    var commentsList = form.closest('.comments-wrap').querySelector('.comments-list');
                    commentsList.insertAdjacentHTML('beforeend', buildCommentHTML(data.comment));

                    var lastComment = commentsList.lastElementChild;
                    var replyBtn = lastComment.querySelector('.btn-reply');
                    if (replyBtn) bindReplyButton(replyBtn);

                    input.value = '';
                    form.querySelector('.comment-parent-id').value = '0';

                    var indicator = form.querySelector('.reply-indicator');
                    if (indicator) indicator.remove();

                    var reviewId = form.dataset.reviewId;
                    var countEl = document.querySelector('.btn-toggle-comments[data-review-id="' + reviewId + '"] .comment-count');
                    var currentCount = parseInt(countEl.textContent) || 0;
                    countEl.textContent = (currentCount + 1) + ' bình luận';
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(function () {
                showToast('Có lỗi xảy ra', 'error');
            })
            .finally(function () {
                sendBtn.disabled = false;
            });
        });
    }

    function bindReplyButton(btn) {
        btn.addEventListener('click', function () {
            var reviewId = this.dataset.reviewId;
            var parentId = this.dataset.parentId;
            var userName = this.dataset.userName;
            var form = document.querySelector('.comment-form[data-review-id="' + reviewId + '"]');
            if (!form) return;
            form.querySelector('.comment-parent-id').value = parentId;
            var old = form.querySelector('.reply-indicator');
            if (old) old.remove();
            var indicator = document.createElement('div');
            indicator.className = 'reply-indicator';
            indicator.innerHTML = '<i class="fa fa-reply"></i> Trả lời <strong>' + escapeHtml(userName) + '</strong>' +
                '<span class="cancel-reply" title="Hủy trả lời">&times;</span>';
            form.insertBefore(indicator, form.firstChild);
            indicator.querySelector('.cancel-reply').addEventListener('click', function () {
                form.querySelector('.comment-parent-id').value = '0';
                indicator.remove();
            });
            form.querySelector('.comment-input').focus();
        });
    }

    function showToast(message, type) {
        var existing = document.querySelector('.review-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.className = 'review-toast';
        if (type === 'error') {
            toast.style.background = '#ef4444';
        }
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(function () { toast.remove(); }, 3000);
    }

});
