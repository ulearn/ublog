// JavaScript Document
var FbAll = {
    facebookLogin: function () {
        var clientid = document.getElementById("client_id");
        var redirecturi = document.getElementById("redirect_uri");
 
        if (clientid.value == '') {
            alert("You have not configure facebook api settings.")
        } else {
            window.open('https://graph.facebook.com/oauth/authorize?client_id=' + clientid.value + '&redirect_uri=' + redirecturi.value + '&scope=email,publish_stream',
				'', 'scrollbars=no,menubar=no,height=400,width=800,resizable=yes,toolbar=no,status=no');
        }
    },
 
    parentRedirect: function (config) {
        var redirectto = document.getElementById("fball_login_form_uri");
        var form = document.createElement('form');
        form.id = 'fball-loginform';
        form.method = 'post';
        form.action = redirectto.value;
        form.innerHTML = '<input type="hidden" id="fball_redirect" name="fball_redirect" value="' + redirectto.value + '">';
 
        var key;
        for (key in config) {
            form.innerHTML += '<input type="hidden" id="' + key + '" name="' + key + '" value="' + config[key] + '">';
        }
 
        document.body.appendChild(form);
        form.submit();
    }
 }



