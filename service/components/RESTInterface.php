<?php

namespace app\components;

/**
 * Interface RESTInterface
 * @package app\components
 */
interface RESTInterface
{
    public function actionCreate($params);
    public function actionRead($params);
    public function actionUpdate($params);
    public function actionDelete($params);
}