function handleLogout(){
    document.getElementById('logout-form').submit();
}

function getNextSaturday() {
    try {
    const csNextBattle = document.getElementById("cs-next-battle");
    const now = new Date();
    
    const daysUntilSaturday = (6 - now.getDay() + 7) % 7 || 7;

    const nextSaturday = new Date(now);
    nextSaturday.setDate(now.getDate() + daysUntilSaturday);

    const day = String(nextSaturday.getDate()).padStart(2, '0');
    const month = String(nextSaturday.getMonth() + 1).padStart(2, '0');
    const year = nextSaturday.getFullYear();
    
    const formattedDate = `Próxima batalha: ${day}/${month}/${year} às 15:00 - Sábado`;
    
    csNextBattle.innerHTML = formattedDate;
    }catch{}
}

document.addEventListener('DOMContentLoaded', function () {
    let isFetching = false;
    let countdownTimer;

    function getUpdatedEventTimes() {
        if (isFetching) return;
        isFetching = true;
        fetch('/loadEvents')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('event-container').innerHTML = html;
                updateCountdowns();
            })
            .catch(error => {
                console.error('Error fetching event HTML:', error);
            })
            .finally(() => {
                isFetching = false;
                window.setTimeout(getUpdatedEventTimes, 60000);
            });
    }

    function updateCountdowns() {
        if (countdownTimer) window.clearInterval(countdownTimer);
        const render = () => {
            const now = new Date();
            document.querySelectorAll('.event-item').forEach(event => {
                const timestamp = event.querySelector('.event-timestamp');
                const remaining = event.querySelector('.event-remaining');
                if (!timestamp || !remaining || !timestamp.dataset.hour) return;

                const eventTime = getNextEventTime(timestamp.dataset.hour, timestamp.dataset.dow, now);
                const remainingTime = calculateRemainingTime(eventTime, now);
                remaining.textContent = remainingTime;
                remaining.classList.toggle('event-imminent', remainingTime !== '00:00:00' && isLessThanTenMinutes(remainingTime));
            });
        };

        render();
        countdownTimer = window.setInterval(render, 1000);
    }

    function getNextEventTime(eventTimestamp, eventDay, currentTime) {
        const [hour, minute] = eventTimestamp.split(':').map(Number);
        const eventDate = new Date(currentTime);
        eventDate.setHours(hour, minute, 0, 0);

        const dayNames = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
        const targetDay = dayNames.indexOf((eventDay || '').toLowerCase());
        if (targetDay >= 0) {
            eventDate.setDate(eventDate.getDate() + ((targetDay - eventDate.getDay() + 7) % 7));
        }
        if (eventDate <= currentTime) {
            eventDate.setDate(eventDate.getDate() + (targetDay >= 0 ? 7 : 1));
        }
        return eventDate;
    }

    function calculateRemainingTime(eventTime, now) {
        const remainingMilliseconds = eventTime - now;
        if (remainingMilliseconds <= 0) return '00:00:00';
        
        const remainingSeconds = Math.floor(remainingMilliseconds / 1000);
        const hours = Math.floor(remainingSeconds / 3600);
        const minutes = Math.floor((remainingSeconds % 3600) / 60);
        const seconds = remainingSeconds % 60;
        if (isNaN(remainingMilliseconds)) {
            return 'N/A';
        }
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
    function isLessThanTenMinutes(remainingTime) {
        const [hours, minutes] = remainingTime.split(':').map(Number);
        return (hours === 0 && minutes < 10);
    }
    
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            let input = e.target.value.replace(/\D/g, ''); // Remove all non-digit characters
            input = input.substring(0, 11); // Limit the length to 11 digits
            
            if (input.length > 6) {
                input = `(${input.substring(0, 2)}) ${input.substring(2, 7)}-${input.substring(7)}`;
            } else if (input.length > 2) {
                input = `(${input.substring(0, 2)}) ${input.substring(2)}`;
            }

            e.target.value = input; // Set formatted value
        });
    }
    try{
        const rankingSelector = document.getElementById("ranking-event");
        const rankingButton = document.getElementById('ranking-search');
        rankingSelector.addEventListener('change', function(){
            if(!(rankingSelector.value === 'select')){
                rankingButton.disabled=false;
            }
        });
    }catch{}

    getNextSaturday();
    getUpdatedEventTimes();
});

window.onscroll = function() {
    const button = document.getElementById("goToTop");
    if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
        button.style.display = "block";
    } else {
        button.style.display = "none";
    }
};
const goToTop = document.getElementById("goToTop");
if (goToTop) {
    goToTop.onclick = function(event) {
        event.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };
}