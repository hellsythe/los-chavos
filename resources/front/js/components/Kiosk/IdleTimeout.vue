<template>
    <teleport to="body">
        <transition name="kiosk-fade">
            <div v-if="state !== 'idle'" class="kiosk-modal-overlay">
                <div class="kiosk-modal">
                    <p class="kiosk-modal-eyebrow">Sin actividad</p>
                    <h2 class="kiosk-modal-title">¿Sigues aquí?</h2>
                    <p class="kiosk-modal-text">
                        La pantalla volverá al inicio en
                        <strong>{{ countdown }}</strong> {{ countdown === 1 ? 'segundo' : 'segundos' }}.
                    </p>
                    <button type="button" class="kiosk-btn-cta" @click="reset">
                        Seguir aquí
                    </button>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<script>
const IDLE_MS = 90 * 1000;
const WARNING_MS = 15 * 1000;

export default {
    name: 'KioskIdleTimeout',
    data() {
        return {
            state: 'idle', // 'idle' | 'warning' | 'expired'
            countdown: Math.ceil(WARNING_MS / 1000),
            timer: null,
            countdownTimer: null,
        };
    },
    mounted() {
        this.bindEvents();
        this.ping();
    },
    beforeUnmount() {
        this.unbindEvents();
        this.clearTimers();
    },
    methods: {
        bindEvents() {
            this.handler = () => this.ping();
            window.addEventListener('pointerdown', this.handler, { passive: true });
            window.addEventListener('keydown', this.handler);
        },
        unbindEvents() {
            window.removeEventListener('pointerdown', this.handler);
            window.removeEventListener('keydown', this.handler);
        },
        clearTimers() {
            if (this.timer) {
                clearTimeout(this.timer);
                this.timer = null;
            }
            if (this.countdownTimer) {
                clearInterval(this.countdownTimer);
                this.countdownTimer = null;
            }
        },
        ping() {
            if (this.state === 'warning') return;
            this.state = 'idle';
            this.clearTimers();
            this.timer = setTimeout(this.showWarning, IDLE_MS);
        },
        showWarning() {
            this.state = 'warning';
            this.countdown = Math.ceil(WARNING_MS / 1000);
            this.countdownTimer = setInterval(() => {
                this.countdown -= 1;
                if (this.countdown <= 0) {
                    this.expire();
                }
            }, 1000);
            this.timer = setTimeout(this.expire, WARNING_MS);
        },
        expire() {
            this.clearTimers();
            this.state = 'expired';
            this.$emit('reset');
            this.ping();
        },
        reset() {
            this.clearTimers();
            this.state = 'idle';
            this.ping();
        },
    },
};
</script>
