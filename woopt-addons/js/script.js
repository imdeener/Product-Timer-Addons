document.addEventListener("DOMContentLoaded", function() {
    var countdownElements = document.querySelectorAll('.woopt-countdown');

    countdownElements.forEach(function(countdownElement) {
        var endDate = new Date(countdownElement.getAttribute('data-enddate')).getTime();

        var interval = setInterval(function() {
            // แก้ไขให้ใช้เวลา UTC+0 โดยการคำนวณจาก getTimezoneOffset
            var now = new Date().getTime() + (new Date().getTimezoneOffset() * 60 * 1000);
            var distance = endDate - now;

            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

            countdownElement.innerHTML = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

            if (distance < 0) {
                clearInterval(interval);
                countdownElement.innerHTML = "EXPIRED";
            }
        }, 1000);
    });
});