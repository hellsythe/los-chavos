const BASE_URL = '/api/kiosk';

async function request(path) {
    const response = await fetch(`${BASE_URL}${path}`, {
        headers: {
            Accept: 'application/json',
        },
        credentials: 'same-origin',
    });
    if (!response.ok) {
        const error = new Error(`HTTP ${response.status}`);
        error.status = response.status;
        throw error;
    }
    return response.json();
}

export function fetchSchools() {
    return request('/schools').then((payload) => payload.data);
}

export function fetchSchool(id) {
    return request(`/schools/${id}`).then((payload) => payload.data);
}

export function fetchUniform(id) {
    return request(`/uniforms/${id}`).then((payload) => payload.data);
}
