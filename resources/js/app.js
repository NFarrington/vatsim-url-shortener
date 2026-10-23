import './bootstrap';

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
