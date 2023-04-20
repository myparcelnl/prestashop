import './assets/scss/index.scss';
import {
  Bootstrap4Box,
  Bootstrap4ButtonGroup,
  Bootstrap4CheckboxInput,
  Bootstrap4Col,
  Bootstrap4Image,
  Bootstrap4Modal,
  Bootstrap4Notification,
  Bootstrap4RadioInput,
  Bootstrap4SelectInput,
  Bootstrap4TextInput,
  bootstrap4Config,
} from '@myparcel-pdk/admin-preset-bootstrap4/src';
import {
  DefaultButton,
  DefaultCurrencyInput,
  DefaultHeading,
  DefaultLink,
  DefaultMultiSelectInput,
  DefaultNumberInput,
  DefaultRadioGroup,
  DefaultTable,
  DefaultTableCol,
  DefaultTableRow,
  DefaultTimeInput,
} from '@myparcel-pdk/admin-preset-default/src';
import {LogLevel, createPdkAdmin} from '@myparcel-pdk/admin/src';
import PsCheckboxGroup from './components/pdk/PsCheckboxGroup.vue';
import PsDropOffInput from './components/pdk/PsDropOffInput.vue';
import PsFormGroup from './components/pdk/PsFormGroup.vue';
import PsPluginSettingsWrapper from './components/pdk/PsPluginSettingsWrapper.vue';
import PsRow from './components/pdk/PsRow.vue';
import PsTabNavButton from './components/pdk/PsTabNavButton.vue';
import PsTabNavButtonWrapper from './components/pdk/PsTabNavButtonWrapper.vue';
import PsTextArea from './components/pdk/PsTextArea.vue';
import PsToggleInput from './components/pdk/PsToggleInput.vue';
import {h} from 'vue';
import {PsIcon} from './components';
import PsDropdownButton from './components/pdk/PsDropdownButton.vue';

// eslint-disable-next-line max-lines-per-function
window.onload = () => {
  createPdkAdmin({
    ...bootstrap4Config,

    components: {
      PdkBox: Bootstrap4Box,
      PdkButton: DefaultButton,
      PdkButtonGroup: Bootstrap4ButtonGroup,
      PdkCheckboxGroup: PsCheckboxGroup,
      PdkCheckboxInput: Bootstrap4CheckboxInput,
      PdkCodeEditor: PsTextArea,
      PdkCol: Bootstrap4Col,
      PdkCurrencyInput: DefaultCurrencyInput,
      PdkDropOffInput: PsDropOffInput,
      PdkDropdownButton: PsDropdownButton,
      PdkFormGroup: PsFormGroup,
      PdkHeading: DefaultHeading,
      PdkIcon: PsIcon,
      PdkImage: Bootstrap4Image,
      PdkLink: DefaultLink,
      PdkLoader: h('div'),
      PdkModal: Bootstrap4Modal,
      PdkMultiSelectInput: DefaultMultiSelectInput,
      PdkNotification: Bootstrap4Notification,
      PdkNumberInput: DefaultNumberInput,
      PdkPluginSettingsWrapper: PsPluginSettingsWrapper,
      PdkRadioGroup: DefaultRadioGroup,
      PdkRadioInput: Bootstrap4RadioInput,
      PdkRow: PsRow,
      PdkSelectInput: Bootstrap4SelectInput,
      PdkSettingsDivider: h('hr'),
      // PdkShipmentLabelWrapper,
      PdkTabNavButton: PsTabNavButton,
      PdkTabNavButtonWrapper: PsTabNavButtonWrapper,
      PdkTable: DefaultTable,
      PdkTableCol: DefaultTableCol,
      PdkTableRow: DefaultTableRow,
      PdkTextArea: PsTextArea,
      PdkTextInput: Bootstrap4TextInput,
      PdkTimeInput: DefaultTimeInput,
      PdkToggleInput: PsToggleInput,
    },

    formConfig: {
      form: {
        attributes: {
          class: 'form-horizontal',
        },
      },
    },

    logLevel: LogLevel.Debug,

    transitions: {
      modal: 'fade',
      modalBackdrop: 'fade',
      notification: 'fade',
      shipmentBox: 'fade',
      shipmentRow: 'fade',
      tabNavigation: 'fade',
      tableRow: 'fade',
    },
  });
};
