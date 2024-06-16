const { registerBlockType } = wp.blocks;
const { ServerSideRender } = wp.components;

registerBlockType('coudar/course-calendar', {
    title: 'Course Calendar',
    icon: 'calendar',
    category: 'widgets',
    edit: function() {
        return wp.element.createElement(ServerSideRender, {
            block: 'coudar/course-calendar'
        });
    },
    save: function() {
        return null; // Let the server-side rendering handle this.
    }
});
