import './assets/css/myparceladmin.css';
import * as COMPONENTS from '@myparcel-pdk/frontend-components';
import {ModalKey, createPdkFrontend, useModalStore} from '@myparcel/pdk-frontend';
import PsAlert from '@/components/pdk/PsAlert.vue';
import PsButton from '@/components/pdk/PsButton.vue';
import PsCard from '@/components/pdk/PsCard.vue';
import PsCheckbox from '@/components/pdk/PsCheckbox.vue';
import PsDropdownButton from '@/components/pdk/PsDropdownButton.vue';
import PsDropdownButtonItem from '@/components/pdk/PsDropdownButtonItem.vue';
import PsInput from '@/components/pdk/PsInput.vue';
import PsRadio from '@/components/pdk/PsRadio.vue';
import PsSelect from '@/components/pdk/PsSelect.vue';

createPdkFrontend({
  components: {
    PdkAccordion: COMPONENTS.DefaultPdkAccordion,
    PdkAlert: PsAlert,
    PdkButton: PsButton,
    PdkCard: PsCard,
    PdkCheckbox: PsCheckbox,
    PdkDropdownButton: PsDropdownButton,
    PdkDropdownButtonItem: PsDropdownButtonItem,
    PdkFormGroup: COMPONENTS.DefaultPdkFormGroup,
    PdkIcon: COMPONENTS.DefaultPdkIcon,
    PdkInput: PsInput,
    PdkModal: COMPONENTS.DefaultPdkModal,
    PdkMultiCheckbox: COMPONENTS.DefaultPdkMultiCheckbox,
    PdkRadio: PsRadio,
    PdkSelect: PsSelect,
    PdkTable: COMPONENTS.DefaultPdkTable,
    PdkTableCol: COMPONENTS.DefaultPdkTableCol,
    PdkTableRow: COMPONENTS.DefaultPdkTableRow,
  },

  onCreateStore: () => {
    const modalStore = useModalStore();

    modalStore.$patch({
      onOpen: (modal: ModalKey) => {
        jQuery(`#${modal}`).modal('show');
      },

      onClose: (modal: ModalKey) => {
        jQuery(`#${modal}`).modal('hide');
      },
    });
  },
});
