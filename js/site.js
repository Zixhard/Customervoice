var chatDelay = 0;
var msgSizes = ['small', 'normal', 'large', 'extra'];
var msgSpaces = ['near','middle','far'];
function calculateMessageSizing(message) {
    //var msgSizes = ['small', 'normal', 'large', 'extra'];
    var messageLength = message.length;
    var messageSize = 'extra';
    if(messageLength <= 10){
        messageSize = 'extra';
    }else if (messageLength > 10 && messageLength<=25 ){
        messageSize = 'large'
    }else if (messageLength > 25 && messageLength<=40 ){
        messageSize = 'normal'
    }else{
        messageSize = 'small'
    }
    return messageSize;
}
function onRowAdded() {
    $('.chat-container').animate({
        scrollTop: $('.chat-container').prop('scrollHeight')
    });
};
var Pruksa = {
    loadFeedback : function () {
        var reloadStatus = 0;
        var latestMessage = $('.chat-message-list li').last();
        var latestMessageID = latestMessage.length > 0 ? latestMessage.attr('rel') : 0;
        var opts = {
            url : 'site/feedback',
            method : 'post',
            success : function (callback) {
                $.each(callback, function (index, obj) {
                    if(parseInt(latestMessageID) < parseInt(obj.name)){
                        reloadStatus = 1;
                        var msgSize =  "message-size-" +calculateMessageSizing(obj.msg);
                        var msgSpace = "message-space-"+msgSpaces[Math.floor(Math.random()*msgSpaces.length)];
                        scrollDelay = chatDelay;
                        chatTimeString = " ";
                        msgname = "." + obj.name;
                        msginner = ".messageinner-" + obj.name;
                        spinner = ".sp-" + obj.name;
                        avatar = ".message-avatar-" + obj.name;

                        if (obj.showTime == true) {
                            chatTimeString = "<span class='message-time'>" + obj.time + "</span>";
                        }
                        $(".chat-message-list").append("<li class='message-" + obj.align + " " + obj.name + " " + msgSize + " " + msgSpace + "' hidden rel='"+obj.name+"'><div class='sp-" + obj.name + "'><span class='spinme-" + obj.align + "'><div class='spinner'><div class='bounce1'></div><div class='bounce2'></div><div class='bounce3'></div></div></span></div><div class='messageinner-" + obj.name + "' hidden><span class='message-text'><div class='message-avatar message-avatar-" + obj.name + "' hidden><img src='"+obj.avatar+"' class='img-responsive'></div>" + obj.msg + "</span>" + chatTimeString + "</div></li>");
                        $(msgname).fadeIn();
                        $(spinner).hide(1);
                        $(msginner).fadeIn();
                        $(avatar).fadeIn();
                    }


                });
                if(reloadStatus == 1) onRowAdded();
            }
        }
        $.ajax(opts);
    }
}
setInterval(function () { Pruksa.loadFeedback() }, 15000);
$(function () {
    $('.award-prize-dialog').on('click', function () {
        $(this).fadeOut(300);

    })
    Pruksa.loadFeedback();
    $('.btn-send-message').on('click', function (e) {
        gtag_report_conversion();
        return;
        var _this = $(this);
        var _parent = _this.parents('.feedback-form-wrapper');
        var inputMessage = _parent.children().find('.messenger-input');
        var _message = inputMessage.val();

        if(_message.trim().length == 0){
            inputMessage.addClass('error');
            setTimeout(function () {
                inputMessage.removeClass('error');
            }, 3000)
            return;
        }
        FB.login(function(response){
            if(response.status==='connected'){
                FB.api('/me',function(response){
                    var opts = {
                        url : 'site/comment',
                        method : 'post',
                        data : {
                            'secure_token' : $('input[name=secure_code]').val(),
                            'customer_name' : response.name,
                            'customer_feedback' : _message,
                            'customer_photo' : 'https://graph.facebook.com/'+response.id+'/picture?type=large',

                        },
                        success : function (callback) {
                            Pruksa.loadFeedback();
                            inputMessage.val("");
                            /*var obj = {
                                name: "response.id",
                                avatar : 'https://graph.facebook.com/'+response.id+'/picture?type=large',
                                msg: _message,
                                delay: 2000,
                                align: "left",
                                showTime: false,
                                time: null
                            }
                            var msgSize =  "message-size-" +calculateMessageSizing(_message);
                            var msgSpace = "message-space-"+msgSpaces[Math.floor(Math.random()*msgSpaces.length)];

                            chatDelay = chatDelay + 4000;
                            chatDelay2 = chatDelay + obj.delay;
                            chatDelay3 = chatDelay2 + 10;
                            scrollDelay = chatDelay;
                            chatTimeString = " ";
                            msgname = "." + obj.name;
                            msginner = ".messageinner-" + obj.name;
                            spinner = ".sp-" + obj.name;
                            avatar = ".message-avatar-" + obj.name;

                            if (obj.showTime == true) {
                                chatTimeString = "<span class='message-time'>" + obj.time + "</span>";
                            }
                            $(".chat-message-list").append("<li class='message-" + obj.align + " " + obj.name + " " + msgSize + " " + msgSpace + "'><div class='messageinner-" + obj.name + "'><span class='message-text'><div class='message-avatar message-avatar-" + obj.name + "'><img src='"+obj.avatar+"' class='img-responsive'></div>" + obj.msg + "</span>" + chatTimeString + "</div></li>");
                            onRowAdded();
                            inputMessage.val("");*/

                        }
                    }
                    $.ajax(opts);
                })
            }
        });
    });

});