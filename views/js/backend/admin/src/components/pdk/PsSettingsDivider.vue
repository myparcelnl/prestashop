<template>
  <div
    v-test="AdminComponent.SettingsDivider"
    class="mb-0 pb-0">
    <PdkHeading
      v-if="element.props.heading"
      :level="element.props.level"
      class="card-header">
      {{ translate(element.props.heading) }}
    </PdkHeading>

    <p
      v-if="element.props.content && has(element.props.content)"
      class="card-block text-muted"
      v-text="translate(element.props.content)" />
  </div>
</template>

<script lang="ts" setup>
import {AdminComponent, useLanguage, type SettingsDividerProps} from '@myparcel-dev/pdk-admin';

// eslint-disable-next-line vue/no-unused-properties
defineProps<SettingsDividerProps>();

const {translate, has} = useLanguage();
</script>
