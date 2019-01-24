<template>
    <div class="m-3">
        <div class="card overflow-hidden border border-60">
            <div class="border-b border-60 bg-40 p-4 mb-2">
                <div class="flex">
                    <h4 class="flex-1">{{ field.indexName }}</h4>
                    <div class="flex-1 text-right">
                        <button v-if="index > -1" @click="$emit('remove')" type="button" class="float-right hover:bg-grey-lightest text-grey-darkest font-semibold">
                            <svg height="18px" id="Layer_1" style="enable-background:new 0 0 512 512;" version="1.1" viewBox="0 0 512 512" width="18px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <path d="M443.6,387.1L312.4,255.4l131.5-130c5.4-5.4,5.4-14.2,0-19.6l-37.4-37.6c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4  L256,197.8L124.9,68.3c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4L68,105.9c-5.4,5.4-5.4,14.2,0,19.6l131.5,130L68.4,387.1  c-2.6,2.6-4.1,6.1-4.1,9.8c0,3.7,1.4,7.2,4.1,9.8l37.4,37.6c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1L256,313.1l130.7,131.1  c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1l37.4-37.6c2.6-2.6,4.1-6.1,4.1-9.8C447.7,393.2,446.2,389.7,443.6,387.1z"/>
                    </svg>
                        </button>
                    </div>
                </div>
            </div>
            <component
                v-for="(field, index) in fields"
                :key="index"
                :is="'form-' + field.component"
                :errors="errors"
                :resource-name="field.resourceName"
                :field="field"
            />
            <div class="flex border-b border-40" v-for="(sections, attribute) in content">
                <div class="flex-shrink py-4 px-6">
                    <label class="inline-block text-80 pt-2 leading-tight">{{ attribute | capitalize }}</label>
                </div>
                <div class="flex-grow py-4 px-6">
                    <component
                        v-for="(block, index) in sections"
                        :key="index"
                        :is="'form-' + block.component"
                        :errors="errors"
                        :resource-name="block.resourceName"
                        :field="block"
                        :index="index"
                        @remove="removeBlock(attribute, index)"
                    />
                </div>
            </div>
            <div class="py-4 px-6">
                <button type="button" v-for="component in components" @click="addBlock(component)" class="bg-white hover:bg-grey-lightest text-grey-darkest font-semibold py-2 px-4 m-1 border border-grey-light rounded shadow">
                    {{ component.indexName }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 20 20" fill="#9babb4" class="pt-1">
                        <title>add</title>
                        <path d="M16 9h-5V4H9v5H4v2h5v5h2v-5h5V9z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import {FormField, HandlesValidationErrors} from 'laravel-nova';
    import Vue from 'vue'

    export default {
        mixins: [HandlesValidationErrors, FormField],

        props: ['resourceName', 'resourceId', 'field', 'index'],

        data() {
            return {
                content: {},
            };
        },

        beforeMount() {

            _.each(this.components, component => {
                Vue.set(this.content, component.attribute, [])
            });

            _.each(this.field.value, (value, attribute) => {
                if (this.content.hasOwnProperty(attribute)) {
                    _.each(value, block => {
                        const component = this.components.find(component => component.intl_slug === block.component);
                        this.addBlock(component, block);
                    })
                }
            });

            _.each(this.fields, field => {
                field.value = this.field.value ? this.field.value[field.attribute] : null;
            });
        },

        methods: {

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                formData.append(this.field.attribute, []);
                _.each(this.fields, field  => {
                    field.attribute = `${this.field.attribute}[${field.attribute}]`;
                    field.fill(formData)
                });
                _.each(this.content, (sections, attribute) => {
                    _.each(sections, (block, index) => {
                        block.attribute = `${this.field.attribute}[${attribute}][${index}]`;
                        block.fill(formData);
                        formData.append(`${block.attribute}[component]`, block.intl_slug);
                    });
                });
            },

            addBlock(block, value = null) {
                const clone = _.cloneDeep(block);
                clone.value = value;
                this.content[clone.attribute].push(clone);
                this.$forceUpdate();
            },

            removeBlock(attribute, index) {
                this.content[attribute].splice(index, 1);
                this.$forceUpdate();
            },
        },

        computed: {
            components() {
                return this.field.fields.filter(field => field.component === this.field.component)
            },

            fields() {
                return this.field.fields.filter(field => field.component !== this.field.component)
            }
        },

        filters: {
            capitalize: function (value) {
                if (!value) return ''
                value = value.toString()
                return value.charAt(0).toUpperCase() + value.slice(1)
            }
        }
    };
</script>
