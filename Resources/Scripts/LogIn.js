/**
 * Created by ragedwiz on 12/11/16.
 */
class LogIn {
    constructor(formId) {
        document.querySelector('#' + formId).addEventListener('submit', LogIn.submit);
    }

    static submit(e) {
        e.preventDefault();

        var overlay = document.querySelector('.spinner-overlay');

        overlay.style.display = 'block';
        overlay.style.height = '75%';
        overlay.style.width = '105%';

        overlay.focus();

        var store = {
            username: document.querySelector('#username').value,
            password: document.querySelector('#password').value
        };

        if(!LogIn.check(store)) {
            overlay.style.height = 0;
            overlay.style.width = 0;
            setTimeout(function () {
                overlay.style.display = 'none';
            }, 3010);
            return;
        }

        var loginXhr = new XMLHttpRequest();
        loginXhr.open('POST', '/login', true);

        loginXhr.onreadystatechange = function () {
            if(loginXhr.readyState == 4 && loginXhr.status == 200) {
                var response = JSON.parse(loginXhr.responseText);
                if(response['status'] == 'success') {
                    document.querySelector('.spinner-overlay').innerHTML = '<i class="mdl-color-text--green success-icon material-icons">&#xE5CA;</i>' +
                        '<br>' +
                        '<br>' +
                        'You have been successfully logged in!' +
                        '<br>' +
                        'Just a moment...';
                    setTimeout(function () {
                        window.location = '/';
                    }, 3000);
                } else if(response['status'] == 'already-logged-in') {
                    document.querySelector('.spinner-overlay').innerHTML = '<i class="mdl-color-text--green success-icon material-icons">&#xE5CA;</i>' +
                        '<br>' +
                        '<br>' +
                        'You are already logged in!' +
                        '<br>' +
                        'Just a moment...';
                    setTimeout(function () {
                        window.location = '/';
                    }, 3000);
                } else if(response['status'] == 'failure') {
                    document.querySelector('.spinner-overlay').innerHTML = '<i class="mdl-color-text--red success-icon material-icons">&#xE5CD;</i>' +
                        '<br>' +
                        '<br>' +
                        response['reason'];
                }
            }
        };

        var data = new FormData();
        data.append('username', store.username);
        data.append('password', store.password);

        loginXhr.send(data);
    }

    static check(store) {
        var toast = new Toast(document.querySelector('#snackbar'));
        if(store.username.trim().length == 0) {
            toast.show('The username cannot be empty');
            return false;
        } else if(store.password.trim().length == 0) {
            toast.show('The password cannot be empty');
            return false;
        }
        return true;
    }
}
