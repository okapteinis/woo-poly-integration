/**
 * Handle product variations sync
 */
class Variables {
    constructor($, document, data) {
        this.$ = $;
        this.document = document;
        this.data = data;
    }

    init() {
        this.createDialog().dialog('open');
    }

    createDialog() {
        const self = this;
        const dialog = this.$('<div/>')
            .html(self.data['content'])
            .attr('title', self.data['title'])
            .appendTo("body");

        dialog.dialog({
            dialogClass: "wp-dialog",
            autoOpen: false,
            modal: true,
            width: 400,
            height: 250,
            position: {
                my: "center",
                at: "center"
            },
            buttons: [{
                text: 'Got It',
                click: function() {
                    this.$(this).dialog('close');
                }
            }]
        });

        return dialog;
    }
}

// Bootstrap when document is ready
jQuery(document).ready(function($) {
    new Variables($, document, HYYAN_WPI_VARIABLES).init();
});
