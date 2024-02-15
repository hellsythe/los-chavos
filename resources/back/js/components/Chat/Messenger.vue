<template>
    <div class="flex min-h-full h-full" style="height: 800px;">
        <div class="w-1/3 bg-base-200">
            <div class="p-2">
                <input type="text" placeholder="Buscar Chat" class="input w-full" />
            </div>
            <ul class="menu w-full p-0 overflow-auto">
                <li v-for="conversation in conversations" @click="setConversation(conversation)">
                    <a :class="{ active: conversation.id == current_conversation.id }">{{ conversation.client_phone }}</a>
                </li>
            </ul>
        </div>
        <div class="w-2/3 pl-1">
            <div class="w-full overflow-auto h-full">
                <div :class="{chat:true, 'chat-start': message.direction == 'toApp', 'chat-end': message.direction != 'toApp'}" v-for="message in messages">
                    <div :class="{'chat-bubble':true, 'chat-bubble-primary': message.direction != 'toApp'}">{{messages}}</div>
                </div>
            </div>
            <div class="w-full flex mt-3">
                <div class="w-5/6 mr-1">
                    <input type="text" placeholder="Escribe un mensaje" class="input border-1 border-gray-200 w-full" />
                </div>
                <div class="w-1/6">
                    <button class="btn btn-primary w-full">Enviar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const conversations = ref('')
const current_conversation = ref({ id: 0 })
const messages = ref({})

loadConversations();

async function loadConversations() {
    await fetch('/get-conversations')
        .then(response => response.json())
        .then(data => conversations.value = data);
    current_conversation.value = conversations.value[0];
    loadMessagesFromConversation();
}

function setConversation(conversation) {
    current_conversation.value = conversation;
    loadMessagesFromConversation();
}

async function loadMessagesFromConversation() {
    await fetch('/message?chat_id=' + current_conversation.value.id)
        .then(response => response.json())
        .then(data => messages.value = data.data);
}
</script>
