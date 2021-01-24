require('./bootstrap');

window.Vue = require('vue');

const bugsnagVue = require('@bugsnag/plugin-vue');
window.bugsnagClient.use(bugsnagVue, window.Vue);

Vue.component('flash', require('./components/Flash.vue').default);
Vue.component('delete-resource', require('./components/ResourceDeleteForm.vue').default);
Vue.component('reveal-text', require('./components/RevealText.vue').default);

const app = new Vue({
    el: '#app',
});
