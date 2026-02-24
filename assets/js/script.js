// Custom JavaScript for eCommerce Website

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 70
            }, 1000);
        }
    });

    // Add to cart functionality
    $('.add-to-cart').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        var quantity = $(this).data('quantity') || 1;
        
        addToCart(productId, quantity);
    });

    // Quantity selector
    $('.quantity-selector').on('change', function() {
        var productId = $(this).data('product-id');
        var quantity = $(this).val();
        updateCartQuantity(productId, quantity);
    });

    // Remove from cart
    $('.remove-from-cart').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        removeFromCart(productId);
    });

    // Product image gallery
    $('.product-gallery img').on('click', function() {
        var mainImage = $('#main-product-image');
        mainImage.attr('src', $(this).attr('src'));
        $('.product-gallery img').removeClass('border-primary');
        $(this).addClass('border-primary');
    });

    // Search functionality
    $('#search-input').on('input', function() {
        var query = $(this).val();
        if (query.length >= 3) {
            performSearch(query);
        }
    });

    // Category filter
    $('.category-filter').on('change', function() {
        var categoryId = $(this).val();
        filterProducts(categoryId);
    });

    // Sort products
    $('#sort-products').on('change', function() {
        var sortBy = $(this).val();
        sortProducts(sortBy);
    });

    // Wishlist toggle
    $('.wishlist-toggle').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        toggleWishlist(productId);
    });

    // Form validation
    $('form[data-validate]').on('submit', function(e) {
        if (!validateForm($(this))) {
            e.preventDefault();
        }
    });

    // Image upload preview
    $('#image-upload').on('change', function() {
        previewImage(this);
    });

    // Price range slider
    if ($('#price-range').length) {
        $('#price-range').slider({
            range: true,
            min: 0,
            max: 1000,
            values: [0, 500],
            slide: function(event, ui) {
                $('#price-min').val(ui.values[0]);
                $('#price-max').val(ui.values[1]);
            }
        });
    }
});

// Cart Functions
function addToCart(productId, quantity) {
    $.ajax({
        url: 'includes/cart_functions.php',
        type: 'POST',
        data: {
            action: 'add',
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateCartCount();
                displayAlert('success', response.message);
            } else {
                displayAlert('danger', response.message);
            }
        },
        error: function() {
            displayAlert('danger', 'An error occurred. Please try again.');
        }
    });
}

function updateCartQuantity(productId, quantity) {
    $.ajax({
        url: 'includes/cart_functions.php',
        type: 'POST',
        data: {
            action: 'update',
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                displayAlert('danger', response.message);
            }
        },
        error: function() {
            displayAlert('danger', 'An error occurred. Please try again.');
        }
    });
}

function removeFromCart(productId) {
    if (confirm('Are you sure you want to remove this item from cart?')) {
        $.ajax({
            url: 'includes/cart_functions.php',
            type: 'POST',
            data: {
                action: 'remove',
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    displayAlert('danger', response.message);
                }
            },
            error: function() {
                displayAlert('danger', 'An error occurred. Please try again.');
            }
        });
    }
}

function updateCartCount() {
    $.ajax({
        url: 'includes/cart_functions.php',
        type: 'POST',
        data: { action: 'count' },
        dataType: 'json',
        success: function(response) {
            $('.cart-count').text(response.count);
        }
    });
}

// Search Functions
function performSearch(query) {
    $.ajax({
        url: 'includes/search_functions.php',
        type: 'GET',
        data: { q: query },
        dataType: 'json',
        success: function(response) {
            displaySearchResults(response.results);
        }
    });
}

function displaySearchResults(results) {
    var container = $('#search-results');
    container.empty();
    
    if (results.length === 0) {
        container.html('<div class="alert alert-info">No products found.</div>');
        return;
    }
    
    results.forEach(function(product) {
        var html = `
            <div class="col-md-4 mb-4">
                <div class="product-card">
                    <img src="uploads/products/${product.image}" class="product-image" alt="${product.name}">
                    <div class="p-3">
                        <h5 class="product-title">${product.name}</h5>
                        <p class="product-price">${formatPrice(product.price)}</p>
                        <a href="product.php?id=${product.id}" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
        `;
        container.append(html);
    });
}

// Filter Functions
function filterProducts(categoryId) {
    var url = new URL(window.location.href);
    url.searchParams.set('category', categoryId);
    window.location.href = url.toString();
}

function sortProducts(sortBy) {
    var url = new URL(window.location.href);
    url.searchParams.set('sort', sortBy);
    window.location.href = url.toString();
}

// Wishlist Functions
function toggleWishlist(productId) {
    $.ajax({
        url: 'includes/wishlist_functions.php',
        type: 'POST',
        data: {
            action: 'toggle',
            product_id: productId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayAlert('success', response.message);
                $('.wishlist-toggle[data-product-id="' + productId + '"]').toggleClass('text-danger');
            } else {
                displayAlert('danger', response.message);
            }
        },
        error: function() {
            displayAlert('danger', 'An error occurred. Please try again.');
        }
    });
}

// Form Validation
function validateForm(form) {
    var isValid = true;
    form.find('input[required], textarea[required], select[required]').each(function() {
        if (!$(this).val()) {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Email validation
    form.find('input[type="email"]').each(function() {
        var email = $(this).val();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            isValid = false;
        }
    });
    
    return isValid;
}

// Image Preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#image-preview').attr('src', e.target.result).show();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Utility Functions
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

function displayAlert(type, message) {
    var alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('#alert-container').html(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

// Loading States
function showLoading(element) {
    element.prop('disabled', true).html('<span class="loading"></span> Loading...');
}

function hideLoading(element, originalText) {
    element.prop('disabled', false).text(originalText);
}

// Confirm Actions
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Initialize on page load
$(window).on('load', function() {
    // Add fade-in animation to elements
    $('.fade-in').each(function(index) {
        $(this).delay(index * 100).animate({ opacity: 1 }, 500);
    });
});

// Scroll effects
$(window).on('scroll', function() {
    var scrollTop = $(this).scrollTop();
    
    // Parallax effect for hero section
    $('.hero-section').css('transform', 'translateY(' + (scrollTop * 0.5) + 'px)');
    
    // Show/hide back to top button
    if (scrollTop > 300) {
        $('#back-to-top').fadeIn();
    } else {
        $('#back-to-top').fadeOut();
    }
});

// Back to top button
$('#back-to-top').on('click', function() {
    $('html, body').animate({ scrollTop: 0 }, 1000);
});
