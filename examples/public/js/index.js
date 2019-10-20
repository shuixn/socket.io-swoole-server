$(function() {
    var socket = io('http://' + document.domain + ':9991');
    var nickname;
    var isEnter = false;

    $(document).keyup(function(event){
        if(event.keyCode == 13) {
            if (isEnter) {
                var message = $('#msg').val();
                if (message.length !== 0) {
                    socket.emit('new message', message);

                    $('#msg').val('');
                    $('#chat').append('<li>' + nickname + ':' + message + '</li>');
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
                

                socket.emit('new user', nickname);
            }
        }
    });

    socket.on('login', function (data) {
        $('#chat').append('<li>Welcome ' + data.username.toString() + '!</li>');
    });
});