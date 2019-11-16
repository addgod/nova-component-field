<template>
    <default-field :field="field" :errors="errors" :fullWidthContent="true">
        <template slot="field">
            <div class="relative flex bg-white mb-4 pb-1" v-for="section in orderedSections" :key="section.id">
                <div class="z-10 bg-white border-t border-l border-b border-60 h-auto pin-l pin-t rounded-l self-start w-8">
                    <div>
                        <button v-if="!collapsed" type="button" title="Collapse" class="group-control btn border-r border-40 w-8 h-8 block text-70 hover:text-80" @click="section.collapsed = !section.collapsed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" class="fill-current align-top">
                                <path d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2zm0 2v14h14V5H5zm11 7a1 1 0 0 1-1 1H9a1 1 0 0 1 0-2h6a1 1 0 0 1 1 1z" />
                            </svg>
                        </button>
                        <button v-if="collapsed" type="button" title="Expand" class="group-control btn border-r border-40 w-8 h-8 block text-70 hover:text-80" @click="section.collapsed = !section.collapsed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" class="fill-current align-top">
                                <path d="M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5c0-1.1.9-2 2-2zm0 2v14h14V5H5zm8 6h2a1 1 0 0 1 0 2h-2v2a1 1 0 0 1-2 0v-2H9a1 1 0 0 1 0-2h2V9a1 1 0 0 1 2 0v2z" />
                            </svg>
                        </button>
                        <button type="button" title="Move up" class="group-control btn border-t border-r border-40 w-8 h-8 block text-70 hover:text-80" :class="collapsedClass(section)" @click="moveUp(section)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 8 4.8" class="fill-current">
                                <path d="M1.3,4.5C1,4.8,0.5,4.8,0.2,4.5s-0.3-0.8,0-1.1l3.2-3.2c0.3-0.3,0.8-0.3,1.1,0l3.2,3.1C8,3.6,8,4.1,7.7,4.4c-0.3,0.3-0.8,0.3-1.1,0L3.9,1.8L1.3,4.5z" />
                            </svg>
                        </button>
                        <button type="button" title="Move down" class="group-control btn border-t border-r border-40 w-8 h-8 block text-70 hover:text-80" :class="collapsedClass(section)" @click="moveDown(section)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 8 4.8" class="fill-current">
                                <path d="M6.6,0.2c0.3-0.3,0.8-0.3,1.1,0C8,0.5,8,1,7.7,1.3L4.5,4.5c-0.3,0.3-0.8,0.3-1.1,0L0.2,1.4c-0.3-0.3-0.3-0.8,0-1.1C0.5,0,1,0,1.3,0.3L4,2.9L6.6,0.2z" />
                            </svg>
                        </button>
                        <button type="button" title="Delete" class="group-control btn border-t border-r border-40 w-8 h-8 block text-70 hover:text-80" :class="collapsedClass(section)" @click="removeSection(section)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" class="fill-current">
                                <path fill-rule="nonzero" d="M6 4V2a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6H1a1 1 0 1 1 0-2h5zM4 6v12h12V6H4zm8-2V2H8v2h4zM8 8a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0V9a1 1 0 0 1 1-1z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex flex-col min-h-full w-full">
                    <div class="border border-60 rounded-tr-lg rounded-br-lg">
                        <div class="leading-normal py-1 px-8 border-40" :class="collapsedTitleClass(section)">
                            <p class="text-80">{{ section.name }}</p>
                        </div>
                        <div :class="collapsedClass(section)" style="padding-bottom: 2px">
                            <component
                                v-for="(field, index) in section.fields"
                                :key="index"
                                :is="'form-' + field.component"
                                :errors="sanitizedErrors"
                                :resource-name="resourceName"
                                :field="field"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div class="py-4 px-6">
                <button type="button" v-for="section in field.sections" v-if="showSection(section)" @click="addSection(section)" class="bg-white hover:bg-grey-lightest text-grey-darkest font-semibold py-2 px-4 m-1 border border-grey-light rounded shadow">
                    {{ section.name }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="#9babb4" class="pt-1">
                        <title>add</title>
                        <path d="M16 9h-5V4H9v5H4v2h5v5h2v-5h5V9z"/>
                    </svg>
                </button>
            </div>
        </template>
    </default-field>
</template>

<script>
    import {FormField, HandlesValidationErrors} from 'laravel-nova';
    import { Errors } from 'form-backend-validation'

    export default {
        mixins: [HandlesValidationErrors, FormField],

        props: ['resourceName', 'resourceId', 'field', 'index', 'errors'],

        data() {
            return {
                sections: {},
                order: [],
                collapsed: false,
            };
        },

        beforeMount() {

            if (this.field.value && this.field.value.length) {
                _.each(this.field.value, section => {
                    this.addSection(section);
                });
            } else {
                _.each(this.field.sections, section => {
                    if (section.limit === 1) {
                        this.addSection(section);
                    }
                })
            }
        },

        methods: {

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                _.each(this.order, (id, index) => formData.append(`${this.field.attribute}_order[${index}]`, id));
                _.each(this.orderedSections, section => {
                    formData.append(`${this.field.attribute}[${section.id}][attribute]`, section.attribute);
                    _.each(section.fields, field => {
                        field.fill(formData);
                    })
                })
            },

            showSection(section) {
                if (section.limit === 0) {
                    return true;
                }
                let count = 0;
                _.each(this.sections, s => {
                    if (s.attribute === section.attribute) {
                        count++
                    }
                });

                return count < section.limit;
            },

            addSection(section) {
                section = JSON.parse(JSON.stringify(section));
                const id = '_' + Math.random().toString(36).substr(2, 9);
                section.fields = _.map(section.fields, field => {
                    field.attribute = `${this.field.attribute}[${id}][fields][${field.attribute}]`;

                    return field;
                });
                this.order.push(id);
                this.$set(section, 'id', id);
                this.$set(section, 'collapsed', false);
                this.$set(this.sections, id, section);
            },

            removeSection(section) {
                this.order.splice(this.order.indexOf(section.id), 1);
                this.$delete(this.sections, section.id);
            },

            moveUp(section) {
                let index = this.order.indexOf(section.id);
                if (index <= 0) return;
                this.order.splice(index - 1, 0, this.order.splice(index, 1)[0]);
            },

            moveDown(section) {
                let index = this.order.indexOf(section.id);
                if(index < 0 || index >= this.order.length - 1) return;
                this.order.splice(index + 1, 0, this.order.splice(index, 1)[0]);
            },

            collapsedClass(section) {
                return {
                    'hidden': section.collapsed
                }
            },

            collapsedTitleClass(section) {
                return {
                    'border-b' : !section.collapsed
                }
            }
        },

        filters: {
            capitalize: function (value) {
                if (!value) return '';
                value = value.toString();
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        },

        computed: {
            orderedSections() {
                return this.order.reduce((sections, id) => {
                    sections.push(this.sections[id]);
                    return sections;
                }, []);
            },

            sanitizedErrors() {
                const errors = {};
                _.each(this.errors.errors, (messages, key) => {
                    const blocks = key.split('.');
                    let attribute = blocks.shift();
                    let name = blocks.slice(-1).pop();
                    _.each(blocks, block => {
                        attribute += `[${block}]`;
                    });
                    _.each(messages, (message, index) => {
                        messages[index] = message.replace(key, name)
                    });
                    errors[attribute] = messages;
                });
                return new Errors(errors);
            }
        }
    };
</script>
