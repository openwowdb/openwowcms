var lang = {};
var core = "";

function db_con(busy, nextstep, failed, success) {
		var host = $("#db_host").val();
		var user = $("#db_user").val();
		var pass = $("#db_pass").val();
		var dbtype = $("#db_type").val();
		$('#db_con').html(busy + "...");
		$('#db_con').fadeIn('slow', function () { });
		$.post("./engine/installer/dynamic/db_con.php?l=" + nextstep + "&f=" + failed + "&s=" + success,
				{ host: host, user: user, pass: pass, dbtype: dbtype },
				function (data) { $("#db_con").html(data); }
		);
}

function checkadmin() {
		var host = $("#host").val();
		var user = $("#user").val();
		var pass = $("#pass").val();
		var admin_username = $("#admin_username").val();
		var admin_password = $("#admin_password").val();
		$("#checkadmin").html(lang['Connecting'] + "...");
		$.post("./engine/installer/dynamic/checkadmin.php?l=" + lang['Next Step'] + "&f=" + lang['Connection Failed'] + "&s=" + lang['Connection Successful'],
				{ host: host, user: user, pass: pass, admin_username: admin_username, admin_password: admin_password },
				function (data) { $("#checkadmin").html(data); }
		);
}

function db_install() {
		$('#db_process').fadeIn('slow', function () { });
		$('#errorcounts').remove();
		$.get("./engine/installer/dynamic/db_install.php", {}, function (data) { $("#db_process").append(data); db_errors(); });
}

function db_ignore(id) {
		$('#db_error' + id).remove();
		$('#db_error_ignore').html(parseInt($('#db_error_ignore').html()) + 1);
		$('#db_error_count').html(parseInt($('#db_error_count').html()) - 1);
		db_errors();
}

function db_errors() {
		if ($('span[id^="db_error"]').size() > 0)
				return;
		$('#db_install a').html(lang['Continue']);
		$('#db_install').appendTo('#db_process');
		$('#db_install').fadeIn('slow');
}

function pastetext(text, id) {
		$('#char_db' + id).val(text);
		$('#charcontentchar_db' + id).hide();
}

function addremoteaccess(id) {
		$('#charcontentchar_db' + id).append('<br>&nbsp;&nbsp;&nbsp;' + lang['Remote Access Port'] + ': ')
		.append('<input name="char_rasoap[]" id="char_rasoap' + id + '" value="3443" style="width: 250px;" type="text">')
		.append( '(' + lang['required'] + ')');
}

function addsoapaccess(id) {
		$('#charcontentchar_db' + id).append('<br>&nbsp;&nbsp;&nbsp;' + lang['SOAP Port'] + ': ')
		.append('<input name="char_rasoap[]" id="char_rasoap' + id + '" value="7878" style="width: 250px;" type="text">')
		.append('(' + lang['required'] + ')');
}

function addmore() {
		var id = $('#addmore div').size() / 2;
		id2 = id + 1;
		var realm = $('<div id="addmore' + id2 + '">' +
				'<b>No' + id2 + '</b> ' +
				'<input name="char_db[]" id="char_db' + id2 + '" value="" style="width: 250px;" type="text" onkeypress="$(\'#charcontentchar_db' + id2 + '\').show()"> <a href="javascript:void();" onclick="pastetext(\'\',' + id2 + ')">[-' + lang['remove'] + ']</a>' +
				'<div id="charcontentchar_db' + id2 + '" style="display: none;">&nbsp;&nbsp;&nbsp;' +
				'<strong>' + lang['Port'] + '</strong>: <input name="char_port[]" id="char_port' + id2 + '" value="3306" style="width: 250px;" type="text"> (' + lang['required'] + ')' +
				'<br>&nbsp;&nbsp;&nbsp;' + lang['Host'] + ': <input name="char_host[]" id="char_host' + id2 + '" value="" style="width: 250px;" type="text"> (' + lang['optional'] + ')' +
				'<br>&nbsp;&nbsp;&nbsp;' + lang['DB user'] + ': <input name="char_dbuser[]" id="char_dbuser' + id2 + '" value="" style="width: 250px;" type="text"> (' + lang['optional'] + ')' +
				'<br>&nbsp;&nbsp;&nbsp;' + lang['DB pass'] + ': <input name="char_dbpass[]" id="char_dbpass' + id2 + '" value="" style="width: 250px;" type="text"> (' + lang['optional'] + ')</div>');
		$('#addmore').append(realm);
		if (core != undefined) {
				if (core == "Trinity")
						addremoteaccess(id2);
				else if (core == "MaNGOS" || core == "Trinitysoap")
						addsoapaccess(id2);
		}
		$('#charcontentchar_db' + id2).append('<br>&nbsp;&nbsp;&nbsp;<strong>' + lang['Name'] + '</strong>: <input name="char_names[]" id="char_names' + id2 + '" value="' + lang['Realm'] + ' ' + lang['Name'] + '" style="width: 250px;" type="text"> (' + lang['required'] + ')<br><br>');
		$('#addmorebtn').appendTo($('#addmore').last());
		return id2;
}

function addfromdb(db, port) {
		var id = addmore();
		$('#char_db' + id).val(db);
		$('#char_port' + id).val(port);
		$('#charcontentchar_db' + id).show();
}