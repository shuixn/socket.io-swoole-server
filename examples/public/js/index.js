$(function() {
    var socket = io('http://' + document.domain + ':9501');
    var nickname;
    var isEnter = false;

    $(document).keyup(function(event){
        if(event.keyCode == 13) {
            if (isEnter) {
                var message = $('#msg').val();
                if (message.length !== 0) {
                    socket.emit('new message', {
                        nickname: nickname,
                        message: message
                    });

                    $('#msg').val('');
                }
            } else {
                nickname = $('#nickname').val();
                if (nickname.length === 0) {
                    alert('please enter a nickname');
                    return;
                }
                
                isEnter = true;
                $('.home-page').hide();
                $('.chat-page').show();
                $('#msg').focus();
                

                socket.emit('new user', {
                    nickname: nickname
                });
            }
        }
    });

    socket.on('login', function (data) {
        data = JSON.parse(data);
        $('#chat').append('<li>Welcome ' + data.nickname + '!</li>');
    });

    socket.on('new message', function (data) {
        data = JSON.parse(data);
        $('#chat').append('<li>' + data.nickname + ':' + data.message + '</li>');
    });

    socket.on('user left', function (data) {
        data = JSON.parse(data);
        $('#chat').append('<li>' + data.nickname + ' has left</li>');
    });
});