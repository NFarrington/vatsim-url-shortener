/**
 * Mount a Vue instance on each component tag in the page, rather than one on
 * a wrapper around the whole page.
 *
 * Vue compiles the HTML it mounts on as a template, so mounting on the page
 * would run any {{ }} in user-supplied text (an organization name, say) as
 * JavaScript. Blade's escaping doesn't prevent this. Only the component tags
 * are compiled here, so their attributes and slots must not contain
 * user-supplied text.
 */
module.exports = function mountComponents(Vue, root, tagNames) {
    const selector = tagNames.join(',');

    return Array.from(root.querySelectorAll(selector))
        // A component nested in another's slot is mounted along with its parent.
        .filter((el) => !el.parentElement || !el.parentElement.closest(selector))
        .map((el) => new Vue({ el }));
};
