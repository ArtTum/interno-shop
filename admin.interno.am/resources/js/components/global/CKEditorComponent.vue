<template>
    <ckeditor
        v-model="editorData"
        :editor="editor"
        :config="editorConfig"
        :disabled="disabled"
    />
</template>

<script>
import {
    ClassicEditor,
    Bold,
    Essentials,
    Italic,
    Paragraph,
    Undo,
    Heading,
    Table,
    TableCaption,
    TableCellProperties,
    TableColumnResize,
    TableProperties,
    TableToolbar,
    TableSelection,
    Link,
    SourceEditing,
    List,
    Font,
    Image,
    FontColor,
    ImageToolbar,
    ImageCaption,
    ImageResize,
    MediaEmbed,
    GeneralHtmlSupport,
    Alignment,
    Subscript,
    Superscript, HtmlEmbed, HtmlComment
} from 'ckeditor5';

import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import 'ckeditor5/ckeditor5.css';
import '@assets/css/code.css';

export default {
    name: 'app',
    props: {
        model: {
            type: String
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    components: {
        Ckeditor
    },
    data() {
        return {
            editor: ClassicEditor,
            editorData: this.model,
            editorConfig: {
                plugins: [
                    HtmlEmbed,
                    HtmlComment,
                    Essentials,
                    Bold,
                    Italic,
                    Heading,
                    Paragraph,
                    Undo,
                    Table,
                    TableCaption,
                    TableCellProperties,
                    TableColumnResize,
                    TableProperties,
                    TableToolbar,
                    TableSelection,
                    SourceEditing,
                    List,
                    Link,
                    Font,
                    FontColor,
                    Image,
                    ImageToolbar,
                    ImageCaption,
                    ImageResize,
                    MediaEmbed,
                    GeneralHtmlSupport,
                    Alignment,
                    Subscript,
                    Superscript
                ],
                toolbar: [
                    'undo', 'redo', '|', 'bold', 'italic', 'heading', '|',
                    'link', '|', 'insertTable', 'tableColumn', 'tableRow', 'mergeTableCells', 'numberedList', 'bulletedList',
                    'sourceEditing', 'fontColor', 'mediaEmbed', '|',
                    'alignment', 'Subscript', 'Superscript', 'htmlEmbed'
                ],
                htmlEmbed: {
                    showPreviews: true
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                        { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                        { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                        { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                    ]
                },
                table: {
                    contentToolbar: [
                        'tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties',
                        'tableCaption', 'toggleTableCaption', 'tableColumnResize'
                    ],

                    tableProperties: {
                        backgroundColors: [
                            {
                                color: 'hsl(0, 0%, 97%)',
                                label: 'Light gray'
                            },
                            {
                                color: 'hsl(203, 100%, 44%)',
                                label: 'Blue'
                            }
                        ],
                        borderColors: [
                            {
                                color: 'hsl(0, 0%, 97%)',
                                label: 'Light gray'
                            },
                            {
                                color: 'hsl(203, 100%, 44%)',
                                label: 'Blue'
                            }
                        ],
                    },

                    tableCellProperties: {
                        backgroundColors: [
                            {
                                color: 'hsl(0, 0%, 97%)',
                                label: 'Light gray'
                            },
                            {
                                color: 'hsl(203, 100%, 44%)',
                                label: 'Blue'
                            }
                        ],
                        borderColors: [
                            {
                                color: 'hsl(0, 0%, 97%)',
                                label: 'Light gray'
                            },
                            {
                                color: 'hsl(203, 100%, 44%)',
                                label: 'Blue'
                            }
                        ],
                    }
                },
                mediaEmbed: {
                    previewsInData: true
                },
                image: {
                    toolbar: ['imageTextAlternative', 'resizeImage'],
                    styles: ['full', 'side'],
                    upload: {
                        types: ['png', 'jpeg', 'gif', 'bmp', 'webp']
                    },
                    resizeOptions: [
                        {
                            name: 'resizeImage:original',
                            value: null,
                            label: 'Original',
                            icon: 'original'
                        },
                        {
                            name: 'resizeImage:50',
                            value: '50',
                            label: '50%',
                            icon: 'medium'
                        },
                        {
                            name: 'resizeImage:75',
                            value: '75',
                            label: '75%',
                            icon: 'large'
                        }
                    ],
                },
                // **New Allowed Content Rules**
                htmlSupport: {
                    allow: [
                        {
                            name: 'iframe',
                            attributes: true,
                            classes: true,
                            styles: true
                        },
                        {
                            name: 'video',
                            attributes: true,
                            classes: true,
                            styles: true
                        },
                        {
                            name: 'source',
                            attributes: true,
                            classes: true,
                            styles: true
                        },
                        {
                            name: 'img',
                            attributes: true,
                            classes: true,
                            styles: false
                        },
                        {
                            name: 'div',
                            attributes: true,
                            classes: true,
                            styles: false
                        },
                        {
                            name: 'script',
                            attributes: {
                                src: true
                            }
                        },
                        {
                            name: 'figure',
                            attributes: true,
                            classes: true,
                            styles: {
                                'max-width': true,
                                width: true
                            }
                        },
                    ]
                },
                alignment: {
                    options: ['left', 'center', 'right', 'justify'] // Specify alignment options
                }
            }
        };
    },
    watch: {
        editorData(newVal) {
            this.$emit('updateValue', newVal);
        },
        model(newVal) {
            this.editorData = newVal;
        }
    },
};
</script>

<style>
.ck-editor {
    color: black;
}

.ck-heading_heading1 {
    font-size: 2.3em; /* Example for heading 1 */
}

.ck-heading_heading2 {
    font-size: 1.84em; /* Example for heading 2 */
}

.ck-heading_heading3 {
    font-size: 1.48em; /* Example for heading 3 */
}

.ck-heading_heading4 {
    font-size: 1.22em; /* Example for heading 1 */
}

.ck-heading_heading5 {
    font-size: 1.06em; /* Example for heading 2 */
}

.ck-heading_heading6 {
    font-size: 1em; /* Example for heading 3 */
}

.ck-balloon-panel_visible {
    z-index: 99999999999 !important;
}

.ck-editor__editable_inline {
    min-height: 200px;
}
</style>
