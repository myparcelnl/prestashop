<template>
  <PdkRow
    v-test="[AdminComponent.TriStateInput, element]"
    :class="config?.cssUtilities?.displayFlex">
    <div class="col-md-6">
      <PdkRow>
        <div class="col-4">
          <input
            v-model="model"
            :name="id"
            type="hidden" />

          <PdkToggleInput
            v-model="toggleModel"
            :class="config?.cssUtilities?.marginYAuto"
            :element="toggleElement" />
        </div>

        <div class="col-2">
          <PdkButton
            :class="config?.cssUtilities?.displayFlex"
            :size="Size.ExtraSmall"
            :title="inheritElement?.label"
            @click="inheritModel = !inheritModel">
            <div>
              <i
                :class="config?.cssUtilities?.marginYAuto"
                class="material-icons"
                role="none"
                v-text="inheritModel ? 'lock' : 'lock_open'" />
            </div>

            <PdkCheckboxInput
              v-model="inheritModel"
              :element="{...inheritElement, label: undefined}"
              class="sr-only"
              tabindex="-1" />
          </PdkButton>
        </div>
      </PdkRow>
    </div>
  </PdkRow>
</template>

<script lang="ts" setup>
import {
  useTriStateInputContext,
  AdminComponent,
  useAdminConfig,
  Size,
  type TriStateInputProps,
  type TriStateInputEmits,
} from '@myparcel-pdk/admin';

// eslint-disable-next-line vue/no-unused-properties
const props = defineProps<TriStateInputProps>();
const emit = defineEmits<TriStateInputEmits>();

const config = useAdminConfig();

const {inheritElement, toggleElement, inheritModel, toggleModel, model, id} = useTriStateInputContext(props, emit);
</script>
