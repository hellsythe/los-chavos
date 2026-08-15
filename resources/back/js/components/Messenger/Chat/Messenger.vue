<template>
    <div class="flex min-h-full h-full" style="height: 800px;">
        <div class="w-1/3 bg-base-200 overflow-auto h-full flex flex-col" ref="chatListRef"
             @scroll="onScroll">
            <div class="p-2 flex gap-1">
                <input @change="loadConversations(true)" v-model="search" type="text" placeholder="Buscar Chat"
                    class="input w-full" />
                <button @click="toggleUnreadOnly"
:class="['btn btn-info gap-1', onlyUnread ? '' : 'btn-warning']"
                    :title="onlyUnread ? 'Mostrando solo chats con mensajes sin leer. Click para mostrar todos.' : 'Mostrar solo chats con mensajes sin leer'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                    </svg>
                    <span>No leídos</span>
                    <span v-if="unreadCount > 0" class="badge badge-sm">{{ unreadCount }}</span>
                </button>
                <SendNew />
            </div>
            <ul class="menu w-full p-0">
                <li v-for="conversation in conversations" :key="conversation.id" @click="setConversation(conversation)">
                    <a :class="{ active: conversation.id == current_conversation.id }">
                        {{ conversation.client_phone }}
                        <span v-if="conversation.unread_messages" class="indicator-item badge badge-secondary">{{
                    conversation.unread_messages }}</span>
                    </a>
                </li>
                <li v-if="loadingMore" class="text-center py-2">
                    <span class="loading loading-spinner loading-sm"></span>
                </li>
                <li v-else-if="!hasMore && conversations.length > 0" class="text-center text-xs text-gray-500 py-2">
                    — fin de la lista ({{ conversations.length }} chats) —
                </li>
                <li v-else-if="conversations.length === 0" class="text-center text-sm text-gray-500 py-4">
                    Sin chats
                </li>
            </ul>
        </div>
        <div class="w-2/3">
            <div class="bg-base-200 p-4">{{ current_conversation.client_phone }}</div>
            <div class="chat chat-start"></div>
            <div class="chat chat-end"></div>
            <div class="w-full overflow-auto h-full">
                <div :class="{ chat: true, 'chat-start': message.direction == 'toApp', 'chat-end': message.direction != 'toApp' }"
                    v-for="message in messages">
                    <ChatBubble :key="message.id" :message="message" />
                </div>
            </div>
            <div class="w-full flex mt-3">
                <SendMediaMessage @sendMessage="sendMediaMessage" />
                <div class="w-5/6 mr-1">
                    <input v-model="message" type="text" placeholder="Escribe un mensaje" @keyup.enter="sendMessage"
                        class="input border-1 border-gray-200 w-full" />
                </div>
                <div class="w-1/6">
                    <button class="btn btn-primary w-full" @click="sendMessage">Enviar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import SendNew from './SendNew.vue';
import SendMediaMessage from './SendMediaMessage.vue';
import ChatBubble from './ChatBubble.vue';

const message = ref('')
const search = ref('')
const conversations = ref([])
const current_conversation = ref({ id: 0 })
const messages = ref({})

const onlyUnread = ref(false)
const nextPageUrl = ref(null)
const hasMore = ref(false)
const loadingMore = ref(false)
const unreadCount = ref(0)

const chatListRef = ref(null)
let scrollHandler = null
let resizeObserver = null
let mutationObserver = null

let sendingMessage = false;

loadConversations(true);

function buildChatUrl() {
    const params = new URLSearchParams();
    params.set('client_phone', search.value || '');
    if (onlyUnread.value) {
        params.set('unread', '1');
    }
    return '/api/v1/chat?' + params.toString();
}

function attachScrollListener() {
    const el = chatListRef.value;
    if (!el) return;

    scrollHandler = () => {
        const distanceFromBottom = el.scrollHeight - el.scrollTop - el.clientHeight;
        if (distanceFromBottom < 150 && hasMore.value && !loadingMore.value) {
            loadMoreConversations();
        }
    };
    el.addEventListener('scroll', scrollHandler, { passive: true });

    mutationObserver = new MutationObserver(() => {
        if (el.scrollHeight - el.scrollTop - el.clientHeight < 250 && hasMore.value && !loadingMore.value) {
            loadMoreConversations();
        }
    });
    mutationObserver.observe(el, { childList: true, subtree: true });
}

function detachScrollListener() {
    const el = chatListRef.value;
    if (el && scrollHandler) {
        el.removeEventListener('scroll', scrollHandler);
    }
    if (mutationObserver) {
        mutationObserver.disconnect();
    }
}

onMounted(() => {
    attachScrollListener();
});

onBeforeUnmount(() => {
    detachScrollListener();
});

async function loadConversations(reset = true) {
    if (reset) {
        conversations.value = [];
        nextPageUrl.value = null;
        hasMore.value = false;
        loadingMore.value = false;
    }

    const url = buildChatUrl();
    const res = await fetch(url);
    const data = await res.json();

    if (reset && current_conversation.value.id == 0 && data.data && data.data.length > 0) {
        current_conversation.value = data.data[0];
        loadMessagesFromConversation();
    }

    conversations.value = [...conversations.value, ...data.data];
    nextPageUrl.value = data.next_page_url || null;
    hasMore.value = !!nextPageUrl.value;
    recomputeUnreadCount();

    refreshSidebarBadge();
}

async function loadMoreConversations() {
    if (!nextPageUrl.value || loadingMore.value) return;
    loadingMore.value = true;
    try {
        const res = await fetch(nextPageUrl.value);
        const data = await res.json();
        conversations.value = [...conversations.value, ...data.data];
        nextPageUrl.value = data.next_page_url || null;
        hasMore.value = !!nextPageUrl.value;
        recomputeUnreadCount();
    } finally {
        loadingMore.value = false;
    }
}

function recomputeUnreadCount() {
    unreadCount.value = conversations.value.reduce(
        (sum, c) => sum + (parseInt(c.unread_messages || 0, 10)),
        0
    );
}

function toggleUnreadOnly() {
    onlyUnread.value = !onlyUnread.value;
    loadConversations(true);
}

function setConversation(conversation) {
    current_conversation.value = conversation;
    loadMessagesFromConversation();
}

async function loadMessagesFromConversation() {
    await fetch('/api/v1/message?chat_id=' + current_conversation.value.id)
        .then(response => response.json())
        .then(data => messages.value = data.data);

    refreshSidebarBadge();
}

function refreshSidebarBadge() {
    fetch('/admin/unread')
        .then(response => response.json())
        .then(count => {
            window.dispatchEvent(new CustomEvent('chat:unread-updated', { detail: count }));
        });
}

function sendMessage() {

    if (sendingMessage) {
        return;
    }

    if (message.value == '') {
        return;
    }

    sendingMessage = true;

    fetch('/api/v1/message/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            waba_phone_id: current_conversation.value.waba_phone_id,
            to: current_conversation.value.client_phone,
            message: {
                "type": "text",
                "text": {
                    "preview_url": false,
                    "body": message.value
                }

            }
        })
    }).then(response => response.json())
        .then(data => {
            message.value = '';
            loadMessagesFromConversation();
            sendingMessage = false;
        });
}

function sendMediaMessage(file, type) {
    const formdata = new FormData();
    formdata.append("waba_phone_id", current_conversation.value.waba_phone_id);
    formdata.append("to", current_conversation.value.client_phone,);
    formdata.append("message[type]", type);
    formdata.append(`message[${type}]`, file);
    console.log(formdata);
    fetch('/api/v1/message/send', {
        method: 'POST',
        body: formdata,
    }).then(response => response.json())
        .then(data => {
            loadMessagesFromConversation();
        });
}

setTimeout(() => {
    window.Echo.channel(`new_whatsapp_message`)
        .listen('.Sdkconsultoria\\WhatsappCloudApi\\Events\\NewWhatsappMessageHook', (e) => {
            loadConversations(true);
            if (e.chat.chat_id == current_conversation.value.id) {
                loadMessagesFromConversation();
            }
        });
}, 1000);

</script>