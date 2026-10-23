const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.resolve(__dirname, '../../src/Resources/views/components/tree/view.blade.php'), 'utf8');
const moduleScript = source.match(/<script type="module">([\s\S]*?)<\/script>/)[1];
let component;
vm.runInNewContext(moduleScript, {
    app: {component: (name, definition) => { component = definition; }},
    document: {documentElement: {lang: 'fr'}},
});
const label = (item, options = {}) => component.methods.getLabel.call({
    labelField: 'name', idField: 'id', valueField: 'value', fallbackLocale: 'en', ...options,
}, item);

test('prefers the resolved label and then the current language', () => {
    assert.equal(label({name: 'Resolved name', translations: [{locale: 'fr', name: 'French'}]}), 'Resolved name');
    assert.equal(label({translations: [{locale: 'en', name: 'English'}, {locale: 'fr', name: 'French'}]}), 'French');
});

test('falls back without crashing when a category translation is missing', () => {
    assert.equal(label({translations: [{locale: 'en', name: 'English'}]}), 'English');
    assert.equal(label({translations: [{locale: 'ar', name: 'Arabic'}]}), 'Arabic');
    assert.equal(label({translations: [{locale: 'en', name: ''}, {locale: 'ar', name: 'Arabic'}]}), 'Arabic');
    assert.equal(label({id: 7, translations: []}), '7');
    assert.equal(label({id: 8}), '8');
});

test('supports custom field names used by other tree selectors', () => {
    assert.equal(label({translations: [{locale: 'ar', title: 'Translated title'}]}, {labelField: 'title'}), 'Translated title');
    assert.equal(label({code: 'catalog'}, {valueField: 'code'}), 'catalog');
    assert.equal(component.props.fallbackLocale.default, 'en');
});
