/**
 * Created by ragedwiz on 13/11/16.
 */
class Chat {
    constructor(inputElement) {
        window['messageCounter'] = 0;
        inputElement.addEventListener('keydown', Chat.inputKeyHandler);

        window['chatWebsocket'] = new WebSocket('ws://localhost:2001/chatWebSocket');

        window['chatWebsocket'].onmessage = this.message;
    }

    message(event) {
        var message = JSON.parse(event.data);
        switch(message.type) {
            case 'ASSIGN':
                window['id'] = message.id;
                window['chatWebsocket'].send(JSON.stringify({
                    type: 'REGISTER',
                    username: window['selfUsername']
                }));
                var onlineCount = message['count'];
                window['onlineCount'] = onlineCount;
                document.querySelector('.online').innerHTML = onlineCount + ' people are online now!';
                break;
            case 'NOTIFICATION':
                switch (message['notification-type']) {
                    case 'USER_JOIN':
                        Chat.notifyUserJoin(message.username);
                        document.querySelector('.online').innerHTML = (++window['onlineCount']) + ' people are online now!';
                        break;
                    case 'USER_LEAVE':
                        Chat.notifyUserLeave(message.username);
                        if(message.username == window['selfUsername']) {
                            var overlay = document.createElement('div');
                            overlay.setAttribute('class', 'page-overlay');
                            overlay.innerHTML = 'You have closed one of the tabs/windows running the chat. Refresh to chat again.';
                            document.querySelector('body').appendChild(overlay);
                        }
                        document.querySelector('.online').innerHTML = (--window['onlineCount']) + ' people are online now!';
                        break;
                }
                break;
            case 'MESSAGE':
                var content = message['content'];
                var username = message['username'];
                var time = message['time'];

                if(username == window['selfUsername']) {
                    Chat.insertSelfMessage(time, content);
                } else {
                    Chat.insertMessage(username, time, content);
                }
                Chat.scrollToBottom(document.querySelector('.transcript'));
                break;
        }

        Chat.scrollToBottom(document.querySelector('.transcript'));
    }

    static avatarBind() {
        document.querySelectorAll('.name-circle').forEach(function (v) {
            v.addEventListener('click', Chat.openDetailsDialog);
        });
    }

    static inputKeyHandler(e) {
        if(e.keyCode == 13) {
            var ws = window['chatWebsocket'];
            ws.send(JSON.stringify({
                type: 'MESSAGE',
                content: document.querySelector('#message').value.trim()
            }));
            document.querySelector('#message').value = '';
        }
    }

    static openDetailsDialog() {
        var username = this.getAttribute('data-username');
        var dialog = document.createElement('dialog');
        dialog.setAttribute('class', 'mdl-dialog');
        var title = document.createElement('h5');
        title.setAttribute('class', 'mdl-dialog__title');
        var content = document.createElement('div');
        content.setAttribute('class', 'mdl-dialog__content');
        var actionBar = document.createElement('div');
        actionBar.setAttribute('class', 'mdl-dialog__actions');
        var doneButton = document.createElement('button');
        doneButton.setAttribute('class', 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent');
        doneButton.innerHTML = 'Done <i class="material-icons">&#xE5CA;</i>';
        var mentionButton = document.createElement('button');
        mentionButton.setAttribute('class', 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent');
        mentionButton.innerHTML = 'Mention @' + username + ' <i class="material-icons">&#xE24C;</i>';

        doneButton.addEventListener('click', function() {
            dialog.close();
        });

        mentionButton.addEventListener('click', function () {
            dialog.close();
            document.querySelector('#message').focus();
            document.querySelector('#message').value += ' @' + username;
            Chat.upgradeElements();
        });

        actionBar.appendChild(doneButton);
        if(username != window['selfUsername']) {
            actionBar.appendChild(mentionButton);
        }

        dialog.appendChild(title);
        dialog.appendChild(content);
        dialog.appendChild(actionBar);

        content.innerHTML = '<div class="details-preloader">' +
            '<div class="mdl-spinner mdl-js-spinner is-active">' +
            '</div>' +
            '</div>';

        title.innerHTML = 'Pulling user details ...';

        document.querySelector('body').appendChild(dialog);

        //noinspection JSUnresolvedFunction
        dialog.showModal();

        Chat.upgradeElements();

        var detailsXhr = new XMLHttpRequest();
        detailsXhr.open('GET', '/api/users/details/' + username, true);

        detailsXhr.onreadystatechange = function () {
            if(detailsXhr.readyState == 4 && detailsXhr.status == 200) {
                var resp = JSON.parse(detailsXhr.responseText);
                content.innerHTML = '<b>Email: </b>' + resp[username]['email'];
                title.innerHTML = 'Details for @' + username;
            }
        };

        detailsXhr.send();
    }

    static upgradeElements() {
        componentHandler.upgradeElements(document.querySelectorAll('.mdl-spinner'));
        componentHandler.upgradeElements(document.querySelectorAll('.mdl-button'));
        componentHandler.upgradeElements(document.querySelectorAll('dialog'));
        componentHandler.upgradeElement(document.querySelector('#message'));
    }

    static notifyUserJoin(username) {
        if(username != window['selfUsername']) {
            document.querySelector('.transcript').innerHTML += '' +
                '<div class="notification mdl-color--green">' +
                '@' + username + ' joined the room! Say hi to him!' +
                '</div>';
        } else {
            document.querySelector('.transcript').innerHTML += '' +
                '<div class="notification mdl-color--green">' +
                'You joined the room! Say hi to all of them!' +
                '</div>';
        }
    }

    static notifyUserLeave(username) {
        document.querySelector('.transcript').innerHTML += '' +
            '<div class="notification mdl-color--red">' +
            '@' + username + ' left the room...' +
            '</div>';
    }

    static insertMessage(username, time, content) {
        document.querySelector('.transcript').innerHTML += '' +
            '<div class="message">' +
                '<div class="name-circle" id="message' + (++window['messageCounter']) + '" data-username="' + username + '">' + username[0] + '</div>' +
                '<div class="mdl-tooltip" data-mdl-for="message' + (messageCounter) + '">' +
                    username + ' - Click for details!' +
                '</div>' +
                '<div class="box-container">' +
                    '<div class="box">' +
                        '<span class="name">' + username + '</span>' +
                        '<time>' + time + '</time>' +
                        '<div class="content">' +
                            content +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        componentHandler.upgradeElements(document.querySelectorAll('.mdl-tooltip'));
        Chat.avatarBind();
    }

    static insertSelfMessage(time, content) {
        document.querySelector('.transcript').innerHTML += '' +
            '<div class="message self">' +
            '<div class="box-container">' +
            '<div class="box">' +
            '<span class="name">' + window['selfUsername'] + '</span>' +
            '<time>' + time + '</time>' +
            '<div class="content">' +
            content +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="name-circle" id="message' + (++window['messageCounter']) + '" data-username="' + window['selfUsername'] + '">' + window['selfUsername'][0] + '</div>' +
            '<div class="mdl-tooltip" data-mdl-for="message' + (messageCounter) + '">' +
            window['selfUsername'] + ' - Click for details!' +
            '</div>' +
            '</div>';

        componentHandler.upgradeElements(document.querySelectorAll('.mdl-tooltip'));
        Chat.avatarBind();
    }

    static scrollToBottom(element) {
        element.scrollTop = element.scrollHeight;
    }
}