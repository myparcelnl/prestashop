<template>
  <div class="form-group row">
    <label
      class="col-sm-4 form-control-label"
      :class="{
        required: !optional,
      }">
      <slot name="label">
        {{ translate(label) }}
      </slot>
    </label>
    <div class="col-sm-8">
      <component
        :is="component"
        v-bind="{...$attrs, ...$props}" />
    </div>
  </div>
</template>

<script lang="ts">
import {PdkComponentName, useTranslate} from '@myparcel/pdk-frontend';
import {PropType, defineComponent} from 'vue';

export default defineComponent({
  name: 'PsFormGroup',
  inheritAttrs: false,
  props: {
    component: {
      type: String as PropType<PdkComponentName>,
      default: null,
    },

    errors: {
      type: Array as PropType<string[]>,
      default: () => [],
    },

    label: {
      type: String,
      default: null,
    },

    optional: {
      type: Boolean,
    },

    suspended: {
      type: Boolean,
    },

    valid: {
      type: Boolean,
    },
  },

  setup: (props, ctx) => {
    return {
      translate: useTranslate(),
    };
  },
});
</script>
