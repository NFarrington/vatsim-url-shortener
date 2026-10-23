import Bugsnag from '@bugsnag/js';
import BugsnagPluginVue from '@bugsnag/plugin-vue';

Bugsnag.start({
    apiKey: 'e23990720def37b4768db24be446ac74',
    appVersion: window.appEnv.version,
    releaseStage: window.appEnv.environment,
    plugins: [new BugsnagPluginVue()],
});

/**
 * Bootstrap's JavaScript. Importing it registers the data-bs-* behaviour (navbar
 * collapse, dropdowns, alerts and so on). Dropdowns and tooltips use Popper.
 */
import 'bootstrap';
