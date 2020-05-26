let acc = {

	api:"api/account-handler.php",
	msg:$("#server_resp"),

	init:function(){
		document.title = "You must login to continue.";
		acc.assignEvents();
	},

	assignEvents: function(){
		$("#login_form,#create_form").on("submit", acc.formSender);
	},

	getParams: function(form) {
		let ret = {};
		$(form).find("input").each(function(){
			ret[$(this).attr("name")] = $(this).val();
		});
		return ret;
	},

	formSender: function(e){
		e.preventDefault();

		$.post(acc.api, acc.getParams($(this)), function(resp){
			let data = {};
			try {
				data = JSON.parse(resp);
			} catch(e) {
				acc.error();
				return;
			}

			if (data.error === true) {
				acc.error(resp);
				return;
			}

			acc.msg.text(e.message).attr("class", "alert alert-success");

			window.location.href = "?logged_in";
		}).fail(acc.error);
	},

	error: function(resp){
		if (!navigator.onLine) {
			acc.msg.text("No internet").attr("class", "alert alert-danger");
		} else {
			let e = {"message":"Couldn't decode server response"};
			try {
				e = JSON.parse(resp);
			} catch(e) {}
			acc.msg.text(e.message).attr("class", "alert alert-danger");
		}
	}

};

acc.init();