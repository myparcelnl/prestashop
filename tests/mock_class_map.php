<?php
/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,LongInheritanceChainInspection,EmptyClassInspection */

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Mock\MockPrestaShopLogger;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsCarrier;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsConfiguration;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsContext;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsController;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsCustomerMessage;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDbQuery;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsFileLogger;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsLanguage;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsLink;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModel;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsOrder;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsRangeObjectModel;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsSmarty;

###
# Core
###

/** @see \ConfigurationCore */
abstract class ConfigurationCore extends MockPsConfiguration { }

final class Configuration extends ConfigurationCore { }

/** @see \ContextCore */
abstract class ContextCore extends MockPsContext { }

final class Context extends ContextCore { }

/** @see \LinkCore */
abstract class LinkCore extends MockPsLink { }

final class Link extends LinkCore { }

/** @see \Smarty */
final class Smarty extends MockPsSmarty { }

/** @see \DbCore */
abstract class DbCore extends MockPsDb { }

final class Db extends DbCore { }

/** @see \DbQueryCore */
abstract class DbQueryCore extends MockPsDbQuery { }

final class DbQuery extends DbQueryCore { }

###
# Modules
###

/** @see \ModuleCore */
abstract class ModuleCore extends MockPsModule { }

abstract class Module extends ModuleCore { }

/** @see \CarrierModule */
abstract class CarrierModuleCore extends Module { }

class CarrierModule extends CarrierModuleCore { }

/** @see \FileLoggerCore */
abstract class FileLoggerCore extends MockPsFileLogger { }

final class FileLogger extends FileLoggerCore { }

/** @see \LanguageCore */
abstract class LanguageCore extends MockPsLanguage { }

final class Language extends LanguageCore { }

/** @see \PrestaShopLoggerCore */
abstract class PrestaShopLoggerCore extends MockPrestaShopLogger { }

final class PrestaShopLogger extends PrestaShopLoggerCore { }

###
# Controllers
###

/** @see \ControllerCore */
abstract class ControllerCore extends MockPsController { }

/** @see \Controller */
abstract class Controller extends ControllerCore { }

/** @see \AdminControllerCore */
abstract class AdminControllerCore extends Controller { }

final class AdminController extends AdminControllerCore { }

/** @see \FrontControllerCore */
abstract class FrontControllerCore extends Controller { }

final class FrontController extends FrontControllerCore { }

###
# Object models
###

/** @see \ObjectModelCore */
abstract class ObjectModelCore extends MockPsObjectModel { }

abstract class ObjectModel extends ObjectModelCore { }

/** @see \AddressCore */
abstract class AddressCore extends ObjectModel { }

final class Address extends AddressCore { }

/** @see \CarrierCore */
abstract class CarrierCore extends MockPsCarrier
{
    protected $hasCustomIdKey = true;
}

final class Carrier extends CarrierCore { }

/** @see \CartCore */
abstract class CartCore extends ObjectModel { }

final class Cart extends CartCore { }

/** @see \CountryCore */
abstract class CountryCore extends ObjectModel { }

final class Country extends CountryCore { }

/** @see \CurrencyCore */
abstract class CurrencyCore extends ObjectModel { }

final class Currency extends CurrencyCore { }

/** @see \CustomerCore */
abstract class CustomerCore extends ObjectModel { }

final class Customer extends CustomerCore { }

/** @see \CustomerMessageCore */
abstract class CustomerMessageCore extends MockPsCustomerMessage { }

final class CustomerMessage extends CustomerMessageCore { }

/** @see \GenderCore */
abstract class GenderCore extends ObjectModel { }

final class Gender extends GenderCore { }

/** @see \GroupCore */
abstract class GroupCore extends ObjectModel { }

final class Group extends GroupCore { }

/** @see \LangCore */
abstract class LangCore extends ObjectModel { }

final class Lang extends LangCore { }

/** @see \ManufacturerCore */
abstract class ManufacturerCore extends ObjectModel { }

final class Manufacturer extends ManufacturerCore { }

/** @see \OrderCore */
abstract class OrderCore extends MockPsOrder { }

final class Order extends OrderCore { }

/** @see \OrderStateCore */
abstract class OrderStateCore extends ObjectModel
{
    protected $hasCustomIdKey = true;
}

final class OrderState extends OrderStateCore { }

/** @see \ProductCore */
abstract class ProductCore extends ObjectModel
{
    protected $hasCustomIdKey = true;
}

final class Product extends ProductCore { }

/** @see \RangePriceCore */
abstract class RangePriceCore extends MockPsRangeObjectModel { }

final class RangePrice extends RangePriceCore { }

/** @see \RangeWeightCore */
abstract class RangeWeightCore extends MockPsRangeObjectModel { }

final class RangeWeight extends RangeWeightCore { }

/** @see \RiskCore */
abstract class RiskCore extends ObjectModel { }

final class Risk extends RiskCore { }

/** @see \ShopCore */
abstract class ShopCore extends ObjectModel { }

final class Shop extends ShopCore { }

/** @see \ShopGroupCore */
abstract class ShopGroupCore extends ObjectModel { }

final class ShopGroup extends ShopGroupCore { }

/** @see \StateCore */
abstract class StateCore extends ObjectModel { }

final class State extends StateCore { }

/** @see \SupplierCore */
abstract class SupplierCore extends ObjectModel { }

final class Supplier extends SupplierCore { }

/** @see \WarehouseCore */
abstract class WarehouseCore extends ObjectModel { }

final class Warehouse extends WarehouseCore { }

/** @see \ZoneCore */
abstract class ZoneCore extends ObjectModel
{
    protected $hasCustomIdKey = true;
}

/** @see \Zone */
final class Zone extends ZoneCore { }
