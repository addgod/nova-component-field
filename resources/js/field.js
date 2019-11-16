Nova.booting((Vue, router, store) => {
    Vue.component('detail-component-field', require('./components/DetailField'));
    Vue.component('form-component-field', require('./components/FormField'));
});
