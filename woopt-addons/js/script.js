document.addEventListener("DOMContentLoaded", function() {
    var countdownElements = document.querySelectorAll('.woopt-countdown');

    // Function สำหรับการอัพเดตเวลาถอยหลัง
    function updateCountdown(countdownElement, endDate, interval) {
        var now = Date.now();
        var distance = endDate - now;

        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownElement.textContent = days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

        if (distance < 0) {
            clearInterval(interval);
            countdownElement.textContent = "EXPIRED";
        }
    }

    countdownElements.forEach(function(countdownElement) {
        var endDate = new Date(countdownElement.getAttribute('data-enddate')).getTime();
        
        var interval = setInterval(function() {
            updateCountdown(countdownElement, endDate, interval);
        }, 1000);
    });
});