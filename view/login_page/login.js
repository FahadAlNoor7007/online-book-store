document.addEventListener('DOMContentLoaded', function() {

    //login.js
    
    var loginForm = document.getElementById('login-form');

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();

        var email    = document.querySelector('input[name="email"]').value.trim();
        var password = document.querySelector('input[name="password"]').value.trim();

        if(email === '') {
            alert('Email is required.');
            return;
        }

        if(password === '') {
            alert('Password is required.');
            return;
        }

       
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../control/login_control.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function() {
            if(xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);

                if(response.success) {
                    
                    window.location.href = '../home_page/customer_homepage.html';
                } else {
                    alert(response.message || 'Login failed. Please try again.');
                }
            } else if(xhr.readyState == 4) {
                alert('Server error. Please try again.');
            }
        };

        
        xhr.send('login=1&email=' + encodeURIComponent(email) + '&password=' + encodeURIComponent(password));
    });

});