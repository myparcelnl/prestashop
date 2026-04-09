import {describe, it, expect} from 'vitest';
import {mount} from '@vue/test-utils';
import {defineComponent} from 'vue';
import PsConceptBoxWrapper from '../components/pdk/PsConceptBoxWrapper.vue';

const PdkBoxStub = defineComponent({
  name: 'PdkBox',
  props: ['loading'],
  template: `
    <div class="pdk-box" :data-loading="loading">
      <div v-if="$slots.header" class="pdk-box-header"><slot name="header" /></div>
      <slot />
      <div v-if="$slots.footer" class="pdk-box-footer"><slot name="footer" /></div>
    </div>
  `,
});

const PdkButtonGroupStub = defineComponent({
  name: 'PdkButtonGroup',
  template: '<div class="pdk-button-group"><slot /></div>',
});

const ActionButtonStub = defineComponent({
  name: 'ActionButton',
  props: ['action'],
  template: '<button class="action-button" :data-action-id="action?.id" />',
});

const globalStubs = {
  global: {
    stubs: {
      PdkBox: PdkBoxStub,
      PdkButtonGroup: PdkButtonGroupStub,
      ActionButton: ActionButtonStub,
    },
  },
};

function createAction(id: string) {
  return {id, handler: () => {}} as any;
}

describe('PsConceptBoxWrapper', () => {
  it('renders default slot content', () => {
    const wrapper = mount(PsConceptBoxWrapper, {
      ...globalStubs,
      slots: {default: 'Main content'},
    });

    expect(wrapper.text()).toContain('Main content');
  });

  it('passes loading prop to PdkBox', () => {
    const wrapper = mount(PsConceptBoxWrapper, {
      ...globalStubs,
      props: {loading: true},
    });

    expect(wrapper.find('.pdk-box').attributes('data-loading')).toBe('true');
  });

  it('renders header slot when provided', () => {
    const wrapper = mount(PsConceptBoxWrapper, {
      ...globalStubs,
      slots: {header: '<span>Header text</span>'},
    });

    expect(wrapper.find('.pdk-box-header').exists()).toBe(true);
    expect(wrapper.find('.pdk-box-header').text()).toBe('Header text');
  });

  it('does not render header when slot is not provided', () => {
    const wrapper = mount(PsConceptBoxWrapper, {
      ...globalStubs,
      slots: {default: 'Content only'},
    });

    expect(wrapper.find('.pdk-box-header').exists()).toBe(false);
  });

  it('renders action buttons when actions are provided', () => {
    const actions = [createAction('action-1'), createAction('action-2')];

    const wrapper = mount(PsConceptBoxWrapper, {
      ...globalStubs,
      props: {actions},
    });

    expect(wrapper.find('.pdk-box-footer').exists()).toBe(true);
    expect(wrapper.find('.pdk-button-group').exists()).toBe(true);

    const buttons = wrapper.findAll('.action-button');
    expect(buttons).toHaveLength(2);
    expect(buttons[0].attributes('data-action-id')).toBe('action-1');
    expect(buttons[1].attributes('data-action-id')).toBe('action-2');
  });

  it('does not render footer when actions is empty', () => {
    const wrapper = mount(PsConceptBoxWrapper, {
      ...globalStubs,
      props: {actions: []},
    });

    expect(wrapper.find('.pdk-box-footer').exists()).toBe(false);
    expect(wrapper.find('.pdk-button-group').exists()).toBe(false);
  });
});
