import PsAlert from './PsAlert.vue';
import PsButton from './PsButton.vue';
import PsCard from './PsCard.vue';
import PsCheckbox from './PsCheckbox.vue';
import PsDropdownButton from './PsDropdownButton.vue';
import PsDropdownButtonItem from './PsDropdownButtonItem.vue';
import PsInput from './PsInput.vue';
import PsRadio from './PsRadio.vue';
import PsSelect from './PsSelect.vue';
import {executePdkComponentTests} from '@myparcel/pdk-frontend/test';

executePdkComponentTests({
  PdkAlert: PsAlert,
  PdkButton: PsButton,
  PdkCard: PsCard,
  PdkCheckbox: PsCheckbox,
  PdkDropdownButton: PsDropdownButton,
  PdkDropdownButtonItem: PsDropdownButtonItem,
  PdkInput: PsInput,
  PdkRadio: PsRadio,
  PdkSelect: PsSelect,
});
