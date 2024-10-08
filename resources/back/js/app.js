import '@sdkconsultoria/base';
import './components/components';
import './chat';
import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
import {notify, notifyb} from './desktopnotifications';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

Notification.requestPermission();

window.Echo.private(`orders`)
    .listen('NewOrder', (e) => {
        notify(e.order);
        location.reload();
    });


window.Echo.private(`orders_auth_request`)
.listen('OrderAuthRequest', (e) => {
    notifyb(e.order);
    // location.reload();
});


window.Echo.private(`orders_auth`)
.listen('OrderAuth', (e) => {
    notifyb(e.order);
});

let messages = 0;
let menu = document.getElementById('chat-menu').getElementsByTagName('a')[0];
window.Echo.channel(`new_whatsapp_message`)
    .listen('.Sdkconsultoria\\WhatsappCloudApi\\Events\\NewWhatsappMessageHook', (e) => {
        messages++;
        loadMessages();

    });

    fetch('/admin/unread').then(response => response.json()).then(data => {
        messages = data;
        if(messages > 0){
            loadMessages();
        }
    });

    function loadMessages(){
        let lastBadge = menu.getElementsByClassName('badge')[0];


        let badge = document.createElement('span');
        badge.classList.add('badge', 'badge-warning');
        badge.innerHTML = '+'+messages;

        if(lastBadge){
            lastBadge.remove();
        }

        menu.appendChild(badge);
    }
