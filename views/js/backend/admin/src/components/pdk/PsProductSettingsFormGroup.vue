<template>
  <PdkRow
    v-show="element.isVisible"
    v-test="[AdminComponent.FormGroup, element]">
    <template v-if="isInteractive">
      <div class="col-md-6">
        <div class="form-group">
          <label
            :for="id"
            class="form-control-label">
            <slot name="label">
              {{ element.label }}
            </slot>
          </label>

          <div>
            <slot />
          </div>

          <p
            v-if="element.props?.description && has(element.props.description)"
            class="italic subtitle">
            {{ translate(element.props.description) }}
          </p>

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
      </div>
    </template>

    <div
      v-else
      class="col-12 mb-4">
      <slot />
    </div>
  </PdkRow>
</template>

<script lang="ts" setup>
import {toRefs, computed} from 'vue';
import {generateFieldId, useLanguage, type FormGroupProps, AdminComponent} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<FormGroupProps>();

const propRefs = toRefs(props);

const {translate, has} = useLanguage();

const id = generateFieldId(propRefs.element.value);

const isInteractive = computed<boolean>(() => {
  return props.element.hasOwnProperty('ref');
});
</script>
