import PsAlert from '../components/pdk/PsAlert.vue';
import PsButton from '../components/pdk/PsButton.vue';
import PsCard from '../components/pdk/PsCard.vue';
import PsCheckbox from '../components/pdk/PsCheckbox.vue';
import PsDropdownButton from '../components/pdk/PsDropdownButton.vue';
import PsFormGroup from '../components/pdk/PsFormGroup.vue';
import PsIcon from '../components/pdk/PsIcon.vue';
import PsTextInput from '../components/pdk/PsTextInput.vue';
import PsModal from '../components/pdk/PsModal.vue';
import PsRadioInput from '../components/pdk/PsRadio.vue';
import PsSelectInput from '../components/pdk/PsSelectInput.vue';
import PsToggleInput from '../components/pdk/PsToggleInput.vue';
import {executePdkComponentTests} from '@myparcel/pdk-frontend-component-tests';

executePdkComponentTests({
  PdkAlert: PsAlert,
  PdkButton: PsButton,
  PdkCard: PsCard,
  PdkCheckboxInput: PsCheckbox,
  PdkDropdownButton: PsDropdownButton,
  PdkFormGroup: PsFormGroup,
  PdkIcon: PsIcon,
  PdkInput: PsTextInput,
  PdkModal: PsModal,
  PdkRadio: PsRadioInput,
  PdkSelect: PsSelectInput,
  PdkToggle: PsToggleInput,
});
