
var cartContainer   = document.getElementById('cart-items-container');
var totalPriceElem  = document.getElementById('total-price');
var globalCartCount = document.getElementById('global-cart-count');


var display_books = document.getElementById('display-books');
var searchInput   = document.getElementById('search');
var filterSelect  = document.getElementById('filter');


function handleResponse(obj, successfullCallback, errorMessage) {
    
    obj.onreadystatechange = function() {
        if(obj.readyState == 4 && obj.status == 200) {
            successfullCallback();
        } else if(obj.readyState == 4) {
            console.error(errorMessage + obj.status);
        }
    };
}


function bookData(value) {
    
    var isAvailable = parseInt(value['stock']) > 0;
    var stockHTML = isAvailable 
        ? '<div class="stock-status">In Stock (' + value['stock'] + ' left)</div>' 
        : '<div class="stock-status out-of-stock">Out of Stock</div>';
        
    var buttonHTML = isAvailable 
    ? '<button class="btn-add-cart" onclick="addToCart(' + value['id'] + ')">Add to Cart</button>' 
    : '<button class="btn-add-cart" disabled>Add to Cart</button>';

    
    var imagePath = value['image_path'] ? value['image_path'] : 'public/uploads/books/sample.jpg';

    
    return '<div class="book-card">'
        + '<img src="' + imagePath + '" alt="Book Cover" class="book-image">'
        + '<div class="book-info">'
        + '<h3 class="book-title">' + value['title'] + '</h3>'
        + '<p class="book-author">By ' + value['author'] + '</p>'
        + '<div class="book-price">$' + parseFloat(value['price']).toFixed(2) + '</div>'
        + stockHTML
        + buttonHTML
        + '</div>'
        + '</div>';
}



function cartData(value) {
    
    var cartId   = value['cart_id'] ? value['cart_id'] : value['id'];
    var itemPrice = parseFloat(value['price']);
    var quantity  = parseInt(value['quantity'] ? value['quantity'] : 1);

    
    return '<div class="cart-item" data-cart-id="' + cartId + '">'
        + '<div class="cart-item-details">'
        + '<div class="cart-item-name">' + value['title'] + '</div>'
        + '<div class="cart-item-price">$' + itemPrice.toFixed(2) + '</div>'
        + '</div>'
        + '<div class="cart-item-actions">'
        + '<button class="qty-btn btn-minus" onclick="updateQuantity(' + cartId + ', \'minus\')">-</button>'
        + '<span class="item-qty">' + quantity + '</span>'
        + '<button class="qty-btn btn-plus" onclick="updateQuantity(' + cartId + ', \'plus\')">+</button>'
        + '<button class="btn-remove" onclick="removeFromCart(' + cartId + ')">Remove</button>'
        + '</div>'
        + '</div>';
}



function getData() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../control/customer_homepage_control.php?chk_fetch=true', true);

    handleResponse(xhr, function() {
        var response = JSON.parse(xhr.responseText);
        display_books.innerHTML = ''; 

        response.forEach(function(value) {
            display_books.innerHTML += bookData(value);
        });
    }, 'Error fetching data: ');

    xhr.send();
}



document.addEventListener('DOMContentLoaded', function() {
    getData(); 
    getCartData();

    
    searchInput.addEventListener('keyup', function() {
    var query = this.value.trim();
    var filterType = filterSelect.value;

    if(query === "") {
        getData();
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../control/customer_homepage_control.php?chk_search=true&query=' + encodeURIComponent(query) + '&filter=' + encodeURIComponent(filterType), true);

    handleResponse(xhr, function() {
        var response = JSON.parse(xhr.responseText);
        display_books.innerHTML = '';
        
        if(response.length === 0) {
            display_books.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #7f8c8d; margin-top: 20px;">No books found matching your criteria.</p>';
            return;
        }

        response.forEach(function(value) {
            display_books.innerHTML += bookData(value);
        });
    }, 'Search failed: ');

    xhr.send();
    });
});





function getCartData() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../control/customer_homepage_control.php?chk_fetch_cart=true', true);

    handleResponse(xhr, function() {
        var response = JSON.parse(xhr.responseText);
        cartContainer.innerHTML = '';

        var totalAmount = 0;
        var totalItems  = 0;

        if(response.length === 0) {
            cartContainer.innerHTML = '<p style="text-align:center;color:#7f8c8d;padding:15px 0;">Your cart is empty.</p>';
            totalPriceElem.innerHTML  = '$0.00';
            globalCartCount.innerHTML = '0';
            return;
        }

        response.forEach(function(value) {
            cartContainer.innerHTML += cartData(value);
            var itemQty  = parseInt(value['quantity']) || 1;
            totalAmount += parseFloat(value['price']) * itemQty;
            totalItems  += itemQty;
        });

        totalPriceElem.innerHTML  = '$' + totalAmount.toFixed(2);
        globalCartCount.innerHTML = totalItems;
    }, 'Error fetching cart: ');

    xhr.send();
}

function addToCart(bookId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../control/customer_homepage_control.php?chk_add_cart=true', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    handleResponse(xhr, function() {
        var response = JSON.parse(xhr.responseText);
        if(response.success) {
            getCartData();
        } else {
            alert(response.message || 'Could not add to cart.');
        }
    }, 'Add to cart failed: ');

    xhr.send('book_id=' + bookId);
}


function updateQuantity(cartId, action) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../control/customer_homepage_control.php?chk_update_cart=true', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    handleResponse(xhr, function() {
        var response = JSON.parse(xhr.responseText);
        if(response.success) {
            getCartData();
        }
    }, 'Update failed: ');

    xhr.send('cart_id=' + cartId + '&action=' + action);
}


function removeFromCart(cartId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../control/customer_homepage_control.php?chk_remove_cart=true', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    handleResponse(xhr, function() {
        var response = JSON.parse(xhr.responseText);
        if(response.success) {
            getCartData();
        }
    }, 'Remove failed: ');

    xhr.send('cart_id=' + cartId);
}


document.getElementById('btn-checkout').addEventListener('click', function() {
    window.location.href = '../checkout_page/checkout.html';
});


