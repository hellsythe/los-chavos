<template>
    <article class="prose">
        <h1>Chats</h1>
        <button class="btn btn-primary">Nueva conversación</button>
    </article>
    <div class="flex flex-row mt-2">
        <div v-for="category in categories" class="mr-2">
            <h3 :class="category.color + ' text-sky-50 p-2 rounded-t-lg'">{{ category.name }}</h3>
            <draggable class="join join-vertical" :list="category.chats" group="chats" @change="log" itemKey="id">
                <template #item="{ element, index }">
                    <div class="btn join-item">{{ element.number }} </div>
                </template>
            </draggable>
        </div>
    </div>
</template>
<script>
import draggable from 'vuedraggable';

export default {
    name: "two-lists",
    display: "Two Lists",
    order: 1,
    components: {
        draggable
    },
    data() {
        return {
            categories: [
                {
                    name: "Sin Leer",
                    color: "bg-yellow-600",
                    chats: [
                        {
                            id: 1,
                            order: 1,
                            number: 2213428198,
                        },
                        {
                            id: 11,
                            order: 2,
                            number: 7713428198,
                        }
                    ]
                },
                {
                    name: "Sin clasificar",
                    color: "bg-blue-700",
                    chats: [
                        {
                            id: 1,
                            order: 1,
                            number: 2213428198,
                        },
                        {
                            id: 11,
                            order: 2,
                            number: 7713428198,
                        }
                    ]
                },
                {
                    name: "Ventas",
                    color: "bg-green-500",
                    chats: [
                        {
                            id: 2,
                            order: 1,
                            number: 2741039133,
                        },
                        {
                            id: 21,
                            order: 2,
                            number: 9913428198,
                        }
                    ]
                },
                {
                    name: "Seguimiento",
                    color: "bg-sky-500",
                    chats: [
                        {
                            id: 4,
                            order: 1,
                            number: 2741039133,
                        }
                    ]
                }
            ]
        };
    },
    methods: {
        add: function () {
            this.list.push({ name: "Juan" });
        },
        replace: function () {
            this.list = [{ name: "Edgard" }];
        },
        log: function (evt) {
            window.console.log(evt);
        }
    }
};
</script>
