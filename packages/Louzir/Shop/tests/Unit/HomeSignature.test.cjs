const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.resolve(__dirname, '../../src/Resources/views/home/signature.blade.php'), 'utf8');
const moduleScript = source.match(/<script type="module">([\s\S]*?)<\/script>/)[1];
let component;
vm.runInNewContext(moduleScript, {
    app: {component: (name, definition) => { component = definition; }},
    CustomEvent: class { constructor(type) { this.type = type; } },
});

function instance(get) {
    const events = [];
    const state = {
        products: [], isLoading: true, hasError: false, src: '/api/products',
        $axios: {get},
        $nextTick: callback => queueMicrotask(callback),
    };
    state.$el = {dispatchEvent: event => events.push({type: event.type, loading: state.isLoading, error: state.hasError})};
    return {state, events};
}

test('announces loaded products only after the loading state is cleared', async () => {
    const {state, events} = instance(() => Promise.resolve({data: {data: [{id: 1}]}}));
    component.methods.getProducts.call(state);
    await new Promise(setImmediate);
    assert.equal(state.products[0].id, 1);
    assert.deepEqual(events, [{type: 'louzir-products-ready', loading: false, error: false}]);
});

test('ends loading on failure and allows a successful retry', async () => {
    const {state, events} = instance(() => Promise.reject(new Error('Offline')));
    component.methods.getProducts.call(state);
    await new Promise(setImmediate);
    assert.equal(state.hasError, true);
    assert.equal(state.isLoading, false);
    state.$axios.get = () => Promise.resolve({data: {data: []}});
    component.methods.getProducts.call(state);
    await new Promise(setImmediate);
    assert.equal(state.hasError, false);
    assert.equal(state.isLoading, false);
    assert.equal(events.length, 2);
    assert(events.every(event => event.loading === false));
});
