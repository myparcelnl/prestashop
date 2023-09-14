<template>
  <ul
    v-test="[AdminComponent.DropOffInput, element]"
    class="list-group">
    <li
      v-for="[day, human] in Object.entries(weekdaysObject)"
      :key="day"
      class="list-group-item">
      <PdkRow :columns="2">
        <PdkCol>
          <label>
            {{ human }}

            <PdkToggleInput
              v-model="toggleRefs[day]"
              :element="toggleElements[day]" />
          </label>
        </PdkCol>

        <PdkCol>
          <template v-if="toggleRefs[day]">
            <label>
              {{ translate('settings_carrier_cutoff_time') }}

              <PdkTimeInput
                v-model="cutoffRefs[day]"
                :element="cutoffElements[day]" />
            </label>
          </template>
        </PdkCol>
      </PdkRow>
    </li>
  </ul>
</template>

<script lang="ts" setup>
import {toRefs} from 'vue';
import {
  AdminComponent,
  useDropOffInputContext,
  useLanguage,
  type DropOffInputProps,
  type DropOffInputEmits,
} from '@myparcel-pdk/admin';

const props = defineProps<DropOffInputProps>();
const emit = defineEmits<DropOffInputEmits>();

const propRefs = toRefs(props);

const {weekdaysObject, cutoffElements, toggleElements, toggleRefs, cutoffRefs, id} = useDropOffInputContext(
  propRefs.modelValue?.value,
  emit,
);

const {translate} = useLanguage();
</script>
