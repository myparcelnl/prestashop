import {executePdkComponentTests} from '@myparcel-pdk/admin-component-tests';
import {
  PsFormGroup,
  PsIcon,
  PsToggleInput,
  PsTextArea,
  PsTabNavButtonWrapper,
  PsTabNavButton,
  PsRow,
  PsPluginSettingsWrapper,
} from '../components';

executePdkComponentTests({
  FormGroup: PsFormGroup,
  Icon: PsIcon,
  PluginSettingsWrapper: PsPluginSettingsWrapper,
  Row: PsRow,
  TabNavButton: PsTabNavButton,
  TabNavButtonWrapper: PsTabNavButtonWrapper,
  TextArea: PsTextArea,
  ToggleInput: PsToggleInput,
});
