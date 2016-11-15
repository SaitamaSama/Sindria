/**
 * Created by ragedwiz on 8/11/16.
 */
class SignUp {
    constructor(formId) {
        this.formId = formId;
    }

    submit(e) {
        e.preventDefault();

        var overlay = document.querySelector('.spinner-overlay');

        overlay.style.display = 'block';
        overlay.style.height = '75%';
        overlay.style.width = '105%';

        overlay.focus();

        var store = {
            username: document.querySelector('#username').value,
            email: document.querySelector('#email').value,
            password: document.querySelector('#password').value,
            passwordRe: document.querySelector('#password-re').value
        };

        componentHandler.upgradeElement(document.querySelector('#signupLoader'));

        if(!SignUp.check(store)) {
            overlay.style.height = 0;
            overlay.style.width = 0;
            setTimeout(function () {
                overlay.style.display = 'none';
            }, 3010);
            return;
        }

        var existsXhr = new XMLHttpRequest();
        existsXhr.open('GET', '/api/CheckUsername/' + store.username, true);

        existsXhr.onreadystatechange = function() {
            if(existsXhr.readyState == 4 && existsXhr.status == 200) {
                var response = JSON.parse(existsXhr.responseText);

                if(response['exists'] == true) {
                    var toast = new Toast(document.querySelector('#snackbar'));

                    toast.show('Username already exists, use another username');

                    overlay.style.display = 'none';
                    overlay.style.height = 0;
                    overlay.style.width = 0;
                }
            } else {
                var sendXhr = new XMLHttpRequest();
                sendXhr.open('POST', '/signup', true);

                var data = new FormData();
                data.append('username', store.username);
                data.append('email', store.email);
                data.append('password', store.password);

                sendXhr.onreadystatechange = function () {
                    if(sendXhr.readyState == 4 && sendXhr.status == 200) {
                        var response = JSON.parse(sendXhr.responseText);
                        if(response['status'] == 'success') {
                            document.querySelector('.spinner-overlay').innerHTML = '<i class="mdl-color-text--green success-icon material-icons">&#xE5CA;</i>' +
                                '<br>' +
                                '<br>' +
                                'You have been successfully signed up!' +
                                '<br>' +
                                'Just a moment...';
                            setTimeout(function () {
                                window.location = '/';
                            }, 3000);
                        }
                    }
                };

                sendXhr.send(data);
            }
        };

        existsXhr.send();
    }

    static check(store) {
        var toast = new Toast(document.querySelector('#snackbar'));
        if(store.username.trim().length == 0) {
            toast.show('Username cannot be empty');
            return false;
        } else if(store.email.trim().length == 0) {
            toast.show('Email cannot be empty');
            return false;
        } else if(store.password.trim().length == 0) {
            toast.show('Password cannot be empty');
            return false;
        } else if(store.passwordRe.trim().length == 0) {
            toast.show('Please confirm the password');
            return false;
        } else if(store.password.trim().length < 8) {
            toast.show('Password must be of 8 characters or more');
            return false;
        } else if(store.password != store.passwordRe) {
            toast.show('Passwords do not match');
            return false;
        }

        return true;
    }

    init() {
        var form = document.querySelector('#' + this.formId);
        form.addEventListener('submit', this.submit);
    }
}