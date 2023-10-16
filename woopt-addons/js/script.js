jQuery(document).ready(function($) {
    function updateCountdown() {
        $('.woopt-countdown').each(function() {
            var endDate = new Date($(this).data('enddate')).getTime();
            var now = new Date().getTime() + (new Date().getTimezoneOffset() * 60 * 1000);
            var distance = endDate - now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            var display = "";
            if (days > 0) {
                display += days + "d : ";
            }
            if (days > 0 || hours > 0) {
                display += hours + "h : ";
            }
            if (days > 0 || hours > 0 || minutes > 0) {
                display += minutes + "m : ";
            }
            display += seconds + "s";

            $(this).html(display);

            if (distance < 0) {
                $(this).html("EXPIRED");
            }
        });
    }

    // อัปเดต countdown ทันทีเมื่อ Document พร้อม
    updateCountdown();

    // อัปเดต countdown ทุก ๆ 1 วินาที
    setInterval(updateCountdown, 1000);
});