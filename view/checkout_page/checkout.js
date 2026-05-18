document.addEventListener('DOMContentLoaded', function() {

    if (document.getElementById('checkout-form')) {
        loadOrderSummary();

        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();

            var address        = document.getElementById('address').value.trim();
            var payment_method = document.getElementById('payment_method').value;

            
            if(address === '') {
                alert('Please enter your delivery address.');
                return;
            }

            if(payment_method === '') {
                alert('Please select a payment method.');
                return;
            }

            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../../control/checkout_control.php?chk_place_order=true', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            handleResponse(xhr, function() {
                var response = JSON.parse(xhr.responseText);

                if(response.success) {
                    alert('Order placed successfully! Order ID: #' + response.order_id);
                    window.location.href = '../home_page/customer_homepage.html';
                } else {
                    alert(response.message || 'Order failed. Please try again.');
                }
            }, 'Order failed: ');

            xhr.send(
                'address='         + encodeURIComponent(address) +
                '&payment_method=' + encodeURIComponent(payment_method)
            );
        });
    }

   
    if (document.getElementById('order-history-body')) {
        loadOrderHistory();
    }

});


function handleResponse(obj, successfullCallback, errorMessage) {
    obj.onreadystatechange = function() {
        if(obj.readyState == 4 && obj.status == 200) {
            successfullCallback();
        } else if(obj.readyState == 4) {
            console.error(errorMessage + obj.status);
        }
    };
}


function loadOrderSummary() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../control/customer_homepage_control.php?chk_fetch_cart=true', true);

    handleResponse(xhr, function() {
        var response  = JSON.parse(xhr.responseText);
        var itemsList = document.getElementById('checkout-items-list');
        var totalElem = document.getElementById('checkout-total-price');

        itemsList.innerHTML = '';
        var total = 0;

        if(response.length === 0) {
          alert('Your cart is empty. Please add books before checkout.');
          window.location.href = '../home_page/customer_homepage.html';
          return;
        }

        response.forEach(function(item) {
            var qty      = parseInt(item['quantity']) || 1;
            var price    = parseFloat(item['price']);
            var subtotal = price * qty;
            total += subtotal;

            itemsList.innerHTML +=
                '<div class="summary-item">'
                + '<div>'
                + '<div class="summary-item-name">' + item['title'] + '</div>'
                + '<div class="summary-item-qty">Qty: ' + qty + ' × $' + price.toFixed(2) + '</div>'
                + '</div>'
                + '<div class="summary-item-price">$' + subtotal.toFixed(2) + '</div>'
                + '</div>';
        });

        totalElem.innerHTML = '$' + total.toFixed(2);
        window.cartItems = response;
        window.cartTotal = total;

    }, 'Error loading cart: ');

    xhr.send();
}


function loadOrderHistory() {
    var orderHistoryBody = document.getElementById('order-history-body');
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../control/checkout_control.php?chk_fetch_orders=true', true);

    handleResponse(xhr, function() {
        var response = JSON.parse(xhr.responseText);
        orderHistoryBody.innerHTML = '';

        if (response.length === 0) {
            orderHistoryBody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#7f8c8d;padding:20px;">No orders found.</td></tr>';
            return;
        }

        response.forEach(function(order) {
            var statusClass = 'status-' + order['status'];
            var date = new Date(order['order_date']).toLocaleDateString();

            orderHistoryBody.innerHTML +=
                '<tr>'
                + '<td>#' + order['id'] + '</td>'
                + '<td>' + order['book_titles'] + '</td>'
                + '<td>$' + parseFloat(order['total_amount']).toFixed(2) + '</td>'
                + '<td>' + order['payment_method'] + '</td>'
                + '<td><span class="' + statusClass + '">' + order['status'] + '</span></td>'
                + '<td>' + date + '</td>'
                + '</tr>';
        });
    }, 'Error loading orders: ');

    xhr.send();
}