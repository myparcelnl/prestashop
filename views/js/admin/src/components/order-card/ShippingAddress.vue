<template>
  <PsCard>
    <p v-html="contextData.formattedAddress" />
  </PsCard>
</template>

<script lang="ts">
import { ContextKey } from '@/data/global/context';
import PsCard from '@/components/common/PsCard.vue';
import { contextProps } from '@/composables/props/contextProps';
import { defineComponent } from '@vue/composition-api';
import { useContext } from '@/composables/context/useContext';

export default defineComponent({
  name: 'ShippingAddress',
  components: { PsCard },
  props: contextProps,
  setup: (props) => ({
    contextData: useContext(props.context ?? ContextKey.SHIPPING_ADDRESS),
  }),
});
</script>
