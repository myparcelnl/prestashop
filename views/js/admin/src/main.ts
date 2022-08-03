import {
  Bootstrap4Box,
  Bootstrap4Button,
  Bootstrap4ButtonGroup,
  Bootstrap4CheckboxInput,
  Bootstrap4Col,
  Bootstrap4DropdownButton,
  Bootstrap4FormGroup,
  Bootstrap4Image,
  Bootstrap4Modal,
  Bootstrap4Notification,
  Bootstrap4NumberInput,
  Bootstrap4RadioInput,
  Bootstrap4Row,
  Bootstrap4SelectInput,
  Bootstrap4Table,
  Bootstrap4TextInput,
  bootstrap4Config,
} from '@myparcel-pdk/admin-preset-bootstrap4/src';
import {
  DefaultCurrencyInput,
  DefaultDropOffInput,
  DefaultHeading,
  DefaultLink,
  DefaultMultiCheckbox,
  DefaultMultiRadio,
  DefaultTabNavButton,
  DefaultTableCol,
  DefaultTableRow,
  DefaultTimeInput,
  DefaultToggleInput,
} from '@myparcel-pdk/admin-components/src';
import {LogLevel, createPdkAdmin} from '@myparcel-pdk/admin/src';
import {PsIcon} from './components';
import PsPluginSettingsWrapper from './components/pdk/PsPluginSettingsWrapper.vue';

createPdkAdmin({
  ...bootstrap4Config,

  logLevel: LogLevel.DEBUG,

  transitions: {
    modal: 'fade',
    modalBackdrop: 'fade',
    notification: 'fade',
    shipmentBox: 'fade',
    shipmentRow: 'fade',
    tabNavigation: 'fade',
    tableRow: 'fade',
  },

  formConfig: {
    form: {
      attributes: {
        class: 'form-horizontal',
      },
    },

    field: {
      elementProp: false,
    },
  },

  components: {
    PdkBox: Bootstrap4Box,
    PdkButton: Bootstrap4Button,
    PdkButtonGroup: Bootstrap4ButtonGroup,
    PdkCheckboxInput: Bootstrap4CheckboxInput,
    PdkCol: Bootstrap4Col,
    PdkDropdownButton: Bootstrap4DropdownButton,
    PdkFormGroup: Bootstrap4FormGroup,
    PdkImage: Bootstrap4Image,
    PdkModal: Bootstrap4Modal,
    PdkNotification: Bootstrap4Notification,
    PdkNumberInput: Bootstrap4NumberInput,
    PdkRadioInput: Bootstrap4RadioInput,
    PdkRow: Bootstrap4Row,
    PdkSelectInput: Bootstrap4SelectInput,
    PdkTable: Bootstrap4Table,
    PdkTextInput: Bootstrap4TextInput,

    PdkCurrencyInput: DefaultCurrencyInput,
    PdkLink: DefaultLink,
    PdkMultiCheckbox: DefaultMultiCheckbox,
    PdkMultiRadio: DefaultMultiRadio,
    PdkTableCol: DefaultTableCol,
    PdkTableRow: DefaultTableRow,
    PdkToggleInput: DefaultToggleInput,
    PdkTimeInput: DefaultTimeInput,
    PdkDropOffInput: DefaultDropOffInput,
    PdkHeading: DefaultHeading,
    PdkTabNavButton: DefaultTabNavButton,

    PdkPluginSettingsWrapper: PsPluginSettingsWrapper,
    PdkIcon: PsIcon,
  },
});
