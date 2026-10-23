import './bootstrap';

import Bugsnag from '@bugsnag/js';
import { Tooltip } from 'bootstrap';
import Flash from './components/Flash.vue';
import ResourceDeleteForm from './components/ResourceDeleteForm.vue';
import RevealText from './components/RevealText.vue';
import mountComponents from './mount-components';

const components = {
    'flash': Flash,
    'delete-resource': ResourceDeleteForm,
    'reveal-text': RevealText,
};

const bugsnagVue = Bugsnag.getPlugin('vue');

mountComponents(document, components, (app) => app.use(bugsnagVue));

// Inline event handlers and scripts aren't allowed by the Content Security
// Policy, so page behaviour is attached here. Module scripts run once the
// page has been parsed, so the elements are already there.
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new Tooltip(el));

document.querySelectorAll('[data-submit-form]').forEach((el) => {
    el.addEventListener('click', (event) => {
        event.preventDefault();
        document.getElementById(el.dataset.submitForm).submit();
    });
});
