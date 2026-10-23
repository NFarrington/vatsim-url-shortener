import './bootstrap';

import $ from 'jquery';

import Bugsnag from '@bugsnag/js';
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
// Policy, so page behaviour is attached here.
$(function () {
    $('[data-toggle="tooltip"]').tooltip();

    $('[data-submit-form]').on('click', function (event) {
        event.preventDefault();
        document.getElementById(this.dataset.submitForm).submit();
    });
});
