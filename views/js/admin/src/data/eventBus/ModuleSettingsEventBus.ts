import { EventBus } from '@/data/eventBus/EventBus';
import { getAdminUrl } from '@/services/ajax/getAdminUrl';

export class ModuleSettingsEventBus extends EventBus {
  protected url = getAdminUrl(window.MyParcelActions.pathModuleSettings);

  public async save(settings: Record<string, unknown>): Promise<RequestResponse> {
    return this.post(this.url, {
      action: 'save',
    }, {
      data: settings,
    });
  }
}

export const moduleSettingsEventBus = new ModuleSettingsEventBus();
