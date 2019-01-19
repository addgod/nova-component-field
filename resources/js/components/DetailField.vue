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
    };
</script>
