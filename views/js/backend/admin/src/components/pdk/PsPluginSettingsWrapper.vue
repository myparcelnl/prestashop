<template>
  <div
    v-test="AdminComponent.PluginSettingsWrapper"
    class="card row">
    <div class="card-body col-sm-12">
      <slot />
    </div>
  </div>
</template>

<script lang="ts" setup>
import {AdminComponent} from '@myparcel-pdk/admin';
</script>
