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
import {
  AdminComponent,
  useDropOffInputContext,
  useLanguage,
  type DropOffInputProps,
  type DropOffInputEmits,
} from '@myparcel-dev/pdk-admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<DropOffInputProps>();
const emit = defineEmits<DropOffInputEmits>();

const {weekdaysObject, cutoffElements, toggleElements, toggleRefs, cutoffRefs} = useDropOffInputContext(props, emit);

const {translate} = useLanguage();
</script>
