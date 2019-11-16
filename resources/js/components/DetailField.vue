<template>
    <panel-item :field="field">
        <h4 class="font-normal text-80">{{ field.name }}</h4>
        <template v-slot:value>
            <div class="component border border-b border-60 mb-3">
                <div v-for="panel in sections" :key="panel.attribute">
                    <div class="leading-normal py-1 px-3 py-2 bg-20 border-b border-40">
                        <p class="text-80">{{ panel.name }}</p>
                    </div>
                    <card class="mb-6 py-3 px-6">
                        <component
                            :key="index"
                            v-for="(field, index) in panel.fields"
                            :is="'detail-' + field.component"
                            :resource-name="resourceName"
                            :resource-id="resourceId"
                            :resource="resource"
                            :field="field"
                        />
                    </card>
                </div>
            </div>
        </template>
    </panel-item>
</template>

<script>
    export default {
        props: ['resource', 'resourceName', 'resourceId', 'field'],

        data() {
            return {
                sections: {},
            };
        },

        beforeMount() {
            if (this.field.value) {
                _.each(this.field.value, section => {
                    this.addSection(section);
                });
            }
        },

        methods: {
            addSection(section) {
                section = JSON.parse(JSON.stringify(section));
                const id = '_' + Math.random().toString(36).substr(2, 9);
                section.fields = _.map(section.fields, field => {
                    field.attribute = `${this.field.attribute}->${id}->fields->${field.attribute}`;

                    return field;
                });
                this.$set(section, 'id', id);
                this.$set(section, 'collapsed', false);
                this.$set(this.sections, id, section);
            },
        },
    };
</script>

<style scoped>

    .component .card {
        box-shadow: none!important;
        -moz-box-shadow: none!important;
        -o-box-shadow: none!important;
    }
</style>
