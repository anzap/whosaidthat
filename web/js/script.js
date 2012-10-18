var vInterval;
var timeOut;

jQuery.fn.anim_progressbar = function (aOptions) {
    
    // def options
    var aDefOpts = {
        start: 100, 
        finish: 0, 
        interval: 1000
    }
    var aOpts = jQuery.extend(aDefOpts, aOptions);
    var vPb = this;

    // each progress bar
    return this.each(
        function() {
            var iDuration = aOpts.start - aOpts.finish;

            // calling original progressbar
            $(vPb).children('.pbar').progressbar();

            timeOut = aOpts.start;

            // looping process
            vInterval = setInterval(
                function(){
                    var iElapsedMs = timeOut - aOpts.finish, // elapsed time in MS
                        iPerc = (iElapsedMs > 0) ? iElapsedMs / iDuration * 100 : 0; // percentages

                    // display current positions and progress
                    $(vPb).children('.percent').html('<b>'+parseInt(timeOut)+'</b>');
                    $(vPb).children('.pbar').children('.ui-progressbar-value').css('width', iPerc+'%');

                    // in case of Finish
                    if (iPerc <= 0) {
                        clearInterval(vInterval);
                        $(vPb).children('.percent').html('<b>0</b>');
                        $(vPb).parent('form').submit();
                    }
                    timeOut--;
                } ,aOpts.interval
            );
        }
    );
}
