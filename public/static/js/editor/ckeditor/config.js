/**
 * @license Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
    config.toolbarGroups = [
            { name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
            { name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
            { name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
            { name: 'links' },
            { name: 'insert' },
            { name: 'tools' },
            '/',
            { name: 'styles' },
            { name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align' ] },
            { name: 'colors',      groups: [ 'TextColor', 'BGColor', 'templates' ] },
            { name: 'basicstyles', groups: [ 'basicstyles',  'cleanup' ] }
        ]; 
};
