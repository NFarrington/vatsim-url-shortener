const Bugsnag = require('@bugsnag/js');

require('./bootstrap');

window.Vue = require('vue');

Bugsnag.getPlugin('vue').installVueErrorHandler(Vue);

Vue.component('flash', require('./components/Flash.vue').default);
Vue.component('delete-resource', require('./components/ResourceDeleteForm.vue').default);
Vue.component('reveal-text', require('./components/RevealText.vue').default);

const app = new Vue({
    el: '#app',
});
