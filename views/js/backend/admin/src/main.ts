import './assets/scss/index.scss';
import {
  DefaultCheckboxGroup,
  DefaultCurrencyInput,
  DefaultDropOffInput,
  DefaultHeading,
  DefaultLink,
  DefaultLoader,
  DefaultNumberInput,
  DefaultRadioGroup,
  DefaultTable,
  DefaultTableCol,
  DefaultTableRow,
  DefaultTimeInput,
  DefaultTriStateInput,
} from '@myparcel-pdk/admin-preset-default';
import {
  Bootstrap4Box,
  Bootstrap4Button,
  Bootstrap4ButtonGroup,
  Bootstrap4CheckboxInput,
  Bootstrap4Col,
  Bootstrap4Image,
  Bootstrap4Modal,
  Bootstrap4Notification,
  Bootstrap4RadioInput,
  Bootstrap4TextInput,
  bootstrap4Config,
} from '@myparcel-pdk/admin-preset-bootstrap4';
import {LogLevel, createPdkAdmin, type ElementInstance} from '@myparcel-pdk/admin';
import {
  PsDropdownButton,
  PsFormGroup,
  PsIcon,
  PsPluginSettingsWrapper,
  PsRow,
  PsTabNavButton,
  PsTabNavButtonWrapper,
  PsSettingsDivider,
  PsTextArea,
  PsToggleInput,
  PsMultiSelectInput,
  PsSelectInput,
} from './components';

// eslint-disable-next-line max-lines-per-function
window.onload = () => {
  createPdkAdmin({
    ...bootstrap4Config,

    components: {
      PdkBox: Bootstrap4Box,
      PdkButton: Bootstrap4Button,
      PdkButtonGroup: Bootstrap4ButtonGroup,
      PdkCheckboxGroup: DefaultCheckboxGroup,
      PdkCheckboxInput: Bootstrap4CheckboxInput,
      PdkCodeEditor: PsTextArea,
      PdkCol: Bootstrap4Col,
      PdkCurrencyInput: DefaultCurrencyInput,
      PdkDropOffInput: DefaultDropOffInput,
      PdkDropdownButton: PsDropdownButton,
      PdkFormGroup: PsFormGroup,
      PdkHeading: DefaultHeading,
      PdkIcon: PsIcon,
      PdkImage: Bootstrap4Image,
      PdkLink: DefaultLink,
      PdkLoader: DefaultLoader,
      PdkModal: Bootstrap4Modal,
      PdkMultiSelectInput: PsMultiSelectInput,
      PdkNotification: Bootstrap4Notification,
      PdkNumberInput: DefaultNumberInput,
      PdkPluginSettingsWrapper: PsPluginSettingsWrapper,
      PdkRadioGroup: DefaultRadioGroup,
      PdkRadioInput: Bootstrap4RadioInput,
      PdkRow: PsRow,
      PdkSelectInput: PsSelectInput,
      PdkSettingsDivider: PsSettingsDivider,
      PdkTabNavButton: PsTabNavButton,
      PdkTabNavButtonWrapper: PsTabNavButtonWrapper,
      PdkTable: DefaultTable,
      PdkTableCol: DefaultTableCol,
      PdkTableRow: DefaultTableRow,
      PdkTextArea: PsTextArea,
      PdkTextInput: Bootstrap4TextInput,
      PdkTimeInput: DefaultTimeInput,
      PdkToggleInput: PsToggleInput,
      PdkTriStateInput: DefaultTriStateInput,
    },

    formConfig: {
      form: {
        attributes: {
          class: 'form-horizontal py-4',
        },
      },
    },

    formConfigOverrides: {
      productSettings: {
        form: {
          tag: 'div',
        },
      },
    },

    cssUtilities: {
      animationSpin: 'mypa-spinner',
      whitespaceNoWrap: 'whitespace-nowrap',
      displayFlex: 'd-flex justify-content-between',
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

    generateFieldId(element: ElementInstance) {
      const {form, name} = element;

      return `myparcelnl-${form.name}-${name}`;
    },
  });
};