var shoutbox = {
/**
* Configuration
**/
    refresh_time: '30',
    idle_time: '120',
    shoutid: '0',
    lang: {},
    runtime: 0,

    update: function () {
        if (shoutbox.idle_time == 0 || shoutbox.idle_time > shoutbox.runtime)
        {
            shoutbox.sendpost("update", {latest: shoutbox.shoutid});
            setTimeout(shoutbox.update, shoutbox.refresh_time);
            shoutbox.runtime += shoutbox.refresh_time;
        }
        else
        {
            $('#idle').html('You have been idle for ' + (shoutbox.idle_time / 1000) +' secs <a href="javascript:void();" onclick="shoutbox.reset();">click here to reload</a>');
            $('#idle').fadeIn('slow');
        }
    },

    addshout: function() {
        var message = $('#shoutout').val();
        if (message != "")
        {
            shoutbox.sendpost("shout", {message: message});
            $('#shoutout').val("");
            shoutbox.runtime = 0;
            $("#idle").hide();
        }
    },

    sendpost: function (type, info) {
        $.post("./engine/modules/shoutbox.php",
            info,
            function (data) {
                var count = 1;
                $($(data).get().reverse()).each(
                    function ()
                    {
                        shoutbox.shoutid++;
                        $(this).hide();
                        $(this).css("background-color", "#DBB84D");
                        $(this).prependTo('#shoutcontent').delay(800 * count).slideDown('slow', function() { $(this).css("background-color", ""); });
                        count++;
                    });
            });
    },

    init: function () {
        shoutbox.runtime = 0;
        shoutbox.update();
        shoutbox.updatetimes();
    },

    reset: function() {
        $("#idle").hide();
        shoutbox.runtime = 0;
        shoutbox.update();
    },

    updatetimes: function () {
        var times = $('var[id^="time_"]');
        $(times).each(function(){
            newtime = shoutbox.nicetime($(this).html());
            $('#' + $(this).attr('id') + '_update').html('(' + newtime + ')');
        });
        setTimeout(shoutbox.updatetimes, 5000);
    },

    nicetime: function (date) {
        periods = [shoutbox.lang["second"], shoutbox.lang["minute"], shoutbox.lang["hour"], shoutbox.lang["day"], shoutbox.lang["week"], shoutbox.lang["month"], shoutbox.lang["year"], shoutbox.lang["decade"]];
        lengths = ["60", "60", "24", "7", "4.35", "12", "10"];

        now = Math.floor(new Date().getTime() / 1000);
        unix_date = date;

        // is it future date or past date
        if (now > unix_date) {
            difference = now - unix_date;
            tense = shoutbox.lang["ago"];
        } else {
            difference = unix_date - now;
            tense = shoutbox.lang["from now"];
        }

        for (var j = 0; difference >= lengths[j] && j < (lengths.length - 1); j++) {
            difference = difference / lengths[j];
        }

        difference = Math.round(difference);

        if (difference != 1) {
            periods[j] = periods[j] + shoutbox.lang["s"];
        }

        return difference + " " + periods[j] + " " + tense;
    },
};
$(document).ready(function(){ shoutbox.init(); });