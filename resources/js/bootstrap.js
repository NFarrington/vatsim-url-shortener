import Bugsnag from '@bugsnag/js';
import BugsnagPluginVue from '@bugsnag/plugin-vue';

Bugsnag.start({
    apiKey: 'e23990720def37b4768db24be446ac74',
    appVersion: window.appEnv.version,
    releaseStage: window.appEnv.environment,
    plugins: [new BugsnagPluginVue()],
});

/**
 * Bootstrap's JavaScript (navbar collapse, dropdowns and so on), which needs
 * jQuery and Popper.
 */
import 'bootstrap';
