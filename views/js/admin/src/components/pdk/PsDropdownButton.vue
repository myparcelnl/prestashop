<template>
  <div class="btn-group">
    <ActionButton
      v-for="action in dropdownActions.standalone"
      :key="`dropdown_${action.id}`"
      :action="action"
      :disabled="disabled"
      :hide-text="hideText"
      class="btn btn-primary btn-sm text-nowrap" />

    <PdkButton
      v-if="dropdownActions.hidden.length > 0"
      :aria-expanded="toggled"
      :aria-label="translate('toggle_dropdown')"
      :disabled="disabled"
      :size="size"
      aria-haspopup="true"
      class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split"
      @click="toggled = !toggled"
      @focusout="unToggle">
      <slot />

      <div
        v-show="toggled"
        class="dropdown-menu">
        <ActionButton
          v-for="(action, index) in dropdownActions.hidden"
          :key="`${index}_${action.id}`"
          v-test="'HiddenDropdownAction'"
          :action="action"
          :disabled="disabled"
          :icon="action.icon"
          class="dropdown-item">
          {{ translate(action.label) }}
        </ActionButton>
      </div>
    </PdkButton>
  </div>
</template>

<script lang="ts" setup>
import {ActionButton, ActionDefinition, useDropdownData, useLanguage} from '@myparcel-pdk/frontend-admin-core/src';
import {Size} from '@myparcel-pdk/common/src';

const unToggle = () => {
  setTimeout(() => {
    toggled.value = false;
  }, 200);
};

const props = defineProps<{
  // eslint-disable-next-line vue/no-unused-properties
  actions: ActionDefinition[];
  disabled: boolean;
  hideText: boolean;
  size?: Size;
}>();

const {dropdownActions, toggled} = useDropdownData(props);

const {translate} = useLanguage();
</script>
