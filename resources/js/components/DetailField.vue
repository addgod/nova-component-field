<template>
    <component
        is="detail-code-field"
        :resource-name="field.resourceName"
        :field="field"
    />
</template>

<script>
    export default {
        props: ['resource', 'resourceName', 'resourceId', 'field'],

        beforeMount() {
            this.field.value = JSON.stringify(this.field.value, null, 2)
            this.field.options = {'mode': 'application/json'}
        }
    };
</script>
