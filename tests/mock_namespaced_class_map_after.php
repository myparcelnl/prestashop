<?php
/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace PrestaShopBundle\Exception;

use Exception;

class InvalidModuleException extends Exception { }

namespace Symfony\Bundle\FrameworkBundle\Controller;

use MyParcelNL\PrestaShop\Tests\Mock\BaseMock;

abstract class AbstractController extends BaseMock { }

namespace PrestaShopBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrameworkBundleAdminController extends AbstractController { }
