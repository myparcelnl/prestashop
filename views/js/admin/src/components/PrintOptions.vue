<template>
  <div>
    <FormGroup label="format">
      <PsRadio
        v-for="item in formatOptions"
        :key="`${item.label}_${item.value}`"
        v-model="format"
        :checked="format === item.value"
        v-bind="item" />
    </FormGroup>
    <FormGroup
      v-show="format === 'a4'"
      label="positions">
      <PsCheckbox
        v-for="item in positionOptions"
        :key="`${item.label}_${item.value}`"
        v-model="position[item.value]"
        v-bind="item" />
    </FormGroup>
    <FormGroup label="output">
      <PsRadio
        v-for="item in outputOptions"
        :key="`${item.label}_${item.value}`"
        v-model="output"
        :checked="output === item.value"
        v-bind="item" />
    </FormGroup>
  </div>
</template>

<script lang="ts">
import { UnwrapRef, defineComponent, reactive, ref, watchEffect } from '@vue/composition-api';
import { formatOptions, outputOptions, positionOptions, positions } from '@/data/printOptions';
import { ContextKey } from '@/data/global/context';
import FormGroup from './common/form/FormGroup.vue';
import PsCheckbox from './common/form/PsCheckbox.vue';
import PsRadio from '@/components/common/form/PsRadio.vue';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

export default defineComponent({
  name: 'PrintOptions',
  components: { PsRadio, PsCheckbox, FormGroup },

  setup: () => {
    const contextData = useGlobalContext(ContextKey.PRINT_OPTIONS);
    const { labelFormat, labelPosition, labelOutput } = contextData.value;

    const format = ref<LabelFormat>(labelFormat);
    const position = reactive<UnwrapRef<Partial<Record<LabelPosition, boolean>>>>(positions.reduce((acc, val) => ({
      ...acc,
      [val]: labelPosition.includes(val),
    }), {}));
    const output = ref<LabelOutput>(labelOutput);

    watchEffect(() => {
      contextData.value.labelFormat = format.value;
      contextData.value.labelPosition = positions.filter((key) => position[key]);
      contextData.value.labelOutput = output.value;
    });

    return {
      format,
      position,
      output,
      positionOptions,
      formatOptions,
      outputOptions,
    };
  },
});
</script>
