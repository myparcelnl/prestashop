<template>
  <ul
    v-test="AdminComponent.TabNavButtonWrapper"
    class="mb-2 nav nav-pills">
    <slot />
  </ul>
</template>

<script lang="ts" setup>
import {AdminComponent} from '@myparcel-pdk/admin';
</script>
