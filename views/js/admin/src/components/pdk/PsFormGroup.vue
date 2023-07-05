<template>
  <div class="form-group row">
    <label
      :for="id"
      class="col-sm-4 form-control-label">
      <span
        v-if="!element.isOptional && undefined !== element.props.description"
        class="text-danger"
        >*
      </span>
      <slot name="label">
        {{ element.label }}
      </slot>
    </label>

    <div class="col-sm-8">
      <slot />

      <small
        v-if="element.props?.description"
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
  </div>
</template>

<script lang="ts" setup>
import {type ElementInstance, generateFieldId, useLanguage} from '@myparcel-pdk/admin';

const props = defineProps<{element: ElementInstance<{description: string}>}>();

const {translate} = useLanguage();

const id = generateFieldId(props.element);
</script>
