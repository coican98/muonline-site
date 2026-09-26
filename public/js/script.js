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
    
    if(csNextBattle) {
        csNextBattle.innerHTML = formattedDate;
    }
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
                const container = document.getElementById('event-container');
                if (container) {
                    container.innerHTML = html;
                    updateCountdowns();
                }
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
            const tableBody = document.querySelector('.event-list table tbody') || document.querySelector('.event-list table');
            const items = Array.from(document.querySelectorAll('.event-item'));

            items.forEach(event => {
                const timestamp = event.querySelector('.event-timestamp');
                const remaining = event.querySelector('.event-remaining');
                if (!timestamp || !remaining || !timestamp.dataset.hour) return;

                const eventTime = getNextEventTime(timestamp.dataset.hour, timestamp.dataset.dow, now);
                const remainingMs = eventTime - now;
                const remainingTime = calculateRemainingTime(eventTime, now);

                remaining.textContent = remainingTime;
                remaining.classList.toggle('event-imminent', remainingTime !== '00:00:00' && remainingTime !== 'N/A' && isLessThanTenMinutes(remainingTime));
                event.dataset.remainingMs = remainingMs > 0 ? remainingMs : 0;
            });

            // Reordena no DOM: Ativos primeiro ordenados por tempo restante (menor primeiro), inativos ao final
            if (tableBody && items.length > 1) {
                items.sort((a, b) => {
                    const enabledA = a.dataset.enabled === '1' ? 1 : 0;
                    const enabledB = b.dataset.enabled === '1' ? 1 : 0;

                    if (enabledA !== enabledB) {
                        return enabledB - enabledA; // Ativos (1) vêm antes de inativos (0)
                    }

                    const msA = parseFloat(a.dataset.remainingMs) || 999999999;
                    const msB = parseFloat(b.dataset.remainingMs) || 999999999;
                    return msA - msB;
                });
                items.forEach(item => tableBody.appendChild(item));
            }
        };

        render();
        countdownTimer = window.setInterval(render, 1000);
    }

    function getNextEventTime(eventTimestamp, eventDay, currentTime) {
        if (!eventTimestamp || eventTimestamp === 'null') return currentTime;
        const parts = eventTimestamp.split(':');
        if (parts.length < 2) return currentTime;
        const [hour, minute] = parts.map(Number);

        const dayNames = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
        const configuredDays = (eventDay || '').split(',').map(d => d.trim().toLowerCase());

        let earliestDate = null;

        for (const singleDay of configuredDays) {
            const eventDate = new Date(currentTime);
            eventDate.setHours(hour, minute, 0, 0);

            const targetDay = dayNames.indexOf(singleDay);
            if (targetDay >= 0) {
                eventDate.setDate(eventDate.getDate() + ((targetDay - eventDate.getDay() + 7) % 7));
            }
            if (eventDate <= currentTime) {
                eventDate.setDate(eventDate.getDate() + (targetDay >= 0 ? 7 : 1));
            }

            if (!earliestDate || eventDate < earliestDate) {
                earliestDate = eventDate;
            }
        }

        return earliestDate || currentTime;
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

        const timeStr = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        // Se faltar mais de 24h (>= 24 horas), informa o dia da semana da próxima execução
        if (hours >= 24) {
            const dayNamesShort = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            const nextDayName = dayNamesShort[eventTime.getDay()] || '';
            return `${nextDayName} (${timeStr})`;
        }

        return timeStr;
    }
    
    function isLessThanTenMinutes(remainingTime) {
        if (!remainingTime || remainingTime === 'N/A') return false;
        // Se tem o dia da semana ex: "Sáb (48:00:00)", não é menor que 10 minutos
        if (remainingTime.includes('(')) return false;
        const parts = remainingTime.split(':');
        if (parts.length !== 3) return false;
        const [hours, minutes] = parts.map(Number);
        return (hours === 0 && minutes < 10);
    }
    
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function (e) {
            let input = e.target.value.replace(/\D/g, ''); 
            input = input.substring(0, 11); 
            
            if (input.length > 6) {
                input = `(${input.substring(0, 2)}) ${input.substring(2, 7)}-${input.substring(7)}`;
            } else if (input.length > 2) {
                input = `(${input.substring(0, 2)}) ${input.substring(2)}`;
            }

            e.target.value = input; 
        });
    }
    
    try {
        const rankingSelector = document.getElementById("ranking-event");
        const rankingButton = document.getElementById('ranking-search');
        if (rankingSelector && rankingButton) {
            rankingSelector.addEventListener('change', function(){
                if(!(rankingSelector.value === 'select')){
                    rankingButton.disabled = false;
                } else {
                    rankingButton.disabled = true;
                }
            });
        }
    } catch{}

    getNextSaturday();
    if(document.getElementById('event-container')) {
        getUpdatedEventTimes();
    }
});

window.onscroll = function() {
    const button = document.getElementById("goToTop");
    if(button) {
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            button.style.display = "block";
        } else {
            button.style.display = "none";
        }
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

// ---------------------------------------------------
// Componente de Modal de Confirmação (Acessível)
// ---------------------------------------------------
window.createConfirmModal = function(options) {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-modal-overlay';
    
    const modal = document.createElement('div');
    modal.className = 'confirm-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'modal-title');
    
    let html = `<h3 id="modal-title">${options.title || 'Confirmação'}</h3>`;
    html += `<p>${options.message || 'Tem certeza que deseja realizar esta ação?'}</p>`;
    
    if (options.accountText) {
        html += `<p>Conta afetada: <span class="modal-account">${options.accountText}</span></p>`;
    }
    
    html += `<div class="confirm-modal-actions">
        <button type="button" class="btn btn-secondary" id="modal-cancel-btn">${options.cancelText || 'Cancelar'}</button>
        <button type="button" class="btn btn-danger" id="modal-confirm-btn">${options.confirmText || 'Confirmar'}</button>
    </div>`;
    
    modal.innerHTML = html;
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    const cancelBtn = modal.querySelector('#modal-cancel-btn');
    const confirmBtn = modal.querySelector('#modal-confirm-btn');
    
    const previouslyFocused = document.activeElement;
    confirmBtn.focus();
    
    const closeModal = () => {
        if (document.body.contains(overlay)) {
            document.body.removeChild(overlay);
        }
        if (previouslyFocused) previouslyFocused.focus();
    };
    
    cancelBtn.addEventListener('click', closeModal);
    
    confirmBtn.addEventListener('click', () => {
        if (typeof options.onConfirm === 'function') {
            options.onConfirm();
        }
        closeModal();
    });
    
    modal.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            return;
        }
        if (e.key === 'Tab') {
            const focusables = modal.querySelectorAll('button');
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            
            if (e.shiftKey) {
                if (document.activeElement === first) {
                    last.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === last) {
                    first.focus();
                    e.preventDefault();
                }
            }
        }
    });
};

/* ============================================================
   MOTOR DE BRASAS INCANDESCENTES (EMBERS ENGINE)
   ============================================================ */
function startEmbersEngine() {
    const canvas = document.getElementById('emberCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const emberCount = 85;
    const embers = [];

    class Ember {
        constructor() {
            this.reset(true);
        }

        reset(initial = false) {
            this.x = Math.random() * width;
            this.y = initial ? Math.random() * height : height + Math.random() * 30;
            this.size = Math.random() * 2.8 + 1.2; // Brasa visível
            this.speedY = Math.random() * 1.8 + 0.9; // Subida contínua
            this.speedX = (Math.random() - 0.5) * 1.0;
            this.life = 0;
            this.maxLife = Math.random() * 240 + 160;
            this.color = Math.random() > 0.35 ? 'fire' : 'gold';
            this.flickerSpeed = Math.random() * 0.12 + 0.06;
        }

        update() {
            this.y -= this.speedY;
            this.x += Math.sin(this.life * 0.04) * 0.9 + this.speedX;
            this.life++;

            if (this.y < -15 || this.life > this.maxLife) {
                this.reset();
            }
        }

        draw() {
            const progress = this.life / this.maxLife;
            let alpha = 1;
            if (progress < 0.12) {
                alpha = progress / 0.12;
            } else if (progress > 0.65) {
                alpha = 1 - (progress - 0.65) / 0.35;
            }

            const flicker = Math.sin(this.life * this.flickerSpeed) * 0.25;
            const finalAlpha = Math.max(0, Math.min(1, alpha + flicker));

            ctx.save();
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);

            if (this.color === 'fire') {
                ctx.fillStyle = `rgba(255, 65, 25, ${finalAlpha * 0.95})`;
                ctx.shadowColor = 'rgba(255, 45, 0, 1)';
                ctx.shadowBlur = 8;
            } else {
                ctx.fillStyle = `rgba(255, 210, 90, ${finalAlpha * 0.95})`;
                ctx.shadowColor = 'rgba(245, 175, 50, 0.9)';
                ctx.shadowBlur = 6;
            }

            ctx.fill();
            ctx.restore();
        }
    }

    for (let i = 0; i < emberCount; i++) {
        embers.push(new Ember());
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);
        for (let i = 0; i < embers.length; i++) {
            embers[i].update();
            embers[i].draw();
        }
        requestAnimationFrame(animate);
    }

    animate();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startEmbersEngine);
} else {
    startEmbersEngine();
}