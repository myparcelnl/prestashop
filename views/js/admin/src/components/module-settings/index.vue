<template>
  <div>
    <ul
      class="nav nav-tabs"
      role="tablist">
      <li
        v-for="(tab, index) in moduleSettingsFormContext"
        :key="[tab.name, index, 'tab'].join('_')"
        class="nav-item"
        :class="{
          'active': activeTab === tab.name,
        }">
        <a
          class="active nav-link"
          :href="`#${tab.name}`"
          @click="() => changeTab(tab.name)"
          v-text="tab.label" />
      </li>
    </ul>

    <PsCard
      v-for="(tab, index) in moduleSettingsFormContext"
      v-show="activeTab === tab.name"
      :key="[tab.name, index].join('_')">
      <SettingsFormItem
        v-for="child in tab.children"
        :key="[tab.name, child.name, child.action, child.label, child.type, index].join('_')"
        :item="child"
        @action="doTheBartman"
        @change="processValue" />

      <PsButton
        label="save"
        @click="save" />
    </PsCard>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, watch } from '@vue/composition-api';
import { ContextKey } from '@/data/global/context';
import HeaderAlerts from '@/components/after-header/HeaderAlerts.vue';
import PsButton from '@/components/common/PsButton.vue';
import PsCard from '@/components/common/PsCard.vue';
import { buttonActionsEventBus } from '@/data/eventBus/ButtonActionsEventBus';
import { moduleSettingsEventBus } from '@/data/eventBus/ModuleSettingsEventBus';
import { useGlobalContext } from '@/composables/context/useGlobalContext';
import { useEventBusAlerts } from '@/composables/useEventBusAlerts';

export default defineComponent({
  name: 'ModuleSettings',
  components: {
    PsCard,
    PsButton,
    HeaderAlerts,
  },

  setup: () => {
    const moduleSettingsFormContext = useGlobalContext(ContextKey.MODULE_SETTINGS_FORM);
    const accountSettingsContext = useGlobalContext(ContextKey.ACCOUNT_SETTINGS);

    const values = ref<Record<string, unknown>>({});

    const activeTab = ref<string>(moduleSettingsFormContext.value[0].name);

    watch(activeTab, (tab) => {
      const { clear } = useEventBusAlerts();

      clear();
      console.log(tab);
    });

    const save = async() => {
      await moduleSettingsEventBus.save(values.value);
    };

    return {
      save,
      moduleSettingsFormContext,
      accountSettingsContext,

      processValue(changed: { name: string; value: unknown }) {
        values.value[changed.name] = changed.value;
      },

      doTheBartman: async(event: { action: [string, string] }) => {
        // TODO
        if (event.action[0] === 'showModal') {
          const carrierSettings = moduleSettingsFormContext.value.find((item) => item.name === 'carrier-settings');

          console.log($('#' + event.action[1]));
          $('#' + event.action[1]).modal('show');
          return;
        }

        await buttonActionsEventBus.execute(event.action);
      },

      changeTab(tab: string): void {
        activeTab.value = tab;
      },

      activeTab,
    };
  },
});
</script>
