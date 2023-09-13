<template>
  <ul
    v-test="AdminComponent.TabNavButtonWrapper"
    class="nav nav-pills">
    <slot />
  </ul>
</template>

<script lang="ts" setup>
import {AdminComponent} from '@myparcel-pdk/admin';
</script>
