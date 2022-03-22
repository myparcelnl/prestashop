<template>
  <div>
    accordion

    <PsCard
      v-for="(child, index) in item.children"
      :key="[child.name, child.type, index].join('_')"
      @click-header="openedItem = child.name === openedItem ? null : child.name">
      <template #header>
        {{ child.label }}

        <span v-if="child.name === openedItem">
          v
        </span>
        <span v-else>
          >
        </span>
      </template>

      <template #default>
        <SettingsFormItem
          v-for="(grandchild, grandchildIndex) in child.children"
          v-show="openedItem === child.name"
          :key="[child.name, grandchild.name, grandchild.type, grandchildIndex].join('_')"
          :item="grandchild"
          @click="$emit('click', $event)"
          @change="$emit('change', $event)" />
      </template>
    </PsCard>
  </div>
</template>

<script lang="ts">
import { PropType, defineComponent, ref, watch, watchEffect } from '@vue/composition-api';
import PsCard from '@/components/common/PsCard.vue';

export default defineComponent({
  name: 'PsAccordion',
  components: {
    PsCard,
  },

  props: {
    item: {
      type: Object as PropType<ModuleSettingsFormItem>,
      required: true,
    },
  },

  setup: (props) => {
    const openedItem = ref<string>();

    return {
      openedItem,
    };
  },
});
</script>
