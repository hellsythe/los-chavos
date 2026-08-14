<template>
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div role="tablist" class="tabs tabs-bordered">
                <a role="tab" :class="['tab', { 'tab-active': activeTab === 'hours' }]" @click.prevent="activeTab = 'hours'">
                    Horarios
                </a>
                <a role="tab" :class="['tab', { 'tab-active': activeTab === 'holidays' }]" @click.prevent="activeTab = 'holidays'">
                    Días feriados
                </a>
                <a role="tab" :class="['tab', { 'tab-active': activeTab === 'info' }]" @click.prevent="activeTab = 'info'">
                    Datos del negocio
                </a>
            </div>

            <div v-if="activeTab === 'hours'" class="mt-4">
                <div class="alert alert-info mb-4">
                    <span>Define el horario de apertura de cada día. El bot usará esta info para responder "¿a qué hora abren?" o "¿abren los domingos?".</span>
                </div>

                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th class="w-1/4">Día</th>
                            <th class="w-1/4">Abierto</th>
                            <th class="w-1/4">Hora apertura</th>
                            <th class="w-1/4">Hora cierre</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(day, key) in config.business_hours" :key="key">
                            <td class="font-semibold">{{ dayLabels[key] }}</td>
                            <td>
                                <input type="checkbox" class="toggle toggle-success" v-model="config.business_hours[key].closed"
                                    :true-value="false" :false-value="true" />
                                <span class="ml-2 text-sm">{{ config.business_hours[key].closed ? 'Cerrado' : 'Abierto' }}</span>
                            </td>
                            <td>
                                <input type="time" class="input input-bordered input-sm w-full"
                                    v-model="config.business_hours[key].open"
                                    :disabled="config.business_hours[key].closed" />
                            </td>
                            <td>
                                <input type="time" class="input input-bordered input-sm w-full"
                                    v-model="config.business_hours[key].close"
                                    :disabled="config.business_hours[key].closed" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="activeTab === 'holidays'" class="mt-4">
                <div class="alert alert-info mb-4">
                    <span>Lista de fechas en las que el negocio está cerrado. El bot anunciará estos días en respuestas sobre "¿cuándo abren?".</span>
                </div>

                <div v-if="config.business_holidays.length === 0" class="text-center text-gray-500 py-8">
                    No hay días feriados registrados. Agrega uno con el botón "Agregar".
                </div>

                <table v-else class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Motivo</th>
                            <th>Cerrado</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(holiday, index) in config.business_holidays" :key="index">
                            <td>
                                <input type="date" class="input input-bordered input-sm" v-model="holiday.date" />
                            </td>
                            <td>
                                <input type="text" class="input input-bordered input-sm w-full" v-model="holiday.reason"
                                    placeholder="Ej: Navidad" />
                            </td>
                            <td>
                                <input type="checkbox" class="toggle toggle-sm" v-model="holiday.closed" />
                            </td>
                            <td>
                                <button class="btn btn-sm btn-ghost text-error" @click="removeHoliday(index)" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button class="btn btn-outline btn-sm mt-4" @click="addHoliday">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                    Agregar día feriado
                </button>
            </div>

            <div v-if="activeTab === 'info'" class="mt-4">
                <div class="alert alert-info mb-4">
                    <span>Información general del negocio que el bot puede incluir en sus respuestas (dirección, teléfono, etc.).</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Nombre del negocio</span>
                        </label>
                        <input type="text" class="input input-bordered" v-model="config.business_info.business_name"
                            placeholder="Ej: Uniformes Los Chavos" />
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Teléfono</span>
                        </label>
                        <input type="text" class="input input-bordered" v-model="config.business_info.phone"
                            placeholder="Ej: +52 274 123 4567" />
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text">Dirección</span>
                        </label>
                        <input type="text" class="input input-bordered" v-model="config.business_info.address"
                            placeholder="Ej: Centro, Tierra Blanca" />
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input type="email" class="input input-bordered" v-model="config.business_info.email"
                            placeholder="Ej: contacto@loschavos.com" />
                    </div>
                </div>
            </div>

            <div class="divider mt-6"></div>

            <div class="flex justify-between items-center">
                <div v-if="lastSaved" class="text-sm text-success">
                    Guardado {{ lastSaved }}
                </div>
                <div v-else></div>
                <button class="btn btn-primary" @click="save" :disabled="saving">
                    <span v-if="saving" class="loading loading-spinner loading-sm"></span>
                    {{ saving ? 'Guardando...' : 'Guardar cambios' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'

const props = defineProps({
    initialConfig: { type: Object, required: true },
    urls: { type: Object, required: true },
})

const dayLabels = {
    monday: 'Lunes',
    tuesday: 'Martes',
    wednesday: 'Miércoles',
    thursday: 'Jueves',
    friday: 'Viernes',
    saturday: 'Sábado',
    sunday: 'Domingo',
}

const activeTab = ref('hours')
const saving = ref(false)
const lastSaved = ref(null)

const config = reactive(JSON.parse(JSON.stringify(props.initialConfig)))

function ensureHourShape(day) {
    if (config.business_hours[day] === undefined) {
        config.business_hours[day] = { closed: false, open: '09:00', close: '18:00' }
    }
    if (config.business_hours[day].closed === undefined) {
        config.business_hours[day].closed = true
    }
    if (config.business_hours[day].open === undefined) {
        config.business_hours[day].open = ''
    }
    if (config.business_hours[day].close === undefined) {
        config.business_hours[day].close = ''
    }
}

for (const d of Object.keys(dayLabels)) {
    ensureHourShape(d)
}

function addHoliday() {
    config.business_holidays.push({ date: '', reason: '', closed: true })
}

function removeHoliday(index) {
    config.business_holidays.splice(index, 1)
}

async function save() {
    saving.value = true
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        const response = await fetch(props.urls.save, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(config),
        })

        if (!response.ok) {
            const data = await response.json().catch(() => ({}))
            const errors = data.errors ? Object.values(data.errors).flat().join('\n') : ''
            throw new Error(errors || `Error ${response.status}`)
        }

        const data = await response.json()
        lastSaved.value = new Date().toLocaleTimeString('es-MX')
        alert(data.message || 'Guardado.')
    } catch (e) {
        alert('No se pudo guardar: ' + e.message)
    } finally {
        saving.value = false
    }
}
</script>
