<template>
  <li class="nav-item">
    <a
      v-test="AdminComponent.TabNavButton"
      :class="{active}"
      class="nav-link tab"
      href="#"
      @click.prevent="$emit('click')">
      <slot />
    </a>
  </li>
</template>

<script lang="ts" setup>
import {AdminComponent} from '@myparcel-dev/pdk-admin';

defineProps<{active: boolean}>();
defineEmits<(event: 'click') => void>();
</script>
