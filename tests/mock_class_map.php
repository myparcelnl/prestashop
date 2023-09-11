<?php
/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Mock\MockPsConfiguration;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsContext;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsController;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsCustomerMessage;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntity;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsLink;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModel;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsOrder;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsSmarty;

/** @see \ObjectModelCore */
abstract class ObjectModelCore extends MockPsObjectModel { }

/** @see \ObjectModel */
abstract class ObjectModel extends ObjectModelCore { }

/** @see \Context */
final class Context extends MockPsContext { }

/** @see \Smarty */
final class Smarty extends MockPsSmarty { }

/** @see \LinkCore */
abstract class LinkCore extends MockPsLink { }

/** @see \Link */
final class Link extends LinkCore { }

/** @see \ConfigurationCore */
abstract class ConfigurationCore extends MockPsConfiguration { }

/** @see \Configuration */
final class Configuration extends ConfigurationCore { }

###
# Modules
###

/** @see \ModuleCore */
abstract class ModuleCore extends MockPsModule { }

/** @see \Module */
abstract class Module extends ModuleCore { }

/** @see \Module */
abstract class CarrierModule extends Module { }

###
# Controllers
###

/** @see \ControllerCore */
abstract class ControllerCore extends MockPsController { }

/** @see \Controller */
class Controller extends ControllerCore { }

/** @see \AdminControllerCore */
abstract class AdminControllerCore extends Controller { }

/** @see \AdminController */
final class AdminController extends AdminControllerCore { }

###
# Entities
###

/** @see \OrderCore */
abstract class OrderCore extends MockPsOrder { }

/** @see \Order */
final class Order extends OrderCore { }

/** @see \AddressDeliveryCore */
abstract class AddressDeliveryCore extends MockPsEntity { }

/** @see \AddressDelivery */
final class AddressDelivery extends AddressDeliveryCore { }

/** @see \AddressInvoiceCore */
abstract class AddressInvoiceCore extends MockPsEntity { }

/** @see \AddressInvoice */
final class AddressInvoice extends AddressInvoiceCore { }

/** @see \CarrierCore */
abstract class CarrierCore extends MockPsEntity { }

/** @see \Carrier */
final class Carrier extends CarrierCore { }

/** @see \CartCore */
abstract class CartCore extends MockPsEntity { }

/** @see \Cart */
final class Cart extends CartCore { }

/** @see \CurrencyCore */
abstract class CurrencyCore extends MockPsEntity { }

/** @see \Currency */
final class Currency extends CurrencyCore { }

/** @see \CustomerCore */
abstract class CustomerCore extends MockPsEntity { }

/** @see \Customer */
final class Customer extends CustomerCore { }

/** @see \LangCore */
abstract class LangCore extends MockPsEntity { }

/** @see \Lang */
final class Lang extends LangCore { }

/** @see \ShopCore */
abstract class ShopCore extends MockPsEntity { }

/** @see \Shop */
final class Shop extends ShopCore { }

/** @see \ShopGroupCore */
abstract class ShopGroupCore extends MockPsEntity { }

/** @see \ShopGroup */
final class ShopGroup extends ShopGroupCore { }

/** @see \AddressCore */
abstract class AddressCore extends MockPsEntity { }

/** @see \Address */
final class Address extends AddressCore { }

/** @see \CountryCore */
abstract class CountryCore extends MockPsEntity { }

/** @see \Country */
final class Country extends CountryCore { }

/** @see \StateCore */
abstract class StateCore extends MockPsEntity { }

/** @see \State */
final class State extends StateCore { }

/** @see \CustomerMessageCore */
abstract class CustomerMessageCore extends MockPsCustomerMessage { }

/** @see \CustomerMessage */
final class CustomerMessage extends CustomerMessageCore { }
