<template>
  <PsCard>
    <template #header>
      MyParcel
    </template>

    <template #default>
      <ConceptCard />
      <ShipmentsCard />
    </template>
  </PsCard>
</template>

<script lang="ts">
import ConceptCard from '@/components/order/ConceptCard.vue';
import PsCard from '@/components/common/PsCard.vue';
import ShipmentsCard from '@/components/order/ShipmentsCard.vue';
import { defineComponent } from '@vue/composition-api';

export default defineComponent({
  name: 'OrderGridCard',
  components: {
    ConceptCard,
    PsCard,
    ShipmentsCard,
  },
});
</script>
