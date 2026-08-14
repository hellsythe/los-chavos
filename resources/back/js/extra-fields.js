import TextAreaFieldComponent from './components/Crud/Form/Fields/TextAreaFieldComponent.vue';

const registerExtra = () => {
    const mounts = document.querySelectorAll('[id="app"], [id="chatbot-config"], [id="messenger"]');
    if (mounts.length === 0) return;

    mounts.forEach((mount) => {
        const app = mount.__vue_app__;
        if (app) {
            app.component('TextAreaField', TextAreaFieldComponent);
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', registerExtra);
} else {
    registerExtra();
}
