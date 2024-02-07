<template>
    <h1>Plan de trabajo - Bordado</h1>
    <FullCalendar :options="calendarOptions" />
</template>

<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale from '@fullcalendar/core/locales/es';

export default {
    name: "Calendar",
    components: {
        FullCalendar
    },
    props: {
        events: Object
    },
    data() {
        return {
            calendarOptions: {
                plugins: [dayGridPlugin, interactionPlugin],
                editable: true,
                initialView: 'dayGridWeek',
                locale: esLocale,
                events: this.events,
                timeZone: 'local',
                firstDay: new Date().getDay(),
            }
        }
    }
};
</script>
