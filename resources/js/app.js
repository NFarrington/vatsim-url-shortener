const Bugsnag = require('@bugsnag/js');
const mountComponents = require('./mount-components');

require('./bootstrap');

window.Vue = require('vue').default;

Bugsnag.getPlugin('vue').installVueErrorHandler(Vue);

const components = {
    'flash': require('./components/Flash.vue').default,
    'delete-resource': require('./components/ResourceDeleteForm.vue').default,
    'reveal-text': require('./components/RevealText.vue').default,
};

Object.entries(components).forEach(([name, component]) => Vue.component(name, component));

mountComponents(Vue, document, Object.keys(components));
