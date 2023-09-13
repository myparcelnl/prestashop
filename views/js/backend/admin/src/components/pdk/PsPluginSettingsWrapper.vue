<template>
  <div
    v-test="AdminComponent.PluginSettingsWrapper"
    class="card row">
    <div class="card-body col-sm-12 pdk-plugin-settings-wrapper">
      <slot />
    </div>
  </div>
</template>

<script lang="ts" setup>
import {AdminComponent} from '@myparcel-pdk/admin';
</script>
