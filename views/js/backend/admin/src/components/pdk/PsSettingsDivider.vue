<template>
  <div v-test="AdminComponent.SettingsDivider">
    <PdkHeading
      v-if="element.props.heading"
      :level="element.props.level"
      class="bg-transparent card-header mt-3 mx-0">
      {{ translate(element.props.heading) }}
    </PdkHeading>

    <p
      v-if="element.props.content && has(element.props.content)"
      class="card-text"
      v-text="translate(element.props.content)" />
  </div>
</template>

<script lang="ts" setup>
import {AdminComponent, useLanguage, type SettingsDividerProps} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
defineProps<SettingsDividerProps>();

const {translate, has} = useLanguage();
</script>
