/**
 * SiLLA Client-Side Scripting
 * Voice Announcements & Real-Time Monitoring
 */

// Auto-hide alert messages
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

/**
 * Helper untuk mendeteksi base path dinamis (diinject oleh PHP via window.APP_BASE_PATH)
 */
function getApiUrl(path) {
    const basePath = (window.APP_BASE_PATH || '').replace(/\/$/, '');
    return basePath + path;
}

/**
 * Text to Speech Voice Announcer (Bahasa Indonesia)
 */
function announceQueue(queueNumber, counterName) {
    if ('speechSynthesis' in window) {
        // Format text agar diucapkan dengan jelas per karakter
        // Misal: "A001" menjadi "A. Kosong. Kosong. Satu."
        const prefix = queueNumber.charAt(0);
        const numberPart = queueNumber.substring(1);
        
        let spokenNumber = '';
        for (let i = 0; i < numberPart.length; i++) {
            const char = numberPart.charAt(i);
            if (char === '0') {
                spokenNumber += ' kosong ';
            } else {
                spokenNumber += ' ' + char + ' ';
            }
        }

        const text = `Nomor antrian. ${prefix}. ${spokenNumber}. silakan menuju ke. ${counterName}`;
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'id-ID';
        utterance.rate = 0.85; // Sedikit pelan agar jelas
        utterance.pitch = 1;
        
        // Cari suara Bahasa Indonesia jika tersedia
        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(voice => voice.lang.includes('id'));
        if (idVoice) {
            utterance.voice = idVoice;
        }

        window.speechSynthesis.speak(utterance);
    } else {
        console.warn('Speech Synthesis tidak didukung oleh browser ini.');
    }
}

/**
 * Monitor Display Real-Time Polling
 */
class QueueDisplayMonitor {
    constructor() {
        this.previousStates = {};
        this.pollingInterval = null;
    }

    start() {
        this.fetchData(); // Fetch awal
        this.pollingInterval = setInterval(() => this.fetchData(), 3000); // Polling setiap 3 detik
    }

    stop() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }

    fetchData() {
        fetch(getApiUrl('/display/data'))
            .then(res => res.json())
            .then(data => {
                this.updateUI(data.counters);
                this.updateWaitingCount(data.waitingCount);
            })
            .catch(err => console.error('Error fetching display data:', err));
    }

    updateWaitingCount(count) {
        const waitingEl = document.getElementById('display-waiting-count');
        if (waitingEl) {
            waitingEl.textContent = count;
        }
    }

    updateUI(counters) {
        counters.forEach(counter => {
            const cardEl = document.getElementById(`counter-card-${counter.id}`);
            const numEl = document.getElementById(`counter-num-${counter.id}`);
            
            if (!cardEl || !numEl) return;

            const prevNum = this.previousStates[counter.id];
            const currentNum = counter.currentQueueNumber;

            // Update teks nomor antrian
            numEl.textContent = currentNum;

            if (currentNum !== '-' && currentNum !== prevNum) {
                // Ada perubahan nomor antrian (panggilan baru)
                if (prevNum !== undefined) {
                    // Beri efek highlight/flash
                    cardEl.classList.add('active');
                    numEl.style.animation = 'none';
                    numEl.offsetHeight; // Trigger reflow
                    numEl.style.animation = 'pulseGlow 1s infinite ease-in-out';

                    // Suarakan panggilan
                    announceQueue(currentNum, counter.name);

                    // Hilangkan efek aktif penuh setelah 10 detik
                    setTimeout(() => {
                        cardEl.classList.remove('active');
                    }, 10000);
                }
            }

            // Simpan state saat ini
            this.previousStates[counter.id] = currentNum;
        });
    }
}

/**
 * Kiosk Mandiri Ticket Generation
 */
function registerKioskAction() {
    const kioskBtn = document.getElementById('kiosk-btn-trigger');
    const container = document.getElementById('kiosk-container');

    if (!kioskBtn || !container) return;

    kioskBtn.addEventListener('click', () => {
        kioskBtn.disabled = true;
        kioskBtn.querySelector('.kiosk-btn-text').textContent = 'Mencetak...';

        fetch(getApiUrl('/queues/create?ajax=1'), {
            method: 'POST'
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                // Tampilkan tiket
                const now = new Date();
                const dateStr = now.toLocaleDateString('id-ID', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
                });
                const timeStr = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit', minute: '2-digit'
                });

                container.innerHTML = `
                    <div class="ticket-print">
                        <div class="ticket-header">SiLLA</div>
                        <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 10px;">Sistem Layanan Loket Antrian</div>
                        <hr style="border: none; border-top: 1px dashed #cbd5e1; margin: 10px 0;">
                        <div style="font-size: 0.9rem; font-weight: 600; color: #475569;">NOMOR ANTRIAN</div>
                        <div class="ticket-num">${res.queue.queueNumber}</div>
                        <div style="font-size: 0.85rem; color: #475569; margin-bottom: 15px;">Mohon menunggu nomor Anda dipanggil.</div>
                        <div class="ticket-date">${dateStr} - ${timeStr}</div>
                    </div>
                    <button class="btn btn-primary" onclick="window.location.reload()" style="margin-top: 20px;">
                        Kembali
                    </button>
                `;
            } else {
                alert('Gagal mengambil antrian: ' + res.error);
                window.location.reload();
            }
        })
        .catch(err => {
            console.error('Error creating queue ticket:', err);
            alert('Terjadi kesalahan jaringan.');
            window.location.reload();
        });
    });
}
