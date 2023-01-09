<template>
  <div class="input-container">
    <div class="input-group">
      <span class="ps-switch">
        <input
          :id="`${id}_no`"
          :checked="model === false"
          class="ps-switch"
          value="0"
          type="radio"
          @change="model = false" />
        <label
          :for="`${id}_no`"
          v-text="translate(labelNo)" />

        <input
          :id="`${id}_yes`"
          :checked="model === true"
          class="ps-switch"
          value="1"
          type="radio"
          @change="model = true" />
        <label
          :for="`${id}_yes`"
          v-text="translate(labelYes)" />

        <span class="slide-button" />
      </span>
    </div>

    <!--    <small class="form-text">Enable suppliers page on your front office even when its module is disabled.</small> -->
  </div>
</template>

<script lang="ts">
import {computed, defineComponent} from 'vue';
import {generateId, useTranslate} from '@myparcel/pdk-frontend';

/**
 * A checkbox. Needs an unique value.
 */
export default defineComponent({
  name: 'PsToggleInput',

  props: {
    /**
     * Controls the disabled state.
     */
    disabled: {
      type: Boolean,
    },

    /**
     * Label in the disabled state.
     */
    labelNo: {
      type: String,
      default: 'no',
    },

    /**
     * Label in the enabled state.
     */
    labelYes: {
      type: String,
      default: 'yes',
    },

    /**
     * The value of the model.
     */
    // eslint-disable-next-line vue/no-unused-properties
    modelValue: {
      type: [String, Number, Boolean],
      default: false,
    },
  },

  emits: ['update:modelValue'],

  setup: (props, ctx) => {
    return {
      id: generateId(),

      model: computed({
        get: () => Boolean(props.modelValue),
        set: (value) => {
          ctx.emit('update:modelValue', value);
        },
      }),

      translate: useTranslate(),
    };
  },
});
</script>
