<template>
  <div
    v-show="element.isVisible"
    v-test="[AdminComponent.FormGroup, element]"
    :class="wrapperClass"
    class="form-group row">
    <template v-if="isInteractive">
      <label
        :class="{required: !isOptional}"
        :for="id"
        class="form-control-label">
        <span
          v-if="!isOptional"
          class="text-danger"
          >*
        </span>
        <slot name="label">
          {{ element.label }}
        </slot>
      </label>

      <div class="col-sm input-container">
        <slot />

        <small
          v-if="element.props?.description && has(element.props.description)"
          class="form-text text-muted">
          {{ translate(element.props.description) }}
        </small>

        <div
          v-if="!element.isValid"
          class="invalid-feedback">
          <ul class="list-unstyled">
            <li
              v-for="(error, index) in element.errors"
              :key="`error_${index}`">
              {{ error }}
            </li>
          </ul>
        </div>
      </div>
    </template>

    <div
      v-else
      class="col-12">
      <slot />
    </div>
  </div>
</template>

<script lang="ts" setup>
import {toRefs, computed} from 'vue';
import {
  generateFieldId,
  useLanguage,
  type FormGroupProps,
  AdminComponent,
  useAdminConfig,
  prefixComponent,
} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<FormGroupProps>();

const propRefs = toRefs(props);

const {translate, has} = useLanguage();

const id = generateFieldId(propRefs.element.value);

// This is wrapped because isOptional is not present on all elements
const isOptional = computed<boolean>(() => {
  return Boolean(props.element.isOptional);
});

const isInteractive = computed<boolean>(() => {
  return props.element.hasOwnProperty('ref');
});

const config = useAdminConfig();

const wrapperClass = computed(() => {
  const resolvedComponent = Object.keys(config.components).find((key) => {
    return config.components[key] === props.element.component;
  });

  switch (resolvedComponent) {
    case prefixComponent(AdminComponent.ToggleInput):
      return ['switch-widget'];

    case prefixComponent(AdminComponent.SelectInput):
    case prefixComponent(AdminComponent.MultiSelectInput):
      return ['select-widget'];

    default:
      return [];
  }
});
</script>
