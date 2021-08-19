<?php

function upgrade_module_1_1_2(Module $module)
{
    foreach(Db::getInstance()->executeS('
        SELECT `carrier`.* FROM `ps_carrier` AS `carrier`
	    LEFT JOIN `ps_myparcelbe_carrier_configuration` AS `config` ON `carrier`.`id_carrier` = `config`.`id_carrier` AND `config`.`name` = "carrierType"
	    WHERE `carrier`.`external_module_name` = "' . $module::MODULE_NAME . '"
	    AND `config`.`id_configuration` IS NULL;
    ') as $record) {
        if (preg_match('/Post\s?NL/', $record)) {
            $carrierType = \Gett\MyparcelBE\Constant::POSTNL_CARRIER_NAME;
        } elseif (preg_match('/DPD/', $record)) {
            $carrierType = \Gett\MyparcelBE\Constant::DPD_CARRIER_NAME;
        } else {
            $carrierType = $module->isNL()
                ? \Gett\MyparcelBE\Constant::POSTNL_CARRIER_NAME
                : \Gett\MyparcelBE\Constant::BPOST_CARRIER_NAME
            ;
        }

        Db::getInstance()->insert('myparcelbe_carrier_configuration', [
            [
                'id_carrier' => (int) $record['id_carrier'],
                'name' => 'carrierType',
                'value' => $carrierType
            ]
        ]);
    }

    return true;
}
