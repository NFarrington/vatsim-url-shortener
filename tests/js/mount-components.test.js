const { test, beforeEach } = require('node:test');
const assert = require('node:assert');
const { JSDOM } = require('jsdom');

// Vue checks for a browser when it's first loaded, so set up the DOM first.
const dom = new JSDOM('<!doctype html><html><body></body></html>');
global.window = dom.window;
global.document = dom.window.document;
global.navigator = dom.window.navigator;

// The full build, with the template compiler, as Laravel Mix bundles it.
const Vue = require('vue/dist/vue.common.js');
const mountComponents = require('../../resources/js/mount-components');

Vue.config.productionTip = false;
Vue.config.devtools = false;
Vue.component('test-greeting', {
    props: ['name'],
    template: '<span class="greeting">Hello, {{ name }}<slot></slot></span>',
});

const payload = "{{ constructor.constructor('window.injected = true')() }}";

beforeEach(() => {
    delete window.injected;
});

test('does not evaluate template syntax in user-supplied page content', () => {
    document.body.innerHTML = `
        <div id="app">
            <span class="organization-name">${payload}</span>
            <test-greeting name="Ada"></test-greeting>
        </div>`;

    mountComponents(Vue, document, ['test-greeting']);

    assert.strictEqual(window.injected, undefined);
    assert.strictEqual(document.querySelector('.organization-name').textContent, payload);
});

test('does not evaluate template syntax in component attributes', () => {
    document.body.innerHTML = `<test-greeting name="${payload}"></test-greeting>`;

    mountComponents(Vue, document, ['test-greeting']);

    assert.strictEqual(window.injected, undefined);
    assert.strictEqual(document.querySelector('.greeting').textContent, `Hello, ${payload}`);
});

test('mounts each component', () => {
    document.body.innerHTML = `
        <p>Text <test-greeting name="Ada"></test-greeting></p>
        <test-greeting name="Grace"><test-greeting name="nested"></test-greeting></test-greeting>`;

    const instances = mountComponents(Vue, document, ['test-greeting']);

    assert.strictEqual(instances.length, 2);
    assert.deepStrictEqual(
        Array.from(document.querySelectorAll('.greeting')).map((el) => el.textContent),
        ['Hello, Ada', 'Hello, GraceHello, nested', 'Hello, nested'],
    );
});
