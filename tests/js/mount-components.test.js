import { test, beforeEach } from 'node:test';
import assert from 'node:assert';
import { JSDOM } from 'jsdom';

// Vue checks for a browser when it's first loaded, so set up the DOM first.
const dom = new JSDOM('<!doctype html><html><body></body></html>');
globalThis.window = dom.window;
globalThis.document = dom.window.document;
// Vue checks the type of the element it mounts on.
for (const name of ['Node', 'Element', 'HTMLElement', 'SVGElement']) {
    globalThis[name] = dom.window[name];
}

const { h } = await import('vue');
const { default: mountComponents } = await import('../../resources/js/mount-components.js');

// A precompiled-style component, like the .vue files once built.
const greeting = {
    props: ['name', 'shout-name'],
    render() {
        const name = this.shoutName === '' ? this.name.toUpperCase() : this.name;
        return h('span', { class: 'greeting' }, ['Hello, ', name, this.$slots.default ? this.$slots.default() : '!']);
    },
};
const components = { 'test-greeting': greeting };

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

    mountComponents(document, components);

    assert.strictEqual(window.injected, undefined);
    assert.strictEqual(document.querySelector('.organization-name').textContent, payload);
});

test('does not evaluate template syntax in component attributes', () => {
    document.body.innerHTML = `<test-greeting name="${payload}"></test-greeting>`;

    mountComponents(document, components);

    assert.strictEqual(window.injected, undefined);
    assert.strictEqual(document.querySelector('.greeting').textContent, `Hello, ${payload}!`);
});

test('does not evaluate template syntax in slot text', () => {
    document.body.innerHTML = `<test-greeting name="Ada">${payload}</test-greeting>`;

    mountComponents(document, components);

    assert.strictEqual(window.injected, undefined);
    assert.strictEqual(document.querySelector('.greeting').textContent, `Hello, Ada${payload}`);
});

test('renders slot markup as text', () => {
    document.body.innerHTML = '<test-greeting name="Ada"><b>bold</b></test-greeting>';

    mountComponents(document, components);

    assert.strictEqual(document.querySelector('.greeting b'), null);
    assert.strictEqual(document.querySelector('.greeting').textContent, 'Hello, Adabold');
});

test('passes kebab-case attributes as props', () => {
    document.body.innerHTML = '<test-greeting name="Ada" shout-name></test-greeting>';

    mountComponents(document, components);

    assert.strictEqual(document.querySelector('.greeting').textContent, 'Hello, ADA!');
});

test('removes the attributes from the tag, so props like hidden do not hide it', () => {
    document.body.innerHTML = '<test-greeting name="Ada" hidden="secret"></test-greeting>';

    mountComponents(document, components);

    const tag = document.querySelector('test-greeting');
    assert.strictEqual(tag.hasAttribute('hidden'), false);
    assert.strictEqual(tag.hasAttribute('name'), false);
    assert.strictEqual(tag.textContent, 'Hello, Ada!');
});

test('uses the default slot content when the tag has only whitespace', () => {
    document.body.innerHTML = '<test-greeting name="Ada">\n    </test-greeting>';

    mountComponents(document, components);

    assert.strictEqual(document.querySelector('.greeting').textContent, 'Hello, Ada!');
});

test('mounts each top-level component and calls setup for each app', () => {
    document.body.innerHTML = `
        <p>Text <test-greeting name="Ada"></test-greeting></p>
        <test-greeting name="Grace"><test-greeting name="nested">inner</test-greeting></test-greeting>`;

    const setUp = [];
    const apps = mountComponents(document, components, (app) => setUp.push(app));

    assert.strictEqual(apps.length, 2);
    assert.deepStrictEqual(setUp, apps);
    assert.deepStrictEqual(
        Array.from(document.querySelectorAll('.greeting')).map((el) => el.textContent),
        ['Hello, Ada!', 'Hello, Graceinner'],
    );
});
