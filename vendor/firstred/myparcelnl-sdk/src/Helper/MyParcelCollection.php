<?php

/**
 * Stores all data to communicate with the MyParcel API
 *
 * If you want to add improvements, please create a fork in our GitHub:
 * https://github.com/myparcelnl
 *
 * @author      Reindert Vetter <reindert@myparcel.nl>
 * @copyright   2010-2017 MyParcel
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US  CC BY-NC-ND 3.0 NL
 * @link        https://github.com/myparcelnl/sdk
 * @since       File available since Release v0.1.0
 */
namespace MyParcelModule\MyParcelNL\Sdk\src\Helper;

use MyParcelModule\MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelModule\MyParcelNL\Sdk\src\Exception\ConnectionException;
use MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelConsignment;
use MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest;
use MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository;
/**
 * Stores all data to communicate with the MyParcel API
 *
 * Class MyParcelCollection
 *
 * @package Model
 */
class MyParcelCollection
{
    const PREFIX_REFERENCE_ID = 'REFERENCE_ID_';
    const PREFIX_PDF_FILENAME = 'myparcel-label-';
    const DEFAULT_A4_POSITION = 1;
    /**
     * @var MyParcelConsignmentRepository[]
     */
    private $consignments = array();
    /**
     * @var string
     */
    private $paper_size = 'A6';
    /**
     * The position of the label on the paper.
     * pattern: [1 - 4]
     * example: 1. (top-left)
     *          2. (top-right)
     *          3. (bottom-left)
     *          4. (bottom-right)
     *
     * @var string
     */
    private $label_position = null;
    /**
     * Link to download the PDF
     *
     * @var string
     */
    private $label_link = null;
    /**
     * Label in PDF format
     *
     * @var string
     */
    private $label_pdf = null;
    /**
     * @var string
     */
    private $user_agent = '';
    /**
     * @param bool $keepKeys
     *
     * @return MyParcelConsignmentRepository[]
     */
    public function getConsignments($keepKeys = true)
    {
        if ($keepKeys) {
            return $this->consignments;
        }
        return array_values($this->consignments);
    }
    /**
     * Get one consignment
     *
     * @return \MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository|null
     *
     * @throws ApiException
     */
    public function getOneConsignment()
    {
        if (count($this->getConsignments()) > 1) {
            throw new \MyParcelModule\MyParcelNL\Sdk\src\Exception\ApiException('Can\'t run getOneConsignment(): Multiple items found');
        }
        foreach ($this->getConsignments() as $consignment) {
            return $consignment;
        }
        return null;
    }
    /**
     * @param string $id
     *
     * @return MyParcelConsignmentRepository
     */
    public function getConsignmentByReferenceId($id)
    {
        // return if referenceId not is set as a key
        foreach ($this->getConsignments() as $consignment) {
            if ($consignment->getReferenceId() == $id) {
                return $consignment;
            }
        }
        return null;
    }
    /**
     * @param integer $id
     *
     * @return MyParcelConsignmentRepository
     */
    public function getConsignmentByApiId($id)
    {
        // return if ApiId not is set as a key
        foreach ($this->getConsignments() as $consignment) {
            if ($consignment->getMyParcelConsignmentId() == $id) {
                return $consignment;
            }
        }
        return null;
    }
    /**
     * @return string
     *
     * this is used by third parties to access the label_pdf variable.
     */
    public function getLabelPdf()
    {
        return $this->label_pdf;
    }
    /**
     * @return string
     */
    public function getLinkOfLabels()
    {
        return $this->label_link;
    }
    /**
     * @param \MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository $consignment
     *
     * @param bool                                                               $needReferenceId
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addConsignment(\MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository $consignment, $needReferenceId = true)
    {
        if ($consignment->getApiKey() === null) {
            throw new \InvalidArgumentException('First set the API key with setApiKey() before running addConsignment()');
        }
        if ($needReferenceId && !empty($this->consignments)) {
            if ($consignment->getReferenceId() === null) {
                throw new \InvalidArgumentException('First set the reference id with setReferenceId() before running addConsignment() for multiple shipments');
            } elseif (key_exists($consignment->getReferenceId(), $this->consignments)) {
                throw new \InvalidArgumentException('setReferenceId() must be unique. For example, do not use an ID of an order as an order has multiple shipments. In that case, use the shipment ID.');
            }
        }
        if ($consignment->getReferenceId()) {
            $this->consignments[self::PREFIX_REFERENCE_ID . $consignment->getReferenceId()] = $consignment;
        } else {
            $this->consignments[] = $consignment;
        }
        return $this;
    }
    /**
     * Create concepts in MyParcel
     *
     * @todo    Produce all the items in one time with reference ID.
     *
     * @return  $this
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws \ErrorException
     */
    public function createConcepts()
    {
        /* @var $consignments MyParcelConsignmentRepository[] */
        foreach ($this->getConsignmentsSortedByKey() as $key => $consignments) {
            foreach ($consignments as $consignment) {
                if ($consignment->getMyParcelConsignmentId() === null) {
                    $data = $this->apiEncode(array($consignment));
                    $request = new \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest();
                    $request->setUserAgent($this->getUserAgent())->setRequestParameters($key, $data, \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_SHIPMENT)->sendRequest();
                    $result = $request->getResult();
                    $consignment->setMyParcelConsignmentId($result['data']['ids'][0]['id']);
                }
            }
        }
        return $this;
    }
    /**
     * Delete concepts in MyParcel
     *
     * @return  $this
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws \ErrorException
     */
    public function deleteConcepts()
    {
        /* @var $consignments MyParcelConsignmentRepository[] */
        foreach ($this->getConsignmentsSortedByKey() as $key => $consignments) {
            foreach ($consignments as $consignment) {
                if ($consignment->getMyParcelConsignmentId() !== null) {
                    $request = new \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest();
                    $request->setUserAgent($this->getUserAgent())->setRequestParameters($key, $consignment->getMyParcelConsignmentId(), \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_DELETE)->sendRequest('DELETE');
                }
            }
        }
        return $this;
    }
    /**
     * Get all current data
     *
     * Set id and run this function to update all the information about this shipment
     *
     * @param int $size
     *
     * @return $this
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws \ErrorException
     */
    public function setLatestData($size = 300)
    {
        $consignmentIds = $this->getConsignmentIds($key);
        if ($consignmentIds !== null) {
            $params = implode(';', $consignmentIds) . '?size=' . $size;
        } else {
            $referenceIds = $this->getConsignmentReferenceIds($key);
            if ($referenceIds != null) {
                $params = '?reference_identifier=' . implode(';', $referenceIds) . '&size=' . $size;
            } else {
                return $this;
            }
        }
        $request = new \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest();
        $request->setUserAgent($this->getUserAgent())->setRequestParameters($key, $params, \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_RETRIEVE_SHIPMENT)->sendRequest('GET');
        if ($request->getResult() === null) {
            throw new \MyParcelModule\MyParcelNL\Sdk\src\Exception\ConnectionException('Unable to transport data to MyParcel.');
        }
        $consignmentsToReplace = array();
        $result = $request->getResult();
        foreach ($result['data']['shipments'] as $shipment) {
            $consignment = $this->getConsignmentByApiId($shipment['id']);
            if ($consignment === null) {
                $consignment = $this->getConsignmentByReferenceId($shipment['reference_identifier']);
            }
            $consignmentsToReplace[] = $consignment->apiDecode($shipment);
        }
        $this->clearConsignmentsCollection();
        foreach ($consignmentsToReplace as $consignmentToReplace) {
            $this->addConsignment($consignmentToReplace, false);
        }
        return $this;
    }
    /**
     * Get all current data
     *
     * Set id and run this function to update all the information about this shipment
     *
     * @param     $key
     * @param int $size
     *
     * @return $this
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws \ErrorException
     */
    public function setLatestDataWithoutIds($key, $size = 300)
    {
        $params = '?size=' . $size;
        $request = new \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest();
        $request->setUserAgent($this->getUserAgent())->setRequestParameters($key, $params, \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_RETRIEVE_SHIPMENT)->sendRequest('GET');
        if ($request->getResult() === null) {
            throw new \MyParcelModule\MyParcelNL\Sdk\src\Exception\ConnectionException('Unable to transport data to MyParcel.');
        }
        $result = $request->getResult();
        foreach ($result['data']['shipments'] as $shipment) {
            $consignment = new \MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository();
            $consignment->setApiKey($key)->apiDecode($shipment);
            $this->addConsignment($consignment, false);
        }
        return $this;
    }
    /**
     * Get link of labels
     *
     * @param array|int|bool $positions The position of the label on an A4 sheet. Set to false to create an A6 sheet.
     *                                  You can specify multiple positions by using an array. E.g. [2,3,4]. If you do
     *                                  not specify an array, but specify a number, the following labels will fill the
     *                                  ascending positions. Positioning is only applied on the first page with labels.
     *                                  All subsequent pages will use the default positioning [1,2,3,4].
     *
     * @return $this
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws \ErrorException
     */
    public function setLinkOfLabels($positions = self::DEFAULT_A4_POSITION)
    {
        /** If $positions is not false, set paper size to A4 */
        $this->createConcepts()->setLabelFormat($positions);
        $conceptIds = $this->getConsignmentIds($key);
        if ($key) {
            $request = new \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest();
            $request->setUserAgent($this->getUserAgent())->setRequestParameters($key, implode(';', $conceptIds) . '/' . $this->getRequestBody(), \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_RETRIEVE_LABEL_LINK)->sendRequest('GET', \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_TYPE_RETRIEVE_LABEL);
            $result = $request->getResult();
            $this->label_link = \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_URL . $result['data']['pdfs']['url'];
        }
        $this->setLatestData();
        return $this;
    }
    /**
     * Receive label PDF
     *
     * After setPdfOfLabels() apiId and barcode is present
     *
     * @param array|integer|bool $positions The position of the label on an A4 sheet. You can specify multiple positions by
     *                                      using an array. E.g. [2,3,4]. If you do not specify an array, but specify a
     *                                      number, the following labels will fill the ascending positions. Positioning is
     *                                      only applied on the first page with labels. All subsequent pages will use the
     *                                      default positioning [1,2,3,4].
     *
     * @return $this
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws \ErrorException
     */
    public function setPdfOfLabels($positions = self::DEFAULT_A4_POSITION)
    {
        /** If $positions is not false, set paper size to A4 */
        $this->createConcepts()->setLabelFormat($positions);
        $conceptIds = $this->getConsignmentIds($key);
        if ($key) {
            $request = new \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest();
            $request->setUserAgent($this->getUserAgent())->setRequestParameters($key, implode(';', $conceptIds) . '/' . $this->getRequestBody(), \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_RETRIEVE_LABEL_PDF)->sendRequest('GET', \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_TYPE_RETRIEVE_LABEL);
            $this->label_pdf = $request->getResult();
        }
        $this->setLatestData();
        return $this;
    }
    /**
     * Download labels
     *
     * @param bool $inline_download
     *
     * @return string
     */
    public function downloadPdfOfLabels($inline_download = false)
    {
        if ($this->label_pdf == null) {
            throw new \InvalidArgumentException('First set label_pdf key with setPdfOfLabels() before running downloadPdfOfLabels()');
        }
        header('Content-Type: application/pdf');
        header('Content-Length: ' . strlen($this->label_pdf));
        header('Content-disposition: ' . ($inline_download === true ? "inline" : "attachment") . '; filename="' . self::PREFIX_PDF_FILENAME . gmdate('Y-M-d H-i-s') . '.pdf"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        echo $this->label_pdf;
        exit;
    }
    /**
     * Send return label to customer. The customer can pay and download the label.
     *
     * @return $this
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws \ErrorException
     */
    public function sendReturnLabelMails()
    {
        $parentConsignment = $this->getConsignments(false);
        $parentConsignment = $parentConsignment[0];
        $apiKey = $parentConsignment->getApiKey();
        $data = $this->apiEncodeReturnShipments($parentConsignment);
        $request = new \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest();
        $request->setUserAgent($this->getUserAgent())->setRequestParameters($apiKey, $data, \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_RETURN)->sendRequest('POST');
        $result = $request->getResult();
        if ($result === null) {
            throw new \MyParcelModule\MyParcelNL\Sdk\src\Exception\ConnectionException('Unable to connect to MyParcel.');
        }
        if (empty($result['data']['ids'][0]['id']) || (int) $result['data']['ids'][0]['id'] < 1) {
            throw new \MyParcelModule\MyParcelNL\Sdk\src\Exception\ApiException('Can\'t send retour label to customer. Please create an issue on GitHub or contact MyParcel; support@myparcel.nl. Note this request body: ' . $data);
        }
        return $this;
    }
    /**
     * Get all consignment ids
     *
     * @param $key
     *
     * @return array
     */
    private function getConsignmentIds(&$key)
    {
        $conceptIds = array();
        foreach ($this->getConsignments() as $consignment) {
            if ($consignment->getMyParcelConsignmentId()) {
                $conceptIds[] = $consignment->getMyParcelConsignmentId();
                $key = $consignment->getApiKey();
            }
        }
        if (empty($conceptIds)) {
            return null;
        }
        return $conceptIds;
    }
    /**
     * Get all consignment ids
     *
     * @param $key
     *
     * @return array
     */
    private function getConsignmentReferenceIds(&$key)
    {
        $referenceIds = array();
        foreach ($this->getConsignments() as $consignment) {
            if ($consignment->getReferenceId()) {
                $referenceIds[] = $consignment->getReferenceId();
                $key = $consignment->getApiKey();
            }
        }
        if (empty($referenceIds)) {
            return null;
        }
        return $referenceIds;
    }
    /**
     * Set label format settings        The position of the label on an A4 sheet. You can specify multiple positions by
     *                                  using an array. E.g. [2,3,4]. If you do not specify an array, but specify a
     *                                  number, the following labels will fill the ascending positions. Positioning is
     *                                  only applied on the first page with labels. All subsequent pages will use the
     *                                  default positioning [1,2,3,4].
     *
     * @param integer $positions
     *
     * @return $this
     */
    private function setLabelFormat($positions)
    {
        /** If $positions is not false, set paper size to A4 */
        if (is_numeric($positions)) {
            /** Generating positions for A4 paper */
            $this->paper_size = 'A4';
            $this->label_position = $this->getPositions($positions);
        } elseif (is_array($positions)) {
            /** Set positions for A4 paper */
            $this->paper_size = 'A4';
            $this->label_position = implode(';', $positions);
        } else {
            /** Set paper size to A6 */
            $this->paper_size = 'A6';
            $this->label_position = null;
        }
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function getRequestBody()
    {
        $body = $this->paper_size == 'A4' ? '?format=A4&positions=' . $this->label_position : '?format=A6';
        return $body;
    }
    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }
    /**
     * @param string $platform
     * @param string $version
     *
     * @internal param string $user_agent
     * @return $this
     */
    public function setUserAgent($platform, $version = null)
    {
        $this->user_agent = 'MyParcel-' . $platform;
        if ($version !== null) {
            $this->user_agent .= '/' . str_replace('v', '', $version);
        }
        return $this;
    }
    /**
     * Clear this collection
     */
    public function clearConsignmentsCollection()
    {
        $this->consignments = array();
    }
    /**
     * Encode multiple shipments so that the data can be sent to MyParcel.
     *
     * @param $consignments MyParcelConsignmentRepository[]
     *
     * @return string
     */
    private function apiEncode($consignments)
    {
        $data = array();
        foreach ($consignments as $consignment) {
            $data['data']['shipments'][] = $consignment->apiEncode();
        }
        // Remove \\n because json_encode encode \\n for \s
        return str_replace('\\n', " ", json_encode($data));
    }
    /**
     * Encode multiple ReturnShipment Objects
     *
     * @param MyParcelConsignmentRepository $consignment
     *
     * @return string
     */
    private function apiEncodeReturnShipments($consignment)
    {
        $data['data']['return_shipments'][] = $consignment->encodeReturnShipment();
        return json_encode($data);
    }
    /**
     * Generating positions for A4 paper
     *
     * @param int $start
     *
     * @return string
     */
    private function getPositions($start)
    {
        $aPositions = array();
        switch ($start) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 1:
                $aPositions[] = 1;
                /** @noinspection PhpMissingBreakStatementInspection */
            /** @noinspection PhpMissingBreakStatementInspection */
            case 2:
                $aPositions[] = 2;
                /** @noinspection PhpMissingBreakStatementInspection */
            /** @noinspection PhpMissingBreakStatementInspection */
            case 3:
                $aPositions[] = 3;
                /** @noinspection PhpMissingBreakStatementInspection */
            /** @noinspection PhpMissingBreakStatementInspection */
            case 4:
                $aPositions[] = 4;
                break;
        }
        return implode(';', $aPositions);
    }
    /**
     * @return MyParcelConsignmentRepository[]
     */
    private function getConsignmentsSortedByKey()
    {
        $aConsignments = array();
        /** @var $consignment MyParcelConsignment */
        foreach ($this->getConsignments() as $consignment) {
            $aConsignments[$consignment->getApiKey()][] = $consignment;
        }
        return $aConsignments;
    }
}
