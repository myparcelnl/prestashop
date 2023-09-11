<template>
  <div class="btn-group">
    <ActionButton
      v-for="action in dropdownActions.standalone"
      :key="`dropdown_${action.id}`"
      :action="action"
      :disabled="disabled"
      :hide-text="hideText"
      class="btn-sm text-nowrap" />

    <PdkButton
      v-if="dropdownActions.hidden.length > 0"
      ref="dropdown"
      :aria-label="translate('toggle_dropdown')"
      :disabled="disabled"
      :size="size"
      aria-haspopup="true"
      class="btn-sm dropdown-toggle dropdown-toggle-split"
      data-toggle="dropdown">
      <slot />

      <div class="dropdown-menu">
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
import {type ComponentPublicInstance, onMounted, ref} from 'vue';
import {type Size, ActionButton, type ActionDefinition, useDropdownData, useLanguage} from '@myparcel-pdk/admin';

const props = defineProps<{
  // eslint-disable-next-line vue/no-unused-properties
  actions: ActionDefinition[];
  disabled: boolean;
  hideText: boolean;
  size?: Size;
}>();

const {dropdownActions} = useDropdownData(props);

const {translate} = useLanguage();

const dropdown = ref<ComponentPublicInstance | null>(null);

onMounted(() => {
  if (!dropdown.value) {
    return;
  }

  jQuery(dropdown.value.$el).dropdown();
});
</script>
