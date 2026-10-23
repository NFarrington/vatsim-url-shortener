import { createApp, h } from 'vue';

/**
 * Mount each component tag in the page as its own Vue app.
 *
 * Components are rendered from their precompiled render functions, with the
 * tag's attributes as props and its text as the default slot. Nothing in the
 * page is compiled as a Vue template, so `{{ }}` in user-supplied text (an
 * organization name, say) is shown as text and never run.
 *
 * @param {ParentNode} root
 * @param {Object<string, Object>} components Components, keyed by tag name.
 * @param {function(App): void} [setup] Called with each app before it's mounted.
 * @returns {App[]}
 */
export default function mountComponents(root, components, setup = () => {}) {
    const selector = Object.keys(components).join(',');

    return Array.from(root.querySelectorAll(selector))
        // A component tag inside another is part of the outer one's slot text.
        .filter((el) => !el.parentElement || !el.parentElement.closest(selector))
        .map((el) => {
            const component = components[el.localName];
            const props = Object.fromEntries(Array.from(el.attributes, (attribute) => [attribute.name, attribute.value]));
            const text = el.textContent.trim();

            // The app renders inside the tag, so remove the attributes now they're
            // props. Some are also HTML attributes, such as `hidden`.
            Object.keys(props).forEach((name) => el.removeAttribute(name));
            const slots = text ? { default: () => text } : undefined;

            const app = createApp({ render: () => h(component, props, slots) });
            setup(app);
            app.mount(el);

            return app;
        });
}
