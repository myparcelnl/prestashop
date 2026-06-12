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

namespace PrestaShop\PrestaShop\Core\Grid\Column;

use MyParcelNL\PrestaShop\Tests\Mock\BaseMock;

class AbstractColumn extends BaseMock { }

namespace Doctrine\Common\Annotations;

use MyParcelNL\PrestaShop\Tests\Mock\BaseMock;

class DocParser extends BaseMock { }

class AnnotationReader extends BaseMock { }

class PsrCachedReader extends BaseMock { }

namespace Doctrine\ORM;

use Exception;
use MyParcelNL\PrestaShop\Tests\Mock\BaseMock;

class EntityManager extends BaseMock { }

class EntityRepository extends BaseMock { }

class EntityNotFoundException extends Exception { }

interface EntityManagerInterface { }

namespace Doctrine\ORM\Mapping\Driver;

use MyParcelNL\PrestaShop\Tests\Mock\BaseMock;

class AnnotationDriver extends BaseMock { }

namespace Symfony\Component\Cache\Adapter;

use MyParcelNL\PrestaShop\Tests\Mock\BaseMock;

class ArrayAdapter extends BaseMock { }

namespace PrestaShop\PrestaShop\Core\Grid\Record;

/**
 * Minimal stand-in for PrestaShop's grid RecordCollection: holds raw record arrays and exposes
 * them via all(), which is the only part the module's order-grid hook consumes.
 */
class RecordCollection
{
    /**
     * @var array
     */
    private $items;

    /**
     * @param  array $records
     */
    public function __construct(array $records = [])
    {
        $this->items = $records;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }
}
