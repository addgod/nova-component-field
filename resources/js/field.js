Nova.booting((Vue, router, store) => {
    Vue.component('index-component-field', require('./components/IndexField'))
    Vue.component('detail-component-field', require('./components/DetailField'))
    Vue.component('form-component-field', require('./components/FormField'))
})
