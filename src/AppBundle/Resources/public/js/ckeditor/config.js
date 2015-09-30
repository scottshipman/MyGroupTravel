/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

if ( typeof CKEDITOR != 'undefined' ) {

    CKEDITOR.editorConfig = function (config) {
        // Define changes to default configuration here. For example:

        config.stylesSet = [
            {"name": "Normal Text", "element": "p", "styles": {"color": "rgba(0, 0, 0, 0.87)"}},
            {"name": "Blue Text", "element": "p", "styles": {"color": "#26a"}},
            {"name": "Orange Text", "element": "p", "styles": {"color": "#ea7200"}},
            {'name': "Green Text", "element": "p", "styles": {"color": "#92b73e"}},
            {'name': "Red Text", "element": "p", "styles": {"color": "#d62d20"}},
            {
                "name": "Normal Heading",
                "element": "p",
                "styles": {"margin": "0 0 15px", "padding": "0", "font-size": "18px"}
            },
            {
                "name": "Blue Heading",
                "element": "p",
                "styles": {"margin": "0 0 15px", "padding": "0", "color": "#26a", "font-size": "18px"}
            },
            {
                "name": "Orange Heading",
                "element": "p",
                "styles": {"margin": "0 0 15px", "padding": "0", "color": "#ea7200", "font-size": "18px"}
            },
            {
                "name": "Red Heading",
                "element": "p",
                "styles": {"margin": "0 0 15px", "padding": "0", "color": "#d62d20", "font-size": "18px"}
            },
            {
                "name": "Green Heading",
                "element": "p",
                "styles": {"margin": "0 0 15px", "padding": "0", "color": "#92b73e", "font-size": "18px"}
            }

        ];

    }

}
