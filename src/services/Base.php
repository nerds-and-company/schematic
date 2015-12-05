<?php

namespace NerdsAndCompany\Schematic\Services;

use Craft\BaseApplicationComponent as BaseApplication;
use NerdsAndCompany\Schematic\Models\Result;

/**
 * Schematic Base Service for some easy access methods.
 *
 * @author    Nerds & Company
 * @copyright Copyright (c) 2015, Nerds & Company
 * @license   MIT
 *
 * @link      http://www.nerds.company
 */
abstract class Base extends BaseApplication
{
    /**
     * @var Schematic_ResultModel
     */
    protected $resultModel;

    /**
     * Required import method.
     *
     * @param array $data
     * @param bool  $force
     *
     * @return Schematic_ResultModel
     */
    abstract public function import(array $data, $force);

    /**
     * Required export method.
     *
     * @param array|null $data
     *
     * @return mixed
     */
    abstract public function export(array $data = array());

    /**
     * Constructor to setup result model.
     */
    public function __construct()
    {
        $this->resultModel = new Result();
    }

    /**
     * @return DbConnection
     */
    protected function getDbService()
    {
        return craft()->db;
    }

    /**
     * Returns current transaction.
     *
     * @return \CDbTransaction
     *
     * @throws \CDbException
     */
    protected function getTransaction()
    {
        if ($transaction = $this->getDbService()->getCurrentTransaction()) {
            return $transaction;
        }
        throw new \CDbException('Start transaction first before getting it');
    }

    /**
     * Starts DB transaction.
     */
    protected function beginTransaction()
    {
        $this->getDbService()->beginTransaction();
    }

    /**
     * Commits transaction.
     */
    protected function commitTransaction()
    {
        try {
            $this->getTransaction()->commit();
        } catch (\CDbException $e) {
            $this->addError($e->getMessage());
        }
    }

    /**
     * Rolls back transaction.
     */
    protected function rollbackTransaction()
    {
        try {
            $this->getTransaction()->rollback();
        } catch (\CDbException $e) {
            $this->addError($e->getMessage());
        }
    }

    /**
     * Adds error to result model.
     *
     * @param $message
     * @param string $attribute
     */
    protected function addError($message, $attribute = 'errors')
    {
        $this->resultModel->addError($attribute, $message);
    }

    /**
     * Adds multiple errors to result model.
     *
     * @param array  $messages
     * @param string $attribute
     */
    protected function addErrors(array $messages, $attribute = 'errors')
    {
        $this->resultModel->addErrors(array($attribute => $messages));
    }

    /**
     * Returns if there are errors or not.
     *
     * @param string $attribute
     *
     * @return bool
     */
    protected function hasErrors($attribute = 'errors')
    {
        return $this->resultModel->hasErrors($attribute);
    }

    /**
     * Returns current result model.
     *
     * @return Schematic_ResultModel
     */
    public function getResultModel()
    {
        return $this->resultModel;
    }
}
