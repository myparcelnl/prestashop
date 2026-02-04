import './assets/scss/index.scss';
import {
  DefaultCheckboxGroup,
  DefaultCurrencyInput,
  DefaultHeading,
  DefaultLink,
  DefaultNumberInput,
  DefaultTable,
  DefaultTableCol,
  DefaultTableRow,
  DefaultTimeInput,
  DefaultRadioGroup,
  DefaultMultiDateInput,
} from '@myparcel-dev/pdk-admin-preset-default';
import {
  Bootstrap4Box,
  Bootstrap4Button,
  Bootstrap4ButtonGroup,
  Bootstrap4Col,
  Bootstrap4Image,
  Bootstrap4Modal,
  Bootstrap4Notification,
  Bootstrap4TextInput,
  bootstrap4Config,
  Bootstrap4Loader,
  Bootstrap4ShipmentLabelWrapper,
  Bootstrap4DropdownButton,
} from '@myparcel-dev/pdk-admin-preset-bootstrap4';
import {AdminComponent, LogLevel, createPdkAdmin, type ElementInstance} from '@myparcel-dev/pdk-admin';
import {listenForBulkActions} from './functions/listenForBulkActions';
import {
  PsFormGroup,
  PsProductSettingsFormGroup,
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
  PsTriStateInput,
  PsRadioInput,
  PsCheckboxInput,
  PsDropoffInput,
} from './components';

// eslint-disable-next-line max-lines-per-function
window.addEventListener('load', () => {
  createPdkAdmin({
    ...bootstrap4Config,

    components: {
      [AdminComponent.Box]: Bootstrap4Box,
      [AdminComponent.Button]: Bootstrap4Button,
      [AdminComponent.ButtonGroup]: Bootstrap4ButtonGroup,
      [AdminComponent.CheckboxGroup]: DefaultCheckboxGroup,
      [AdminComponent.CheckboxInput]: PsCheckboxInput,
      [AdminComponent.CodeEditor]: PsTextArea,
      [AdminComponent.Col]: Bootstrap4Col,
      [AdminComponent.CurrencyInput]: DefaultCurrencyInput,
      [AdminComponent.DropOffInput]: PsDropoffInput,
      [AdminComponent.DropdownButton]: Bootstrap4DropdownButton,
      [AdminComponent.FormGroup]: PsFormGroup,
      [AdminComponent.Heading]: DefaultHeading,
      [AdminComponent.Icon]: PsIcon,
      [AdminComponent.Image]: Bootstrap4Image,
      [AdminComponent.Link]: DefaultLink,
      [AdminComponent.Loader]: Bootstrap4Loader,
      [AdminComponent.Modal]: Bootstrap4Modal,
      [AdminComponent.MultiSelectInput]: PsMultiSelectInput,
      [AdminComponent.Notification]: Bootstrap4Notification,
      [AdminComponent.NumberInput]: DefaultNumberInput,
      [AdminComponent.PluginSettingsWrapper]: PsPluginSettingsWrapper,
      [AdminComponent.RadioGroup]: DefaultRadioGroup,
      [AdminComponent.RadioInput]: PsRadioInput,
      [AdminComponent.Row]: PsRow,
      [AdminComponent.SelectInput]: PsSelectInput,
      [AdminComponent.SettingsDivider]: PsSettingsDivider,
      [AdminComponent.ShipmentLabelWrapper]: Bootstrap4ShipmentLabelWrapper,
      [AdminComponent.TabNavButton]: PsTabNavButton,
      [AdminComponent.TabNavButtonWrapper]: PsTabNavButtonWrapper,
      [AdminComponent.Table]: DefaultTable,
      [AdminComponent.TableCol]: DefaultTableCol,
      [AdminComponent.TableRow]: DefaultTableRow,
      [AdminComponent.TextArea]: PsTextArea,
      [AdminComponent.TextInput]: Bootstrap4TextInput,
      [AdminComponent.TimeInput]: DefaultTimeInput,
      [AdminComponent.ToggleInput]: PsToggleInput,
      [AdminComponent.TriStateInput]: PsTriStateInput,
      [AdminComponent.MultiDateInput]: DefaultMultiDateInput,
    },

    formConfig: {
      form: {
        attributes: {
          class: 'form-horizontal',
        },
      },
    },

    formConfigOverrides: {
      productSettings: {
        form: {
          tag: 'div',
        },
        field: {
          wrapper: PsProductSettingsFormGroup,
        },
      },
    },

    cssUtilities: {
      ...bootstrap4Config.cssUtilities,
      // We have bootstrap 4 and tailwind classes (prefixed with mypa-) in the same project. Prefer bootstrap 4 classes.
      animationLoading: 'mypa-loading',
      animationSpin: 'mypa-spin',
      cursorDefault: 'mypa-cursor-default',
      cursorPointer: 'mypa-cursor-pointer',
      whitespaceNoWrap: 'mypa-whitespace-nowrap',
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

    onInitialized() {
      listenForBulkActions();
    },
  });
});
