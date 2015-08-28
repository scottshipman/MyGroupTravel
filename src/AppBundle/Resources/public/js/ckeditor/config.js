/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function (config) {
    // Define changes to default configuration here. For example:

    config.stylesSet = [
        {"name": "Blue Text", "element": "p", "styles": {"color": "#26a"}},
        {"name": "Orange Text", "element": "p", "styles": {"color": "#ea7200"}},
        {"name": "Normal Text", "element": "p", "styles": {"class": " "}},
        {'name': "Green Text", "element": "p", "styles": {"color": "#92b73e"}},
        {
            "name": "Blue Heading",
            "element": "p",
            "styles": {"margin": "0 0 15px", "padding": "0", "color": "#26a", "font-size": "18px"}
        },
        {
            "name": "Orange Heading",
            "element": "p",
            "styles": {"color": "#ea7200", "margin": "0 0 15px", "padding": "0", "font-size": "18px"}
        },
        {
            "name": "Normal Heading",
            "element": "p",
            "styles": {"margin": "0 0 15px", "padding": "0", "font-size": "18px"}
        },
        {
            "name": "Red Heading",
            "element": "p",
            "styles": {"margin": "0 0 10px", "font-size": "18px", "color": "#d62d20"}
        },
        {
            "name": "Green Heading",
            "element": "p",
            "styles": {"margin": "0 0 10px", "font-size": "18px", "color": "#92b73e"}
        }

    ];

};